<?php
session_start();
require_once 'config/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login_page.php');
    exit();
}

// Tự động hủy đơn hàng quá hạn 24h
require_once 'auto_cancel_expired_orders.php';

$user_id = $_SESSION['user_id'];

// Lấy danh sách đơn hàng của user
$order_sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$orders = $order_stmt->get_result();

// Số ngày cho phép trả hàng
$return_days_limit = 7;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theo dõi đơn hàng - HKT Store</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/theodoidonhang.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="order-tracking">
        <h2>Theo dõi đơn hàng</h2>

        <?php if ($orders->num_rows > 0): ?>
            <?php while ($order = $orders->fetch_assoc()): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <h3>Don hàng #<?php echo $order['order_id']; ?></h3>
                            <p>Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                            <span class="order-status status-<?php echo strtolower($order['order_status']); ?>">
                                <?php echo $order['order_status']; ?>
                            </span>
                            <?php if ($order['payment_method'] === 'bank_transfer' && $order['order_status'] === 'Chờ thanh toán'): ?>
                                <span style="background:#dc3545;color:white;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;">
                                    <i class="fas fa-exclamation-circle"></i> Chưa thanh toán
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="order-details">
                        <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                        <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                        <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                        <p><strong>Hình thức thanh toán:</strong> 
                            <?php if ($order['payment_method'] === 'bank_transfer'): ?>
                                <span style="color:#dc3545;font-weight:600;"><i class="fas fa-university"></i> Chuyển khoản</span>
                            <?php else: ?>
                                <span style="color:#28a745;font-weight:600;"><i class="fas fa-money-bill-wave"></i> COD</span>
                            <?php endif; ?>
                        </p>
                        
                        <?php if ($order['payment_method'] === 'bank_transfer' && $order['order_status'] === 'Chờ thanh toán'): 
                            $created_time = strtotime($order['order_date']);
                            $hours_passed = floor((time() - $created_time) / 3600);
                            $hours_left = 24 - $hours_passed;
                        ?>
                            <div style="background:#fff3cd;border-left:4px solid #ffc107;padding:15px;margin:15px 0;border-radius:8px;">
                                <p style="margin:0;color:#856404;font-weight:600;">
                                    <i class="fas fa-clock"></i> 
                                    <?php if ($hours_left > 0): ?>
                                        Còn <strong><?php echo $hours_left; ?> giờ</strong> để hoàn tất thanh toán
                                    <?php else: ?>
                                        Đơn hàng sắp hết hạn thanh toán!
                                    <?php endif; ?>
                                </p>
                                <p style="margin:5px 0 0 0;color:#856404;font-size:13px;">
                                    Vui lòng chuyển khoản theo thông tin đã gửi sau khi đặt hàng
                                </p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($order['notes']): ?>
                            <p><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order['notes']); ?></p>
                        <?php endif; ?>

                        <div class="product-list">
                            <h4>Sản phẩm đã đặt:</h4>
                            <?php
                            $detail_sql = "SELECT od.*, p.product_name, p.image_url 
                                         FROM order_details od 
                                         JOIN products p ON od.product_id = p.product_id 
                                         WHERE od.order_id = ?";
                            $detail_stmt = $conn->prepare($detail_sql);
                            $detail_stmt->bind_param("i", $order['order_id']);
                            $detail_stmt->execute();
                            $details = $detail_stmt->get_result();
                            
                            while ($detail = $details->fetch_assoc()):
                            ?>
                                <div class="product-item">
                                    <img src="<?php echo htmlspecialchars($detail['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($detail['product_name']); ?>">
                                    <div>
                                        <h5><?php echo htmlspecialchars($detail['product_name']); ?></h5>
                                        <p>Số lượng: <?php echo $detail['quantity']; ?></p>
                                        <p>Giá: <?php echo number_format($detail['price'], 0, ',', '.'); ?> VNĐ</p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <div class="total-amount">
                            Tổng tiền: <?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ
                        </div>
                    </div>

                    <?php 
                    // Kiểm tra các trạng thái trả hàng
                    $return_statuses = ['Chờ xác nhận trả hàng', 'Đã duyệt trả hàng', 'Không đồng ý duyệt trả hàng'];
                    $is_return_order = in_array($order['order_status'], $return_statuses);
                    
                    if ($order['order_status'] == 'Hoàn thành' && !$is_return_order): 
                        $completed_date = (isset($order['completed_date']) && $order['completed_date']) ? strtotime($order['completed_date']) : time();
                        $days_passed = floor((time() - $completed_date) / 86400);
                        $days_left = $return_days_limit - $days_passed;
                        $customer_confirmed = $order['customer_confirmed'] ?? 0;
                        $return_request = $order['return_request'] ?? 0;
                        $return_status = $order['return_status'] ?? '';
                    ?>
                        <div class="order-actions" style="background:#f8f9fa;padding:20px;margin-top:15px;border-radius:8px;">
                            <h4 style="margin-bottom:15px;color:#333;"><i class="fas fa-tasks"></i> Thao tác với đơn hàng</h4>
                            
                            <?php if ($return_request == 1): ?>
                                <!-- Đã yêu cầu trả hàng -->
                                <?php
                                // Xác định màu background dựa trên order_status
                                $bg_color = '#fff3cd';
                                $border_color = '#ffc107';
                                $text_color = '#856404';
                                $status_text = $order['order_status'];
                                
                                if ($order['order_status'] === 'Đã duyệt trả hàng') {
                                    $bg_color = '#d4edda';
                                    $border_color = '#28a745';
                                    $text_color = '#155724';
                                } elseif ($order['order_status'] === 'Không đồng ý duyệt trả hàng') {
                                    $bg_color = '#f8d7da';
                                    $border_color = '#dc3545';
                                    $text_color = '#721c24';
                                }
                                ?>
                                <div style="background:<?php echo $bg_color; ?>;border-left:4px solid <?php echo $border_color; ?>;padding:15px;border-radius:8px;">
                                    <p style="margin:0;color:<?php echo $text_color; ?>;font-weight:600;">
                                        <i class="fas fa-undo"></i> Đã gửi yêu cầu trả hàng/hoàn tiền
                                    </p>
                                    <p style="margin:8px 0 0 0;color:<?php echo $text_color; ?>;font-size:14px;">
                                        <strong>Trạng thái:</strong> 
                                        <span style="background:#fff;padding:4px 12px;border-radius:12px;display:inline-block;margin-top:5px;">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </p>
                                    <?php if ($order['return_reason']): ?>
                                        <p style="margin:8px 0 0 0;color:<?php echo $text_color; ?>;font-size:14px;">
                                            <strong>Lý do:</strong> <?php echo htmlspecialchars($order['return_reason']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($customer_confirmed == 1): ?>
                                <!-- Đã xác nhận hài lòng -->
                                <div style="background:#d4edda;border-left:4px solid #28a745;padding:15px;border-radius:8px;">
                                    <p style="margin:0;color:#155724;font-weight:600;">
                                        <i class="fas fa-check-circle"></i> Bạn đã xác nhận hài lòng với đơn hàng này
                                    </p>
                                    <p style="margin:8px 0 0 0;color:#155724;font-size:14px;">
                                        Cảm ơn bạn đã tin tưởng và mua sắm tại HKT Store!
                                    </p>
                                </div>
                            <?php elseif ($days_left > 0): ?>
                                <!-- Trong thời gian cho phép trả hàng -->
                                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                    <button onclick="confirmDelivery(<?php echo $order['order_id']; ?>)" 
                                            class="btn-confirm-delivery" 
                                            style="flex:1;min-width:200px;padding:12px 20px;background:#28a745;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600;transition:all 0.3s;">
                                        <i class="fas fa-check-circle"></i> Đã nhận hàng và hài lòng
                                    </button>
                                    <button onclick="openReturnModal(<?php echo $order['order_id']; ?>)" 
                                            class="btn-request-return" 
                                            style="flex:1;min-width:200px;padding:12px 20px;background:#dc3545;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600;transition:all 0.3s;">
                                        <i class="fas fa-undo"></i> Yêu cầu trả hàng/hoàn tiền
                                    </button>
                                </div>
                                <p style="margin:10px 0 0 0;color:#666;font-size:13px;">
                                    <i class="fas fa-info-circle"></i> 
                                    Còn <strong><?php echo $days_left; ?> ngày</strong> để yêu cầu trả hàng/hoàn tiền
                                </p>
                            <?php else: ?>
                                <!-- Hết thời gian trả hàng -->
                                <div style="background:#f8d7da;border-left:4px solid #dc3545;padding:15px;border-radius:8px;">
                                    <p style="margin:0;color:#721c24;font-weight:600;">
                                        <i class="fas fa-exclamation-triangle"></i> Đã hết thời gian trả hàng/hoàn tiền
                                    </p>
                                    <p style="margin:8px 0 0 0;color:#721c24;font-size:14px;">
                                        Thời gian cho phép trả hàng là <?php echo $return_days_limit; ?> ngày kể từ khi nhận hàng
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Đánh giá sản phẩm - chỉ hiện khi đã xác nhận hài lòng -->
                    <?php if ($order['order_status'] == 'Hoàn thành' && $order['customer_confirmed'] == 1): ?>
                        <div class="review-section">
                            <h4><i class="fas fa-star"></i> Đánh giá sản phẩm</h4>
                            <?php
                            $detail_sql = "SELECT od.*, p.product_name, p.image_url, r.rating, r.content as review_content
                                          FROM order_details od 
                                          JOIN products p ON od.product_id = p.product_id 
                                          LEFT JOIN reviews r ON r.product_id = p.product_id AND r.user_id = ?
                                          WHERE od.order_id = ?";
                            $detail_stmt = $conn->prepare($detail_sql);
                            $detail_stmt->bind_param("ii", $_SESSION['user_id'], $order['order_id']);
                            $detail_stmt->execute();
                            $details = $detail_stmt->get_result();
                            
                            while ($detail = $details->fetch_assoc()):
                            ?>
                                <div class="product-review">
                                    <img src="<?php echo htmlspecialchars($detail['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($detail['product_name']); ?>">
                                    <div class="review-form">
                                        <h5><?php echo htmlspecialchars($detail['product_name']); ?></h5>
                                        <?php if (!$detail['rating']): ?>
                                            <form method="POST" action="submit_review.php">
                                                <input type="hidden" name="product_id" value="<?php echo $detail['product_id']; ?>">
                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                <div class="rating">
                                                    <input type="radio" name="rating" value="5" id="5"><label for="5">☆</label>
                                                    <input type="radio" name="rating" value="4" id="4"><label for="4">☆</label>
                                                    <input type="radio" name="rating" value="3" id="3"><label for="3">☆</label>
                                                    <input type="radio" name="rating" value="2" id="2"><label for="2">☆</label>
                                                    <input type="radio" name="rating" value="1" id="1"><label for="1">☆</label>
                                                </div>
                                                <textarea name="review_content" placeholder="Nhập đánh giá của bạn"></textarea>
                                                <button type="submit" name="submit_review">Gửi đánh giá</button>
                                            </form>
                                        <?php else: ?>
                                            <div class="existing-review">
                                                <div class="stars">
                                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                                        <span class="<?php echo $i <= $detail['rating'] ? 'filled' : ''; ?>">★</span>
                                                    <?php endfor; ?>
                                                </div>
                                                <p><?php echo htmlspecialchars($detail['review_content']); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Thông báo cần xác nhận trước khi đánh giá -->
                    <?php if ($order['order_status'] == 'Hoàn thành' && $order['customer_confirmed'] == 0): ?>
                        <div style="background:#e3f2fd;border-left:4px solid #2196f3;padding:15px;margin:20px 0;border-radius:8px;">
                            <p style="margin:0;color:#1565c0;font-size:14px;line-height:1.6;">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Lưu ý:</strong> Vui lòng xác nhận đã nhận hàng và hài lòng để có thể đánh giá sản phẩm.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-orders">
                <i class="fas fa-box-open fa-3x"></i>
                <h3>Bạn chưa có đơn hàng nào</h3>
                <p>Hãy đặt hàng để xem thông tin đơn hàng tại đây</p>
                <a href="products.php" class="btn-order">Mua sắm ngay</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Yêu Cầu Trả Hàng -->
    <div id="returnModal" class="modal" style="display:none !important;">
        <div class="modal-content" style="max-width:500px !important;background:white !important;border-radius:12px !important;box-shadow:0 10px 40px rgba(0,0,0,0.3) !important;position:relative !important;">
            <div class="modal-header" style="padding:20px !important;border-bottom:2px solid #667eea !important;background:white !important;">
                <h3 style="margin:0 !important;color:#333 !important;font-size:1.5rem !important;display:block !important;"><i class="fas fa-undo"></i> Yêu cầu trả hàng/hoàn tiền</h3>
                <span class="close-modal" onclick="closeReturnModal()" style="position:absolute !important;right:20px !important;top:20px !important;font-size:28px !important;cursor:pointer !important;color:#999 !important;line-height:1 !important;">&times;</span>
            </div>
            <form id="returnForm" style="display:block !important;">
                <div class="modal-body" style="padding:25px !important;display:block !important;background:white !important;">
                    <input type="hidden" id="return_order_id" name="order_id">
                    
                    <div class="form-group" style="margin-bottom:20px !important;display:block !important;">
                        <label style="display:block !important;margin-bottom:8px !important;font-weight:600 !important;color:#333 !important;font-size:15px !important;">
                            <i class="fas fa-comment-dots"></i> Lý do trả hàng <span style="color:red !important;">*</span>
                        </label>
                        <textarea name="return_reason" 
                                  style="width:100% !important;padding:12px !important;border:2px solid #ddd !important;border-radius:8px !important;min-height:120px !important;font-family:inherit !important;font-size:14px !important;resize:vertical !important;display:block !important;box-sizing:border-box !important;"
                                  placeholder="Vui lòng mô tả rõ lý do bạn muốn trả hàng (sản phẩm lỗi, không đúng mô tả, v.v.)"
                                  required></textarea>
                    </div>
                    
                    <div style="background:#fff3cd !important;border-left:4px solid #ffc107 !important;padding:15px !important;margin:20px 0 !important;border-radius:8px !important;display:block !important;">
                        <p style="margin:0 !important;color:#856404 !important;font-size:14px !important;line-height:1.6 !important;display:block !important;">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Lưu ý:</strong> Chúng tôi sẽ xem xét yêu cầu trong vòng 24-48h và liên hệ lại với bạn qua email/số điện thoại đã đăng ký.
                        </p>
                    </div>
                </div>
                
                <div class="modal-footer" style="padding:20px !important;border-top:1px solid #ddd !important;display:flex !important;gap:10px !important;justify-content:flex-end !important;background:white !important;">
                    <button type="button" onclick="closeReturnModal()" class="btn-cancel" 
                            style="padding:12px 24px !important;background:#6c757d !important;color:white !important;border:none !important;border-radius:8px !important;cursor:pointer !important;font-weight:600 !important;font-size:15px !important;">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn-confirm" 
                            style="padding:12px 24px !important;background:#dc3545 !important;color:white !important;border:none !important;border-radius:8px !important;cursor:pointer !important;font-weight:600 !important;font-size:15px !important;">
                        <i class="fas fa-paper-plane"></i> Gửi yêu cầu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Xác nhận đã nhận hàng
        function confirmDelivery(orderId) {
            if (confirm('Xác nhận bạn đã nhận hàng và hài lòng với sản phẩm?')) {
                fetch('process_order_action.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'order_id=' + orderId + '&action=confirm_delivery'
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => {
                    alert('Có lỗi xảy ra. Vui lòng thử lại!');
                });
            }
        }

        // Mở modal yêu cầu trả hàng
        function openReturnModal(orderId) {
            document.getElementById('return_order_id').value = orderId;
            const modal = document.getElementById('returnModal');
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        // Đóng modal
        function closeReturnModal() {
            const modal = document.getElementById('returnModal');
            modal.style.display = 'none';
            document.getElementById('returnForm').reset();
            document.body.style.overflow = 'auto';
        }

        // Xử lý submit form trả hàng
        document.getElementById('returnForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'request_return');
            
            fetch('process_order_action.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    closeReturnModal();
                    location.reload();
                }
            })
            .catch(error => {
                alert('Có lỗi xảy ra. Vui lòng thử lại!');
            });
        });

        // Đóng modal khi click bên ngoài
        document.getElementById('returnModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReturnModal();
            }
        });
        
        // Style hover cho buttons
        document.addEventListener('DOMContentLoaded', function() {
            const confirmBtns = document.querySelectorAll('.btn-confirm-delivery');
            confirmBtns.forEach(btn => {
                btn.addEventListener('mouseenter', () => btn.style.background = '#218838');
                btn.addEventListener('mouseleave', () => btn.style.background = '#28a745');
            });
            
            const returnBtns = document.querySelectorAll('.btn-request-return');
            returnBtns.forEach(btn => {
                btn.addEventListener('mouseenter', () => btn.style.background = '#c82333');
                btn.addEventListener('mouseleave', () => btn.style.background = '#dc3545');
            });
        });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>

<?php
$conn->close();
?>