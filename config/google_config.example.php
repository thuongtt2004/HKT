<?php
// Google OAuth Configuration Template
// Copy file này thành google_config.php và điền thông tin thật

define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', 'http://localhost/TTHUONG/google_callback.php');

// Hoặc sử dụng cho production
// define('GOOGLE_REDIRECT_URI', 'https://yourdomain.com/google_callback.php');

/**
 * Hướng dẫn cấu hình Google OAuth:
 * 
 * 1. Truy cập: https://console.cloud.google.com/
 * 2. Tạo project mới hoặc chọn project có sẵn
 * 3. Vào "APIs & Services" > "Credentials"
 * 4. Click "Create Credentials" > "OAuth 2.0 Client IDs"
 * 5. Chọn "Web application"
 * 6. Thêm Authorized redirect URIs:
 *    - http://localhost/TTHUONG/google_callback.php (cho development)
 *    - https://yourdomain.com/google_callback.php (cho production)
 * 7. Copy Client ID và Client Secret vào file này
 * 8. Enable Google+ API trong API Library
 * 
 * Xem hướng dẫn chi tiết trong file: GOOGLE_OAUTH_SETUP.md
 */
?>
