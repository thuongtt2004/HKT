# Hướng Dẫn Cấu Hình Google OAuth 2.0

## Bước 1: Tạo Project trên Google Cloud Console

1. Truy cập: https://console.cloud.google.com/
2. Click vào menu dropdown ở góc trên bên trái (bên cạnh "Google Cloud")
3. Click "NEW PROJECT"
4. Nhập tên project (ví dụ: "HKT Store")
5. Click "CREATE"

## Bước 2: Enable Google+ API

1. Trong Google Cloud Console, vào menu "APIs & Services" > "Library"
2. Tìm kiếm "Google+ API" hoặc "People API"
3. Click vào API và nhấn "ENABLE"

## Bước 3: Tạo OAuth 2.0 Credentials

1. Vào "APIs & Services" > "Credentials"
2. Click "CREATE CREDENTIALS" > "OAuth client ID"
3. Nếu chưa có OAuth consent screen:
   - Click "CONFIGURE CONSENT SCREEN"
   - Chọn "External" (cho testing)
   - Điền thông tin:
     * App name: HKT Store
     * User support email: your-email@gmail.com
     * Developer contact: your-email@gmail.com
   - Click "SAVE AND CONTINUE"
   - Trong "Scopes", click "ADD OR REMOVE SCOPES"
   - Chọn: `email`, `profile`, `openid`
   - Click "UPDATE" và "SAVE AND CONTINUE"
   - Trong "Test users", thêm email của bạn để test
   - Click "SAVE AND CONTINUE"

4. Quay lại "Credentials", click "CREATE CREDENTIALS" > "OAuth client ID"
5. Chọn "Web application"
6. Điền thông tin:
   - Name: HKT Store Web Client
   - Authorized JavaScript origins:
     * http://localhost
     * http://localhost:80
   - Authorized redirect URIs:
     * http://localhost/TTHUONG/google_callback.php
     * Hoặc đường dẫn chính xác của project bạn
7. Click "CREATE"
8. Copy **Client ID** và **Client Secret**

## Bước 4: Cấu Hình trong Project

1. Mở file `config/google_config.php`
2. Thay thế giá trị:
   ```php
   define('GOOGLE_CLIENT_ID', 'YOUR_CLIENT_ID_HERE.apps.googleusercontent.com');
   define('GOOGLE_CLIENT_SECRET', 'YOUR_CLIENT_SECRET_HERE');
   define('GOOGLE_REDIRECT_URI', 'http://localhost/TTHUONG/google_callback.php');
   ```

3. Chạy SQL để thêm cột vào database:
   ```bash
   # Mở phpMyAdmin hoặc MySQL client
   # Chạy file: database/add_google_oauth_columns.sql
   ```

## Bước 5: Test Đăng Nhập

1. Khởi động XAMPP (Apache và MySQL)
2. Truy cập: http://localhost/TTHUONG/login_page.php
3. Click nút "Đăng nhập bằng Google"
4. Chọn tài khoản Google
5. Cho phép quyền truy cập email và profile
6. Sẽ được chuyển về trang chủ với tài khoản đã đăng nhập

## Lưu Ý Quan Trọng

### Development (localhost):
- Redirect URI: `http://localhost/TTHUONG/google_callback.php`
- Cần thêm email test trong OAuth consent screen

### Production (domain thật):
- Cập nhật Authorized redirect URIs trong Google Console
- Thay đổi `GOOGLE_REDIRECT_URI` trong config
- Ví dụ: `https://yourdomain.com/google_callback.php`

## Troubleshooting

### Lỗi "redirect_uri_mismatch"
- Kiểm tra redirect URI trong Google Console phải khớp chính xác với `GOOGLE_REDIRECT_URI`
- Lưu ý: http vs https, có/không có www, đường dẫn chính xác

### Lỗi "Access blocked: This app's request is invalid"
- Kiểm tra OAuth consent screen đã được cấu hình
- Thêm email test trong Test users (nếu ở chế độ Testing)

### Lỗi "Error 400: invalid_client"
- Kiểm tra Client ID và Client Secret đã đúng
- Kiểm tra không có khoảng trắng thừa khi copy/paste

### Không nhận được thông tin user
- Kiểm tra scope đã bao gồm: `email profile`
- Kiểm tra Google+ API hoặc People API đã được enable

## Cấu Trúc Database

Sau khi chạy SQL migration, bảng `users` sẽ có thêm:
- `google_id` VARCHAR(255) - Google User ID (unique)
- `google_picture` VARCHAR(500) - URL ảnh đại diện từ Google

## Luồng Hoạt Động

1. User click "Đăng nhập bằng Google"
2. Redirect đến trang đăng nhập Google
3. User chọn tài khoản và cho phép quyền
4. Google redirect về `google_callback.php` với authorization code
5. Server exchange code để lấy access token
6. Server dùng token để lấy thông tin user
7. Kiểm tra user đã tồn tại (theo email hoặc google_id)
   - Nếu có: Đăng nhập
   - Nếu chưa: Tạo tài khoản mới
8. Lưu session và redirect về trang chủ

## Bảo Mật

- Client Secret KHÔNG được public trên Git
- Thêm `config/google_config.php` vào `.gitignore`
- Sử dụng HTTPS cho production
- Giới hạn scope chỉ lấy email và profile cơ bản
