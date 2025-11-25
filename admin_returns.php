<?php
session_start();
require_once 'config/connect.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_page.php');
    exit();
}

// Xử lý duyệt/từ chối yêu cầu trả hàng
if (isset($_POST['process_return'])) {
    $order_id = intval($_POST['order_id']);
    $action = $_POST['action']; // 'approve' hoặc 'reject'
    $admin_note = trim($_POST['admin_note'] ?? '');
    
    if ($action === 'approve') {
        // Duyệt trả hàng - hoàn lại tồn kho
        $conn->begin_transaction();
        
        try {
            // Lấy chi tiết đơn hàng
            $details_sql = "SELECT product_id, quantity FROM order_details WHERE order_id = ?";
            $details_stmt = $conn->prepare($details_sql);
            $details_stmt->bind_param("i", $order_id);
            $details_stmt->execute();
            $details_result = $details_stmt->get_result();
            
            // Hoàn lại tồn kho
            $restore_stock_sql = "UPDATE products 
                                  SET stock_quantity = stock_quantity + ?, 
                                      sold_quantity = sold_quantity - ? 
                                  WHERE product_id = ?";
            $restore_stock_stmt = $conn->prepare($restore_stock_sql);
            
            while ($detail = $details_result->fetch_assoc()) {
                $restore_stock_stmt->bind_param("iis", $detail['quantity'], $detail['quantity'], $detail['product_id']);
                $restore_stock_stmt->execute();
            }
            
            // Cập nhật trạng thái yêu cầu
            $update_sql = "UPDATE orders SET return_status = 'Đã duyệt', order_status = 'Đã trả hàng' WHERE order_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $order_id);
            $update_stmt->execute();
            
            $conn->commit();
            $success_message = "Đã duyệt yêu cầu trả hàng và hoàn lại tồn kho!";
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Lỗi: " . $e->getMessage();
        }
        
    } elseif ($action === 'reject') {
        // Từ chối trả hàng
        $update_sql = "UPDATE orders SET return_status = 'Từ chối' WHERE order_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $order_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Đã từ chối yêu cầu trả hàng!";
        } else {
            $error_message = "Không thể cập nhật trạng thái!";
        }
    }
}

// Lấy danh sách yêu cầu trả hàng
$return_sql = "SELECT o.*, u.username 
               FROM orders o 
               JOIN users u ON o.user_id = u.user_id 
               WHERE o.return_request = 1 
               ORDER BY o.return_request_date DESC";
$return_result = $conn->query($return_sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Yêu Cầu Trả Hàng - Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .return-requests {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .return-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .return-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 15px;
        }
        
        .return-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .return-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-approve {
            background: #28a745;
            color: white;
        }
        
        .btn-approve:hover {
            background: #218838;
        }
        
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        
        .btn-reject:hover {
            background: #c82333;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <main>
        <div class="return-requests">
            <h1><i class="fas fa-undo"></i> Quản Lý Yêu Cầu Trả Hàng</h1>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($return_result->num_rows > 0): ?>
                <?php while ($request = $return_result->fetch_assoc()): 
                    $status_class = '';
                    switch($request['return_status']) {
                        case 'Chờ duyệt': $status_class = 'pending'; break;
                        case 'Đã duyệt': $status_class = 'approved'; break;
                        case 'Từ chối': $status_class = 'rejected'; break;
                    }
                ?>
                <div class="return-card">
                    <div class="return-header">
                        <div>
                            <h3>Đơn hàng #<?php echo $request['order_id']; ?></h3>
                            <p style="color:#666;margin:5px 0;">
                                Khách hàng: <strong><?php echo htmlspecialchars($request['full_name']); ?></strong> 
                                (@<?php echo htmlspecialchars($request['username']); ?>)
                            </p>
                            <p style="color:#666;margin:5px 0;">
                                Yêu cầu lúc: <?php echo date('d/m/Y H:i', strtotime($request['return_request_date'])); ?>
                            </p>
                        </div>
                        <span class="return-status status-<?php echo $status_class; ?>">
                            <?php echo $request['return_status']; ?>
                        </span>
                    </div>
                    
                    <div style="background:#f8f9fa;padding:15px;border-radius:8px;margin-bottom:15px;">
                        <p style="margin:0;color:#333;font-weight:600;">Lý do trả hàng:</p>
                        <p style="margin:8px 0 0 0;color:#666;">
                            <?php echo nl2br(htmlspecialchars($request['return_reason'])); ?>
                        </p>
                    </div>
                    
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;margin-bottom:15px;">
                        <div>
                            <strong>Tổng tiền:</strong> 
                            <?php echo number_format($request['total_amount'], 0, ',', '.'); ?> VNĐ
                        </div>
                        <div>
                            <strong>Thanh toán:</strong> 
                            <?php echo $request['payment_method'] === 'bank_transfer' ? 'Chuyển khoản' : 'COD'; ?>
                        </div>
                        <div>
                            <strong>SĐT:</strong> <?php echo htmlspecialchars($request['phone']); ?>
                        </div>
                    </div>
                    
                    <?php if ($request['return_status'] === 'Chờ duyệt'): ?>
                    <div class="return-actions">
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận duyệt yêu cầu trả hàng? Tồn kho sẽ được hoàn lại.')">
                            <input type="hidden" name="order_id" value="<?php echo $request['order_id']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" name="process_return" class="btn btn-approve">
                                <i class="fas fa-check"></i> Duyệt trả hàng
                            </button>
                        </form>
                        
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận từ chối yêu cầu trả hàng?')">
                            <input type="hidden" name="order_id" value="<?php echo $request['order_id']; ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" name="process_return" class="btn btn-reject">
                                <i class="fas fa-times"></i> Từ chối
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align:center;padding:60px 20px;background:white;border-radius:12px;">
                    <i class="fas fa-inbox" style="font-size:64px;color:#ddd;margin-bottom:20px;"></i>
                    <h3 style="color:#666;">Không có yêu cầu trả hàng nào</h3>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'admin_footer.php'; ?>
</body>
</html>

<?php
$conn->close();
?>
