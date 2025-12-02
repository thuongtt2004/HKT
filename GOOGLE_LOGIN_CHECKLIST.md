# 🚀 Checklist Cài Đặt Google Login - HKT Store

## ✅ Các File Đã Được Tạo

### 1. Files Backend
- [x] `config/google_config.php` - Cấu hình Google OAuth credentials
- [x] `config/google_config.example.php` - Template config (commit được)
- [x] `google_callback.php` - Xử lý callback từ Google sau khi user đăng nhập
- [x] `test_google_oauth.php` - Trang test cấu hình Google OAuth

### 2. Files Database
- [x] `database/add_google_oauth_columns.sql` - Migration để thêm cột google_id, google_picture

### 3. Files Hướng Dẫn
- [x] `GOOGLE_OAUTH_SETUP.md` - Hướng dẫn chi tiết cấu hình Google Console
- [x] `GOOGLE_LOGIN_README.md` - Hướng dẫn cài đặt nhanh
- [x] `GOOGLE_LOGIN_CHECKLIST.md` - File này (checklist)

### 4. Files Được Cập Nhật
- [x] `login_page.php` - Thêm nút "Đăng nhập bằng Google" + UI
- [x] `.gitignore` - Thêm `config/google_config.php` để không commit credentials

## 📝 Các Bước Cài Đặt

### BƯỚC 1: Cấu Hình Google Cloud Console ⏰ 10 phút

1. [ ] Truy cập https://console.cloud.google.com/
2. [ ] Tạo project mới: "HKT Store"
3. [ ] Enable API:
   - [ ] Google+ API
   - [ ] People API (optional, dùng để lấy thông tin chi tiết hơn)
4. [ ] Cấu hình OAuth Consent Screen:
   - [ ] Chọn External
   - [ ] Điền App name: "HKT Store"
   - [ ] Điền email support
   - [ ] Thêm scopes: `email`, `profile`, `openid`
   - [ ] Thêm test users: email của bạn
5. [ ] Tạo OAuth 2.0 Client ID:
   - [ ] Chọn Web application
   - [ ] Thêm Authorized redirect URIs:
     ```
     http://localhost/TTHUONG/google_callback.php
     ```
   - [ ] Copy Client ID và Client Secret

### BƯỚC 2: Cấu Hình Project ⏰ 2 phút

1. [ ] Copy file config:
   ```bash
   copy config\google_config.example.php config\google_config.php
   ```

2. [ ] Mở `config/google_config.php` và điền:
   ```php
   define('GOOGLE_CLIENT_ID', 'paste-client-id-here.apps.googleusercontent.com');
   define('GOOGLE_CLIENT_SECRET', 'paste-client-secret-here');
   define('GOOGLE_REDIRECT_URI', 'http://localhost/TTHUONG/google_callback.php');
   ```

### BƯỚC 3: Cập Nhật Database ⏰ 1 phút

1. [ ] Mở phpMyAdmin: http://localhost/phpmyadmin
2. [ ] Chọn database: `tthuong_store`
3. [ ] Chạy SQL:
   ```sql
   ALTER TABLE `users` 
   ADD COLUMN `google_id` VARCHAR(255) NULL DEFAULT NULL AFTER `password`,
   ADD COLUMN `google_picture` VARCHAR(500) NULL DEFAULT NULL AFTER `google_id`,
   ADD UNIQUE INDEX `google_id_UNIQUE` (`google_id` ASC);
   ```
   Hoặc import file: `database/add_google_oauth_columns.sql`

### BƯỚC 4: Test Chức Năng ⏰ 2 phút

1. [ ] Khởi động XAMPP (Apache + MySQL)
2. [ ] Truy cập test page: http://localhost/TTHUONG/test_google_oauth.php
3. [ ] Kiểm tra status "Đã cấu hình" (màu xanh)
4. [ ] Click "Test Đăng Nhập Google"
5. [ ] Chọn tài khoản Google của bạn
6. [ ] Cho phép quyền truy cập
7. [ ] Kiểm tra redirect về home.php và đã đăng nhập

### BƯỚC 5: Test Trên Trang Login ⏰ 1 phút

1. [ ] Truy cập: http://localhost/TTHUONG/login_page.php
2. [ ] Nhìn thấy nút "Đăng nhập bằng Google" (có icon Google màu)
3. [ ] Click nút và test đăng nhập
4. [ ] Kiểm tra session đã được tạo

## 🧪 Test Cases

### Test Case 1: Người dùng mới (chưa có tài khoản)
- [ ] Đăng nhập Google lần đầu
- [ ] Hệ thống tự động tạo tài khoản
- [ ] Username = email (phần trước @)
- [ ] Lưu google_id và google_picture
- [ ] Redirect về home.php?welcome=1

### Test Case 2: Người dùng cũ (đã có tài khoản qua đăng ký thường)
- [ ] Email trùng với email Google
- [ ] Hệ thống link tài khoản với google_id
- [ ] Đăng nhập thành công
- [ ] Redirect về home.php

### Test Case 3: Người dùng đã link Google
- [ ] Đã từng đăng nhập Google trước đó
- [ ] Đăng nhập trực tiếp bằng google_id
- [ ] Cập nhật google_picture mới nhất
- [ ] Redirect về home.php

## 🔍 Troubleshooting

### Lỗi: "redirect_uri_mismatch"
- [ ] Kiểm tra redirect URI trong Google Console
- [ ] Phải khớp chính xác: `http://localhost/TTHUONG/google_callback.php`
- [ ] Kiểm tra không có khoảng trắng, không có / cuối cùng

### Lỗi: "Access blocked: This app's request is invalid"
- [ ] Kiểm tra đã enable Google+ API
- [ ] Kiểm tra OAuth consent screen đã hoàn thành
- [ ] Thêm email test vào Test users

### Lỗi: "Error 400: invalid_client"
- [ ] Client ID hoặc Client Secret sai
- [ ] Kiểm tra không copy thừa khoảng trắng
- [ ] Kiểm tra quotes trong PHP config

### Không nhận được email từ Google
- [ ] Scope phải có: `email profile`
- [ ] Kiểm tra API đã được enable
- [ ] Kiểm tra user cho phép quyền email

### Database error khi tạo user
- [ ] Kiểm tra SQL đã chạy (cột google_id, google_picture)
- [ ] Kiểm tra UNIQUE constraint không trùng
- [ ] Kiểm tra username không trùng (thêm random số nếu trùng)

## 📊 Kiểm Tra Dữ Liệu

### Trong phpMyAdmin:
```sql
-- Kiểm tra cột đã được thêm
DESCRIBE users;

-- Kiểm tra user đăng nhập bằng Google
SELECT user_id, username, email, google_id, google_picture, created_at 
FROM users 
WHERE google_id IS NOT NULL;
```

## 🎯 Tính Năng Đã Hoàn Thành

- [x] Nút đăng nhập Google với icon đẹp
- [x] OAuth 2.0 flow hoàn chỉnh
- [x] Tự động tạo tài khoản nếu chưa có
- [x] Link tài khoản cũ với Google
- [x] Lưu ảnh đại diện từ Google
- [x] Error handling đầy đủ
- [x] Bảo mật credentials (gitignore)
- [x] Trang test cấu hình
- [x] Hướng dẫn chi tiết

## 📚 Tài Liệu Tham Khảo

1. **Cài đặt nhanh**: `GOOGLE_LOGIN_README.md`
2. **Hướng dẫn chi tiết**: `GOOGLE_OAUTH_SETUP.md`
3. **Google OAuth 2.0 Docs**: https://developers.google.com/identity/protocols/oauth2
4. **Google Cloud Console**: https://console.cloud.google.com/

## 🚢 Deploy Lên Production

Khi deploy lên server thật:

1. [ ] Cập nhật redirect URI trong Google Console
   ```
   https://yourdomain.com/google_callback.php
   ```

2. [ ] Cập nhật `config/google_config.php`
   ```php
   define('GOOGLE_REDIRECT_URI', 'https://yourdomain.com/google_callback.php');
   ```

3. [ ] Enable SSL/HTTPS (bắt buộc cho production)

4. [ ] Chuyển app từ Testing sang Production trong Google Console

5. [ ] Remove test users (không cần nữa ở chế độ Production)

## ✨ Hoàn Thành!

Sau khi check hết tất cả các bước trên, chức năng Google Login đã sẵn sàng!

Nếu gặp vấn đề, xem phần Troubleshooting hoặc tham khảo file `GOOGLE_OAUTH_SETUP.md`.

---

**Lưu ý**: File `config/google_config.php` chứa thông tin nhạy cảm, đã được thêm vào `.gitignore` và sẽ KHÔNG được commit lên Git.
