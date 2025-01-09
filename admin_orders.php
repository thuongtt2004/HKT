<?php
session_start();
require_once 'config/connect.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Xử lý cập nhật trạng thái đơn hàng
if(isset($_POST['order_id']) && isset($_POST['new_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    $sql = "UPDATE orders SET status = '$new_status' WHERE order_id = $order_id";
    if($conn->query($sql)) {
        echo "<script>alert('Cập nhật trạng thái thành công!');</script>";
    } else {
        echo "<script>alert('Lỗi khi cập nhật: " . $conn->error . "');</script>";
    }
}

// Lấy danh sách đơn hàng
$sql = "SELECT * FROM orders ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng - Admin</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-orders {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .order-table th, .order-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .order-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .status-select {
            padding: 5px 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .update-btn {
            padding: 5px 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .update-btn:hover {
            background: #45a049;
        }

        .view-details {
            color: #007bff;
            cursor: pointer;
        }

        .order-details {
            display: none;
            padding: 15px;
            background: #f8f9fa;
            margin-top: 10px;
            border-radius: 4px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: bold;
        }

        .status-pending {
            background: #ffeeba;
            color: #856404;
        }

        .status-confirmed {
            background: #cce5ff;
            color: #004085;
        }

        .status-shipping {
            background: #d4edda;
            color: #155724;
        }

        .status-delivered {
            background: #c3e6cb;
            color: #1e7e34;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
   
    <div class="admin-orders">
        <h2>Quản Lý Đơn Hàng</h2>

        <table class="order-table">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Tổng tiền</th>
                    <th>Ngày đặt</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $order['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                        <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <select name="new_status" style="padding: 5px; margin-right: 5px;">
                                    <option value="Chờ xác nhận" <?php if($order['status'] == 'Chờ xác nhận') echo 'selected'; ?>>Chờ xác nhận</option>
                                    <option value="Đã xác nhận" <?php if($order['status'] == 'Đã xác nhận') echo 'selected'; ?>>Đã xác nhận</option>
                                    <option value="Đang giao hàng" <?php if($order['status'] == 'Đang giao hàng') echo 'selected'; ?>>Đang giao hàng</option>
                                    <option value="Đã giao hàng" <?php if($order['status'] == 'Đã giao hàng') echo 'selected'; ?>>Đã giao hàng</option>
                                    <option value="Đã hủy" <?php if($order['status'] == 'Đã hủy') echo 'selected'; ?>>Đã hủy</option>
                                </select>
                                <input type="submit" value="Lưu" style="padding: 5px 10px; background: #4CAF50; color: white; border: none; border-radius: 3px; cursor: pointer;">
                            </form>
                        </td>
                        <td>
                            <span class="view-details" onclick="toggleDetails(<?php echo $order['order_id']; ?>)">
                                <i class="fas fa-eye"></i> Chi tiết
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6">
                            <div id="details-<?php echo $order['order_id']; ?>" class="order-details">
                                <?php
                                $detail_sql = "SELECT od.*, p.product_name 
                                             FROM order_details od 
                                             JOIN products p ON od.product_id = p.product_id 
                                             WHERE od.order_id = ?";
                                $detail_stmt = $conn->prepare($detail_sql);
                                $detail_stmt->bind_param("i", $order['order_id']);
                                $detail_stmt->execute();
                                $details = $detail_stmt->get_result();
                                ?>
                                <h4>Chi tiết đơn hàng:</h4>
                                <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                                <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                                <?php if ($order['notes']): ?>
                                    <p><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order['notes']); ?></p>
                                <?php endif; ?>
                                <table style="width: 100%; margin-top: 10px;">
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Số lượng</th>
                                        <th>Giá</th>
                                    </tr>
                                    <?php while ($detail = $details->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($detail['product_name']); ?></td>
                                            <td><?php echo $detail['quantity']; ?></td>
                                            <td><?php echo number_format($detail['price'], 0, ',', '.'); ?> VNĐ</td>
                                        </tr>
                                    <?php endwhile; ?>
                                </table>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
    function toggleDetails(orderId) {
        const detailsDiv = document.getElementById(`details-${orderId}`);
        if (detailsDiv.style.display === 'none' || detailsDiv.style.display === '') {
            detailsDiv.style.display = 'block';
        } else {
            detailsDiv.style.display = 'none';
        }
    }

    document.querySelectorAll('select[name="new_status"]').forEach(select => {
        select.addEventListener('change', function() {
            if(confirm('Bạn có chắc muốn cập nhật trạng thái?')) {
                this.closest('form').submit();
            }
        });
    });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>

<?php $conn->close(); ?>
