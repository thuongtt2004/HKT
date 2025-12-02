<?php
session_start();
require_once 'config/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    $update_sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssi", $full_name, $email, $phone, $address, $user_id);
    
    if ($update_stmt->execute()) {
        $success_message = "Cập nhật thông tin thành công!";
    } else {
        $error_message = "Có lỗi xảy ra. Vui lòng thử lại!";
    }
}

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Kiểm tra mật khẩu hiện tại
    $check_sql = "SELECT password FROM users WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $user_data = $result->fetch_assoc();
    
    if (password_verify($current_password, $user_data['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_pass_sql = "UPDATE users SET password = ? WHERE user_id = ?";
            $update_pass_stmt = $conn->prepare($update_pass_sql);
            $update_pass_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_pass_stmt->execute()) {
                $success_message = "Đổi mật khẩu thành công!";
            }
        } else {
            $error_message = "Mật khẩu mới không khớp!";
        }
    } else {
        $error_message = "Mật khẩu hiện tại không đúng!";
    }
}

// Lấy thông tin user
$user_sql = "SELECT * FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Lấy danh sách wishlist
$wishlist_sql = "SELECT w.*, p.product_name, p.price, p.image_url 
                 FROM wishlist w 
                 JOIN products p ON w.product_id = p.product_id 
                 WHERE w.user_id = ? 
                 ORDER BY w.added_date DESC";
$wishlist_stmt = $conn->prepare($wishlist_sql);
$wishlist_stmt->bind_param("i", $user_id);
$wishlist_stmt->execute();
$wishlist_items = $wishlist_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang cá nhân - HKT Store</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .profile-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #ebe9e5;
        }
        
        .tab-btn {
            padding: 15px 30px;
            border: none;
            background: none;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab-btn.active {
            color: #333;
            border-bottom-color: #dc3545;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
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
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .btn-save {
            background: #333;
            color: #ebe9e5;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-save:hover {
            background: #555;
            transform: translateY(-2px);
        }
        
        .btn-edit {
            background: #333;
            color: #ebe9e5;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-edit:hover {
            background: #555;
        }
        
        .btn-cancel {
            background: #dc3545;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-cancel:hover {
            background: #c82333;
        }
        
        .info-row {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            flex: 0 0 180px;
            font-weight: 600;
            color: #333;
        }
        
        .info-label i {
            margin-right: 8px;
            color: #555;
        }
        
        .info-value {
            flex: 1;
            color: #555;
        }
        
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .wishlist-item {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            position: relative;
        }
        
        .wishlist-item:hover {
            transform: translateY(-5px);
        }
        
        .wishlist-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .wishlist-info {
            padding: 15px;
        }
        
        .wishlist-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .wishlist-price {
            color: #dc3545;
            font-size: 18px;
            font-weight: 700;
        }
        
        .remove-wishlist {
            position: absolute;
            top: 10px;
            right: 10px;
            background: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }
        
        .remove-wishlist:hover {
            background: #dc3545;
            color: white;
            transform: scale(1.1);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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
        
        .empty-wishlist {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-wishlist i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="profile-container">
        <h1><i class="fas fa-user-circle"></i> Trang cá nhân</h1>
        
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
        
        <div class="profile-tabs">
            <button class="tab-btn active" onclick="showTab('info')">
                <i class="fas fa-user"></i> Thông tin cá nhân
            </button>
            <button class="tab-btn" onclick="showTab('wishlist')">
                <i class="fas fa-heart"></i> Yêu thích (<?php echo $wishlist_items->num_rows; ?>)
            </button>
            <button class="tab-btn" onclick="showTab('password')">
                <i class="fas fa-lock"></i> Đổi mật khẩu
            </button>
        </div>
        
        <!-- Tab Thông tin -->
        <div id="info" class="tab-content active">
            <div class="profile-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2>Thông tin cá nhân</h2>
                    <button type="button" class="btn-edit" onclick="toggleEditMode()" id="editBtn">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </button>
                </div>
                
                <!-- View Mode (default) -->
                <div id="viewMode">
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-user"></i> Họ và tên:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['full_name'] ?? 'Chưa cập nhật'); ?></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-envelope"></i> Email:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['email'] ?? 'Chưa cập nhật'); ?></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-phone"></i> Số điện thoại:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'Chưa cập nhật'); ?></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-map-marker-alt"></i> Địa chỉ:</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['address'] ?? 'Chưa cập nhật'); ?></div>
                    </div>
                </div>
                
                <!-- Edit Mode (hidden by default) -->
                <form method="POST" id="editMode" style="display: none;">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Họ và tên</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Số điện thoại</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Địa chỉ</label>
                        <textarea name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="update_profile" class="btn-save">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                        <button type="button" class="btn-cancel" onclick="toggleEditMode()">
                            <i class="fas fa-times"></i> Hủy
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tab Wishlist -->
        <div id="wishlist" class="tab-content">
            <?php if ($wishlist_items->num_rows > 0): ?>
                <div class="wishlist-grid">
                    <?php while ($item = $wishlist_items->fetch_assoc()): ?>
                        <div class="wishlist-item">
                            <button class="remove-wishlist" onclick="removeFromWishlist('<?php echo $item['product_id']; ?>')">
                                <i class="fas fa-times"></i>
                            </button>
                            <a href="sanpham.php?product_id=<?php echo $item['product_id']; ?>">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                            </a>
                            <div class="wishlist-info">
                                <div class="wishlist-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <div class="wishlist-price"><?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-wishlist">
                    <i class="fas fa-heart-broken"></i>
                    <h2>Chưa có sản phẩm yêu thích</h2>
                    <p>Hãy thêm sản phẩm vào danh sách yêu thích để xem sau</p>
                    <a href="home.php" class="btn-save" style="display: inline-block; text-decoration: none; margin-top: 20px;">
                        <i class="fas fa-shopping-bag"></i> Khám phá sản phẩm
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Tab Đổi mật khẩu -->
        <div id="password" class="tab-content">
            <div class="profile-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2>Đổi mật khẩu</h2>
                    <button type="button" class="btn-edit" onclick="togglePasswordMode()" id="passwordBtn">
                        <i class="fas fa-key"></i> Đổi mật khẩu
                    </button>
                </div>
                
                <!-- View Mode (default) -->
                <div id="passwordViewMode">
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-lock"></i> Mật khẩu:</div>
                        <div class="info-value">••••••••</div>
                    </div>
                    <p style="color: #777; font-size: 14px; margin-top: 15px;">
                        <i class="fas fa-info-circle"></i> Nhấn nút "Đổi mật khẩu" để thay đổi mật khẩu của bạn
                    </p>
                </div>
                
                <!-- Edit Mode (hidden by default) -->
                <form method="POST" id="passwordEditMode" style="display: none;">
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Mật khẩu hiện tại</label>
                        <input type="password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-key"></i> Mật khẩu mới</label>
                        <input type="password" name="new_password" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-check"></i> Xác nhận mật khẩu mới</label>
                        <input type="password" name="confirm_password" required minlength="6">
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="change_password" class="btn-save">
                            <i class="fas fa-save"></i> Lưu mật khẩu
                        </button>
                        <button type="button" class="btn-cancel" onclick="togglePasswordMode()">
                            <i class="fas fa-times"></i> Hủy
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.closest('.tab-btn').classList.add('active');
        }
        
        function toggleEditMode() {
            const viewMode = document.getElementById('viewMode');
            const editMode = document.getElementById('editMode');
            const editBtn = document.getElementById('editBtn');
            
            if (viewMode.style.display === 'none') {
                // Switch to view mode
                viewMode.style.display = 'block';
                editMode.style.display = 'none';
                editBtn.innerHTML = '<i class="fas fa-edit"></i> Chỉnh sửa';
            } else {
                // Switch to edit mode
                viewMode.style.display = 'none';
                editMode.style.display = 'block';
                editBtn.innerHTML = '<i class="fas fa-eye"></i> Xem';
            }
        }
        
        function togglePasswordMode() {
            const viewMode = document.getElementById('passwordViewMode');
            const editMode = document.getElementById('passwordEditMode');
            const passwordBtn = document.getElementById('passwordBtn');
            
            if (viewMode.style.display === 'none') {
                // Switch to view mode
                viewMode.style.display = 'block';
                editMode.style.display = 'none';
                passwordBtn.innerHTML = '<i class="fas fa-key"></i> Đổi mật khẩu';
                // Clear password fields
                editMode.querySelectorAll('input[type="password"]').forEach(input => input.value = '');
            } else {
                // Switch to edit mode
                viewMode.style.display = 'none';
                editMode.style.display = 'block';
                passwordBtn.innerHTML = '<i class="fas fa-eye"></i> Xem';
            }
        }
        
        function removeFromWishlist(productId) {
            if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi danh sách yêu thích?')) {
                return;
            }
            
            fetch('toggle_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    product_id: productId,
                    action: 'remove'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra!');
            });
        }
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>

<?php $conn->close(); ?>
