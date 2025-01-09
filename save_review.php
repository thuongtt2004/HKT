<?php
session_start();
require_once 'config/connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];

// Validate dữ liệu
if (empty($data['product_id']) || empty($data['rating']) || empty($data['content'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đánh giá']);
    exit();
}

try {
    // Kiểm tra xem đã đánh giá chưa
    $check_sql = "SELECT * FROM reviews WHERE user_id = ? AND product_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $data['product_id']);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        throw new Exception("Bạn đã đánh giá sản phẩm này rồi");
    }

    // Thêm đánh giá mới
    $sql = "INSERT INTO reviews (user_id, product_id, rating, content, review_date) 
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", 
        $user_id,
        $data['product_id'],
        $data['rating'],
        $data['content']
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Đánh giá đã được gửi']);
    } else {
        throw new Exception("Lỗi khi lưu đánh giá");
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>