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

        // Kiểm tra admin với password_verify
        if ($admin && password_verify($password, $admin['password'])) {
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

            header('Location: dashboard.php');
            exit();
        } elseif ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['address'] = $user['address'];
            $_SESSION['role'] = 'user';
            
            header('Location: home.php');
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
    <title>Đăng nhập - HKT Store</title>
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 25px 0;
            color: #666;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #ddd;
        }
        
        .divider span {
            padding: 0 15px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .btn-google {
            width: 100%;
            padding: 14px;
            background: #fff;
            color: #333;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 10px;
        }
        
        .btn-google:hover {
            background: #f8f9fa;
            border-color: #4285f4;
            color: #4285f4;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(66, 133, 244, 0.2);
        }
        
        .btn-google img {
            width: 20px;
            height: 20px;
        }
        
        .google-icon {
            font-size: 20px;
            background: linear-gradient(to bottom, #4285f4, #34a853, #fbbc05, #ea4335);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2><i class="fas fa-sign-in-alt"></i> Đăng nhập</h2>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php 
                    switch($_GET['error']) {
                        case 'google_auth_failed':
                            echo 'Xác thực Google thất bại. Vui lòng thử lại.';
                            break;
                        case 'token_failed':
                            echo 'Không thể lấy token từ Google. Vui lòng thử lại.';
                            break;
                        case 'no_email':
                            echo 'Không lấy được email từ Google. Vui lòng thử lại.';
                            break;
                        case 'create_failed':
                            echo 'Không thể tạo tài khoản. Vui lòng thử lại.';
                            break;
                        default:
                            echo 'Có lỗi xảy ra. Vui lòng thử lại.';
                    }
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Tên đăng nhập</label>
                <input type="text" id="username" name="username" required autofocus
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Mật khẩu</label>
                <div class="input-icon">
                    <input type="password" id="password" name="password" required>
                    <i class="fas fa-eye" onclick="togglePassword('password')"></i>
                </div>
            </div>

            <div class="remember-forgot">
                <label>
                    <input type="checkbox" name="remember" id="remember">
                    Ghi nhớ đăng nhập
                </label>
                <!-- <a href="forgot_password.php">Quên mật khẩu?</a> -->
            </div>

            <button type="submit" id="submitBtn">
                <i class="fas fa-sign-in-alt"></i> Đăng nhập
            </button>
        </form>

        <div class="divider">
            <span>HOẶC</span>
        </div>

        <button type="button" class="btn-google" onclick="loginWithGoogle()">
            <span class="google-icon"><i class="fab fa-google"></i></span>
            Đăng nhập bằng Google
        </button>

        <div class="form-footer">
                <p>Chưa có tài khoản? <a href="register_page.php"><i class="fas fa-user-plus"></i> Đăng ký ngay</a></p>
                <p><a href="home.php"><i class="fas fa-home"></i> Quay về trang chủ</a></p>
            </div>
    </div>

    <script>
        <?php require_once('config/google_config.php'); ?>
        
        // Google OAuth Login
        function loginWithGoogle() {
            const clientId = '<?php echo GOOGLE_CLIENT_ID; ?>';
            const redirectUri = '<?php echo GOOGLE_REDIRECT_URI; ?>';
            const scope = 'email profile';
            const responseType = 'code';
            
            const googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' +
                'client_id=' + encodeURIComponent(clientId) +
                '&redirect_uri=' + encodeURIComponent(redirectUri) +
                '&response_type=' + encodeURIComponent(responseType) +
                '&scope=' + encodeURIComponent(scope) +
                '&access_type=offline' +
                '&prompt=consent';
            
            window.location.href = googleAuthUrl;
        }
        
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.parentElement.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang đăng nhập...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>