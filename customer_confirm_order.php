<?php
session_start();
header('Content-Type: application/json');
require_once 'config/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Bạn cần đăng nhập để thực hiện thao tác này'
    ]);
    exit();
}

// Kiểm tra dữ liệu đầu vào
if (!isset($_POST['order_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu thông tin đơn hàng'
    ]);
    exit();
}

$order_id = intval($_POST['order_id']);
$user_id = $_SESSION['user_id'];

try {
    // Kiểm tra đơn hàng có thuộc về user này không và có ở trạng thái "Đã giao hàng" không
    $check_query = "SELECT order_status, user_id FROM orders WHERE order_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $order_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy đơn hàng'
        ]);
        exit();
    }
    
    $order = $result->fetch_assoc();
    
    // Kiểm tra quyền sở hữu đơn hàng
    if ($order['user_id'] != $user_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Bạn không có quyền thao tác với đơn hàng này'
        ]);
        exit();
    }
    
    // Kiểm tra trạng thái đơn hàng
    if ($order['order_status'] !== 'Đã giao hàng') {
        echo json_encode([
            'success' => false,
            'message' => 'Chỉ có thể xác nhận đơn hàng đã được giao'
        ]);
        exit();
    }
    
    // Cập nhật trạng thái thành "Hoàn thành"
    $update_query = "UPDATE orders SET order_status = 'Hoàn thành', completed_date = NOW() WHERE order_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $order_id);
    
    if ($update_stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Cảm ơn bạn đã xác nhận! Đơn hàng đã được hoàn thành.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi cập nhật đơn hàng'
        ]);
    }
    
    $update_stmt->close();
    $check_stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống: ' . $e->getMessage()
    ]);
}

$conn->close();
?>