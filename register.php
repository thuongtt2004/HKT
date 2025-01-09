<?php
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize_input($_POST['username']);
    $password = password_hash(sanitize_input($_POST['password']), PASSWORD_DEFAULT);
    $email = sanitize_input($_POST['email']);
    $full_name = sanitize_input($_POST['full_name']);
    
    // Kiểm tra username đã tồn tại chưa
    $check_sql = "SELECT * FROM administrators WHERE username = '$username'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows > 0) {
        $error_message = "Tên đăng nhập đã tồn tại";
    } else {
        // Thêm admin mới
        $sql = "INSERT INTO administrators (username, password, email, full_name) 
                VALUES ('$username', '$password', '$email', '$full_name')";
        
        if ($conn->query($sql) === TRUE) {
            $success_message = "Đăng ký thành công";
        } else {
            $error_message = "Lỗi: " . $conn->error;
        }
    }
}
?> 