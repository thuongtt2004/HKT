<?php
// Thông tin kết nối database
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'user_management');

// Kết nối đến MySQL database
try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Kiểm tra kết nối
    if ($conn->connect_error) {
        throw new Exception("Không thể kết nối đến database: " . $conn->connect_error);
    }

    // Đặt charset là utf8
    $conn->set_charset("utf8");
    
} catch(Exception $e) {
    die("Lỗi: " . $e->getMessage());
}

// Hàm bảo vệ dữ liệu đầu vào
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Hàm kiểm tra người dùng đã đăng nhập chưa
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Hàm chuyển hướng nếu chưa đăng nhập
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /login.php");
        exit();
    }
}
?> 