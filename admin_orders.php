<?php
session_start();
require_once 'config/connect.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_page.php');
    exit();
}

// Xử lý cập nhật trạng thái đơn hàng
if(isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = trim($_POST['status']);
    
    // Lấy trạng thái cũ
    $old_status_sql = "SELECT order_status FROM orders WHERE order_id = ?";
    $old_status_stmt = $conn->prepare($old_status_sql);
    $old_status_stmt->bind_param("i", $order_id);
    $old_status_stmt->execute();
    $old_status_result = $old_status_stmt->get_result();
    $old_status = $old_status_result->fetch_assoc()['order_status'];
    
    // LOGIC KIỂM TRA: Không cho phép thay đổi trạng thái Đã hủy và Hoàn thành
    if ($old_status === 'Đã hủy') {
        echo "<script>alert('Không thể thay đổi trạng thái đơn hàng đã hủy!'); window.location.href='admin_orders.php';</script>";
        exit();
    }
    
    if ($old_status === 'Hoàn thành') {
        echo "<script>alert('Không thể thay đổi trạng thái đơn hàng đã hoàn thành!'); window.location.href='admin_orders.php';</script>";
        exit();
    }
    
    // Kiểm tra logic chuyển trạng thái hợp lý - Admin chỉ set tới "Đã giao hàng"
    $valid_transitions = [
        'Chờ thanh toán' => ['Chờ thanh toán', 'Chờ xác nhận', 'Đã xác nhận', 'Đang giao hàng', 'Đã giao hàng', 'Đã hủy'],
        'Chờ xác nhận' => ['Chờ xác nhận', 'Đã xác nhận', 'Đang giao hàng', 'Đã giao hàng', 'Đã hủy'],
        'Đã xác nhận' => ['Đã xác nhận', 'Đang giao hàng', 'Đã giao hàng', 'Đã hủy'],
        'Đang giao hàng' => ['Đang giao hàng', 'Đã giao hàng', 'Đã hủy'],
        'Đã giao hàng' => ['Đã giao hàng'], // Chỉ giữ nguyên, khách hàng sẽ tick để chuyển thành Hoàn thành
        'Hoàn thành' => ['Hoàn thành'], // Khóa cứng
        'Đã hủy' => ['Đã hủy'] // Khóa cứng
    ];
    
    if (!in_array($new_status, $valid_transitions[$old_status])) {
        echo "<script>alert('Không thể chuyển từ trạng thái \"$old_status\" sang \"$new_status\"!'); window.location.href='admin_orders.php';</script>";
        exit();
    }
    
    $message = 'Cập nhật trạng thái thành công!';
    
    // Lấy chi tiết đơn hàng
    $details_sql = "SELECT product_id, quantity FROM order_details WHERE order_id = ?";
    $details_stmt = $conn->prepare($details_sql);
    $details_stmt->bind_param("i", $order_id);
    $details_stmt->execute();
    $details_result = $details_stmt->get_result();
    
    // Xử lý tồn kho dựa trên trạng thái
    $stock_affecting_statuses = ['Đã giao hàng', 'Hoàn thành'];
    $old_affects_stock = in_array($old_status, $stock_affecting_statuses);
    $new_affects_stock = in_array($new_status, $stock_affecting_statuses);
    
    // Chuyển sang trạng thái ảnh hưởng tồn kho (Đã giao hàng hoặc Hoàn thành)
    if ($new_affects_stock && !$old_affects_stock) {
        $update_stock_sql = "UPDATE products 
                             SET stock_quantity = stock_quantity - ?, 
                                 sold_quantity = sold_quantity + ? 
                             WHERE product_id = ?";
        $update_stock_stmt = $conn->prepare($update_stock_sql);
        
        while ($detail = $details_result->fetch_assoc()) {
            $update_stock_stmt->bind_param("iis", $detail['quantity'], $detail['quantity'], $detail['product_id']);
            $update_stock_stmt->execute();
        }
        $message .= ' Đã trừ tồn kho và cập nhật số lượng đã bán.';
    }
    // Chuyển từ trạng thái ảnh hưởng tồn kho về trạng thái không ảnh hưởng
    elseif (!$new_affects_stock && $old_affects_stock) {
        $restore_stock_sql = "UPDATE products 
                              SET stock_quantity = stock_quantity + ?, 
                                  sold_quantity = sold_quantity - ? 
                              WHERE product_id = ?";
        $restore_stock_stmt = $conn->prepare($restore_stock_sql);
        
        $details_stmt->execute();
        $details_result = $details_stmt->get_result();
        
        while ($detail = $details_result->fetch_assoc()) {
            $restore_stock_stmt->bind_param("iis", $detail['quantity'], $detail['quantity'], $detail['product_id']);
            $restore_stock_stmt->execute();
        }
        $message .= ' Đã hoàn lại tồn kho.';
    }
    // Chuyển sang trạng thái "Đã hủy"
    elseif ($new_status === 'Đã hủy') {
        if ($old_affects_stock) {
            // Hoàn lại tồn kho nếu trước đó đã trừ
            $restore_stock_sql = "UPDATE products 
                                  SET stock_quantity = stock_quantity + ?, 
                                      sold_quantity = sold_quantity - ? 
                                  WHERE product_id = ?";
            $restore_stock_stmt = $conn->prepare($restore_stock_sql);
            
            $details_stmt->execute();
            $details_result = $details_stmt->get_result();
            
            while ($detail = $details_result->fetch_assoc()) {
                $restore_stock_stmt->bind_param("iis", $detail['quantity'], $detail['quantity'], $detail['product_id']);
                $restore_stock_stmt->execute();
            }
            $message .= ' Đã hoàn lại tồn kho do hủy đơn.';
        } else {
            $message .= ' Đơn hàng chưa giao nên không cần hoàn lại tồn kho.';
        }
    }
    
    // Cập nhật trạng thái đơn hàng
    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if($stmt->execute()) {
        // Nếu chuyển sang "Đã giao hàng" hoặc "Hoàn thành", lưu ngày hoàn thành
        if (in_array($new_status, ['Đã giao hàng', 'Hoàn thành']) && !in_array($old_status, ['Đã giao hàng', 'Hoàn thành'])) {
            $update_completed = $conn->prepare("UPDATE orders SET completed_date = NOW() WHERE order_id = ?");
            $update_completed->bind_param("i", $order_id);
            $update_completed->execute();
        }
        
        echo "<script>alert('$message'); window.location.href='admin_orders.php';</script>";
    } else {
        echo "<script>alert('Lỗi khi cập nhật: " . $conn->error . "');</script>";
    }
    $stmt->close();
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
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/admin_orders.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        .status-select {
            padding: 8px 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            min-width: 150px;
            font-weight: 600;
            background: white;
            color: #333;
        }
        
        .btn-update-status:hover {
            background: #5a67d8 !important;
            transform: translateY(-1px);
        }
        
        .order-table td form {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .locked-status {
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 600;
            display: inline-block;
            color: white;
        }
        
        .status-completed {
            background: #28a745;
        }
        
        .status-cancelled {
            background: #dc3545;
        }
        
        @media (max-width: 768px) {
            .order-table td form {
                flex-direction: column;
                gap: 5px;
            }
            
            .status-select {
                min-width: 120px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <main>       
    <div class="admin-orders">
        <h1>Quản Lý Đơn Hàng</h1>

        <table class="order-table">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Tổng tiền</th>
                    <th>Hình thức TT</th>
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
                        <td>
                            <?php if ($order['payment_method'] === 'bank_transfer'): ?>
                                <span style="background:#dc3545;color:white;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600;">
                                    <i class="fas fa-university"></i> Chuyển khoản
                                </span>
                            <?php else: ?>
                                <span style="background:#28a745;color:white;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600;">
                                    <i class="fas fa-money-bill-wave"></i> COD
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <?php if ($order['order_status'] === 'Đã hủy' || $order['order_status'] === 'Hoàn thành'): ?>
                                <!-- Khóa trạng thái "Đã hủy" và "Hoàn thành" -->
                                <span class="locked-status <?php echo $order['order_status'] === 'Đã hủy' ? 'status-cancelled' : 'status-completed'; ?>">
                                    <?php echo $order['order_status']; ?>
                                    <i class="fas fa-lock" style="margin-left:5px;"></i>
                                </span>
                            <?php else: ?>
                                <!-- Cho phép sửa các trạng thái khác -->
                                <form method="POST" action="" style="display: flex; align-items: center; gap: 5px;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <select name="status" class="status-select" style="padding:8px;border:2px solid #ddd;border-radius:6px;min-width:150px;">
                                        <?php
                                        $current = $order['order_status'];
                                        // Admin chỉ có thể set tới "Đã giao hàng"
                                        $valid_next = [
                                            'Chờ thanh toán' => ['Chờ thanh toán', 'Chờ xác nhận', 'Đã xác nhận', 'Đang giao hàng', 'Đã giao hàng', 'Đã hủy'],
                                            'Chờ xác nhận' => ['Chờ xác nhận', 'Đã xác nhận', 'Đang giao hàng', 'Đã giao hàng', 'Đã hủy'],
                                            'Đã xác nhận' => ['Đã xác nhận', 'Đang giao hàng', 'Đã giao hàng', 'Đã hủy'],
                                            'Đang giao hàng' => ['Đang giao hàng', 'Đã giao hàng', 'Đã hủy'],
                                            'Đã giao hàng' => ['Đã giao hàng'] // Chỉ giữ nguyên
                                        ];
                                        
                                        // Nếu trạng thái hiện tại không có trong danh sách, thêm các trạng thái cơ bản
                                        if (!isset($valid_next[$current])) {
                                            $valid_next[$current] = ['Chờ thanh toán', 'Chờ xác nhận', 'Đã xác nhận', 'Đang giao hàng', 'Đã giao hàng', 'Đã hủy'];
                                        }
                                        
                                        foreach ($valid_next[$current] as $status) {
                                            $selected = ($status === $current) ? 'selected' : '';
                                            echo "<option value='$status' $selected>$status</option>";
                                        }
                                        ?>
                                    </select>
                                    <button type="submit" class="btn-update-status" style="padding:8px 15px;background:#667eea;color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;">
                                        <i class="fas fa-save"></i> Lưu
                                    </button>
                                </form>
                            <?php endif; ?>
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
                                <p><strong>Hình thức thanh toán:</strong> 
                                    <?php echo $order['payment_method'] === 'bank_transfer' ? 'Chuyển khoản' : 'COD'; ?>
                                </p>
                                <?php if ($order['payment_method'] === 'bank_transfer' && !empty($order['payment_proof'])): ?>
                                    <p><strong>Chứng từ thanh toán:</strong></p>
                                    <img src="<?php echo htmlspecialchars($order['payment_proof']); ?>" 
                                         style="max-width: 300px; border-radius: 8px; margin-top: 10px; cursor: pointer;"
                                         onclick="window.open('<?php echo htmlspecialchars($order['payment_proof']); ?>', '_blank')">
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
    </main>

    <?php include 'admin_footer.php'; ?>
</body>
</html>

<?php $conn->close(); ?>
