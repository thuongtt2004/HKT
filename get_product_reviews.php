<?php
header('Content-Type: application/json');
require_once 'config/connect.php';

try {
    if (!isset($_GET['product_id'])) {
        throw new Exception('Product ID is required');
    }
    
    $product_id = (int)$_GET['product_id'];
    
    // Lấy tất cả đánh giá của sản phẩm
    $sql = "SELECT r.*, u.username, u.full_name,
                   DATE_FORMAT(r.review_date, '%Y-%m-%d %H:%i:%s') as formatted_date
            FROM reviews r 
            JOIN users u ON r.user_id = u.user_id 
            WHERE r.product_id = ? 
            ORDER BY r.review_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reviews = [];
    $total_rating = 0;
    
    while ($row = $result->fetch_assoc()) {
        $reviews[] = [
            'review_id' => $row['review_id'],
            'username' => $row['username'] ?: $row['full_name'] ?: 'Khách hàng',
            'rating' => (int)$row['rating'],
            'content' => $row['content'],
            'review_date' => $row['formatted_date']
        ];
        $total_rating += $row['rating'];
    }
    
    $avg_rating = count($reviews) > 0 ? round($total_rating / count($reviews), 1) : 0;
    
    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
        'avg_rating' => $avg_rating,
        'total_reviews' => count($reviews)
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>