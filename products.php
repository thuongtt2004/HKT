<?php
session_start();
require_once 'config/connect.php';

// Lấy danh sách sản phẩm từ database với thông tin đánh giá
$sql = "SELECT p.*, c.category_name,
               COUNT(DISTINCT r.review_id) as review_count,
               AVG(r.rating) as avg_rating
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        LEFT JOIN reviews r ON p.product_id = r.product_id
        GROUP BY p.product_id
        ORDER BY p.product_id DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Lỗi truy vấn: " . $conn->error);
}

// Lấy thông tin khuyến mãi hiện tại
$now = date('Y-m-d H:i:s');

// Flash sale
$flash_sale_query = "SELECT * FROM promotions 
                     WHERE promotion_type = 'flash_sale' 
                     AND status = 'active' 
                     AND '$now' BETWEEN start_date AND end_date 
                     ORDER BY discount_value DESC 
                     LIMIT 1";
$flash_sale_result = $conn->query($flash_sale_query);
$flash_sale = $flash_sale_result->num_rows > 0 ? $flash_sale_result->fetch_assoc() : null;

// Product promotions
$product_promotions = [];
$product_promo_query = "SELECT pp.product_id, p.* 
                        FROM promotion_products pp
                        JOIN promotions p ON pp.promotion_id = p.promotion_id
                        WHERE p.promotion_type = 'product'
                        AND p.status = 'active' 
                        AND '$now' BETWEEN p.start_date AND p.end_date";
$product_promo_result = $conn->query($product_promo_query);
while ($row = $product_promo_result->fetch_assoc()) {
    $product_promotions[$row['product_id']] = $row;
}

// Category promotions
$category_promotions = [];
$category_promo_query = "SELECT pc.category_id, p.* 
                         FROM promotion_categories pc
                         JOIN promotions p ON pc.promotion_id = p.promotion_id
                         WHERE p.promotion_type = 'category'
                         AND p.status = 'active' 
                         AND '$now' BETWEEN p.start_date AND p.end_date";
$category_promo_result = $conn->query($category_promo_query);
while ($row = $category_promo_result->fetch_assoc()) {
    $category_promotions[$row['category_id']] = $row;
}

if (!$result) {
    die("Lỗi truy vấn: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - HKT Store</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/sanpham.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-rating {
            margin: 8px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .stars {
            color: #ffd700;
            font-size: 14px;
        }
        
        .stars .far {
            color: #ddd;
        }
        
        .rating-text {
            font-size: 12px;
            color: #666;
        }
        
        .avg-rating {
            font-weight: 600;
            color: #333;
            font-size: 13px;
        }
        
        .no-rating {
            font-size: 12px;
            color: #999;
            font-style: italic;
        }
        
        /* Discount Tag Styles */
        .product {
            position: relative;
        }
        
        .discount-tag {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #ff4757, #ff3742);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            box-shadow: 0 2px 8px rgba(255, 71, 87, 0.3);
            z-index: 2;
            animation: pulse 2s infinite;
        }
        
        .discount-tag.flash-sale {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            animation: flashSale 1.5s infinite alternate;
        }
        
        .discount-tag.product-sale {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
        
        .discount-tag.category-sale {
            background: linear-gradient(135deg, #f093fb, #f5576c);
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes flashSale {
            0% { 
                transform: scale(1);
                box-shadow: 0 2px 8px rgba(255, 107, 53, 0.3);
            }
            100% { 
                transform: scale(1.08);
                box-shadow: 0 4px 16px rgba(255, 107, 53, 0.6);
            }
        }
        
        .original-price {
            text-decoration: line-through;
            color: #999;
            font-size: 14px;
            margin-right: 8px;
        }
        
        .discounted-price {
            color: #ff4757;
            font-weight: 700;
            font-size: 16px;
        }
        
        /* Modal styles */
        .modal-content {
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .product-details {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .product-image {
            flex: 0 0 300px;
        }
        
        .product-info {
            flex: 1;
        }
        
        /* Reviews section */
        .reviews-section {
            border-top: 2px solid #eee;
            padding-top: 25px;
            margin-top: 25px;
        }
        
        .reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .reviews-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .reviews-summary {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .rating-overview {
            text-align: center;
        }
        
        .big-rating {
            font-size: 36px;
            font-weight: 700;
            color: #333;
            line-height: 1;
        }
        
        .big-stars {
            font-size: 20px;
            color: #ffd700;
            margin: 5px 0;
        }
        
        .total-reviews {
            font-size: 14px;
            color: #666;
        }
        
        .reviews-list {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #eee;
            border-radius: 8px;
        }
        
        .review-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
            background: white;
        }
        
        .review-item:last-child {
            border-bottom: none;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .reviewer-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .reviewer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }
        
        .reviewer-details h4 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }
        
        .review-date {
            font-size: 12px;
            color: #999;
        }
        
        .review-rating {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 10px 0;
        }
        
        .review-stars {
            color: #ffd700;
            font-size: 16px;
        }
        
        .rating-number {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .review-content {
            color: #555;
            line-height: 1.6;
            font-size: 14px;
        }
        
        .no-reviews-message {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .no-reviews-message i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.3;
        }
        
        @media (max-width: 768px) {
            .product-details {
                flex-direction: column;
            }
            
            .product-image {
                flex: none;
            }
            
            .reviews-summary {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>

    <!-- Thêm thanh tìm kiếm -->
    <div class="search-filter-container">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Tìm kiếm sản phẩm...">
            <button onclick="searchProducts()">
                <i class="fas fa-search"></i> Tìm kiếm
            </button>
        </div>

        <div class="filter-container">
            <select id="categoryFilter">
                <option value="all">Tất cả danh mục</option>
                <option value="tuong">Tượng trang trí</option>
                <option value="tranh">Tranh treo tường</option>
                <option value="den">Đèn trang trí</option>
                <option value="khac">Khác</option>
            </select>
            <select id="priceFilter">
                <option value="all">Tất cả giá</option>
                <option value="low">Dưới 300,000 VNĐ</option>
                <option value="medium">300,000 - 600,000 VNĐ</option>
                <option value="high">Trên 600,000 VNĐ</option>
            </select>
        </div>
    </div>

    <section id="all-products">
        <h2>Tất cả sản phẩm</h2>
        <div class="products">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Tính toán khuyến mãi tốt nhất
                    $best_discount = null;
                    $discount_class = '';
                    $original_price = $row['price'];
                    $final_price = $original_price;
                    
                    // Kiểm tra Flash Sale (ưu tiên cao nhất)
                    if ($flash_sale) {
                        $discount_value = $flash_sale['discount_value'];
                        if ($flash_sale['discount_type'] == 'percentage') {
                            $discount_amount = ($original_price * $discount_value) / 100;
                            $best_discount = $discount_value . '%';
                        } else {
                            $discount_amount = $discount_value;
                            $best_discount = number_format($discount_value / 1000, 0) . 'k';
                        }
                        $final_price = max(0, $original_price - $discount_amount);
                        $discount_class = 'flash-sale';
                    }
                    // Kiểm tra Product Promotion
                    elseif (isset($product_promotions[$row['product_id']])) {
                        $promo = $product_promotions[$row['product_id']];
                        $discount_value = $promo['discount_value'];
                        if ($promo['discount_type'] == 'percentage') {
                            $discount_amount = ($original_price * $discount_value) / 100;
                            $best_discount = $discount_value . '%';
                        } else {
                            $discount_amount = $discount_value;
                            $best_discount = number_format($discount_value / 1000, 0) . 'k';
                        }
                        $final_price = max(0, $original_price - $discount_amount);
                        $discount_class = 'product-sale';
                    }
                    // Kiểm tra Category Promotion
                    elseif (isset($category_promotions[$row['category_id']])) {
                        $promo = $category_promotions[$row['category_id']];
                        $discount_value = $promo['discount_value'];
                        if ($promo['discount_type'] == 'percentage') {
                            $discount_amount = ($original_price * $discount_value) / 100;
                            $best_discount = $discount_value . '%';
                        } else {
                            $discount_amount = $discount_value;
                            $best_discount = number_format($discount_value / 1000, 0) . 'k';
                        }
                        $final_price = max(0, $original_price - $discount_amount);
                        $discount_class = 'category-sale';
                    }
                    ?>
                    <div class="product" data-category="<?php echo $row['category_id']; ?>" 
                         data-price="<?php echo $row['price']; ?>" 
                         data-name="<?php echo htmlspecialchars($row['product_name']); ?>" 
                         onclick="showProductDetails(
                            '<?php echo addslashes($row['product_name']); ?>', 
                            '<?php echo addslashes($row['description']); ?>', 
                            '<?php echo number_format($final_price, 0, ',', '.'); ?>', 
                            '<?php echo htmlspecialchars($row['image_url']); ?>', 
                            '<?php echo htmlspecialchars($row['product_id']); ?>', 
                            '<?php echo htmlspecialchars($row['category_name']); ?>', 
                            <?php echo $row['stock_quantity']; ?>,
                            <?php echo $row['avg_rating'] ? number_format($row['avg_rating'], 1) : 0; ?>,
                            <?php echo $row['review_count']; ?>,
                            '<?php echo $best_discount ? $best_discount : ''; ?>',
                            '<?php echo number_format($original_price, 0, ',', '.'); ?>'
                        )">
                        <!-- Discount Tag -->
                        <?php if ($best_discount): ?>
                            <div class="discount-tag <?php echo $discount_class; ?>">
                                -<?php echo $best_discount; ?>
                            </div>
                        <?php endif; ?>
                        
                        <button class="wishlist-btn" onclick="event.stopPropagation(); toggleWishlist('<?php echo $row['product_id']; ?>', this);" title="Thêm vào yêu thích">
                            <i class="far fa-heart"></i>
                        </button>
                        <img src="<?php echo htmlspecialchars($row['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                        <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                        <p>MSP: <?php echo htmlspecialchars($row['product_id']); ?></p>
                        
                        <!-- Hiển thị đánh giá -->
                        <?php if ($row['review_count'] > 0): ?>
                            <div class="product-rating">
                                <div class="stars">
                                    <?php 
                                    $avg_rating = round($row['avg_rating']);
                                    for($i = 1; $i <= 5; $i++): 
                                    ?>
                                        <i class="<?php echo $i <= $avg_rating ? 'fas' : 'far'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="avg-rating"><?php echo number_format($row['avg_rating'], 1); ?></span>
                                <span class="rating-text">(<?php echo $row['review_count']; ?> đánh giá)</span>
                            </div>
                        <?php else: ?>
                            <div class="product-rating">
                                <span class="no-rating">Chưa có đánh giá</span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Price Display -->
                        <p>
                            <?php if ($best_discount): ?>
                                <span class="original-price"><?php echo number_format($original_price, 0, ',', '.'); ?> VNĐ</span>
                                <span class="discounted-price"><?php echo number_format($final_price, 0, ',', '.'); ?> VNĐ</span>
                            <?php else: ?>
                                <?php echo number_format($row['price'], 0, ',', '.'); ?> VNĐ
                            <?php endif; ?>
                        </p>
                        <div class="button-group">
                            <button onclick="addToCart('<?php echo $row['product_id']; ?>', 
                                                     '<?php echo addslashes($row['product_name']); ?>', 
                                                     <?php echo $row['price']; ?>)" 
                                    class="add-to-cart">
                                Thêm vào giỏ hàng
                            </button>
                            <button onclick="window.location.href='order.php?id=<?php echo $row['product_id']; ?>'" 
                                    class="buy-now">
                                Mua ngay
                            </button>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p>Không có sản phẩm nào.</p>";
            }
            ?>
        </div>
    </section>

    <!-- Modal chi tiết sản phẩm -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="product-details">
                <div class="product-image">
                    <img id="modalImage" src="" alt="">
                </div>
                <div class="product-info">
                    <h2 id="modalTitle"></h2>
                    <p><strong>Mã sản phẩm:</strong> <span id="modalId"></span></p>
                    <p><strong>Danh mục:</strong> <span id="modalCategory"></span></p>
                    <p><strong>Giá:</strong> 
                        <span id="modalOriginalPrice" class="original-price" style="display: none;"></span>
                        <span id="modalPrice"></span> VNĐ
                        <span id="modalDiscountTag" class="discount-tag" style="display: none; position: relative; top: 0; right: 0; margin-left: 10px;"></span>
                    </p>
                    <p><strong>Số lượng còn:</strong> <span id="modalStock"></span></p>
                    
                    <!-- Đánh giá trong modal -->
                    <div id="modalRating" style="margin: 15px 0;">
                        <strong>Đánh giá:</strong>
                        <div class="product-rating" style="margin-top: 5px;">
                            <div class="stars" id="modalStars"></div>
                            <span class="avg-rating" id="modalAvgRating"></span>
                            <span class="rating-text" id="modalReviewCount"></span>
                        </div>
                    </div>
                    
                    <p><strong>Mô tả:</strong></p>
                    <p id="modalDescription"></p>
                </div>
            </div>
            
            <!-- Reviews Section -->
            <div class="reviews-section" id="reviewsSection">
                <div class="reviews-header">
                    <h3 class="reviews-title">
                        <i class="fas fa-star"></i> Đánh giá từ khách hàng
                    </h3>
                </div>
                
                <!-- Reviews Summary -->
                <div class="reviews-summary" id="reviewsSummary">
                    <div class="rating-overview">
                        <div class="big-rating" id="bigRating">0</div>
                        <div class="big-stars" id="bigStars"></div>
                        <div class="total-reviews" id="totalReviews">0 đánh giá</div>
                    </div>
                </div>
                
                <!-- Reviews List -->
                <div class="reviews-list" id="reviewsList">
                    <div class="no-reviews-message">
                        <i class="fas fa-comments"></i>
                        <p>Đang tải đánh giá...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function searchProducts() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const products = document.querySelectorAll('.product');
            
            products.forEach(product => {
                const productName = product.getAttribute('data-name').toLowerCase();
                if (productName.includes(searchTerm)) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
        }

        // Thêm sự kiện cho input search khi nhấn Enter
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchProducts();
            }
        });

        // Giữ nguyên các hàm filter và addToCart
        document.getElementById('categoryFilter').addEventListener('change', filterProducts);
        document.getElementById('priceFilter').addEventListener('change', filterProducts);

        function filterProducts() {
            const category = document.getElementById('categoryFilter').value;
            const priceRange = document.getElementById('priceFilter').value;
            const products = document.querySelectorAll('.product');

            products.forEach(product => {
                const productCategory = product.getAttribute('data-category');
                const productPrice = parseInt(product.getAttribute('data-price'));
                
                let showByCategory = category === 'all' || productCategory === category;
                let showByPrice = true;

                switch(priceRange) {
                    case 'low':
                        showByPrice = productPrice < 300000;
                        break;
                    case 'medium':
                        showByPrice = productPrice >= 300000 && productPrice <= 600000;
                        break;
                    case 'high':
                        showByPrice = productPrice > 600000;
                        break;
                }

                if (showByCategory && showByPrice) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
        }

        function addToCart(productId, productName, price) {
    // Ngăn chặn sự kiện click lan ra ngoài
    event.stopPropagation();
    
    // Gửi request AJAX để thêm vào giỏ hàng
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Đã thêm ' + productName + ' vào giỏ hàng!');
        } else if (data.message === 'not_logged_in') {
            if (confirm('Bạn cần đăng nhập để thêm vào giỏ hàng. Đến trang đăng nhập?')) {
                window.location.href = 'login_page.php';
            }
        } else {
            alert(data.message || 'Có lỗi xảy ra khi thêm vào giỏ hàng');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra: ' + error.message);
    });
}

        function showProductDetails(name, description, price, image, id, category, stock, avgRating, reviewCount, discount, originalPrice) {
            document.getElementById('modalTitle').textContent = name;
            document.getElementById('modalDescription').textContent = description;
            document.getElementById('modalImage').src = image;
            document.getElementById('modalId').textContent = id;
            document.getElementById('modalCategory').textContent = category;
            document.getElementById('modalStock').textContent = stock;
            
            // Hiển thị giá và discount
            const modalPrice = document.getElementById('modalPrice');
            const modalOriginalPrice = document.getElementById('modalOriginalPrice');
            const modalDiscountTag = document.getElementById('modalDiscountTag');
            
            if (discount) {
                modalOriginalPrice.textContent = originalPrice + ' VNĐ';
                modalOriginalPrice.style.display = 'inline';
                modalPrice.textContent = price;
                modalPrice.style.color = '#ff4757';
                modalPrice.style.fontWeight = '700';
                modalDiscountTag.textContent = '-' + discount;
                modalDiscountTag.style.display = 'inline-block';
            } else {
                modalOriginalPrice.style.display = 'none';
                modalPrice.textContent = price;
                modalPrice.style.color = '';
                modalPrice.style.fontWeight = '';
                modalDiscountTag.style.display = 'none';
            }
            
            // Hiển thị đánh giá trong modal
            const modalStars = document.getElementById('modalStars');
            const modalAvgRating = document.getElementById('modalAvgRating');
            const modalReviewCount = document.getElementById('modalReviewCount');
            
            if (reviewCount > 0) {
                // Tạo stars
                let starsHtml = '';
                const roundedRating = Math.round(avgRating);
                for (let i = 1; i <= 5; i++) {
                    starsHtml += `<i class="${i <= roundedRating ? 'fas' : 'far'} fa-star"></i>`;
                }
                modalStars.innerHTML = starsHtml;
                modalAvgRating.textContent = avgRating;
                modalReviewCount.textContent = `(${reviewCount} đánh giá)`;
            } else {
                modalStars.innerHTML = '';
                modalAvgRating.textContent = '';
                modalReviewCount.textContent = 'Chưa có đánh giá';
            }
            
            // Load reviews cho sản phẩm này
            loadProductReviews(id);
            
            document.getElementById('productModal').style.display = 'block';
        }
        
        // Function để load reviews của sản phẩm
        async function loadProductReviews(productId) {
            try {
                const response = await fetch(`get_product_reviews.php?product_id=${productId}`);
                const data = await response.json();
                
                const reviewsList = document.getElementById('reviewsList');
                const bigRating = document.getElementById('bigRating');
                const bigStars = document.getElementById('bigStars');
                const totalReviews = document.getElementById('totalReviews');
                
                if (data.success && data.reviews.length > 0) {
                    // Update summary
                    bigRating.textContent = data.avg_rating;
                    totalReviews.textContent = `${data.reviews.length} đánh giá`;
                    
                    // Create big stars
                    let bigStarsHtml = '';
                    const roundedRating = Math.round(data.avg_rating);
                    for (let i = 1; i <= 5; i++) {
                        bigStarsHtml += `<i class="${i <= roundedRating ? 'fas' : 'far'} fa-star"></i>`;
                    }
                    bigStars.innerHTML = bigStarsHtml;
                    
                    // Create reviews list
                    let reviewsHtml = '';
                    data.reviews.forEach(review => {
                        const reviewDate = new Date(review.review_date).toLocaleDateString('vi-VN');
                        const reviewerInitial = review.username.charAt(0).toUpperCase();
                        
                        let starsHtml = '';
                        for (let i = 1; i <= 5; i++) {
                            starsHtml += `<i class="${i <= review.rating ? 'fas' : 'far'} fa-star"></i>`;
                        }
                        
                        reviewsHtml += `
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-info">
                                        <div class="reviewer-avatar">${reviewerInitial}</div>
                                        <div class="reviewer-details">
                                            <h4>${review.username}</h4>
                                            <div class="review-date">${reviewDate}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="review-rating">
                                    <div class="review-stars">${starsHtml}</div>
                                    <span class="rating-number">${review.rating}/5</span>
                                </div>
                                <div class="review-content">
                                    ${review.content}
                                </div>
                            </div>
                        `;
                    });
                    
                    reviewsList.innerHTML = reviewsHtml;
                } else {
                    // No reviews
                    bigRating.textContent = '0';
                    bigStars.innerHTML = '<i class="far fa-star"></i>'.repeat(5);
                    totalReviews.textContent = 'Chưa có đánh giá';
                    
                    reviewsList.innerHTML = `
                        <div class="no-reviews-message">
                            <i class="fas fa-comments"></i>
                            <p>Chưa có đánh giá nào cho sản phẩm này</p>
                            <small>Hãy là người đầu tiên đánh giá sản phẩm!</small>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading reviews:', error);
                document.getElementById('reviewsList').innerHTML = `
                    <div class="no-reviews-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Không thể tải đánh giá</p>
                        <small>Vui lòng thử lại sau</small>
                    </div>
                `;
            }
        }

        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }

        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            if (event.target == document.getElementById('productModal')) {
                closeModal();
            }
        }
        
        // Toggle wishlist
        function toggleWishlist(productId, button) {
            <?php if (!isset($_SESSION['user_id'])): ?>
                if (confirm('Bạn cần đăng nhập để thêm vào yêu thích. Đến trang đăng nhập?')) {
                    window.location.href = 'login.php';
                }
                return;
            <?php endif; ?>
            
            fetch('toggle_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    product_id: productId,
                    action: 'toggle'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const icon = button.querySelector('i');
                    if (data.in_wishlist) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        button.style.color = '#dc3545';
                        button.title = 'Xóa khỏi yêu thích';
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        button.style.color = '';
                        button.title = 'Thêm vào yêu thích';
                    }
                } else {
                    alert(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra!');
            });
        }
        
        // Load wishlist status on page load
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['user_id'])): ?>
                loadWishlistStatus();
            <?php endif; ?>
        });
        
        function loadWishlistStatus() {
            fetch('get_wishlist_status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.wishlist.forEach(productId => {
                            const buttons = document.querySelectorAll(`button[onclick*="${productId}"]`);
                            buttons.forEach(button => {
                                if (button.classList.contains('wishlist-btn')) {
                                    const icon = button.querySelector('i');
                                    icon.classList.remove('far');
                                    icon.classList.add('fas');
                                    button.style.color = '#dc3545';
                                    button.title = 'Xóa khỏi yêu thích';
                                }
                            });
                        });
                    }
                });
        }
    </script>
      <?php include 'footer.php'; ?>
</body>
</html>

<?php
$conn->close();
?> 