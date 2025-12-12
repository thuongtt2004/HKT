<?php
session_start();
require_once 'config/connect.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login_page.php');
    exit();
}

// Xử lý xóa danh mục
if (isset($_POST['delete_category'])) {
    $category_id = $_POST['category_id'];
    
    // Kiểm tra xem có sản phẩm nào đang sử dụng danh mục này không
    $check_products = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
    $check_products->bind_param("i", $category_id);
    $check_products->execute();
    $product_count = $check_products->get_result()->fetch_assoc()['count'];
    
    if ($product_count > 0) {
        echo "<script>alert('Không thể xóa danh mục này vì còn $product_count sản phẩm đang sử dụng!');</script>";
    } else {
        $delete_query = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        $delete_query->bind_param("i", $category_id);
        
        if ($delete_query->execute()) {
            echo "<script>alert('Xóa danh mục thành công!'); window.location.href='admin_categories.php';</script>";
        } else {
            echo "<script>alert('Lỗi khi xóa danh mục!');</script>";
        }
        $delete_query->close();
    }
    $check_products->close();
}

// Xử lý thêm/sửa danh mục
if (isset($_POST['save_category'])) {
    $category_id = $_POST['category_id'] ?? null;
    $category_name = trim($_POST['category_name']);
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($category_name)) {
        echo "<script>alert('Tên danh mục không được để trống!');</script>";
    } else {
        if ($category_id) {
            // Cập nhật
            if ($status_column_exists) {
                $stmt = $conn->prepare("UPDATE categories SET category_name=?, description=?, status=? WHERE category_id=?");
                $stmt->bind_param("sssi", $category_name, $description, $status, $category_id);
            } else {
                $stmt = $conn->prepare("UPDATE categories SET category_name=?, description=? WHERE category_id=?");
                $stmt->bind_param("ssi", $category_name, $description, $category_id);
            }
        } else {
            // Thêm mới
            if ($status_column_exists) {
                $stmt = $conn->prepare("INSERT INTO categories (category_name, description, status) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $category_name, $description, $status);
            } else {
                $stmt = $conn->prepare("INSERT INTO categories (category_name, description) VALUES (?, ?)");
                $stmt->bind_param("ss", $category_name, $description);
            }
        }
        
        if ($stmt->execute()) {
            echo "<script>alert('Lưu danh mục thành công!'); window.location.href='admin_categories.php';</script>";
        } else {
            echo "<script>alert('Lỗi: " . $conn->error . "');</script>";
        }
        $stmt->close();
    }
}

// Check if status column exists first
$status_column_exists = false;
$check_status = $conn->query("SHOW COLUMNS FROM categories LIKE 'status'");
if ($check_status && $check_status->num_rows > 0) {
    $status_column_exists = true;
}

// Lấy danh sách danh mục
if ($status_column_exists) {
    $categories_query = "SELECT c.*, COUNT(p.product_id) as product_count 
                         FROM categories c 
                         LEFT JOIN products p ON c.category_id = p.category_id 
                         GROUP BY c.category_id 
                         ORDER BY c.category_id DESC";
} else {
    $categories_query = "SELECT c.*, COUNT(p.product_id) as product_count, 'active' as status
                         FROM categories c 
                         LEFT JOIN products p ON c.category_id = p.category_id 
                         GROUP BY c.category_id 
                         ORDER BY c.category_id DESC";
}
$categories_result = $conn->query($categories_query);

// Lấy thông tin danh mục để sửa
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_query = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
    $edit_query->bind_param("i", $edit_id);
    $edit_query->execute();
    $edit_category = $edit_query->get_result()->fetch_assoc();
    $edit_query->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Danh Mục - Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .categories-container {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .page-header h1 {
            color: #333;
            margin: 0;
        }
        
        .btn-add-category {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-add-category:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .category-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border: 2px solid #f0f0f0;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
            border-color: #667eea;
        }
        
        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .category-name {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin: 0;
        }
        
        .category-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .category-description {
            color: #666;
            line-height: 1.6;
            margin: 15px 0;
            min-height: 40px;
        }
        
        .category-stats {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 15px 0;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #666;
            font-size: 14px;
        }
        
        .stat-item i {
            color: #667eea;
        }
        
        .category-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-edit, .btn-delete, .btn-view-products {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }
        
        .btn-edit {
            background: #17a2b8;
            color: white;
        }
        
        .btn-edit:hover {
            background: #138496;
            transform: translateY(-1px);
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        
        .btn-view-products {
            background: #28a745;
            color: white;
        }
        
        .btn-view-products:hover {
            background: #218838;
            transform: translateY(-1px);
        }
        
        /* Form Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            padding: 20px 30px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            margin: 0;
            color: #333;
        }
        
        .close {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #999;
        }
        
        .close:hover {
            color: #333;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .btn-cancel, .btn-save {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
        }
        
        .btn-save {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-cancel:hover, .btn-save:hover {
            transform: translateY(-1px);
        }
        
        .no-categories {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .no-categories i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        /* Products Modal */
        .products-modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 0;
            border-radius: 12px;
            width: 95%;
            max-width: 1000px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            padding: 20px;
        }
        
        .product-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .product-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        
        .product-name {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .product-price {
            color: #28a745;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 5px;
        }
        
        .product-stock {
            color: #666;
            font-size: 12px;
        }
        
        .no-products {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .no-products i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.3;
        }
        
        @media (max-width: 768px) {
            .categories-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            .category-actions {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <main>
        <div class="categories-container">
            <?php if (!$status_column_exists): ?>
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <h4><i class="fas fa-exclamation-triangle"></i> Cần cập nhật cơ sở dữ liệu</h4>
                    <p>Để sử dụng đầy đủ tính năng quản lý danh mục, vui lòng chạy script cập nhật cơ sở dữ liệu:</p>
                    <p><strong><a href="setup_database.php" target="_blank" style="color: #856404;">Nhấn vào đây để cập nhật tự động</a></strong></p>
                    <p>Hoặc chạy SQL thủ công: <code>ALTER TABLE categories ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' AFTER description;</code></p>
                </div>
            <?php endif; ?>
            
            <div class="page-header">
                <h1><i class="fas fa-list"></i> Quản lý danh mục sản phẩm</h1>
                <button class="btn-add-category" onclick="openModal()">
                    <i class="fas fa-plus"></i> Thêm danh mục mới
                </button>
            </div>

            <?php if ($categories_result->num_rows > 0): ?>
                <div class="categories-grid">
                    <?php while ($category = $categories_result->fetch_assoc()): ?>
                        <div class="category-card">
                            <div class="category-header">
                                <h3 class="category-name"><?php echo htmlspecialchars($category['category_name']); ?></h3>
                                <span class="category-status status-<?php echo isset($category['status']) ? $category['status'] : 'active'; ?>">
                                    <?php echo (isset($category['status']) && $category['status'] == 'inactive') ? 'Tạm dừng' : 'Hoạt động'; ?>
                                </span>
                            </div>
                            
                            <div class="category-description">
                                <?php echo $category['description'] ? htmlspecialchars($category['description']) : '<em>Chưa có mô tả</em>'; ?>
                            </div>
                            
                            <div class="category-stats">
                                <div class="stat-item">
                                    <i class="fas fa-box"></i>
                                    <span><?php echo $category['product_count']; ?> sản phẩm</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-hashtag"></i>
                                    <span>ID: <?php echo $category['category_id']; ?></span>
                                </div>
                            </div>
                            
                            <div class="category-actions">
                                <button class="btn-view-products" onclick="viewCategoryProducts(<?php echo $category['category_id']; ?>, '<?php echo htmlspecialchars($category['category_name']); ?>')">
                                    <i class="fas fa-eye"></i> Xem sản phẩm
                                </button>
                                
                                <a href="?edit=<?php echo $category['category_id']; ?>" class="btn-edit" onclick="openModal(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                
                                <?php if ($category['product_count'] == 0): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc muốn xóa danh mục này?');">
                                        <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                        <button type="submit" name="delete_category" class="btn-delete">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn-delete" disabled title="Không thể xóa danh mục có sản phẩm">
                                        <i class="fas fa-lock"></i> Khóa
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-categories">
                    <i class="fas fa-list"></i>
                    <h3>Chưa có danh mục nào</h3>
                    <p>Nhấn "Thêm danh mục mới" để bắt đầu tạo danh mục sản phẩm</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal Form -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Thêm danh mục mới</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="category_id" id="categoryId">
                    
                    <div class="form-group">
                        <label for="categoryName">
                            <i class="fas fa-tag"></i> Tên danh mục *
                        </label>
                        <input type="text" name="category_name" id="categoryName" required 
                               placeholder="Nhập tên danh mục...">
                    </div>
                    
                    <div class="form-group">
                        <label for="categoryDescription">
                            <i class="fas fa-align-left"></i> Mô tả
                        </label>
                        <textarea name="description" id="categoryDescription" 
                                  placeholder="Mô tả về danh mục này..."></textarea>
                    </div>
                    
                    <?php if ($status_column_exists): ?>
                    <div class="form-group">
                        <label for="categoryStatus">
                            <i class="fas fa-toggle-on"></i> Trạng thái
                        </label>
                        <select name="status" id="categoryStatus">
                            <option value="active">Hoạt động</option>
                            <option value="inactive">Tạm dừng</option>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" name="save_category" class="btn-save">
                        <i class="fas fa-save"></i> Lưu danh mục
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Modal -->
    <div id="productsModal" class="modal">
        <div class="products-modal-content">
            <div class="modal-header">
                <h2 id="productsModalTitle">Sản phẩm trong danh mục</h2>
                <span class="close" onclick="closeProductsModal()">&times;</span>
            </div>
            <div id="productsContainer">
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #667eea;"></i>
                    <p>Đang tải danh sách sản phẩm...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(categoryData = null) {
            const modal = document.getElementById('categoryModal');
            const modalTitle = document.getElementById('modalTitle');
            const form = modal.querySelector('form');
            
            if (categoryData) {
                // Edit mode
                modalTitle.textContent = 'Sửa danh mục';
                document.getElementById('categoryId').value = categoryData.category_id;
                document.getElementById('categoryName').value = categoryData.category_name;
                document.getElementById('categoryDescription').value = categoryData.description || '';
                const statusField = document.getElementById('categoryStatus');
                if (statusField) {
                    statusField.value = categoryData.status || 'active';
                }
            } else {
                // Add mode
                modalTitle.textContent = 'Thêm danh mục mới';
                form.reset();
                document.getElementById('categoryId').value = '';
            }
            
            modal.style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('categoryModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('categoryModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        
        // Auto open modal if editing
        <?php if ($edit_category): ?>
            openModal(<?php echo json_encode($edit_category); ?>);
        <?php endif; ?>
        
        // Products modal functions
        function viewCategoryProducts(categoryId, categoryName) {
            const modal = document.getElementById('productsModal');
            const title = document.getElementById('productsModalTitle');
            const container = document.getElementById('productsContainer');
            
            title.textContent = `Sản phẩm trong danh mục: ${categoryName}`;
            
            // Show loading
            container.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #667eea;"></i>
                    <p>Đang tải danh sách sản phẩm...</p>
                </div>
            `;
            
            modal.style.display = 'block';
            
            // Fetch products
            fetch(`get_category_products.php?category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayProducts(data.products);
                    } else {
                        container.innerHTML = `
                            <div class="no-products">
                                <i class="fas fa-exclamation-triangle"></i>
                                <h3>Lỗi tải dữ liệu</h3>
                                <p>${data.message || 'Không thể tải danh sách sản phẩm'}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = `
                        <div class="no-products">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h3>Lỗi kết nối</h3>
                            <p>Không thể tải danh sách sản phẩm. Vui lòng thử lại.</p>
                        </div>
                    `;
                });
        }
        
        function displayProducts(products) {
            const container = document.getElementById('productsContainer');
            
            if (products.length === 0) {
                container.innerHTML = `
                    <div class="no-products">
                        <i class="fas fa-box-open"></i>
                        <h3>Chưa có sản phẩm</h3>
                        <p>Danh mục này chưa có sản phẩm nào</p>
                    </div>
                `;
                return;
            }
            
            let productsHtml = '<div class="products-grid">';
            
            products.forEach(product => {
                const price = new Intl.NumberFormat('vi-VN').format(product.price);
                productsHtml += `
                    <div class="product-item">
                        <img src="${product.image_url}" alt="${product.product_name}" class="product-image" 
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div style="width: 80px; height: 80px; background: #f0f0f0; border-radius: 6px; display: none; align-items: center; justify-content: center; margin-bottom: 10px; color: #999; font-size: 12px;">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="product-name">${product.product_name}</div>
                        <div class="product-price">${price} VNĐ</div>
                        <div class="product-stock">Còn: ${product.stock_quantity}</div>
                        <div style="font-size: 11px; color: #999; margin-top: 5px;">ID: ${product.product_id}</div>
                    </div>
                `;
            });
            
            productsHtml += '</div>';
            container.innerHTML = productsHtml;
        }
        
        function closeProductsModal() {
            document.getElementById('productsModal').style.display = 'none';
        }
        
        // Close products modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('productsModal');
            if (event.target == modal) {
                closeProductsModal();
            }
        });
    </script>

    <?php include 'admin_footer.php'; ?>
</body>
</html>

<?php
$conn->close();
?>