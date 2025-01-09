<?php
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize_input($_POST['username']);
    $password = sanitize_input($_POST['password']);
    
    // Kiểm tra thông tin đăng nhập
    $sql = "SELECT * FROM administrators WHERE username = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Đăng nhập thành công
            session_start();
            $_SESSION['admin_id'] = $row['admin_id'];
            $_SESSION['username'] = $row['username'];
            
            // Cập nhật thời gian đăng nhập
            $update_sql = "UPDATE administrators SET last_login = CURRENT_TIMESTAMP WHERE admin_id = " . $row['admin_id'];
            $conn->query($update_sql);
            
            header("Location: admin_dashboard.php");
            exit();
        }
    }
    
    // Đăng nhập thất bại
    $error_message = "Tên đăng nhập hoặc mật khẩu không đúng";
}
?>