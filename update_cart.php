<?php
session_start();
require_once 'config/connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$cart_id = $data['cart_id'];
$change = $data['change'];

try {
    // Lấy thông tin giỏ hàng và sản phẩm
    $stmt = $conn->prepare("
        SELECT c.quantity, p.stock_quantity, c.product_id 
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.cart_id = ? AND c.user_id = ?
    ");
    $stmt->bind_param("ii", $cart_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit;
    }

    $new_quantity = $item['quantity'] + $change;

    // Kiểm tra số lượng
    if ($new_quantity <= 0) {
        // Xóa sản phẩm khỏi giỏ hàng
        $delete_stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
        $delete_stmt->bind_param("ii", $cart_id, $_SESSION['user_id']);
        $delete_stmt->execute();
    } elseif ($new_quantity <= $item['stock_quantity']) {
        // Cập nhật số lượng
        $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
        $update_stmt->bind_param("iii", $new_quantity, $cart_id, $_SESSION['user_id']);
        $update_stmt->execute();
    } else {
        echo json_encode(['success' => false, 'message' => 'Số lượng vượt quá tồn kho']);
        exit;
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 