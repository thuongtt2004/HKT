<?php
session_start();
require_once('config/connect.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Thêm debug log
        error_log("Attempting login with username: " . $username);
        
        // Kiểm tra trong bảng administrators
        $admin_stmt = $conn->prepare("SELECT * FROM administrators WHERE username = ?");
        $admin_stmt->bind_param("s", $username);
        $admin_stmt->execute();
        $admin_result = $admin_stmt->get_result();
        $admin = $admin_result->fetch_assoc();

        if ($admin) {
            error_log("Admin found: " . print_r($admin, true));
            // Tạo password hash để kiểm tra
            $hash = password_hash('123456', PASSWORD_DEFAULT);
            error_log("Generated hash for 123456: " . $hash);
            error_log("Stored password hash: " . $admin['password']);
            error_log("Password verify result: " . (password_verify($password, $admin['password']) ? 'true' : 'false'));
        }

        // Kiểm tra trong bảng users
        $user_stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $user_stmt->bind_param("s", $username);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user = $user_result->fetch_assoc();

        if ($user) {
            error_log("User found: " . print_r($user, true));
            error_log("Password verify result: " . (password_verify($password, $user['password']) ? 'true' : 'false'));
        }

        // Thử đăng nhập với mật khẩu trực tiếp (tạm thời để debug)
        if ($admin && $password === '123456') {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['full_name'] = $admin['full_name'];
            $_SESSION['email'] = $admin['email'];
            $_SESSION['phone'] = $admin['phone'];
            $_SESSION['role'] = 'admin';
            
            // Ghi log đăng nhập
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $action = "login";
            $description = "Đăng nhập thành công vào hệ thống quản trị";
            
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
            $log_stmt->bind_param("isss", $admin['admin_id'], $action, $description, $ip_address);
            $log_stmt->execute();
            
            // Cập nhật last_login
            $update_stmt = $conn->prepare("UPDATE administrators SET last_login = CURRENT_TIMESTAMP WHERE admin_id = ?");
            $update_stmt->bind_param("i", $admin['admin_id']);
            $update_stmt->execute();

            header('Location: QTVindex.php');
            exit();
        } elseif ($user && $password === '123456') {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['address'] = $user['address'];
            $_SESSION['role'] = 'user';
            
            header('Location: trangchu.php');
            exit();
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không chính xác';
        }
    } catch(Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $error = 'Có lỗi xảy ra: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - TTHUONG Store</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .form-container {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }

        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background-color: #444;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }

        .form-footer {
            text-align: center;
            margin-top: 15px;
        }

        .form-footer a {
            color: #333;
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Đăng nhập</h2>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Tên đăng nhập:</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Đăng nhập</button>

            <div class="form-footer">
                <p>Chưa có tài khoản? <a href="dangky.php">Đăng ký ngay</a></p>
                <p><a href="trangchu.php">Quay về trang chủ</a></p>
            </div>
        </form>
    </div>
</body>
</html>