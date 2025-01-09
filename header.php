<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <div class="logo">
        <img src="./images/logoo.jpg" width="120" height="124" align="left" alt="Logo">
    </div>
    <h1>TTHUONG Store</h1>

    <div class="user-section">
        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="user-info">
                <i class="fas fa-user"></i>
                <span>Xin chào, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="dangxuat.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
            </div>
        <?php else: ?>
            <div class="auth-buttons">
                <a href="dangnhap.php" class="login-btn"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                <a href="dangky.php" class="register-btn"><i class="fas fa-user-plus"></i> Đăng ký</a>
            </div>
        <?php endif; ?>
    </div>

    <nav>
        <ul>
            <li><a href="trangchu.php">Trang chủ</a></li>
            <li><a href="sanpham.php">Sản phẩm</a></li>
            <li><a href="dathang.php">Đặt hàng</a></li>
            <li><a href="baohanh.php">Chính sách bảo hành</a></li>
            <li><a href="theodoidonhang.php">Theo dõi đơn hàng</a></li>
            <li><a href="danhgia.php">Đánh giá</a></li>
            <li><a href="#contact">Thông tin liên hệ</a></li>
        </ul>
    </nav>
</header>

<style>
.user-section {
    position: absolute;
    top: 10px;
    right: 20px;
    text-align: right;
}

.user-info {
    background: #333;
    color: #EBE9E5;
    padding: 8px 15px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.user-info i {
    color: #EBE9E5;
}

.logout-btn {
    color: #EBE9E5;
    text-decoration: none;
    padding: 5px 10px;
    border-radius: 15px;
    transition: all 0.3s ease;
}

.logout-btn:hover {
    background: #ff4444;
}

.auth-buttons {
    display: flex;
    gap: 10px;
}

.login-btn, .register-btn {
    color: #333;
    text-decoration: none;
    padding: 5px 15px;
    border-radius: 15px;
    transition: all 0.3s ease;
    background: #EBE9E5;
}

.login-btn:hover, .register-btn:hover {
    background: #333;
    color: #EBE9E5;
}

nav ul {
    margin-top: 20px;
}

nav ul li a {
    padding: 8px 15px;
    border-radius: 15px;
    transition: all 0.3s ease;
}

nav ul li a:hover {
    background: #333;
    color: #EBE9E5;
}
</style>

<!-- Thêm Font Awesome cho icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">