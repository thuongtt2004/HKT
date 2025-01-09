<?php
require_once 'config/connect.php';

// Lấy danh sách users từ database
$sql = "SELECT * FROM users ORDER BY user_id DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Lỗi truy vấn: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng - TTHUONG Store</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            margin: 20px 0;
            font-size: 2em;
        }

        /* Style cho bảng */
        table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-collapse: collapse;
            margin: 20px 0;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #333333;
            color: white;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        /* Style cho nút */
        .back-btn {
            display: inline-flex;
            align-items: center;
            background-color: #333333;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .back-btn:hover {
            background-color: #444444;
            transform: translateY(-2px);
        }

        .back-btn i {
            margin-right: 8px;
        }

        /* Style cho trạng thái */
        .status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
        }

        .active {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .inactive {
            background-color: #ffebee;
            color: #c62828;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Quản Lý Người Dùng</h1>
        
        <a href="QTVindex.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Quay về Trang Quản Trị
        </a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên người dùng</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Địa chỉ</th>
                    <th>Ngày đăng ký</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $status = isset($row['status']) ? $row['status'] : 1;
                        $status_class = $status == 1 ? 'active' : 'inactive';
                        $status_text = $status == 1 ? 'Hoạt động' : 'Không hoạt động';
                        ?>
                        <tr>
                            <td><?php echo $row['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                            <td><span class="status <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align: center;'>Không có người dùng nào.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>
