<?php
session_start();
require_once 'config/connect.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_page.php');
    exit();
}

// Tự động hủy đơn hàng quá hạn 24h
require_once 'auto_cancel_expired_orders.php';

// Lấy danh sách đơn hàng chờ thanh toán
$sql = "SELECT * FROM orders 
        WHERE order_status = 'Chờ thanh toán' AND payment_method = 'bank_transfer'
        ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Nhận Thanh Toán - Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .payment-confirmation {
            padding: 20px;
        }

        .payment-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .payment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ebe9e5;
        }

        .order-info {
            flex: 1;
        }

        .order-id {
            font-size: 20px;
            font-weight: 700;
            color: #333;
        }

        .order-meta {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }

        .order-amount {
            text-align: right;
        }

        .amount-label {
            color: #666;
            font-size: 14px;
        }

        .amount-value {
            font-size: 24px;
            font-weight: 700;
            color: #dc3545;
        }

        .payment-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .customer-info, .payment-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }

        .info-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .info-row {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .info-label {
            color: #666;
            font-weight: 500;
        }

        .payment-proof {
            grid-column: 1 / -1;
            text-align: center;
        }

        .proof-image {
            max-width: 100%;
            max-height: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            cursor: pointer;
            transition: transform 0.3s;
        }

        .proof-image:hover {
            transform: scale(1.02);
        }

        .no-proof {
            padding: 40px;
            background: #fff3cd;
            border-radius: 8px;
            color: #856404;
            border: 2px dashed #ffc107;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-approve {
            background: #28a745;
            color: white;
        }

        .btn-approve:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .btn-reject {
            background: #dc3545;
            color: white;
        }

        .btn-reject:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        .btn-view {
            background: #333;
            color: #ebe9e5;
        }

        .btn-view:hover {
            background: #555;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
        }

        .empty-state i {
            font-size: 64px;
            color: #28a745;
            margin-bottom: 20px;
        }

        .order-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .products-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }

        .products-table th,
        .products-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .products-table th {
            background: #e9ecef;
            font-weight: 600;
        }

        /* Modal for full image */
        .image-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }

        .image-modal img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 8px;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <main>
        <div class="payment-confirmation">
            <h1><i class="fas fa-check-circle"></i> Xác Nhận Thanh Toán</h1>

            <?php if ($result->num_rows > 0): ?>
                <?php while ($order = $result->fetch_assoc()): ?>
                    <div class="payment-card">
                        <div class="payment-header">
                            <div class="order-info">
                                <div class="order-id">
                                    <i class="fas fa-receipt"></i> Đơn hàng #<?php echo $order['order_id']; ?>
                                </div>
                                <div class="order-meta">
                                    <i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                    <span class="badge badge-warning" style="margin-left: 10px;">
                                        <i class="fas fa-hourglass-half"></i> Chờ thanh toán
                                    </span>
                                </div>
                            </div>
                            <div class="order-amount">
                                <div class="amount-label">Tổng tiền</div>
                                <div class="amount-value"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</div>
                            </div>
                        </div>

                        <div class="payment-body">
                            <div class="customer-info">
                                <div class="info-title"><i class="fas fa-user"></i> Thông tin khách hàng</div>
                                <div class="info-row">
                                    <span class="info-label">Họ tên:</span> 
                                    <strong><?php echo htmlspecialchars($order['full_name']); ?></strong>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Số điện thoại:</span> 
                                    <strong><?php echo htmlspecialchars($order['phone']); ?></strong>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Email:</span> 
                                    <?php echo htmlspecialchars($order['email']); ?>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Địa chỉ:</span> 
                                    <?php echo htmlspecialchars($order['address']); ?>
                                </div>
                                <?php if ($order['notes']): ?>
                                    <div class="info-row">
                                        <span class="info-label">Ghi chú:</span> 
                                        <?php echo htmlspecialchars($order['notes']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="payment-info">
                                <div class="info-title"><i class="fas fa-credit-card"></i> Thông tin thanh toán</div>
                                <div class="info-row">
                                    <span class="info-label">Hình thức:</span> 
                                    <strong style="color: #dc3545;">Chuyển khoản ngân hàng</strong>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Nội dung CK:</span> 
                                    <strong>HKT <?php echo $order['order_id']; ?> <?php 
                                    $name_parts = explode(' ', $order['full_name']);
                                    echo implode(' ', array_slice($name_parts, -2)); 
                                    ?> <?php echo $order['phone']; ?></strong>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Trạng thái:</span> 
                                    <span class="badge badge-warning">Chờ xác nhận</span>
                                </div>
                            </div>

                            <div class="payment-proof">
                                <div class="info-title"><i class="fas fa-image"></i> Chứng từ chuyển khoản</div>
                                <?php if (!empty($order['payment_proof'])): ?>
                                    <img src="<?php echo htmlspecialchars($order['payment_proof']); ?>" 
                                         alt="Payment Proof" 
                                         class="proof-image"
                                         onclick="openImageModal('<?php echo htmlspecialchars($order['payment_proof']); ?>')">
                                <?php else: ?>
                                    <div class="no-proof">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <p><strong>Khách hàng chưa upload chứng từ chuyển khoản</strong></p>
                                        <p>Vui lòng chờ khách hàng upload hoặc liên hệ xác nhận</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Chi tiết sản phẩm -->
                            <div class="order-details">
                                <div class="info-title"><i class="fas fa-box"></i> Sản phẩm đã đặt</div>
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
                                <table class="products-table">
                                    <thead>
                                        <tr>
                                            <th>Sản phẩm</th>
                                            <th>Số lượng</th>
                                            <th>Đơn giá</th>
                                            <th>Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($detail = $details->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($detail['product_name']); ?></td>
                                                <td><?php echo $detail['quantity']; ?></td>
                                                <td><?php echo number_format($detail['price'], 0, ',', '.'); ?> VNĐ</td>
                                                <td><strong><?php echo number_format($detail['price'] * $detail['quantity'], 0, ',', '.'); ?> VNĐ</strong></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <button class="btn btn-reject" onclick="rejectPayment(<?php echo $order['order_id']; ?>)">
                                <i class="fas fa-times"></i> Từ chối
                            </button>
                            <button class="btn btn-approve" onclick="approvePayment(<?php echo $order['order_id']; ?>)">
                                <i class="fas fa-check"></i> Xác nhận thanh toán
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h2>Không có đơn hàng chờ thanh toán</h2>
                    <p>Tất cả đơn chuyển khoản đã được xử lý</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Image Modal -->
    <div class="image-modal" id="imageModal" onclick="closeImageModal()">
        <img id="modalImage" src="" alt="Payment Proof">
    </div>

    <script>
        function openImageModal(imageSrc) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modal.style.display = 'flex';
            modalImg.src = imageSrc;
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        function approvePayment(orderId) {
            if (!confirm('Xác nhận đã nhận được thanh toán cho đơn hàng #' + orderId + '?')) {
                return;
            }

            fetch('confirm_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    order_id: orderId,
                    action: 'approve'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Đã xác nhận thanh toán thành công!');
                    location.reload();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra. Vui lòng thử lại!');
            });
        }

        function rejectPayment(orderId) {
            const reason = prompt('Lý do từ chối thanh toán:');
            if (!reason) return;

            fetch('confirm_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    order_id: orderId,
                    action: 'reject',
                    reason: reason
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Đã từ chối thanh toán');
                    location.reload();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra. Vui lòng thử lại!');
            });
        }
    </script>

    <?php include 'admin_footer.php'; ?>
</body>
</html>

<?php $conn->close(); ?>
