<?php
session_start();
require_once 'config/connect.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đặt hàng']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không được phép']);
    exit();
}

// Lấy dữ liệu từ form
$user_id = $_SESSION['user_id'];
$full_name = $_POST['full_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$notes = $_POST['notes'] ?? '';
$total_amount = $_POST['total_amount'] ?? 0;
$payment_method = $_POST['payment_method'] ?? 'cod';
$promotion_id = $_POST['promotion_id'] ?? null;
$discount_amount = $_POST['discount_amount'] ?? 0;

// Validate dữ liệu
if (empty($full_name) || empty($email) || empty($phone) || empty($address)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin']);
    exit();
}

try {
    // Bắt đầu transaction
    $conn->begin_transaction();

    // Kiểm tra giỏ hàng có sản phẩm không
    $check_cart_sql = "SELECT COUNT(*) as cart_count FROM cart WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_cart_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $cart_count = $check_stmt->get_result()->fetch_assoc()['cart_count'];
    
    if ($cart_count == 0) {
        throw new Exception("Giỏ hàng trống. Vui lòng thêm sản phẩm trước khi đặt hàng.");
    }

    // Lấy sản phẩm từ giỏ hàng
    $cart_sql = "SELECT c.*, p.price, p.product_name FROM cart c 
                 JOIN products p ON c.product_id = p.product_id 
                 WHERE c.user_id = ?";
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->bind_param("i", $user_id);
    $cart_stmt->execute();
    $cart_items = $cart_stmt->get_result();

    // Tính tổng tiền và lưu items
    $items = [];
    $calculated_total = 0;
    while ($item = $cart_items->fetch_assoc()) {
        $items[] = $item;
        $calculated_total += $item['price'] * $item['quantity'];
    }

    // Tạo đơn hàng mới
    $order_status = $payment_method === 'bank_transfer' ? 'Chờ thanh toán' : 'Chờ xác nhận';
    $order_sql = "INSERT INTO orders (user_id, full_name, email, phone, address, notes, total_amount, payment_method, order_status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("isssssdss", $user_id, $full_name, $email, $phone, $address, $notes, $calculated_total, $payment_method, $order_status);
    $order_stmt->execute();
    $order_id = $conn->insert_id;

    // Thêm chi tiết đơn hàng (KHÔNG trừ tồn kho ngay, chờ đơn hoàn thành)
    $detail_sql = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
    $detail_stmt = $conn->prepare($detail_sql);

    foreach ($items as $item) {
        // Kiểm tra tồn kho trước khi đặt hàng
        $check_stock_sql = "SELECT stock_quantity FROM products WHERE product_id = ?";
        $check_stock_stmt = $conn->prepare($check_stock_sql);
        $check_stock_stmt->bind_param("s", $item['product_id']);
        $check_stock_stmt->execute();
        $stock_result = $check_stock_stmt->get_result()->fetch_assoc();
        
        if ($stock_result['stock_quantity'] < $item['quantity']) {
            throw new Exception("Sản phẩm '{$item['product_name']}' không đủ số lượng trong kho. Còn {$stock_result['stock_quantity']} sản phẩm.");
        }
        
        // Thêm chi tiết đơn hàng
        $detail_stmt->bind_param("isid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
        $detail_stmt->execute();
    }

    // Xóa giỏ hàng
    $clear_cart_sql = "DELETE FROM cart WHERE user_id = ?";
    $clear_cart_stmt = $conn->prepare($clear_cart_sql);
    $clear_cart_stmt->bind_param("i", $user_id);
    $clear_cart_stmt->execute();

    // Lưu thông tin khuyến mãi nếu có
    if ($promotion_id && $discount_amount > 0) {
        require_once 'record_promotion_usage.php';
        recordPromotionUsage($conn, $promotion_id, $user_id, $order_id, $discount_amount);
    }

    // Xử lý upload ảnh chuyển khoản nếu có
    $payment_proof_path = null;
    if ($payment_method === 'bank_transfer' && isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === 0) {
        $upload_dir = 'uploads/payment_proofs/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
        $new_filename = 'payment_' . $order_id . '_' . time() . '.' . $file_extension;
        $payment_proof_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $payment_proof_path)) {
            // Cập nhật đơn hàng với đường dẫn ảnh
            $update_proof = $conn->prepare("UPDATE orders SET payment_proof = ? WHERE order_id = ?");
            $update_proof->bind_param("si", $payment_proof_path, $order_id);
            $update_proof->execute();
            $update_proof->close();
        }
    }

    // Commit transaction
    $conn->commit();

    $message = 'Đặt hàng thành công';
    if ($payment_method === 'bank_transfer') {
        $message .= '! Vui lòng chuyển khoản theo thông tin đã cung cấp. Đơn hàng sẽ được xử lý sau khi nhận được thanh toán.';
    }

    echo json_encode([
        'success' => true, 
        'message' => $message,
        'order_id' => $order_id,
        'payment_method' => $payment_method
    ]);

} catch (Exception $e) {
    // Rollback nếu có lỗi
    $conn->rollback();
    echo json_encode([
        'success' => false, 
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
    ]);
}

// Đóng kết nối
$conn->close();