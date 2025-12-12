<?php
header('Content-Type: application/json');
require_once 'config/connect.php';

// Kiểm tra tham số category_id
if (!isset($_GET['category_id']) || empty($_GET['category_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu thông tin danh mục'
    ]);
    exit();
}

$category_id = intval($_GET['category_id']);

try {
    // Lấy danh sách sản phẩm theo danh mục
    $query = "SELECT product_id, product_name, price, image_url, stock_quantity, sold_quantity 
              FROM products 
              WHERE category_id = ? 
              ORDER BY product_name ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'product_id' => $row['product_id'],
            'product_name' => htmlspecialchars($row['product_name']),
            'price' => floatval($row['price']),
            'image_url' => htmlspecialchars($row['image_url']),
            'stock_quantity' => intval($row['stock_quantity']),
            'sold_quantity' => intval($row['sold_quantity'])
        ];
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'total' => count($products)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi truy vấn cơ sở dữ liệu: ' . $e->getMessage()
    ]);
}

$conn->close();
?>