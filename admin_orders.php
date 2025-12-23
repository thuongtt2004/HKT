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
    
    // LOGIC KIỂM TRA: Không cho phép thay đổi trạng thái Đã hủy hoặc Hoàn thành
    if ($old_status === 'Đã hủy') {
        echo "<script>alert('Không thể thay đổi trạng thái đơn hàng đã hủy!'); window.location.href='admin_orders.php';</script>";
        exit();
    }
    
    if ($old_status === 'Hoàn thành' && $new_status !== 'Hoàn thành') {
        echo "<script>alert('Không thể thay đổi trạng thái đơn hàng đã hoàn thành!'); window.location.href='admin_orders.php';</script>";
        exit();
    }
    
    // KHÓA CÁC TRẠNG THÁI TRẢ HÀNG
    if ($old_status === 'Đã duyệt trả hàng') {
        echo "<script>alert('Không thể thay đổi trạng thái đơn hàng đã duyệt trả hàng!'); window.location.href='admin_orders.php';</script>";
        exit();
    }
    
    if ($old_status === 'Không đồng ý duyệt trả hàng') {
        echo "<script>alert('Không thể thay đổi trạng thái đơn hàng không đồng ý duyệt trả hàng!'); window.location.href='admin_orders.php';</script>";
        exit();
    }
    
    // Kiểm tra logic chuyển trạng thái hợp lý
    $valid_transitions = [
        'Chờ thanh toán' => ['Đã xác nhận', 'Đã hủy'],
        'Chờ xác nhận' => ['Đã xác nhận', 'Đã hủy'],
        'Đã xác nhận' => ['Đang giao', 'Đã hủy'],
        'Đang giao' => ['Hoàn thành', 'Đã hủy'],
        'Hoàn thành' => ['Hoàn thành'],
        'Đã hủy' => ['Đã hủy'],
        'Chờ xác nhận trả hàng' => ['Chờ xác nhận trả hàng'], // Không được thay đổi từ đây, chỉ admin_returns.php mới thay đổi được
        'Đã duyệt trả hàng' => ['Đã duyệt trả hàng'], // Khóa
        'Không đồng ý duyệt trả hàng' => ['Không đồng ý duyệt trả hàng'] // Khóa
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
    
    // Chuyển sang trạng thái "Hoàn thành" - Trừ tồn kho và tăng đã bán
    if ($new_status === 'Hoàn thành' && $old_status !== 'Hoàn thành') {
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
    // Chuyển sang trạng thái "Đã hủy" - Hoàn lại nếu đã trừ trước đó
    elseif ($new_status === 'Đã hủy' && $old_status === 'Hoàn thành') {
        // Nếu đơn đã hoàn thành trước đó, hoàn lại tồn kho
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
    // Hủy đơn chưa hoàn thành - Không cần hoàn lại
    elseif ($new_status === 'Đã hủy') {
        $message .= ' Đơn hàng chưa giao nên không cần hoàn lại tồn kho.';
    }
    
    // Cập nhật trạng thái đơn hàng
    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if($stmt->execute()) {
        // Nếu chuyển sang "Hoàn thành", lưu ngày hoàn thành
        if ($new_status === 'Hoàn thành') {
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
                            <?php 
                            $locked_statuses = ['Đã hủy', 'Hoàn thành', 'Đã duyệt trả hàng', 'Không đồng ý duyệt trả hàng'];
                            if (in_array($order['order_status'], $locked_statuses)): 
                                // Hiển thị màu tương ứng cho từng trạng thái
                                $bg_color = '#28a745'; // Mặc định xanh lá
                                if ($order['order_status'] === 'Đã hủy') $bg_color = '#dc3545';
                                elseif ($order['order_status'] === 'Không đồng ý duyệt trả hàng') $bg_color = '#dc3545';
                                elseif ($order['order_status'] === 'Đã duyệt trả hàng') $bg_color = '#17a2b8';
                            ?>
                                <!-- Không cho phép sửa nếu đã khóa -->
                                <span style="padding:8px 12px;background:<?php echo $bg_color; ?>;color:white;border-radius:6px;font-weight:600;display:inline-block;">
                                    <?php echo $order['order_status']; ?>
                                    <i class="fas fa-lock" style="margin-left:5px;"></i>
                                </span>
                            <?php elseif ($order['order_status'] === 'Chờ xác nhận trả hàng'): ?>
                                <!-- Trạng thái chờ xác nhận trả hàng - chỉ xử lý ở admin_returns.php -->
                                <span style="padding:8px 12px;background:#ffc107;color:#000;border-radius:6px;font-weight:600;display:inline-block;">
                                    <?php echo $order['order_status']; ?>
                                    <i class="fas fa-exclamation-triangle" style="margin-left:5px;"></i>
                                </span>
                            <?php else: ?>
                                <!-- Cho phép sửa với các trạng thái hợp lệ -->
                                <form method="POST" action="" style="display: flex; align-items: center; gap: 5px;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <select name="status" class="status-select" style="padding:8px;border:2px solid #ddd;border-radius:6px;">
                                        <?php
                                        $current = $order['order_status'];
                                        // Chỉ hiển thị các trạng thái hợp lệ có thể chuyển đến
                                        $valid_next = [
                                            'Chờ thanh toán' => ['Chờ thanh toán', 'Đã xác nhận', 'Đã hủy'],
                                            'Chờ xác nhận' => ['Chờ xác nhận', 'Đã xác nhận', 'Đã hủy'],
                                            'Đã xác nhận' => ['Đã xác nhận', 'Đang giao', 'Đã hủy'],
                                            'Đang giao' => ['Đang giao', 'Hoàn thành', 'Đã hủy'],
                                            'Chờ xác nhận trả hàng' => ['Chờ xác nhận trả hàng'] // Chỉ hiển thị, không cho thay đổi
                                        ];
                                        
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
