<?php
session_start();
require_once 'config/connect.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Xử lý xóa đánh giá
if (isset($_POST['delete_review'])) {
    $review_id = $_POST['review_id'];
    $delete_sql = "DELETE FROM reviews WHERE review_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $review_id);
    $delete_stmt->execute();
}

// Lấy danh sách sản phẩm có đánh giá và rating trung bình
$sql = "SELECT 
            p.product_id,
            p.product_name,
            p.image_url,
            p.price,
            COUNT(r.review_id) as review_count,
            AVG(r.rating) as avg_rating
        FROM products p
        LEFT JOIN reviews r ON p.product_id = r.product_id
        GROUP BY p.product_id
        HAVING review_count > 0
        ORDER BY review_count DESC, avg_rating DESC";
$result = $conn->query($sql);

// Lưu tất cả products vào array để tránh conflict với nested queries
$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Đánh Giá - Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .product-review-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border: 2px solid #EBE9E5;
            transition: all 0.3s ease;
        }
        
        .product-review-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(51, 51, 51, 0.15);
        }
        
        .product-header {
            display: flex;
            gap: 15px;
            padding: 20px;
            background: #EBE9E5;
            border-bottom: 2px solid #333333;
        }
        
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #333333;
        }
        
        .product-info {
            flex: 1;
        }
        
        .product-name {
            font-size: 18px;
            font-weight: 700;
            color: #333333;
            margin-bottom: 8px;
        }
        
        .product-price {
            font-size: 16px;
            color: #e74c3c;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .rating-summary {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: white;
            border-radius: 6px;
            border: 1px solid #333333;
        }
        
        .avg-rating {
            font-size: 24px;
            font-weight: 700;
            color: #333333;
        }
        
        .stars {
            color: #ffd700;
            font-size: 18px;
        }
        
        .review-count {
            font-size: 13px;
            color: #666666;
        }
        
        .reviews-list {
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .review-item {
            padding: 15px;
            border-bottom: 1px solid #EBE9E5;
            position: relative;
        }
        
        .review-item:last-child {
            border-bottom: none;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
            padding-right: 80px; /* Để chỗ cho nút xóa */
        }
        
        .review-header-left {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .reviewer-name {
            font-weight: 600;
            color: #333333;
        }
        
        .review-date {
            font-size: 12px;
            color: #999999;
        }
        
        .review-rating {
            margin: 5px 0;
        }
        
        .review-content {
            color: #555555;
            line-height: 1.6;
            margin-top: 8px;
            padding-right: 80px; /* Để chỗ cho nút xóa */
        }
        
        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 11px;
            transition: all 0.3s ease;
            position: absolute;
            top: 15px;
            right: 15px;
            white-space: nowrap;
        }
        
        .delete-btn:hover {
            background: #c82333;
            transform: scale(1.05);
        }
        
        .no-reviews {
            text-align: center;
            padding: 40px;
            color: #999999;
        }
        
        .toggle-reviews {
            width: 100%;
            padding: 12px;
            background: #333333;
            color: #EBE9E5;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
        }
        
        .toggle-reviews:hover {
            background: #444444;
        }
        
        .reviews-list.collapsed {
            display: none;
        }
        
        h1 {
            color: #333333;
            margin-bottom: 10px;
        }
        
        .page-subtitle {
            color: #666666;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <main>
    <div class="container">
        <h1><i class="fas fa-star"></i> Quản Lý Đánh Giá Sản Phẩm</h1>
        <p class="page-subtitle">Xem và quản lý đánh giá của khách hàng theo từng sản phẩm</p>
        
        <?php if (count($products) > 0): ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-review-card">
                        <!-- Header sản phẩm -->
                        <div class="product-header">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                 class="product-image">
                            <div class="product-info">
                                <div class="product-name">
                                    <?php echo htmlspecialchars($product['product_name']); ?>
                                </div>
                                <div class="product-price">
                                    <?php echo number_format($product['price'], 0, ',', '.'); ?>₫
                                </div>
                                <div class="rating-summary">
                                    <span class="avg-rating"><?php echo number_format($product['avg_rating'], 1); ?></span>
                                    <div>
                                        <div class="stars">
                                            <?php 
                                            $avg = round($product['avg_rating']);
                                            for($i = 1; $i <= 5; $i++): 
                                            ?>
                                                <i class="<?php echo $i <= $avg ? 'fas' : 'far'; ?> fa-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="review-count">
                                            <?php echo $product['review_count']; ?> đánh giá
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Toggle button -->
                        <button class="toggle-reviews" onclick="toggleReviews('<?php echo $product['product_id']; ?>')">
                            <i class="fas fa-chevron-down" id="icon-<?php echo $product['product_id']; ?>"></i>
                            Xem chi tiết đánh giá
                        </button>
                        
                        <!-- Danh sách đánh giá -->
                        <div class="reviews-list collapsed" id="reviews-<?php echo $product['product_id']; ?>">
                            <?php
                            // Lấy tất cả đánh giá của sản phẩm này
                            $pid = $product['product_id'];
                            $reviews_sql = "SELECT r.*, u.username 
                                           FROM reviews r 
                                           JOIN users u ON r.user_id = u.user_id 
                                           WHERE r.product_id = $pid 
                                           ORDER BY r.review_date DESC";
                            $reviews_result = $conn->query($reviews_sql);
                            
                            if ($reviews_result && $reviews_result->num_rows > 0):
                                while ($review = $reviews_result->fetch_assoc()):
                            ?>
                                <div class="review-item">
                                    <div class="review-header">
                                        <div class="review-header-left">
                                            <span class="reviewer-name">
                                                <i class="fas fa-user-circle"></i> 
                                                <?php echo htmlspecialchars($review['username']); ?>
                                            </span>
                                            <span class="review-date">
                                                <?php echo date('d/m/Y H:i', strtotime($review['review_date'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="review-rating">
                                        <span class="stars">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <i class="<?php echo $i <= $review['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                                            <?php endfor; ?>
                                        </span>
                                    </div>
                                    <div class="review-content">
                                        <?php echo htmlspecialchars($review['content']); ?>
                                    </div>
                                    <form method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá này?');" style="display:inline;">
                                        <input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>">
                                        <button type="submit" name="delete_review" class="delete-btn">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </form>
                                </div>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <div class="review-item" style="text-align:center;color:#999;">
                                    Không có đánh giá nào
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-reviews">
                <i class="fas fa-inbox" style="font-size: 64px; margin-bottom: 20px; display: block; opacity: 0.3;"></i>
                <h3>Chưa có đánh giá nào</h3>
                <p>Các sản phẩm chưa có đánh giá từ khách hàng</p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function toggleReviews(productId) {
            console.log('Toggle called for product:', productId);
            const reviewsList = document.getElementById('reviews-' + productId);
            const icon = document.getElementById('icon-' + productId);
            
            console.log('reviewsList:', reviewsList);
            console.log('icon:', icon);
            
            if (!reviewsList) {
                console.error('Element not found: reviews-' + productId);
                // Debug: List all elements with reviews- prefix
                const allReviewsElements = document.querySelectorAll('[id^="reviews-"]');
                console.log('All reviews elements found:', allReviewsElements);
                return;
            }
            
            if (!icon) {
                console.error('Icon element not found: icon-' + productId);
                return;
            }
            
            if (reviewsList.classList.contains('collapsed')) {
                reviewsList.classList.remove('collapsed');
                if (icon) {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                }
            } else {
                reviewsList.classList.add('collapsed');
                if (icon) {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            }
        }
        
        // Debug: Log khi trang load xong
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Admin reviews page loaded');
            console.log('Toggle function available:', typeof toggleReviews);
            
            // List all review elements
            const allReviewsElements = document.querySelectorAll('[id^="reviews-"]');
            console.log('Total review sections found:', allReviewsElements.length);
            allReviewsElements.forEach(el => {
                console.log('Found element:', el.id);
            });
        });
    </script>
    </main>

    <?php include 'admin_footer.php'; ?>
</body>
</html>