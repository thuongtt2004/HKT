<?php
session_start();
require_once '../config/connect.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Lấy danh sách danh mục với phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sql = "SELECT c.*, COUNT(p.product_id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.category_id = p.category_id 
        GROUP BY c.category_id 
        ORDER BY c.category_name 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$categories = $stmt->get_result();

// Tổng số danh mục
$total = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];
$total_pages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Danh Mục - HKT Store</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <h1>Quản Lý Danh Mục</h1>
            
            <div class="actions">
                <button onclick="showAddForm()" class="btn-add">Thêm Danh Mục Mới</button>
            </div>

            <div class="category-list">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên danh mục</th>
                            <th>Mô tả</th>
                            <th>Số sản phẩm</th>
                            <th>Thứ tự hiển thị</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($category = $categories->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $category['category_id']; ?></td>
                            <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($category['description']); ?></td>
                            <td><?php echo $category['product_count']; ?></td>
                            <td>
                                <input type="number" 
                                       value="<?php echo $category['display_order']; ?>"
                                       onchange="updateDisplayOrder(<?php echo $category['category_id']; ?>, this.value)"
                                       min="0" 
                                       class="order-input">
                            </td>
                            <td>
                                <select onchange="updateCategoryStatus(<?php echo $category['category_id']; ?>, this.value)"
                                        class="status-select">
                                    <option value="1" <?php echo $category['status'] == 1 ? 'selected' : ''; ?>>
                                        Hiển thị
                                    </option>
                                    <option value="0" <?php echo $category['status'] == 0 ? 'selected' : ''; ?>>
                                        Ẩn
                                    </option>
                                </select>
                            </td>
                            <td>
                                <button onclick="editCategory(<?php echo $category['category_id']; ?>)" 
                                        class="btn-edit">Sửa</button>
                                <?php if($category['product_count'] == 0): ?>
                                    <button onclick="deleteCategory(<?php echo $category['category_id']; ?>)" 
                                            class="btn-delete">Xóa</button>
                                <?php endif; ?>
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

            <!-- Form thêm/sửa danh mục -->
            <div id="categoryForm" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close" onclick="closeForm()">&times;</span>
                    <h2 id="formTitle">Thêm Danh Mục Mới</h2>
                    <form id="categoryFormElement">
                        <input type="hidden" id="category_id" name="category_id">
                        
                        <div class="form-group">
                            <label for="category_name">Tên danh mục:</label>
                            <input type="text" id="category_name" name="category_name" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Mô tả:</label>
                            <textarea id="description" name="description" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="display_order">Thứ tự hiển thị:</label>
                            <input type="number" id="display_order" name="display_order" min="0" value="0">
                        </div>

                        <div class="form-group">
                            <label for="status">Trạng thái:</label>
                            <select id="status" name="status">
                                <option value="1">Hiển thị</option>
                                <option value="0">Ẩn</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-submit">Lưu</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function showAddForm() {
            document.getElementById('formTitle').textContent = 'Thêm Danh Mục Mới';
            document.getElementById('categoryFormElement').reset();
            document.getElementById('categoryForm').style.display = 'block';
        }

        async function editCategory(categoryId) {
            document.getElementById('formTitle').textContent = 'Sửa Danh Mục';
            
            try {
                const response = await fetch(`get_category.php?id=${categoryId}`);
                const data = await response.json();
                
                document.getElementById('category_id').value = data.category_id;
                document.getElementById('category_name').value = data.category_name;
                document.getElementById('description').value = data.description;
                document.getElementById('display_order').value = data.display_order;
                document.getElementById('status').value = data.status;
                
                document.getElementById('categoryForm').style.display = 'block';
            } catch (error) {
                alert('Có l���i xảy ra khi tải thông tin danh mục!');
            }
        }

        async function updateDisplayOrder(categoryId, order) {
            try {
                const response = await fetch('xu_ly_category.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_order',
                        category_id: categoryId,
                        display_order: order
                    })
                });

                const data = await response.json();
                if(!data.success) {
                    alert(data.message || 'Có lỗi xảy ra!');
                }
            } catch (error) {
                alert('Có lỗi xảy ra!');
            }
        }

        async function updateCategoryStatus(categoryId, status) {
            try {
                const response = await fetch('xu_ly_category.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_status',
                        category_id: categoryId,
                        status: status
                    })
                });

                const data = await response.json();
                if(!data.success) {
                    alert(data.message || 'Có lỗi xảy ra!');
                }
            } catch (error) {
                alert('Có lỗi xảy ra!');
            }
        }

        async function deleteCategory(categoryId) {
            if(!confirm('Bạn có chắc muốn xóa danh mục này?')) {
                return;
            }

            try {
                const response = await fetch('xu_ly_category.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        category_id: categoryId
                    })
                });

                const data = await response.json();
                if(data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra!');
                }
            } catch (error) {
                alert('Có lỗi xảy ra!');
            }
        }

        function closeForm() {
            document.getElementById('categoryForm').style.display = 'none';
        }

        // Xử lý submit form
        document.getElementById('categoryFormElement').onsubmit = async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            try {
                const response = await fetch('xu_ly_category.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                if(data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra!');
                }
            } catch (error) {
                alert('Có lỗi xảy ra!');
            }
        };
    </script>
</body>
</html> 