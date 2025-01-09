<?php
session_start();
require_once 'config/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: dangnhap.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy danh sách sản phẩm đã mua thành công
$sql = "SELECT DISTINCT p.*, od.order_id, o.order_date, o.status
        FROM products p
        JOIN order_details od ON p.product_id = od.product_id
        JOIN orders o ON od.order_id = o.order_id
        WHERE o.user_id = ? AND o.status = 'Đã giao hàng'
        AND NOT EXISTS (
            SELECT 1 FROM reviews r 
            WHERE r.user_id = ? AND r.product_id = p.product_id
        )";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$products = $stmt->get_result();

// Lấy danh sách đánh giá đã gửi
$reviews_sql = "SELECT r.*, p.product_name, p.image_url 
                FROM reviews r
                JOIN products p ON r.product_id = p.product_id
                WHERE r.user_id = ?
                ORDER BY r.review_date DESC";
$reviews_stmt = $conn->prepare($reviews_sql);
$reviews_stmt->bind_param("i", $user_id);
$reviews_stmt->execute();
$reviews = $reviews_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá sản phẩm - TTHUONG Store</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .review-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .review-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }

        .product-to-review {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            margin-bottom: 15px;
        }

        .product-to-review img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 20px;
        }

        .rating {
            margin: 15px 0;
        }

        .star {
            color: #ddd;
            font-size: 24px;
            cursor: pointer;
        }

        .star.active {
            color: #ffd700;
        }

        .review-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 10px 0;
            min-height: 100px;
        }

        .submit-review {
            background: #333;
            color: #EBE9E5;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-review:hover {
            background: #555;
        }

        .past-reviews {
            margin-top: 30px;
        }

        .review-item {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .review-date {
            color: #666;
            font-size: 0.9em;
        }

        .yellow-star {
            color: #ffd700;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="review-container">
        <h2>Đánh giá sản phẩm</h2>

        <!-- Phần sản phẩm chưa đánh giá -->
        <div class="review-section">
            <h3>Sản phẩm chờ đánh giá</h3>
            <?php if ($products->num_rows > 0): ?>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <div class="product-to-review">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                        <div class="product-info">
                            <h4><?php echo htmlspecialchars($product['product_name']); ?></h4>
                            <p>Đơn hàng #<?php echo $product['order_id']; ?></p>
                            <p>Ngày mua: <?php echo date('d/m/Y', strtotime($product['order_date'])); ?></p>
                            
                            <form class="review-form" onsubmit="return submitReview(this, <?php echo $product['product_id']; ?>)">
                                <div class="rating" data-product="<?php echo $product['product_id']; ?>">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <span class="star" data-value="<?php echo $i; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <textarea name="review_content" placeholder="Nhập đánh giá của bạn về sản phẩm..." required></textarea>
                                <input type="hidden" name="rating" value="0">
                                <button type="submit" class="submit-review">Gửi đánh giá</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Không có sản phẩm nào chờ đánh giá.</p>
            <?php endif; ?>
        </div>

        <!-- Phần đánh giá đã gửi -->
        <div class="past-reviews">
            <h3>Đánh giá đã gửi</h3>
            <?php if ($reviews->num_rows > 0): ?>
                <?php while ($review = $reviews->fetch_assoc()): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div>
                                <img src="<?php echo htmlspecialchars($review['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($review['product_name']); ?>"
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                <h4><?php echo htmlspecialchars($review['product_name']); ?></h4>
                            </div>
                            <div class="review-date">
                                <?php echo date('d/m/Y H:i', strtotime($review['review_date'])); ?>
                            </div>
                        </div>
                        <div class="rating">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?php echo $i <= $review['rating'] ? 'yellow-star' : ''; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <p><?php echo htmlspecialchars($review['content']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Bạn chưa có đánh giá nào.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
    document.querySelectorAll('.rating').forEach(ratingGroup => {
        const stars = ratingGroup.querySelectorAll('.star');
        const ratingInput = ratingGroup.closest('form').querySelector('input[name="rating"]');

        stars.forEach(star => {
            star.addEventListener('mouseover', function() {
                const value = this.dataset.value;
                stars.forEach(s => {
                    s.classList.toggle('active', s.dataset.value <= value);
                });
            });

            star.addEventListener('click', function() {
                const value = this.dataset.value;
                ratingInput.value = value;
                stars.forEach(s => {
                    s.classList.toggle('active', s.dataset.value <= value);
                });
            });
        });

        ratingGroup.addEventListener('mouseleave', function() {
            const currentRating = ratingInput.value;
            stars.forEach(s => {
                s.classList.toggle('active', s.dataset.value <= currentRating);
            });
        });
    });

    function submitReview(form, productId) {
        const rating = form.querySelector('input[name="rating"]').value;
        const content = form.querySelector('textarea[name="review_content"]').value;

        if (rating === '0') {
            alert('Vui lòng chọn số sao đánh giá');
            return false;
        }

        fetch('save_review.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                rating: rating,
                content: content
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Cảm ơn bạn đã đánh giá sản phẩm!');
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi gửi đánh giá');
        });

        return false;
    }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>

<?php $conn->close(); ?>
