<?php
session_start();
require_once 'config/connect.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login_page.php');
    exit();
}

// Xử lý xóa sản phẩm
if (isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];
    
    try {
        // Debug để kiểm tra ID sản phẩm
        error_log("Đang xóa sản phẩm ID: " . $product_id);
        
        // Bắt đầu transaction
        $conn->begin_transaction();
        
        // Sử dụng Prepared Statement để tránh SQL injection
        $delete_query = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $delete_query->bind_param("s", $product_id);
        
        if ($delete_query->execute()) {
            $conn->commit();
            echo "<script>
                alert('Xóa sản phẩm ID: " . $product_id . " thành công!');
                window.location.href = 'admin_products.php';
            </script>";
        } else {
            throw new Exception($conn->error);
        }
        
        $delete_query->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>
            alert('Lỗi khi xóa sản phẩm: " . $e->getMessage() . "');
            window.location.href = 'admin_products.php';
        </script>";
    }
}

// Xử lý cập nhật sản phẩm
if (isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $product_name = trim($_POST['product_name']);
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $sold_quantity = intval($_POST['sold_quantity']);
    $category_id = intval($_POST['category_id']);
    $description = trim($_POST['description']);
    $current_image = $_POST['current_image'];
    
    // Xử lý upload ảnh mới (nếu có)
    $image_url = $current_image; // Giữ ảnh cũ mặc định
    
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $targetDir = "uploads/";
        
        // Tạo thư mục nếu chưa tồn tại
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($_FILES["product_image"]["name"]);
        $targetFile = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        
        // Kiểm tra file hình ảnh
        $validExtensions = array("jpg", "jpeg", "png", "gif");
        if (in_array($imageFileType, $validExtensions)) {
            // Kiểm tra kích thước file (max 5MB)
            if ($_FILES["product_image"]["size"] <= 5000000) {
                if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $targetFile)) {
                    // Xóa ảnh cũ nếu tồn tại
                    if (file_exists($current_image) && $current_image != $targetFile) {
                        unlink($current_image);
                    }
                    $image_url = $targetFile;
                } else {
                    echo "<script>alert('Lỗi khi tải lên hình ảnh!');</script>";
                }
            } else {
                echo "<script>alert('File quá lớn! Tối đa 5MB.');</script>";
            }
        } else {
            echo "<script>alert('Chỉ chấp nhận file JPG, JPEG, PNG & GIF!');</script>";
        }
    }

    $stmt = $conn->prepare("UPDATE products SET product_name = ?, price = ?, stock_quantity = ?, sold_quantity = ?, category_id = ?, description = ?, image_url = ? WHERE product_id = ?");
    $stmt->bind_param("sdiidsss", $product_name, $price, $stock_quantity, $sold_quantity, $category_id, $description, $image_url, $product_id);

    if ($stmt->execute()) {
        echo "<script>
            alert('Cập nhật sản phẩm thành công!');
            window.location.href = 'admin_products.php';
        </script>";
    } else {
        echo "<script>alert('Lỗi khi cập nhật sản phẩm: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// Xử lý tìm kiếm và lọc
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$stock_filter = isset($_GET['stock']) ? $_GET['stock'] : '';

// Lấy danh sách sản phẩm với điều kiện lọc
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE 1=1";

if ($search) {
    $search_param = '%' . $search . '%';
    $sql .= " AND (p.product_name LIKE ? OR p.product_id LIKE ?)";
}
if ($category_filter > 0) {
    $sql .= " AND p.category_id = ?";
}
if ($stock_filter === 'low') {
    $sql .= " AND p.stock_quantity <= 10 AND p.stock_quantity > 0";
} elseif ($stock_filter === 'out') {
    $sql .= " AND p.stock_quantity = 0";
}

$sql .= " ORDER BY p.product_id DESC";

$stmt = $conn->prepare($sql);
if ($search && $category_filter > 0) {
    $stmt->bind_param("ssi", $search_param, $search_param, $category_filter);
} elseif ($search) {
    $stmt->bind_param("ss", $search_param, $search_param);
} elseif ($category_filter > 0) {
    $stmt->bind_param("i", $category_filter);
}
$stmt->execute();
$result = $stmt->get_result();

// Lấy thống kê
$stats_sql = "SELECT 
    COUNT(*) as total_products,
    SUM(stock_quantity) as total_stock,
    SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
    SUM(CASE WHEN stock_quantity <= 10 AND stock_quantity > 0 THEN 1 ELSE 0 END) as low_stock,
    SUM(sold_quantity) as total_sold
    FROM products";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// Lấy danh sách danh mục cho form cập nhật
$categories_sql = "SELECT * FROM categories";
$categories_result = $conn->query($categories_sql);
$categories = [];
while ($category = $categories_result->fetch_assoc()) {
    $categories[] = $category;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm - HKT Store</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/admin_products.css">
    <link rel="stylesheet" href="css/admin_products_enhanced.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <main>
    <div class="container">
        <h1>Quản Lý Sản Phẩm</h1>
        
        <!-- Thống kê nhanh -->
        <div class="stats-container">
            <div class="stat-card">
                <i class="fas fa-box" style="color:#667eea;"></i>
                <div>
                    <h3><?php echo number_format($stats['total_products']); ?></h3>
                    <p>Tổng sản phẩm</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-warehouse" style="color:#28a745;"></i>
                <div>
                    <h3><?php echo number_format($stats['total_stock']); ?></h3>
                    <p>Tổng tồn kho</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-exclamation-triangle" style="color:#ffc107;"></i>
                <div>
                    <h3><?php echo number_format($stats['low_stock']); ?></h3>
                    <p>Sắp hết hàng</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-times-circle" style="color:#dc3545;"></i>
                <div>
                    <h3><?php echo number_format($stats['out_of_stock']); ?></h3>
                    <p>Hết hàng</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-shopping-cart" style="color:#17a2b8;"></i>
                <div>
                    <h3><?php echo number_format($stats['total_sold']); ?></h3>
                    <p>Đã bán</p>
                </div>
            </div>
        </div>

        <!-- Thanh tìm kiếm và lọc -->
        <div class="filter-bar">
            <form method="GET" action="" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <div style="flex:1;min-width:250px;">
                    <input type="text" name="search" placeholder="Tìm theo tên hoặc mã sản phẩm..." 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           style="width:100%;padding:12px 15px;border:2px solid #e0e0e0;border-radius:8px;font-size:14px;">
                </div>
                <select name="category" style="padding:12px 15px;border:2px solid #e0e0e0;border-radius:8px;font-size:14px;">
                    <option value="0">Tất cả danh mục</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>" 
                                <?php echo $category_filter == $cat['category_id'] ? 'selected' : ''; ?>>
                            <?php echo $cat['category_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="stock" style="padding:12px 15px;border:2px solid #e0e0e0;border-radius:8px;font-size:14px;">
                    <option value="">Tất cả tồn kho</option>
                    <option value="low" <?php echo $stock_filter === 'low' ? 'selected' : ''; ?>>Sắp hết (≤10)</option>
                    <option value="out" <?php echo $stock_filter === 'out' ? 'selected' : ''; ?>>Hết hàng</option>
                </select>
                <button type="submit" style="padding:12px 25px;background:#333333;color:#EBE9E5;border:2px solid #333333;border-radius:8px;cursor:pointer;font-weight:600;transition:all 0.3s ease;">
                    <i class="fas fa-search"></i> Lọc
                </button>
                <?php if($search || $category_filter || $stock_filter): ?>
                    <a href="admin_products.php" style="padding:12px 25px;background:#EBE9E5;color:#333333;text-decoration:none;border-radius:8px;font-weight:600;border:2px solid #333333;transition:all 0.3s ease;display:inline-block;">
                        <i class="fas fa-redo"></i> Xóa lọc
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="button-container">
            <a href="add_product.php" class="add-product-btn">
                <i class="fas fa-plus"></i> Thêm Sản Phẩm Mới
            </a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Sản Phẩm</th>
                    <th>Danh Mục</th>
                    <th>Giá</th>
                    <th>Số Lượng</th>
                    <th>Đã Bán</th>
                    <th>Hình Ảnh</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $row_count = 0;
                while($row = $result->fetch_assoc()): 
                    $row_count++;
                    $stock_status = '';
                    $stock_class = '';
                    if($row['stock_quantity'] == 0) {
                        $stock_status = 'Hết hàng';
                        $stock_class = 'stock-out';
                    } elseif($row['stock_quantity'] <= 10) {
                        $stock_status = 'Sắp hết';
                        $stock_class = 'stock-low';
                    }
                ?>
                    <tr>
                        <td><strong>#<?php echo $row['product_id']; ?></strong></td>
                        <td>
                            <div style="font-weight:600;color:#2c3e50;"><?php echo $row['product_name']; ?></div>
                            <small style="color:#7f8c8d;"><?php echo substr($row['description'], 0, 50); ?>...</small>
                        </td>
                        <td>
                            <span style="padding:5px 10px;background:#e3f2fd;color:#1976d2;border-radius:20px;font-size:12px;font-weight:600;">
                                <?php echo $row['category_name']; ?>
                            </span>
                        </td>
                        <td style="font-weight:700;color:#e74c3c;"><?php echo number_format($row['price'], 0, ',', '.'); ?>₫</td>
                        <td>
                            <span class="<?php echo $stock_class; ?>" style="padding:5px 12px;border-radius:20px;font-weight:600;font-size:13px;">
                                <?php echo $row['stock_quantity']; ?>
                                <?php if($stock_status): ?>
                                    <br><small style="font-size:11px;">(<?php echo $stock_status; ?>)</small>
                                <?php endif; ?>
                            </span>
                        </td>
                        <td><strong style="color:#27ae60;"><?php echo $row['sold_quantity']; ?></strong></td>
                        <td>
                            <img src="<?php echo $row['image_url']; ?>" 
                                 style="width:60px;height:60px;object-fit:cover;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);cursor:pointer;" 
                                 onclick="previewImage('<?php echo $row['image_url']; ?>', '<?php echo htmlspecialchars($row['product_name']); ?>')">
                        </td>
                        <td>
                            <button class="btn-edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($row)); ?>)" 
                                    style="padding:8px 15px;background:#333333;color:#EBE9E5;border:2px solid #333333;border-radius:6px;cursor:pointer;font-weight:600;margin-right:5px;transition:all 0.3s ease;">
                                <i class="fas fa-edit"></i> Sửa
                            </button>
                            <form method="POST" style="display: inline;" onsubmit="return confirmDelete('<?php echo $row['product_id']; ?>', '<?php echo htmlspecialchars($row['product_name']); ?>')">
                                <input type="hidden" name="delete_product" value="1">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <button type="submit" class="btn-delete" 
                                        style="padding:8px 15px;background:#dc3545;color:white;border:2px solid #dc3545;border-radius:6px;cursor:pointer;font-weight:600;transition:all 0.3s ease;">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if($row_count == 0): ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:40px;color:#7f8c8d;">
                            <i class="fas fa-inbox" style="font-size:48px;margin-bottom:15px;display:block;"></i>
                            <strong>Không tìm thấy sản phẩm nào</strong>
                            <?php if($search || $category_filter || $stock_filter): ?>
                                <br><small>Thử thay đổi điều kiện lọc của bạn</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Preview Ảnh -->
    <div id="imagePreviewModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width:800px;background:transparent;box-shadow:none;">
            <span class="close" onclick="closeImagePreview()" style="position:absolute;top:20px;right:40px;font-size:40px;color:white;cursor:pointer;z-index:1001;">&times;</span>
            <img id="previewImage" src="" alt="" style="width:100%;max-height:80vh;object-fit:contain;border-radius:12px;">
            <p id="previewTitle" style="text-align:center;color:white;margin-top:15px;font-size:18px;font-weight:600;"></p>
        </div>
    </div>

    <!-- Modal Sửa Sản Phẩm -->
    <div id="editModal" class="modal">
        <div class="modal-content" style="max-width:700px;">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2 style="margin-bottom:25px;color:#333333;border-bottom:3px solid #333333;padding-bottom:15px;">
                <i class="fas fa-edit"></i> Sửa Thông Tin Sản Phẩm
            </h2>
            <form id="editForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" id="edit_product_id">
                <input type="hidden" name="current_image" id="current_image">
                
                <!-- Hiển thị ảnh hiện tại -->
                <div class="form-group">
                    <label><i class="fas fa-image"></i> Ảnh hiện tại:</label>
                    <div style="text-align:center;margin-bottom:15px;">
                        <img id="current_product_image" src="" alt="Product Image" 
                             style="max-width:200px;max-height:200px;border-radius:8px;border:2px solid #EBE9E5;">
                    </div>
                </div>
                
                <!-- Upload ảnh mới -->
                <div class="form-group">
                    <label><i class="fas fa-upload"></i> Thay đổi ảnh (tùy chọn):</label>
                    <div class="image-upload-area" id="imageUploadArea" onclick="document.getElementById('product_image_input').click();">
                        <input type="file" name="product_image" id="product_image_input" 
                               accept="image/*" style="display:none;" onchange="previewNewImage(this)">
                        <i class="fas fa-cloud-upload-alt upload-icon"></i>
                        <p class="upload-text">Nhấp để chọn ảnh mới</p>
                        <p class="upload-subtext">Hoặc kéo thả ảnh vào đây</p>
                        <img id="new_image_preview" src="" alt="" 
                             style="display:none;max-width:100%;max-height:200px;margin-top:15px;border-radius:8px;">
                    </div>
                </div>
                
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Tên sản phẩm:</label>
                        <input type="text" name="product_name" id="edit_product_name" required 
                               style="padding:12px;border:2px solid #e0e0e0;border-radius:8px;width:100%;">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-dollar-sign"></i> Giá:</label>
                        <input type="number" name="price" id="edit_price" required 
                               style="padding:12px;border:2px solid #e0e0e0;border-radius:8px;width:100%;">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-boxes"></i> Số lượng tồn:</label>
                        <input type="number" name="stock_quantity" id="edit_stock_quantity" required 
                               style="padding:12px;border:2px solid #e0e0e0;border-radius:8px;width:100%;">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-shopping-cart"></i> Đã bán:</label>
                        <input type="number" name="sold_quantity" id="edit_sold_quantity" required 
                               style="padding:12px;border:2px solid #e0e0e0;border-radius:8px;width:100%;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-list"></i> Danh mục:</label>
                    <select name="category_id" id="edit_category_id" required 
                            style="padding:12px;border:2px solid #e0e0e0;border-radius:8px;width:100%;">
                        <?php foreach($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo $category['category_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Mô tả:</label>
                    <textarea name="description" id="edit_description" rows="4" 
                              style="padding:12px;border:2px solid #e0e0e0;border-radius:8px;width:100%;resize:vertical;"></textarea>
                </div>
                
                <button type="submit" name="update_product" 
                        style="width:100%;padding:15px;background:#333333;color:#EBE9E5;border:2px solid #333333;border-radius:8px;font-size:16px;font-weight:700;cursor:pointer;margin-top:10px;transition:all 0.3s ease;">
                    <i class="fas fa-save"></i> Cập Nhật Sản Phẩm
                </button>
            </form>
        </div>
    </div>

    <script>
        // Preview ảnh sản phẩm
        function previewImage(imageUrl, productName) {
            document.getElementById('previewImage').src = imageUrl;
            document.getElementById('previewTitle').textContent = productName;
            document.getElementById('imagePreviewModal').style.display = 'flex';
        }

        function closeImagePreview() {
            document.getElementById('imagePreviewModal').style.display = 'none';
        }

        // Xử lý xóa sản phẩm
        function confirmDelete(productId, productName) {
            if (confirm('⚠️ BẠN CÓ CHẮC CHẮN MUỐN XÓA?\n\nSản phẩm: ' + productName + '\nMã: ' + productId + '\n\nHành động này không thể hoàn tác!')) {
                return true;
            }
            return false;
        }

        // Xử lý modal sửa sản phẩm
        function openEditModal(product) {
            const modal = document.getElementById('editModal');
            modal.style.display = 'flex';
            
            // Điền thông tin vào form
            document.getElementById('edit_product_id').value = product.product_id;
            document.getElementById('edit_product_name').value = product.product_name;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_stock_quantity').value = product.stock_quantity;
            document.getElementById('edit_sold_quantity').value = product.sold_quantity;
            document.getElementById('edit_category_id').value = product.category_id;
            document.getElementById('edit_description').value = product.description;
            
            // Hiển thị ảnh hiện tại
            document.getElementById('current_product_image').src = product.image_url;
            document.getElementById('current_image').value = product.image_url;
            
            // Reset preview ảnh mới
            document.getElementById('new_image_preview').style.display = 'none';
            document.getElementById('new_image_preview').src = '';
        }

        function previewNewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('new_image_preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Drag & Drop support
        const uploadArea = document.getElementById('imageUploadArea');
        if (uploadArea) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, () => {
                    uploadArea.classList.add('drag-over');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, () => {
                    uploadArea.classList.remove('drag-over');
                }, false);
            });

            uploadArea.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                document.getElementById('product_image_input').files = files;
                previewNewImage(document.getElementById('product_image_input'));
            }, false);
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
    </main>

    <?php include 'admin_footer.php'; ?>
</body>
</html>

<?php
$conn->close();
?>
