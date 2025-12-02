<?php
session_start();
require_once '../config/connect.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Lấy danh sách danh mục
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name");

// Lấy danh sách sản phẩm với phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$products = $stmt->get_result();

// Tổng số sản phẩm
$total = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_pages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm - HKT Store</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <h1>Quản Lý Sản Phẩm</h1>
            
            <div class="actions">
                <button onclick="showAddForm()" class="btn-add">Thêm Sản Phẩm Mới</button>
            </div>

            <div class="product-list">
                <table>
                    <thead>
                        <tr>
                            <th>Mã SP</th>
                            <th>Hình ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Tồn kho</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($product = $products->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $product['product_id']; ?></td>
                            <td>
                                <img src="../images/products/<?php echo $product['image']; ?>" 
                                     alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                     width="50">
                            </td>
                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</td>
                            <td><?php echo $product['stock']; ?></td>
                            <td>
                                <button onclick="editProduct(<?php echo $product['product_id']; ?>)" 
                                        class="btn-edit">Sửa</button>
                                <button onclick="deleteProduct(<?php echo $product['product_id']; ?>)" 
                                        class="btn-delete">Xóa</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Phân trang -->
                <div class="pagination">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" 
                           class="<?php echo $page == $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Form thêm/sửa sản phẩm -->
            <div id="productForm" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close" onclick="closeForm()">&times;</span>
                    <h2 id="formTitle">Thêm Sản Phẩm Mới</h2>
                    <form id="productFormElement" enctype="multipart/form-data">
                        <input type="hidden" id="product_id" name="product_id">
                        
                        <div class="form-group">
                            <label for="product_name">Tên sản phẩm:</label>
                            <input type="text" id="product_name" name="product_name" required>
                        </div>

                        <div class="form-group">
                            <label for="category_id">Danh mục:</label>
                            <select id="category_id" name="category_id" required>
                                <?php while($category = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="price">Giá:</label>
                            <input type="number" id="price" name="price" required>
                        </div>

                        <div class="form-group">
                            <label for="stock">Tồn kho:</label>
                            <input type="number" id="stock" name="stock" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Mô tả:</label>
                            <textarea id="description" name="description" rows="4"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="image">Hình ảnh:</label>
                            <input type="file" id="image" name="image" accept="image/*">
                        </div>

                        <button type="submit" class="btn-submit">Lưu</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function showAddForm() {
            document.getElementById('formTitle').textContent = 'Thêm Sản Phẩm Mới';
            document.getElementById('productFormElement').reset();
            document.getElementById('productForm').style.display = 'block';
        }

        function editProduct(productId) {
            document.getElementById('formTitle').textContent = 'Sửa Sản Phẩm';
            // Lấy thông tin sản phẩm và điền vào form
            fetch(`get_product.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('product_id').value = data.product_id;
                    document.getElementById('product_name').value = data.product_name;
                    document.getElementById('category_id').value = data.category_id;
                    document.getElementById('price').value = data.price;
                    document.getElementById('stock').value = data.stock;
                    document.getElementById('description').value = data.description;
                    document.getElementById('productForm').style.display = 'block';
                });
        }

        function deleteProduct(productId) {
            if(confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
                fetch('xu_ly_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        product_id: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Có lỗi xảy ra!');
                    }
                });
            }
        }

        function closeForm() {
            document.getElementById('productForm').style.display = 'none';
        }

        // Xử lý submit form
        document.getElementById('productFormElement').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('xu_ly_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra!');
                }
            });
        };
    </script>
</body>
</html> 