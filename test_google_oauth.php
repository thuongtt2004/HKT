<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Google OAuth - HKT Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .test-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .status-box {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .status-box.warning {
            border-color: #ffc107;
            background: #fff3cd;
        }
        
        .status-box.error {
            border-color: #dc3545;
            background: #f8d7da;
        }
        
        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .status-item:last-child {
            border-bottom: none;
        }
        
        .status-label {
            font-weight: 600;
            color: #333;
        }
        
        .status-value {
            color: #666;
            font-family: monospace;
            font-size: 13px;
            max-width: 400px;
            word-break: break-all;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn-test {
            width: 100%;
            padding: 15px;
            background: #4285f4;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-test:hover {
            background: #357ae8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(66, 133, 244, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .instruction {
            background: #e7f3ff;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-top: 20px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .instruction ol {
            margin-left: 20px;
            margin-top: 10px;
        }
        
        .instruction li {
            margin-bottom: 5px;
        }
        
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #d63384;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1><i class="fab fa-google"></i> Google OAuth Test</h1>
        <p class="subtitle">Kiểm tra cấu hình Google Sign-In</p>
        
        <?php
        // Kiểm tra config file
        $config_exists = file_exists('config/google_config.php');
        
        if ($config_exists) {
            require_once 'config/google_config.php';
            
            $client_id_configured = (GOOGLE_CLIENT_ID !== 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com');
            $client_secret_configured = (GOOGLE_CLIENT_SECRET !== 'YOUR_GOOGLE_CLIENT_SECRET');
            
            if ($client_id_configured && $client_secret_configured) {
                echo '<div class="status-box">';
                echo '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Đã cấu hình</span>';
                echo '</div>';
                
                echo '<div class="status-item">';
                echo '<span class="status-label">Client ID:</span>';
                echo '<span class="status-value">' . substr(GOOGLE_CLIENT_ID, 0, 20) . '...</span>';
                echo '</div>';
                
                echo '<div class="status-item">';
                echo '<span class="status-label">Redirect URI:</span>';
                echo '<span class="status-value">' . GOOGLE_REDIRECT_URI . '</span>';
                echo '</div>';
                
                echo '<button class="btn-test" onclick="testGoogleLogin()">';
                echo '<i class="fab fa-google"></i> Test Đăng Nhập Google';
                echo '</button>';
                
                echo '<a href="login_page.php" style="text-decoration: none;">';
                echo '<button class="btn-test btn-secondary">';
                echo '<i class="fas fa-sign-in-alt"></i> Đến Trang Đăng Nhập';
                echo '</button>';
                echo '</a>';
            } else {
                echo '<div class="status-box warning">';
                echo '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Chưa cấu hình</span>';
                echo '</div>';
                
                echo '<div class="instruction">';
                echo '<strong><i class="fas fa-info-circle"></i> Cần cấu hình Google OAuth:</strong>';
                echo '<ol>';
                echo '<li>Mở file <code>config/google_config.php</code></li>';
                echo '<li>Thay đổi <code>GOOGLE_CLIENT_ID</code> và <code>GOOGLE_CLIENT_SECRET</code></li>';
                echo '<li>Xem hướng dẫn chi tiết trong <code>GOOGLE_OAUTH_SETUP.md</code></li>';
                echo '</ol>';
                echo '</div>';
            }
        } else {
            echo '<div class="status-box error">';
            echo '<span class="badge badge-error"><i class="fas fa-times-circle"></i> File config không tồn tại</span>';
            echo '</div>';
            
            echo '<div class="instruction">';
            echo '<strong><i class="fas fa-exclamation-triangle"></i> Thiếu file cấu hình:</strong>';
            echo '<ol>';
            echo '<li>Copy file <code>config/google_config.example.php</code></li>';
            echo '<li>Đổi tên thành <code>google_config.php</code></li>';
            echo '<li>Điền Client ID và Client Secret từ Google Console</li>';
            echo '<li>Xem hướng dẫn trong <code>GOOGLE_OAUTH_SETUP.md</code></li>';
            echo '</ol>';
            echo '</div>';
        }
        ?>
        
        <div class="instruction" style="margin-top: 30px;">
            <strong><i class="fas fa-book"></i> Tài liệu hướng dẫn:</strong>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li><code>GOOGLE_LOGIN_README.md</code> - Hướng dẫn cài đặt nhanh</li>
                <li><code>GOOGLE_OAUTH_SETUP.md</code> - Hướng dẫn chi tiết</li>
                <li><code>database/add_google_oauth_columns.sql</code> - SQL migration</li>
            </ul>
        </div>
    </div>
    
    <script>
        function testGoogleLogin() {
            <?php if ($config_exists && isset($client_id_configured) && $client_id_configured): ?>
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
            <?php else: ?>
            alert('Vui lòng cấu hình Google OAuth trước!');
            <?php endif; ?>
        }
    </script>
</body>
</html>
