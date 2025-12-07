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
$product_id = $_POST['product_id'] ?? '';
$quantity = intval($_POST['quantity'] ?? 1);
$full_name = $_POST['full_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$notes = $_POST['notes'] ?? '';
$total_amount = floatval($_POST['total_amount'] ?? 0);
$payment_method = $_POST['payment_method'] ?? 'cod';

// Validate dữ liệu
if (empty($product_id) || empty($full_name) || empty($email) || empty($phone) || empty($address)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin']);
    exit();
}

if ($quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Số lượng không hợp lệ']);
    exit();
}

try {
    // Bắt đầu transaction
    $conn->begin_transaction();

    // Lấy thông tin sản phẩm
    $product_sql = "SELECT product_id, product_name, price, stock_quantity FROM products WHERE product_id = ?";
    $product_stmt = $conn->prepare($product_sql);
    $product_stmt->bind_param("s", $product_id);
    $product_stmt->execute();
    $product = $product_stmt->get_result()->fetch_assoc();

    if (!$product) {
        throw new Exception("Sản phẩm không tồn tại");
    }

    // Kiểm tra tồn kho
    if ($product['stock_quantity'] < $quantity) {
        throw new Exception("Sản phẩm chỉ còn {$product['stock_quantity']} trong kho");
    }

    // Tính tổng tiền
    $calculated_total = $product['price'] * $quantity;

    // Tạo đơn hàng mới
    $order_status = $payment_method === 'bank_transfer' ? 'Chờ thanh toán' : 'Chờ xác nhận';
    $order_sql = "INSERT INTO orders (user_id, full_name, email, phone, address, notes, total_amount, payment_method, order_status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("isssssdss", $user_id, $full_name, $email, $phone, $address, $notes, $calculated_total, $payment_method, $order_status);
    $order_stmt->execute();
    $order_id = $conn->insert_id;

    // Thêm chi tiết đơn hàng
    $detail_sql = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
    $detail_stmt = $conn->prepare($detail_sql);
    $detail_stmt->bind_param("isid", $order_id, $product_id, $quantity, $product['price']);
    $detail_stmt->execute();

    // Commit transaction
    $conn->commit();

    // Phản hồi thành công
    if ($payment_method === 'bank_transfer') {
        echo json_encode([
            'success' => true,
            'message' => 'Đặt hàng thành công! Vui lòng chuyển khoản để hoàn tất đơn hàng.',
            'order_id' => $order_id
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Đặt hàng thành công! Đơn hàng của bạn đang được xử lý.',
            'order_id' => $order_id
        ]);
    }

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
