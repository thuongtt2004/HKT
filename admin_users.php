<?php
session_start();
require_once 'config/connect.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login_page.php');
    exit();
}

// Xử lý xóa người dùng
if (isset($_POST['delete_user'])) {
    $user_id = intval($_POST['user_id']);
    $delete_sql = "DELETE FROM users WHERE user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $user_id);
    
    if ($delete_stmt->execute()) {
        $success_message = "Xóa người dùng thành công!";
    } else {
        $error_message = "Lỗi khi xóa người dùng: " . $conn->error;
    }
    $delete_stmt->close();
}

// Xử lý thêm người dùng
if (isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $status = intval($_POST['status']);
    
    $insert_sql = "INSERT INTO users (username, email, password, full_name, phone, address, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ssssssi", $username, $email, $password, $full_name, $phone, $address, $status);
    
    if ($insert_stmt->execute()) {
        $success_message = "Thêm người dùng thành công!";
    } else {
        $error_message = "Lỗi khi thêm người dùng: " . $conn->error;
    }
    $insert_stmt->close();
}

// Xử lý cập nhật người dùng
if (isset($_POST['update_user'])) {
    $user_id = intval($_POST['user_id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $status = intval($_POST['status']);
    
    $update_sql = "UPDATE users SET username = ?, email = ?, full_name = ?, phone = ?, address = ?, status = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssssii", $username, $email, $full_name, $phone, $address, $status, $user_id);
    
    if ($update_stmt->execute()) {
        $success_message = "Cập nhật người dùng thành công!";
    } else {
        $error_message = "Lỗi khi cập nhật: " . $conn->error;
    }
    $update_stmt->close();
}

// Xử lý khóa/mở khóa người dùng
if (isset($_POST['toggle_status'])) {
    $user_id = intval($_POST['user_id']);
    $current_status = intval($_POST['current_status']);
    $new_status = $current_status == 1 ? 0 : 1;
    
    $toggle_sql = "UPDATE users SET status = ? WHERE user_id = ?";
    $toggle_stmt = $conn->prepare($toggle_sql);
    $toggle_stmt->bind_param("ii", $new_status, $user_id);
    
    if ($toggle_stmt->execute()) {
        $success_message = $new_status == 1 ? "Mở khóa người dùng thành công!" : "Khóa người dùng thành công!";
    } else {
        $error_message = "Lỗi khi thay đổi trạng thái: " . $conn->error;
    }
    $toggle_stmt->close();
}

// Xử lý tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

$sql = "SELECT * FROM users WHERE 1=1";

if (!empty($search)) {
    $search_param = "%$search%";
    $sql .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ? OR phone LIKE ?)";
}

if ($status_filter !== 'all') {
    $sql .= " AND status = " . intval($status_filter);
}

$sql .= " ORDER BY user_id DESC";

$stmt = $conn->prepare($sql);

if (!empty($search)) {
    $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng - HKT Store</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .users-container {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h1 {
            color: #333;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-add {
            background: #28a745;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-add:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .search-filter-bar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .search-box {
            flex: 1;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 40px 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #333;
        }

        .search-box button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: #333;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
        }

        .filter-select {
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            min-width: 150px;
        }

        .users-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .users-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th {
            background: #333;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .users-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .users-table tr:hover {
            background: #f8f9fa;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status.active {
            background: #d4edda;
            color: #155724;
        }

        .status.inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-edit, .btn-delete, .btn-lock, .btn-unlock {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-edit {
            background: #007bff;
            color: white;
        }

        .btn-edit:hover {
            background: #0056b3;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .btn-lock {
            background: #ffc107;
            color: #333;
        }

        .btn-lock:hover {
            background: #e0a800;
        }

        .btn-unlock {
            background: #28a745;
            color: white;
        }

        .btn-unlock:hover {
            background: #218838;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            overflow-y: auto;
        }

        .modal-content {
            background: white;
            margin: 50px auto;
            padding: 30px;
            width: 90%;
            max-width: 600px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
        }

        .modal-header h2 {
            margin: 0;
            color: #333;
        }

        .close {
            font-size: 32px;
            color: #aaa;
            cursor: pointer;
            line-height: 1;
        }

        .close:hover {
            color: #000;
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }

        .btn-submit {
            background: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-submit:hover {
            background: #0056b3;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-cancel:hover {
            background: #545b62;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .no-users {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .no-users i {
            font-size: 64px;
            margin-bottom: 20px;
            display: block;
            color: #ddd;
        }
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <main>
        <div class="users-container">
            <div class="page-header">
                <h1><i class="fas fa-users"></i> Quản lý người dùng</h1>
                <button class="btn-add" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Thêm người dùng
                </button>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="search-filter-bar">
                <div class="search-box">
                    <form method="GET" action="">
                        <input type="text" 
                               name="search" 
                               placeholder="Tìm kiếm theo tên, email, số điện thoại..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <select class="filter-select" onchange="window.location.href='?status=' + this.value + '<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>'">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Tất cả trạng thái</option>
                    <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Hoạt động</option>
                    <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Đã khóa</option>
                </select>
            </div>

            <div class="users-table">
                <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên đăng nhập</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Ngày đăng ký</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $result->fetch_assoc()): 
                            $status = isset($user['status']) ? $user['status'] : 1;
                            $status_class = $status == 1 ? 'active' : 'inactive';
                            $status_text = $status == 1 ? 'Hoạt động' : 'Đã khóa';
                        ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone'] ?? ''); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td><span class="status <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-edit" onclick='editUser(<?php echo json_encode($user); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận <?php echo $status == 1 ? 'khóa' : 'mở khóa'; ?> người dùng này?')">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        <input type="hidden" name="current_status" value="<?php echo $status; ?>">
                                        <button type="submit" name="toggle_status" class="<?php echo $status == 1 ? 'btn-lock' : 'btn-unlock'; ?>">
                                            <i class="fas fa-<?php echo $status == 1 ? 'lock' : 'unlock'; ?>"></i>
                                        </button>
                                    </form>
                                    
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận xóa người dùng này? Hành động này không thể hoàn tác!')">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        <button type="submit" name="delete_user" class="btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-users">
                    <i class="fas fa-user-slash"></i>
                    <p>Không tìm thấy người dùng nào</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal Thêm/Sửa Người Dùng -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle"><i class="fas fa-user-plus"></i> Thêm người dùng</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            
            <form method="POST" id="userForm">
                <input type="hidden" name="user_id" id="user_id">
                
                <div class="form-group">
                    <label>Tên đăng nhập <span style="color:red;">*</span></label>
                    <input type="text" name="username" id="username" required>
                </div>
                
                <div class="form-group">
                    <label>Email <span style="color:red;">*</span></label>
                    <input type="email" name="email" id="email" required>
                </div>
                
                <div class="form-group" id="passwordField">
                    <label>Mật khẩu <span style="color:red;">*</span></label>
                    <input type="password" name="password" id="password">
                </div>
                
                <div class="form-group">
                    <label>Họ và tên</label>
                    <input type="text" name="full_name" id="full_name">
                </div>
                
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" id="phone">
                </div>
                
                <div class="form-group">
                    <label>Địa chỉ</label>
                    <textarea name="address" id="address"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Trạng thái <span style="color:red;">*</span></label>
                    <select name="status" id="status" required>
                        <option value="1">Hoạt động</option>
                        <option value="0">Khóa</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Hủy</button>
                    <button type="submit" id="submitBtn" name="add_user" class="btn-submit">
                        <i class="fas fa-save"></i> Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('userModal').style.display = 'block';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Thêm người dùng';
            document.getElementById('userForm').reset();
            document.getElementById('user_id').value = '';
            document.getElementById('passwordField').style.display = 'block';
            document.getElementById('password').required = true;
            document.getElementById('submitBtn').name = 'add_user';
        }

        function editUser(user) {
            document.getElementById('userModal').style.display = 'block';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-edit"></i> Sửa người dùng';
            
            document.getElementById('user_id').value = user.user_id;
            document.getElementById('username').value = user.username;
            document.getElementById('email').value = user.email;
            document.getElementById('full_name').value = user.full_name || '';
            document.getElementById('phone').value = user.phone || '';
            document.getElementById('address').value = user.address || '';
            document.getElementById('status').value = user.status || 1;
            
            document.getElementById('passwordField').style.display = 'none';
            document.getElementById('password').required = false;
            document.getElementById('submitBtn').name = 'update_user';
        }

        function closeModal() {
            document.getElementById('userModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('userModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>

    <?php include 'admin_footer.php'; ?>
</body>
</html>

<?php
$conn->close();
?>
