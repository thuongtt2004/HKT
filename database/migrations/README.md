# Database Migrations

Thư mục này chứa các file migration SQL để cập nhật cấu trúc database.

## 📋 Cách sử dụng

1. **Chạy migration:**
   - Mở phpMyAdmin
   - Chọn database của bạn
   - Mở file SQL trong thư mục này
   - Copy và Execute

2. **Sau khi chạy xong:**
   - Di chuyển file vào thư mục `applied/` để đánh dấu đã sử dụng
   - Hoặc chạy script tự động (xem bên dưới)

## 📂 Cấu trúc

```
migrations/
├── add_return_order_statuses.sql     # Chưa chạy
├── add_users_status_column.sql       # Chưa chạy
├── applied/                          # Đã chạy
│   └── [các file đã apply]
└── README.md
```

## 🚀 Script tự động (Optional)

Tạo file `apply_migrations.php` để tự động chạy:

```php
<?php
require_once '../config/connect.php';

$migrations_dir = __DIR__;
$applied_dir = $migrations_dir . '/applied';

$files = glob($migrations_dir . '/*.sql');

foreach ($files as $file) {
    echo "Applying: " . basename($file) . "\n";
    
    $sql = file_get_contents($file);
    
    if ($conn->multi_query($sql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        
        // Di chuyển file vào applied/
        rename($file, $applied_dir . '/' . basename($file));
        echo "✓ Success\n\n";
    } else {
        echo "✗ Error: " . $conn->error . "\n\n";
    }
}

$conn->close();
?>
```

## ⚠️ Lưu ý

- **KHÔNG** xóa các file migration sau khi chạy
- **NÊN** di chuyển vào thư mục `applied/` để tracking
- **LUÔN** backup database trước khi chạy migration
- **KHI** commit lên Git, giữ cả hai thư mục để team member khác biết lịch sử thay đổi

## 📝 Migration hiện tại

### Chờ apply:
- `add_return_order_statuses.sql` - Thêm trạng thái trả hàng
- `add_users_status_column.sql` - Thêm cột status cho users

### Đã apply:
- (Di chuyển file vào `applied/` sau khi chạy xong)
