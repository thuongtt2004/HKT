# 🛒 HKT Store - E-Commerce Platform

> Nền tảng thương mại điện tử hiện đại với đầy đủ tính năng quản lý bán hàng, đơn hàng và khách hàng.

[![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## ✨ Tính Năng Nổi Bật

### 🎯 Dành Cho Khách Hàng
- 🛍️ **Mua Sắm Thông Minh**: Duyệt sản phẩm theo danh mục, tìm kiếm nhanh chóng
- 💳 **Thanh Toán Linh Hoạt**: Hỗ trợ COD và chuyển khoản ngân hàng
- ⭐ **Đánh Giá & Review**: Chia sẻ trải nghiệm, xem đánh giá từ người dùng khác
- 📦 **Theo Dõi Đơn Hàng**: Cập nhật trạng thái đơn hàng real-time
- ❤️ **Wishlist**: Lưu sản phẩm yêu thích để mua sau
- 🎁 **Khuyến Mãi**: Áp dụng mã giảm giá, flash sale tự động
- 🔄 **Trả Hàng Dễ Dàng**: Yêu cầu trả hàng/hoàn tiền trong 7 ngày

### 👨‍💼 Dành Cho Admin
- 📊 **Dashboard Trực Quan**: Thống kê doanh thu, đơn hàng, sản phẩm bán chạy
- 📦 **Quản Lý Sản Phẩm**: CRUD đầy đủ với upload ảnh, quản lý tồn kho
- 🏷️ **Quản Lý Danh Mục**: Tổ chức sản phẩm theo danh mục
- 🎫 **Quản Lý Khuyến Mãi**: Tạo flash sale, giảm giá theo sản phẩm/danh mục
- 📋 **Quản Lý Đơn Hàng**: Cập nhật trạng thái, xử lý thanh toán
- 👥 **Quản Lý Người Dùng**: Xem thông tin khách hàng, lịch sử mua hàng
- 💬 **Chat Hỗ Trợ**: Trả lời câu hỏi khách hàng trực tiếp
- ⭐ **Quản Lý Đánh Giá**: Xem, xóa đánh giá không phù hợp

---

## 🚀 Công Nghệ Sử Dụng

```
Frontend:  HTML5, CSS3, JavaScript (Vanilla)
Backend:   PHP 7.4+
Database:  MySQL 8.0+
Server:    Apache (XAMPP)
Icons:     Font Awesome 6.0
```

---

## 📦 Cài Đặt

### Yêu Cầu Hệ Thống
- XAMPP (hoặc LAMP/WAMP)
- PHP >= 7.4
- MySQL >= 8.0
- Web Browser hiện đại

### Các Bước Cài Đặt

1️⃣ **Clone Repository**
```bash
git clone https://github.com/thuongtt2004/HKT.git
cd HKT
```

2️⃣ **Cấu Hình Database**
```bash
# Tạo database mới trong phpMyAdmin
CREATE DATABASE hkt_store;

# Import file SQL
mysql -u root -p hkt_store < database/hkt_store.sql
```

3️⃣ **Cấu Hình Kết Nối**
```php
// Chỉnh sửa file config/connect.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hkt_store";
```

4️⃣ **Chạy Ứng Dụng**
```bash
# Khởi động XAMPP
# Truy cập: http://localhost/HKT
```

5️⃣ **Tài Khoản Mặc Định**
```
Admin:
- Email: admin@hkt.com
- Password: admin123

User:
- Email: user@hkt.com  
- Password: user123
```

---

## 📁 Cấu Trúc Thư Mục

```
HKT/
├── 📂 admin/              # Các trang quản trị
├── 📂 config/             # Cấu hình database
├── 📂 css/                # Stylesheet files
├── 📂 database/           # SQL files & migrations
├── 📂 images/             # Hình ảnh sản phẩm, logo
├── 📂 includes/           # PHP includes & helpers
├── 📂 uploads/            # Upload files (payment proofs)
├── 📄 index.php           # Trang chủ
├── 📄 products.php        # Danh sách sản phẩm
├── 📄 cart.php            # Giỏ hàng
├── 📄 order.php           # Đặt hàng
├── 📄 track_order.php     # Theo dõi đơn hàng
├── 📄 reviews.php         # Đánh giá sản phẩm
└── 📄 README.md           # Documentation
```

---

## 🎨 Screenshots

### 🏠 Trang Chủ
Giao diện hiện đại với banner slider, sản phẩm nổi bật và khuyến mãi hot.

### 🛍️ Trang Sản Phẩm
Hiển thị đầy đủ thông tin: giá, đánh giá, tag giảm giá, tồn kho.

### 📊 Admin Dashboard
Thống kê trực quan với biểu đồ doanh thu, top sản phẩm bán chạy.

---

## 🔥 Tính Năng Đặc Biệt

### 🎯 Hệ Thống Khuyến Mãi Thông Minh
- **Flash Sale**: Giảm giá theo thời gian, tự động kết thúc
- **Giảm Giá Theo Sản Phẩm**: Áp dụng cho sản phẩm cụ thể
- **Giảm Giá Theo Danh Mục**: Áp dụng cho cả danh mục
- **Hiển thị Tag**: "Giảm XX%" trên sản phẩm đang khuyến mãi

### 📦 Quản Lý Đơn Hàng Thông Minh
- **Tự Động Hủy**: Đơn hàng chuyển khoản quá 24h tự động hủy
- **Workflow Rõ Ràng**: Chờ thanh toán → Chờ xác nhận → Đã xác nhận → Đang giao → Đã giao → Hoàn thành
- **Khóa Trạng Thái**: Không thể sửa đơn "Hoàn thành" và "Đã hủy"
- **Xác Nhận Khách Hàng**: Khách tự xác nhận hài lòng để hoàn thành đơn

### ⭐ Hệ Thống Đánh Giá Thông Minh
- **Gộp Đánh Giá**: Mua cùng sản phẩm nhiều lần chỉ đánh giá 1 lần
- **Hiển thị Đầy Đủ**: Sao trung bình, số lượng đánh giá, nội dung chi tiết
- **Upload Ảnh**: Khách hàng có thể đính kèm ảnh sản phẩm thực tế

### 🔄 Trả Hàng & Hoàn Tiền
- **Thời Hạn 7 Ngày**: Kể từ khi nhận hàng
- **Quy Trình Rõ Ràng**: Yêu cầu → Chờ duyệt → Đã duyệt/Từ chối
- **Thông Báo Tự Động**: Cập nhật trạng thái qua email

---

## 🛠️ API Endpoints

### Sản Phẩm
```
GET  /products.php              # Danh sách sản phẩm
GET  /get_product_reviews.php   # Lấy đánh giá sản phẩm
POST /add_to_cart.php           # Thêm vào giỏ hàng
POST /toggle_wishlist.php       # Thêm/xóa wishlist
```

### Đơn Hàng
```
POST /process_order.php         # Tạo đơn hàng
GET  /track_order.php           # Theo dõi đơn hàng
POST /process_order_action.php  # Xác nhận/trả hàng
POST /upload_payment_proof.php  # Upload chứng từ thanh toán
```

### Đánh Giá
```
GET  /reviews.php               # Danh sách sản phẩm chờ đánh giá
POST /save_review.php           # Gửi đánh giá
```

---

## 🤝 Đóng Góp

Mọi đóng góp đều được chào đón! Hãy:

1. Fork repository
2. Tạo branch mới (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Mở Pull Request

---

## 📝 Changelog

### Version 2.0.0 (2024-12-12)
- ✨ Thêm hệ thống đánh giá sản phẩm với upload ảnh
- ✨ Thêm quản lý danh mục sản phẩm
- ✨ Thêm hệ thống trả hàng/hoàn tiền
- 🐛 Fix lỗi đơn hàng cũ hiển thị nút xác nhận
- 🎨 Cải thiện UI/UX toàn bộ hệ thống
- 🚀 Tối ưu hiệu suất database queries

### Version 1.0.0 (2024-11-01)
- 🎉 Ra mắt phiên bản đầu tiên
- ✨ Các tính năng cơ bản: sản phẩm, giỏ hàng, đơn hàng
- ✨ Admin dashboard với thống kê cơ bản

---

## 📄 License

Dự án này được phát hành dưới giấy phép [MIT License](LICENSE).

---

## 👨‍💻 Tác Giả

**Thương Trần**
- GitHub: [@thuongtt2004](https://github.com/thuongtt2004)
- Email: thuongtt2004@gmail.com

---

## 🙏 Lời Cảm Ơn

- Font Awesome cho bộ icon tuyệt vời
- Cộng đồng PHP & MySQL
- Tất cả những người đã đóng góp cho dự án

---

<div align="center">
  
### ⭐ Nếu thấy dự án hữu ích, hãy cho một ngôi sao nhé! ⭐

**Made with ❤️ by Thương Trần**

</div>
