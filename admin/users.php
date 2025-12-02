<?php
session_start();
require_once '../config/connect.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Lấy danh sách người dùng với phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Tìm kiếm nếu có
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where_clause = $search ? "WHERE username LIKE ? OR email LIKE ? OR phone LIKE ?" : "";

$sql = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

if($search) {
    $search = "%$search%";
    $stmt->bind_param("sssii", $search, $search, $search, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$users = $stmt->get_result();

// Tổng số người dùng
$total = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_pages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng - HKT Store</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <h1>Quản Lý Người Dùng</h1>
            
            <div class="actions">
                <form class="search-form">
                    <input type="text" name="search" placeholder="Tìm kiếm người dùng..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">Tìm kiếm</button>
                </form>
                <button onclick="showAddForm()" class="btn-add">Thêm Người Dùng</button>
            </div>

            <div class="user-list">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên đăng nhập</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Địa chỉ</th>
                            <th>Ngày tạo</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['address']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <select onchange="updateUserStatus(<?php echo $user['user_id']; ?>, this.value)"
                                        class="status-select">
                                    <option value="1" <?php echo $user['status'] == 1 ? 'selected' : ''; ?>>
                                        Hoạt động
                                    </option>
                                    <option value="0" <?php echo $user['status'] == 0 ? 'selected' : ''; ?>>
                                        Khóa
                                    </option>
                                </select>
                            </td>
                            <td>
                                <button onclick="editUser(<?php echo $user['user_id']; ?>)" 
                                        class="btn-edit">Sửa</button>
                                <button onclick="deleteUser(<?php echo $user['user_id']; ?>)" 
                                        class="btn-delete">Xóa</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Phân trang -->
                <div class="pagination">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.$search : ''; ?>" 
                           class="<?php echo $page == $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Form thêm/sửa người dùng -->
            <div id="userForm" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close" onclick="closeForm()">&times;</span>
                    <h2 id="formTitle">Thêm Người Dùng Mới</h2>
                    <form id="userFormElement">
                        <input type="hidden" id="user_id" name="user_id">
                        
                        <div class="form-group">
                            <label for="username">Tên đăng nhập:</label>
                            <input type="text" id="username" name="username" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Mật khẩu:</label>
                            <input type="password" id="password" name="password">
                            <small>Để trống nếu không muốn thay đổi mật khẩu</small>
                        </div>

                        <div class="form-group">
                            <label for="full_name">Họ tên:</label>
                            <input type="text" id="full_name" name="full_name" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Số điện thoại:</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>

                        <div class="form-group">
                            <label for="address">Địa chỉ:</label>
                            <textarea id="address" name="address" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="status">Trạng thái:</label>
                            <select id="status" name="status">
                                <option value="1">Hoạt động</option>
                                <option value="0">Khóa</option>
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
            document.getElementById('formTitle').textContent = 'Thêm Người Dùng Mới';
            document.getElementById('userFormElement').reset();
            document.getElementById('userForm').style.display = 'block';
        }

        async function editUser(userId) {
            document.getElementById('formTitle').textContent = 'Sửa Thông Tin Người Dùng';
            
            try {
                const response = await fetch(`get_user.php?id=${userId}`);
                const data = await response.json();
                
                document.getElementById('user_id').value = data.user_id;
                document.getElementById('username').value = data.username;
                document.getElementById('full_name').value = data.full_name;
                document.getElementById('email').value = data.email;
                document.getElementById('phone').value = data.phone;
                document.getElementById('address').value = data.address;
                document.getElementById('status').value = data.status;
                
                document.getElementById('userForm').style.display = 'block';
            } catch (error) {
                alert('Có lỗi xảy ra khi tải thông tin người dùng!');
            }
        }

        async function updateUserStatus(userId, status) {
            if(!confirm('Bạn có chắc muốn thay đổi trạng thái người dùng này?')) {
                return;
            }

            try {
                const response = await fetch('xu_ly_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'update_status',
                        user_id: userId,
                        status: status
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

        async function deleteUser(userId) {
            if(!confirm('Bạn có chắc muốn xóa người dùng này?')) {
                return;
            }

            try {
                const response = await fetch('xu_ly_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        user_id: userId
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
            document.getElementById('userForm').style.display = 'none';
        }

        // Xử lý submit form
        document.getElementById('userFormElement').onsubmit = async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            try {
                const response = await fetch('xu_ly_user.php', {
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