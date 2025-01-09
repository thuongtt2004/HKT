<?php
require_once 'config/connect.php';

// Lấy lịch sử hoạt động từ database
$sql = "SELECT l.*, a.username, a.full_name 
        FROM activity_logs l
        LEFT JOIN administrators a ON l.admin_id = a.admin_id
        ORDER BY l.created_at DESC";
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
    <title>Lịch Sử Hoạt Động - TTHUONG Store</title>
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

        /* Style cho nút quay về */
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

        /* Style cho action type */
        .action-type {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
        }

        .login {
            background-color: #e3f2fd;
            color: #1565c0;
        }

        .logout {
            background-color: #fff3e0;
            color: #e65100;
        }

        .update {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .delete {
            background-color: #ffebee;
            color: #c62828;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Lịch Sử Hoạt Động</h1>
        
        <a href="QTVindex.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Quay về Trang Quản Trị
        </a>

        <table>
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Quản trị viên</th>
                    <th>Hành động</th>
                    <th>Mô tả</th>
                    <th>Địa chỉ IP</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        // Xác định class cho loại hành động
                        $action_class = '';
                        switch(strtolower($row['action'])) {
                            case 'login':
                                $action_class = 'login';
                                break;
                            case 'logout':
                                $action_class = 'logout';
                                break;
                            case 'update':
                                $action_class = 'update';
                                break;
                            case 'delete':
                                $action_class = 'delete';
                                break;
                            default:
                                $action_class = '';
                        }
                        ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i:s', strtotime($row['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']) . ' (' . htmlspecialchars($row['username']) . ')'; ?></td>
                            <td><span class="action-type <?php echo $action_class; ?>"><?php echo htmlspecialchars($row['action']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo htmlspecialchars($row['ip_address']); ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align: center;'>Không có lịch sử hoạt động nào.</td></tr>";
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