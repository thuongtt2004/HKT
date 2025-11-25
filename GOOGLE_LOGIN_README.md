# Google Login - Hướng Dẫn Cài Đặt Nhanh

## 📋 Chuẩn Bị

### 1. Cấu Hình Google OAuth
```bash
# Copy template config
copy config\google_config.example.php config\google_config.php

# Mở config\google_config.php và điền:
# - GOOGLE_CLIENT_ID
# - GOOGLE_CLIENT_SECRET
# - GOOGLE_REDIRECT_URI (mặc định: http://localhost/TTHUONG/google_callback.php)
```

### 2. Cập Nhật Database
```sql
-- Chạy SQL này trong phpMyAdmin:
ALTER TABLE `users` 
ADD COLUMN `google_id` VARCHAR(255) NULL DEFAULT NULL AFTER `password`,
ADD COLUMN `google_picture` VARCHAR(500) NULL DEFAULT NULL AFTER `google_id`,
ADD UNIQUE INDEX `google_id_UNIQUE` (`google_id` ASC);
```

Hoặc import file: `database/add_google_oauth_columns.sql`

### 3. Lấy Google OAuth Credentials

Xem hướng dẫn chi tiết trong: **GOOGLE_OAUTH_SETUP.md**

Tóm tắt nhanh:
1. Vào https://console.cloud.google.com/
2. Tạo project mới
3. Enable Google+ API
4. Tạo OAuth 2.0 Client ID
5. Thêm redirect URI: `http://localhost/TTHUONG/google_callback.php`
6. Copy Client ID & Secret vào config

## 🚀 Sử Dụng

1. Truy cập: http://localhost/TTHUONG/login_page.php
2. Click nút **"Đăng nhập bằng Google"**
3. Chọn tài khoản Google
4. Cho phép quyền truy cập
5. Tự động đăng nhập hoặc tạo tài khoản mới

## 📁 Files Được Thêm

- `config/google_config.php` - Cấu hình OAuth (KHÔNG commit)
- `config/google_config.example.php` - Template config
- `google_callback.php` - Xử lý callback từ Google
- `database/add_google_oauth_columns.sql` - Migration database
- `GOOGLE_OAUTH_SETUP.md` - Hướng dẫn chi tiết

## 🔒 Bảo Mật

File `config/google_config.php` đã được thêm vào `.gitignore` và sẽ không được commit lên Git.

## ⚠️ Lưu Ý

- Chỉ email được thêm vào "Test users" mới đăng nhập được (nếu app ở chế độ Testing)
- Redirect URI phải khớp chính xác với cấu hình trong Google Console
- Cần enable Google+ API hoặc People API

## 🐛 Troubleshooting

Xem mục Troubleshooting trong file `GOOGLE_OAUTH_SETUP.md`
