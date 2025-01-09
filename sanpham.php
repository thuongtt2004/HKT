<?php
require_once 'config/connect.php';

// Lấy danh sách sản phẩm từ database
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        ORDER BY p.product_id DESC";
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
    <title>Sản phẩm - TTHUONG</title>
    <link rel="stylesheet" href="css/styles.css">
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
                    ?>
                    <div class="product" data-category="<?php echo $row['category_id']; ?>" 
                         data-price="<?php echo $row['price']; ?>" 
                         data-name="<?php echo htmlspecialchars($row['product_name']); ?>" 
                         onclick="showProductDetails(
                            '<?php echo addslashes($row['product_name']); ?>', 
                            '<?php echo addslashes($row['description']); ?>', 
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
                    <p><strong>Giá:</strong> <span id="modalPrice"></span> VNĐ</p>
                    <p><strong>Số lượng còn:</strong> <span id="modalStock"></span></p>
                    <p><strong>Mô tả:</strong></p>
                    <p id="modalDescription"></p>
                </div>
            </div>
        </div>
    </div>

    <style>
        <style>
    /* ... giữ nguyên các style khác ... */

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
        .search-filter-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            width: 100%;
        }

        .search-box input {
        flex: 1;
        padding: 8px 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
        .search-box input:focus {
        outline: none;
        border-color: #333;
    }


      
    .search-box button {
        padding: 8px 15px;
        height: 35px; /* Thêm chiều cao cố định */
        background-color: #333333;
        color: #EBE9E5;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

       
    .search-box button:hover {
        background-color: #444444;
    }
    .search-box button i {
        font-size: 12px;
    }


        /* Style cho Modal */
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
    </style>

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
    </script>
      <?php include 'footer.php'; ?>
</body>
</html>

<?php
$conn->close();
?> 