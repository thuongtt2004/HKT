<?php
require_once 'config/connect.php';

// Lấy 4 sản phẩm mới nhất thay vì 5
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        ORDER BY p.product_id DESC 
        LIMIT 4";
$result = $conn->query($sql);

if (!$result) {
    die("Lỗi truy vấn: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TTHUONG Store</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* CSS cho Modal */
        .button-group {
        display: flex;
        gap: 8px;
        justify-content: center;
        margin-top: 10px;
        align-items: center;
    }

    .add-to-cart, .buy-now {
        flex: 1;
        padding: 8px 10px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
        height: 35px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
    }

    .add-to-cart {
        background-color: #333333;
        color: #EBE9E5;
        border: 1px solid #333333;
    }

    .buy-now {
        background-color: #EBE9E5;
        color: #333333;
        border: 1px solid #333333;
    }

    .add-to-cart:hover {
        background-color: #444444;
    }

    .buy-now:hover {
        background-color: #333333;
        color: #EBE9E5;
    }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
        }

        .modal-content {
            background-color: #ebe9e5;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 700px;
            position: relative;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 28px;
            cursor: pointer;
        }

        .product-details {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .product-image {
            flex: 0 0 40%;
        }

        .product-image img {
            width: 100%;
            border-radius: 8px;
        }

        .product-info {
            flex: 1;
        }

        .product {
            cursor: pointer;
            transition: transform 0.3s;
        }

        .product:hover {
            transform: translateY(-5px);
        }

        /* Style cho slideshow - xóa style của m�i tên và caption */
        .slideshow-container {
            max-width: 1200px;
            position: relative;
            margin: 0 auto 40px auto;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .mySlides {
            display: none;
            width: 100%;
            height: 500px;
            position: relative;
        }

        .mySlides img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .fade {
            animation-name: fade;
            animation-duration: 1.5s;
        }

        @keyframes fade {
            from {opacity: .4} 
            to {opacity: 1}
        }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>

    <section id="home">
        <div class="slideshow-container">
            <div class="mySlides fade">
                <img src="images/banner1.jpg" style="width:100%">
            </div>
            <div class="mySlides fade">
                <img src="images/banner2.jpg" style="width:100%">
            </div>
            <div class="mySlides fade">
                <img src="images/banner3.jpg" style="width:100%">
            </div>
        </div>
        <h2>Chào mừng đến với TTHUONG Store</h2>
        <p>TTHUONG- "Hãy biến không gian của bạn thành một tác phẩm nghệ thuật"</p>
        <p>TTHUONG Store là điểm đến hoàn hảo cho những ai yêu thích sự sáng tạo và mong muốn mang vẻ đẹp nghệ thuật vào không gian sống. Với đa dạng sản phẩm decor, nội thất, và phụ kiện tinh tế, cửa hàng không chỉ cung cấp mà còn truyền cảm hứng để bạn tạo nên một không gian đậm chất riêng.
        <p>Từng món đồ tại TTHUONG Store đều được chọn lọc kỹ lưỡng, kết hợp giữa phong cách hiện đại và nét tinh tế thủ công, giúp biến từng góc nhỏ trong nhà thành nơi chứa đựng cảm xúc và phong cách nghệ thuật độc đáo.</p>
        <p>Hãy để TTHUONG Store đồng hành cùng bạn trong hành trình tô điểm tổ ấm và biến từng khoảnh khắc sống trở nên đáng nhớ.</p>
    </section>

    <section id="products">
        <h2>Sản phẩm nổi bật</h2>
        <div class="products">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    ?>
                    <div class="product" onclick="showProductDetails(
                        '<?php echo htmlspecialchars($row['product_name']); ?>',
                        '<?php echo htmlspecialchars($row['description']); ?>',
                        '<?php echo number_format($row['price'], 0, ',', '.'); ?>',
                        '<?php echo htmlspecialchars($row['image_url']); ?>',
                        '<?php echo htmlspecialchars($row['product_id']); ?>',
                        '<?php echo htmlspecialchars($row['category_name']); ?>',
                        <?php echo $row['stock_quantity']; ?>
                    )">
                        <img src="<?php echo htmlspecialchars($row['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                        <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                        <p>MSP: <?php echo htmlspecialchars($row['product_id']); ?></p>
                        <p><?php echo number_format($row['price'], 0, ',', '.'); ?> VNĐ</p>
                        <div class="button-group">
                            <button onclick="event.stopPropagation(); addToCart('<?php echo $row['product_id']; ?>', 
                                                     '<?php echo addslashes($row['product_name']); ?>', 
                                                     <?php echo $row['price']; ?>)" 
                                    class="add-to-cart">
                                Thêm vào giỏ hàng
                            </button>
                            <button onclick="event.stopPropagation(); window.location.href='order.php?id=<?php echo $row['product_id']; ?>'" 
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
                    <p><strong>Giá:</strong> <span id="modalPrice"></span> VNĐ</p>
                    <p><strong>Số lượng còn:</strong> <span id="modalStock"></span></p>
                    <p><strong>Mô tả:</strong></p>
                    <p id="modalDescription"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let slideIndex = 0;
        showSlides();

        function showSlides() {
            let i;
            let slides = document.getElementsByClassName("mySlides");
            
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";  
            }
            
            slideIndex++;
            if (slideIndex > slides.length) {slideIndex = 1}
            
            slides[slideIndex-1].style.display = "block";
            setTimeout(showSlides, 4000);
        }

        function showProductDetails(name, description, price, image, id, category, stock) {
            document.getElementById('modalTitle').textContent = name;
            document.getElementById('modalDescription').textContent = description;
            document.getElementById('modalPrice').textContent = price;
            document.getElementById('modalImage').src = image;
            document.getElementById('modalId').textContent = id;
            document.getElementById('modalCategory').textContent = category;
            document.getElementById('modalStock').textContent = stock;
            document.getElementById('productModal').style.display = 'block';
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
                window.location.href = 'dangnhap.php';
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
    </script>
      <?php include 'footer.php'; ?>
</body>
</html>

<?php
$conn->close();
?>
