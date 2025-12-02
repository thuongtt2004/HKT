<?php
session_start();
require_once 'config/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login_page.php');
    exit();
}

// Lấy thông tin giỏ hàng
$user_id = $_SESSION['user_id'];
// Sửa lại câu truy vấn SQL để lấy thông tin đơn hàng
$sql = "SELECT c.cart_id, c.quantity, p.product_id, p.product_name, p.price, p.image_url 
        FROM cart c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();
$has_items = $cart_items->num_rows > 0;
$total = 0;

// Lấy thông tin người dùng
$user_sql = "SELECT * FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Hàng - HKT Store</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dathang.css">
    <link rel="stylesheet" href="css/modal.css">
    <link rel="stylesheet" href="css/payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php require_once 'header.php'; ?>

    <h1 class="page-title"><i class="fas fa-shopping-cart"></i> Giỏ hàng của bạn</h1>

    <?php if ($has_items): ?>
        <div class="cart-container">
            <div class="cart-items">
                <?php while ($item = $cart_items->fetch_assoc()): 
                    $subtotal = $item['quantity'] * $item['price'];
                    $total += $subtotal;
                ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        
                        <div class="item-details">
                            <h3 class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></h3>
                            <p class="item-price">Giá: <?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</p>
                            <div class="quantity-controls">
                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, -1)">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="quantity-display"><?php echo $item['quantity']; ?></span>
                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, 1)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="item-actions">
                            <p class="item-total">Tổng: <?php echo number_format($subtotal, 0, ',', '.'); ?> VNĐ</p>
                            <button class="remove-btn" onclick="removeFromCart(<?php echo $item['cart_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="cart-summary">
                <h3><i class="fas fa-receipt"></i> Chi tiết đơn hàng</h3>
                
                <!-- Promotion Section -->
                <div class="promotion-section">
                    <label class="promo-label">
                        <i class="fas fa-ticket-alt"></i> Mã giảm giá
                    </label>
                    <div class="coupon-input-wrapper">
                        <input type="text" id="couponCode" placeholder="Nhập mã giảm giá" class="coupon-input">
                        <button type="button" onclick="applyCoupon()" class="apply-coupon-btn">
                            <i class="fas fa-tag"></i> Áp dụng
                        </button>
                    </div>
                    <div id="couponMessage" class="coupon-message"></div>
                    <div id="appliedPromotion" class="applied-promotion" style="display: none;">
                        <div class="promo-info">
                            <i class="fas fa-check-circle"></i>
                            <span id="promotionText"></span>
                        </div>
                        <button type="button" onclick="removePromotion()" class="remove-promo-btn" title="Xóa khuyến mãi">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <div class="price-breakdown">
                    <div class="price-row subtotal-row">
                        <span>Tạm tính:</span>
                        <span id="subtotalAmount"><?php echo number_format($total, 0, ',', '.'); ?> VNĐ</span>
                    </div>
                    <div class="price-row discount-row" id="discountRow" style="display: none;">
                        <span><i class="fas fa-tag"></i> Giảm giá:</span>
                        <span id="discountAmount">-0 VNĐ</span>
                    </div>
                    <div class="price-row total-row">
                        <span><strong>Tổng cộng:</strong></span>
                        <span id="totalAmount" class="total-amount"><?php echo number_format($total, 0, ',', '.'); ?> VNĐ</span>
                    </div>
                </div>
                
                <button class="btn-order-main" onclick="openOrderModal()">
                    <i class="fas fa-shopping-bag"></i> Đặt hàng
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="cart-container">
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Giỏ hàng của bạn đang trống</p>
                <p style="color: #666; margin: 10px 0;">Vui lòng thêm ít nhất 1 sản phẩm để đặt hàng</p>
                <a href="products.php" class="submit-btn">
                    <i class="fas fa-store"></i> Tiếp tục mua sắm
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Modal xác nhận thông tin đặt hàng -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-check"></i> Xác nhận thông tin đặt hàng</h2>
                <span class="close-modal" onclick="closeOrderModal()">&times;</span>
            </div>
            <form id="orderForm" onsubmit="return handleOrderSubmit(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Họ và tên:</label>
                        <input type="text" id="modal_full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email:</label>
                        <input type="email" id="modal_email" name="email" 
                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Số điện thoại:</label>
                        <input type="tel" id="modal_phone" name="phone" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                               pattern="[0-9]{10,11}" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Địa chỉ giao hàng:</label>
                        <textarea id="modal_address" name="address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-sticky-note"></i> Ghi chú (không bắt buộc):</label>
                        <textarea id="modal_notes" name="notes" rows="2" placeholder="Ghi chú thêm về đơn hàng..."></textarea>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-credit-card"></i> Phương thức thanh toán:</label>
                        <div class="payment-methods">
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="cod" checked>
                                <span class="payment-label">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <div>
                                        <strong>Thanh toán khi nhận hàng (COD)</strong>
                                        <small>Thanh toán bằng tiền mặt khi nhận hàng</small>
                                    </div>
                                </span>
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="bank_transfer">
                                <span class="payment-label">
                                    <i class="fas fa-university"></i>
                                    <div>
                                        <strong>Chuyển khoản ngân hàng</strong>
                                        <small>Chuyển khoản trước khi giao hàng</small>
                                    </div>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Bank Transfer Info (hidden by default) -->
                    <div class="bank-transfer-info" id="bankTransferInfo" style="display: none;">
                        <div class="bank-card">
                            <h4><i class="fas fa-university"></i> Thông tin chuyển khoản</h4>
                            <div class="bank-details">
                                <p><strong>Ngân hàng:</strong> MB Bank (Ngân hàng Quân Đội)</p>
                                <p><strong>Số tài khoản:</strong> <span class="account-number">0220623499999</span></p>
                                <p><strong>Chủ tài khoản:</strong> TRAN THANH THUONG</p>
                                <p><strong>Chi nhánh:</strong> TP. Hồ Chí Minh</p>
                            </div>
                        </div>
                        <div class="upload-proof" style="display: none;">
                            <label><i class="fas fa-image"></i> Tải lên ảnh chuyển khoản (không bắt buộc):</label>
                            <input type="file" name="payment_proof" id="paymentProof" accept="image/*">
                            <small style="color: #666;">Hỗ trợ: JPG, PNG (tối đa 5MB)</small>
                        </div>
                    </div>

                    <div class="order-summary-modal">
                        <h3><i class="fas fa-receipt"></i> Tổng đơn hàng:</h3>
                        <div class="price-details">
                            <div class="price-line" id="modalSubtotal">
                                <span>Tạm tính:</span>
                                <span><?php echo number_format($total, 0, ',', '.'); ?> VNĐ</span>
                            </div>
                            <div class="price-line discount-line" id="modalDiscount" style="display: none;">
                                <span><i class="fas fa-tag"></i> Giảm giá:</span>
                                <span style="color: #28a745;">-0 VNĐ</span>
                            </div>
                            <div class="price-line total-line">
                                <span><strong>Tổng cộng:</strong></span>
                                <span class="modal-total" id="modalTotal"><?php echo number_format($total, 0, ',', '.'); ?> VNĐ</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeOrderModal()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn-confirm">
                        <i class="fas fa-check-circle"></i> Xác nhận đặt hàng
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Mở modal đặt hàng
        function openOrderModal() {
            const modal = document.getElementById('orderModal');
            const modalBody = modal.querySelector('.modal-body');
            const formGroups = modal.querySelectorAll('.form-group');
            
            modal.style.display = 'flex';
            modal.style.alignItems = 'center';
            modal.style.justifyContent = 'center';
            document.body.style.overflow = 'hidden';
            
            // Force hiển thị modal-body và form-groups
            if (modalBody) {
                modalBody.style.display = 'block';
                modalBody.style.visibility = 'visible';
                modalBody.style.opacity = '1';
                modalBody.style.height = 'auto';
            }
            
            formGroups.forEach(group => {
                group.style.display = 'block';
                group.style.visibility = 'visible';
                group.style.opacity = '1';
            });
            
            // Cập nhật giá trong modal nếu có khuyến mãi
            updateModalPrice();
        }

        // Đóng modal
        function closeOrderModal() {
            document.getElementById('orderModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target === modal) {
                closeOrderModal();
            }
        }

        // Cập nhật số lượng sản phẩm
        function updateQuantity(cartId, change) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cart_id: cartId,
                    change: change
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra khi cập nhật số lượng');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi cập nhật số lượng');
            });
        }

        // Xóa sản phẩm khỏi giỏ hàng
        function removeFromCart(cartId) {
            if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
                fetch('delete_cart_item.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        cart_id: cartId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi xóa sản phẩm');
                });
            }
        }

        // Xử lý submit form đặt hàng
        function handleOrderSubmit(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const totalAmountEl = document.getElementById('totalAmount');
            const totalText = totalAmountEl.textContent.replace(/[^\d]/g, '');
            const totalAmount = parseInt(totalText);
            formData.append('total_amount', totalAmount);
            
            // Thêm thông tin khuyến mãi nếu có
            if (window.appliedPromotionData) {
                formData.append('promotion_id', window.appliedPromotionData.promotion_id);
                formData.append('discount_amount', window.appliedPromotionData.discount_amount);
            }

            // Hiển thị loading
            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

            fetch('process_order.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Đóng modal xác nhận
                    closeOrderModal();
                    
                    // Nếu chọn thanh toán chuyển khoản, hiển thị QR code với order_id
                    if (data.payment_method === 'bank_transfer') {
                        // Gọi openBankApp với order_id thật
                        setTimeout(() => {
                            openBankApp(data.order_id);
                        }, 300);
                        
                        // Hiển thị thông báo
                        setTimeout(() => {
                            alert('Đặt hàng thành công! Mã đơn hàng: #' + data.order_id + '\nVui lòng chuyển khoản theo thông tin hiển thị.');
                        }, 500);
                    } else {
                        alert('Đặt hàng thành công! Mã đơn hàng: #' + data.order_id + '\nCảm ơn bạn đã mua hàng!');
                        window.location.href = 'home.php';
                    }
                } else {
                    alert('Lỗi: ' + data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại!');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });

            return false;
        }

        // Apply promotion
        let appliedPromotionData = null;
        
        async function applyCoupon() {
            const couponCode = document.getElementById('couponCode').value.trim();
            if (!couponCode) {
                showCouponMessage('Vui lòng nhập mã giảm giá', 'error');
                return;
            }
            
            await applyPromotion(couponCode);
        }
        
        async function applyPromotion(couponCode = '') {
            // Lấy thông tin giỏ hàng
            const cartItems = [];
            <?php 
            $cart_items->data_seek(0);
            while ($item = $cart_items->fetch_assoc()): 
            ?>
            cartItems.push({
                product_id: '<?php echo $item['product_id']; ?>',
                quantity: <?php echo $item['quantity']; ?>,
                price: <?php echo $item['price']; ?>
            });
            <?php endwhile; ?>
            
            const formData = new FormData();
            formData.append('cart_items', JSON.stringify(cartItems));
            if (couponCode) {
                formData.append('coupon_code', couponCode);
            }
            
            try {
                const response = await fetch('apply_promotion.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success && data.applied_promotions.length > 0) {
                    const promo = data.applied_promotions[0];
                    window.appliedPromotionData = promo;
                    
                    // Hiển thị khuyến mãi đã áp dụng
                    document.getElementById('appliedPromotion').style.display = 'flex';
                    document.getElementById('promotionText').textContent = promo.promotion_name;
                    
                    // Hiển thị discount
                    document.getElementById('discountRow').style.display = 'flex';
                    document.getElementById('discountAmount').textContent = 
                        '-' + Math.round(data.discount).toLocaleString('vi-VN') + ' VNĐ';
                    
                    // Cập nhật tổng tiền
                    document.getElementById('totalAmount').textContent = 
                        Math.round(data.total).toLocaleString('vi-VN') + ' VNĐ';
                    
                    showCouponMessage(data.message, 'success');
                    document.getElementById('couponCode').value = '';
                } else {
                    showCouponMessage(data.message || 'Không có khuyến mãi áp dụng', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showCouponMessage('Có lỗi xảy ra khi áp dụng khuyến mãi', 'error');
            }
        }
        
        function removePromotion() {
            window.appliedPromotionData = null;
            document.getElementById('appliedPromotion').style.display = 'none';
            document.getElementById('discountRow').style.display = 'none';
            
            // Reset về giá gốc
            const subtotal = <?php echo $total; ?>;
            document.getElementById('totalAmount').textContent = 
                subtotal.toLocaleString('vi-VN') + ' VNĐ';
            
            showCouponMessage('Đã hủy khuyến mãi', 'info');
        }
        
        function showCouponMessage(message, type) {
            const messageEl = document.getElementById('couponMessage');
            messageEl.textContent = message;
            messageEl.className = 'coupon-message ' + type;
            messageEl.style.display = 'block';
            
            setTimeout(() => {
                messageEl.style.display = 'none';
            }, 5000);
        }
        
        // Auto-apply flash sale and other promotions
        document.addEventListener('DOMContentLoaded', function() {
            applyPromotion();
            
            // Payment method toggle
            const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
            const bankInfo = document.getElementById('bankTransferInfo');
            
            paymentRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'bank_transfer') {
                        bankInfo.style.display = 'block';
                    } else {
                        bankInfo.style.display = 'none';
                    }
                });
            });
        });
        
        // Copy account number
        function copyAccountNumber() {
            const accountNumber = '0220623499999';
            navigator.clipboard.writeText(accountNumber).then(() => {
                alert('Đã sao chép số tài khoản: ' + accountNumber);
            }).catch(err => {
                const textArea = document.createElement('textarea');
                textArea.value = accountNumber;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Đã sao chép số tài khoản: ' + accountNumber);
            });
        }
        
        // Update modal price with promotion
        function updateModalPrice() {
            const totalAmountEl = document.getElementById('totalAmount');
            const discountAmountEl = document.getElementById('discountAmount');
            const subtotal = <?php echo $total; ?>;
            
            if (window.appliedPromotionData) {
                // Hiển thị discount trong modal
                const modalDiscountEl = document.getElementById('modalDiscount');
                modalDiscountEl.style.display = 'flex';
                modalDiscountEl.querySelector('span:last-child').textContent = discountAmountEl.textContent;
                
                // Cập nhật tổng trong modal
                document.getElementById('modalTotal').textContent = totalAmountEl.textContent;
            } else {
                // Không có khuyến mãi
                document.getElementById('modalDiscount').style.display = 'none';
                document.getElementById('modalTotal').textContent = subtotal.toLocaleString('vi-VN') + ' VNĐ';
            }
        }
        
        // Open bank app with deep link (VietQR standard)
        function openBankApp(orderId = null) {
            const fullName = document.getElementById('modal_full_name').value.trim();
            const phone = document.getElementById('modal_phone').value.trim();
            
            if (!fullName || !phone) {
                alert('Vui lòng nhập đầy đủ thông tin trước!');
                return;
            }
            
            // Lấy số tiền sau giảm giá
            const totalText = document.getElementById('totalAmount').textContent.replace(/[^\d]/g, '');
            const amount = parseInt(totalText);
            
            // Tạo nội dung chuyển khoản
            // Nếu chưa có orderId (trước khi đặt hàng), dùng XXXX
            const orderIdStr = orderId ? orderId : 'XXXX';
            const shortName = fullName.split(' ').slice(-2).join(' '); // Lấy 2 từ cuối của tên
            const content = `HKT ${orderIdStr} ${shortName} ${phone}`;
            
            // VietQR deep link format (hoạt động với hầu hết app ngân hàng VN)
            // Format: bankapp://pay?beneficiary=<account>&bank=<bank_code>&amount=<amount>&description=<content>
            const bankCode = 'MB'; // MB Bank
            const accountNumber = '0220623499999';
            const accountName = 'TRAN THANH THUONG';
            
            // VietQR Universal App Link (mở app ngân hàng của người dùng)
            const vietQRLink = `https://img.vietqr.io/image/${bankCode}-${accountNumber}-compact2.jpg?amount=${amount}&addInfo=${encodeURIComponent(content)}&accountName=${encodeURIComponent(accountName)}`;
            
            // Intent link cho Android (mở app banking)
            const intentLink = `intent://pay?beneficiary=${accountNumber}&bank=${bankCode}&amount=${amount}&description=${encodeURIComponent(content)}#Intent;scheme=bankapp;package=com.mbbank.mb;end`;
            
            // Thử mở app ngân hàng trước
            const bankAppUrl = `mbbank://pay?account=${accountNumber}&amount=${amount}&content=${encodeURIComponent(content)}`;
            
            // Hiển thị QR code và các options
            const qrModal = `
                <div style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);z-index:20000;display:flex;align-items:center;justify-content:center;overflow-y:auto;padding:20px;" onclick="this.remove()">
                    <div style="background:white;padding:30px;border-radius:15px;max-width:500px;width:100%;text-align:center;margin:auto;max-height:90vh;overflow-y:auto;" onclick="event.stopPropagation()">
                        <h3 style="margin-bottom:20px;color:#333;">Quét mã QR để thanh toán</h3>
                        <img src="${vietQRLink}" style="width:100%;max-width:300px;margin:20px 0;" alt="VietQR">
                        <p style="color:#666;margin:15px 0;font-size:14px;">
                            <strong>Số tiền:</strong> ${amount.toLocaleString('vi-VN')} VNĐ<br>
                            <strong>Nội dung:</strong> ${content}<br>
                            ${orderId ? '<span style="color:#28a745;"><i class="fas fa-check-circle"></i> Mã đơn hàng: #' + orderId + '</span>' : '<span style="color:#ff9800;"><i class="fas fa-info-circle"></i> Vui lòng đặt hàng để có mã đơn</span>'}
                        </p>
                        ${orderId ? `
                        <div style="margin:20px 0;padding:15px;background:#f8f9fa;border-radius:8px;border:2px dashed #28a745;">
                            <p style="color:#333;margin-bottom:10px;font-weight:600;"><i class="fas fa-upload"></i> Upload ảnh chứng từ chuyển khoản</p>
                            <input type="file" id="paymentProofFile_${orderId}" accept="image/*" style="display:none;" onchange="uploadPaymentProof(${orderId}, this.files[0])">
                            <button onclick="document.getElementById('paymentProofFile_${orderId}').click()" style="width:100%;background:#28a745;color:white;padding:10px;border:none;border-radius:6px;cursor:pointer;font-weight:600;margin-bottom:8px;">
                                <i class="fas fa-camera"></i> Chọn ảnh chứng từ
                            </button>
                            <small style="color:#666;display:block;text-align:center;">Chụp màn hình giao dịch thành công</small>
                        </div>
                        ` : ''}
                        <div style="display:flex;gap:10px;margin-top:20px;">
                            <button onclick="window.open('${bankAppUrl}', '_blank')" style="flex:1;background:#333;color:#ebe9e5;padding:12px;border:none;border-radius:8px;cursor:pointer;font-weight:600;">
                                <i class="fas fa-mobile-alt"></i> Mở App Banking
                            </button>
                            <button onclick="this.closest('div[style*=fixed]').remove(); ${orderId ? 'window.location.href=\'home.php\'' : ''};" style="flex:1;background:#dc3545;color:white;padding:12px;border:none;border-radius:8px;cursor:pointer;font-weight:600;">
                                ${orderId ? 'Hoàn thành' : 'Đóng'}
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', qrModal);
            
            // Thử mở app ngân hàng tự động
            setTimeout(() => {
                window.location.href = bankAppUrl;
            }, 500);
        }
        
        // Upload payment proof
        function uploadPaymentProof(orderId, file) {
            if (!file) {
                alert('Vui lòng chọn file ảnh');
                return;
            }
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Vui lòng chọn file ảnh (jpg, png, gif)');
                return;
            }
            
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File ảnh quá lớn (tối đa 5MB)');
                return;
            }
            
            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('payment_proof', file);
            
            // Show loading
            const uploadBtn = document.querySelector(`button[onclick*="paymentProofFile_${orderId}"]`);
            const originalText = uploadBtn.innerHTML;
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang upload...';
            
            fetch('upload_payment_proof.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    uploadBtn.innerHTML = '<i class="fas fa-check"></i> Đã upload thành công!';
                    uploadBtn.style.background = '#28a745';
                    setTimeout(() => {
                        alert(data.message);
                    }, 300);
                } else {
                    alert('Lỗi: ' + data.message);
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi upload. Vui lòng thử lại!');
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = originalText;
            });
        }
    </script>
      <?php include 'footer.php'; ?>
</body>
</html>

<?php
if (isset($conn)) {
    $conn->close();
}
?>