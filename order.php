<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // Tắt hiển thị lỗi để trang không bị crash

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login_page.php');
    exit();
}

// Lấy product_id từ URL
$product_id = $_GET['id'] ?? '';

if (empty($product_id)) {
    header('Location: products.php');
    exit();
}

// Khởi tạo giá trị mặc định
$product = [
    'product_id' => $product_id,
    'product_name' => 'Sản phẩm #' . $product_id,
    'price' => 100000,
    'image_url' => 'images/logo.png',
    'stock_quantity' => 10
];

$user = [
    'full_name' => $_SESSION['full_name'] ?? '',
    'email' => $_SESSION['email'] ?? '',
    'phone' => $_SESSION['phone'] ?? '',
    'address' => $_SESSION['address'] ?? ''
];

// Kết nối database với xử lý lỗi
$db_connected = false;
try {
    @include_once 'config/connect.php';
    
    if (isset($conn) && $conn->ping()) {
        $db_connected = true;
        
        // Lấy thông tin sản phẩm
        $sql = "SELECT * FROM products WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $product = $result->fetch_assoc();
            }
            $stmt->close();
        }

        // Lấy thông tin người dùng
        $user_id = $_SESSION['user_id'];
        $user_sql = "SELECT * FROM users WHERE user_id = ?";
        $user_stmt = $conn->prepare($user_sql);
        if ($user_stmt) {
            $user_stmt->bind_param("i", $user_id);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            if ($user_result && $user_result->num_rows > 0) {
                $user = $user_result->fetch_assoc();
            }
            $user_stmt->close();
        }
    }
} catch (Exception $e) {
    // Tiếp tục với giá trị mặc định
    $db_connected = false;
}

$quantity = 1; // Số lượng mặc định
$total = isset($product['price']) ? $product['price'] * $quantity : 100000;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mua Ngay - <?php echo htmlspecialchars($product['product_name']); ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dathang.css">
    <link rel="stylesheet" href="css/modal.css">
    <link rel="stylesheet" href="css/payment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .buy-now-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .buy-now-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        .buy-now-header h1 {
            margin: 0 0 10px 0;
            font-size: 2rem;
        }
        .buy-now-header p {
            margin: 0;
            opacity: 0.9;
        }
        .product-preview {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .product-preview-content {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        .product-preview img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        .product-info h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 1.5rem;
        }
        .product-info p {
            margin: 8px 0;
            color: #666;
        }
        .product-price {
            font-size: 1.8rem;
            color: #dc3545;
            font-weight: bold;
            margin-top: 15px !important;
        }
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
        }
        .quantity-selector label {
            font-weight: 600;
            color: #333;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-btn {
            background: #667eea;
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        .quantity-btn:hover {
            background: #764ba2;
            transform: scale(1.1);
        }
        .quantity-display {
            font-size: 1.3rem;
            font-weight: bold;
            min-width: 40px;
            text-align: center;
        }
        .cart-summary {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .cart-summary h3 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .payment-option {
            border: 2px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .payment-option:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }
        .payment-option input[type="radio"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        .payment-option input[type="radio"]:checked + .payment-label {
            color: #667eea;
        }
        .payment-label {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
        }
        .payment-label i {
            font-size: 2rem;
            color: #667eea;
        }
        .payment-label strong {
            display: block;
            margin-bottom: 5px;
        }
        .payment-label small {
            color: #666;
        }
        .bank-transfer-info {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9ff;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .bank-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
        }
        .bank-card h4 {
            margin-top: 0;
            color: #667eea;
        }
        .bank-details p {
            margin: 10px 0;
            color: #333;
        }
        .account-number {
            font-weight: bold;
            color: #dc3545;
            font-size: 1.1rem;
        }
        .price-breakdown {
            background: #f8f9ff;
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
        }
        .price-row.total-row {
            border-top: 2px solid #667eea;
            padding-top: 15px;
            font-size: 1.3rem;
        }
        .total-amount {
            color: #dc3545;
            font-weight: bold;
        }
        .btn-order-main {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        .btn-order-main:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <?php 
    // Include header với error handling
    if (file_exists('header.php')) {
        @include 'header.php';
    }
    ?>

    <div class="buy-now-container">
        <div class="buy-now-header">
            <h1><i class="fas fa-bolt"></i> Mua Ngay</h1>
            <p>Hoàn tất đơn hàng nhanh chóng và dễ dàng</p>
        </div>

        <!-- Product Preview -->
        <div class="product-preview">
            <div class="product-preview-content">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                <div class="product-info">
                    <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                    <p><strong>Mã sản phẩm:</strong> <?php echo htmlspecialchars($product['product_id']); ?></p>
                    <p><strong>Tồn kho:</strong> <?php echo $product['stock_quantity']; ?> sản phẩm</p>
                    <div class="quantity-selector">
                        <label>Số lượng:</label>
                        <div class="quantity-controls">
                            <button class="quantity-btn" onclick="updateQuantity(-1)">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="quantity-display" id="quantity"><?php echo $quantity; ?></span>
                            <button class="quantity-btn" onclick="updateQuantity(1)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <p class="product-price">
                        <span id="totalPrice"><?php echo number_format($total, 0, ',', '.'); ?></span> VNĐ
                    </p>
                </div>
            </div>
        </div>

        <!-- Nút đặt hàng -->
        <div class="cart-summary">
            <h3><i class="fas fa-receipt"></i> Tổng đơn hàng</h3>
            <div class="price-breakdown">
                <div class="price-row total-row">
                    <span><strong>Tổng cộng:</strong></span>
                    <span class="total-amount" id="displayTotal"><?php echo number_format($total, 0, ',', '.'); ?> VNĐ</span>
                </div>
            </div>
            <button class="btn-order-main" onclick="openOrderModal()">
                <i class="fas fa-shopping-bag"></i> Đặt hàng
            </button>
        </div>
    </div>

    <!-- Modal xác nhận thông tin đặt hàng -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-check"></i> Xác nhận thông tin đặt hàng</h2>
                <span class="close-modal" onclick="closeOrderModal()">&times;</span>
            </div>
            <form id="buyNowForm" onsubmit="return handleBuyNowSubmit(event)">
                <div class="modal-body">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
                    <input type="hidden" name="quantity" id="quantityInput" value="<?php echo $quantity; ?>">
                    <input type="hidden" name="unit_price" value="<?php echo $product['price']; ?>">
                    
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Họ và tên:</label>
                        <input type="text" name="full_name" id="modal_full_name"
                               value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email:</label>
                        <input type="email" name="email" id="modal_email"
                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Số điện thoại:</label>
                        <input type="tel" name="phone" id="modal_phone"
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                               pattern="[0-9]{10,11}" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Địa chỉ giao hàng:</label>
                        <textarea name="address" id="modal_address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-sticky-note"></i> Ghi chú (không bắt buộc):</label>
                        <textarea name="notes" id="modal_notes" rows="2" placeholder="Ghi chú thêm về đơn hàng..."></textarea>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-credit-card"></i> Phương thức thanh toán:</label>
                        <div class="payment-methods">
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="cod" checked onchange="toggleBankInfo()">
                                <span class="payment-label">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <div>
                                        <strong>Thanh toán khi nhận hàng (COD)</strong>
                                        <small>Thanh toán bằng tiền mặt khi nhận hàng</small>
                                    </div>
                                </span>
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="bank_transfer" onchange="toggleBankInfo()">
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

                    <!-- Bank Transfer Info -->
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
                    </div>

                    <div class="order-summary-modal">
                        <h3><i class="fas fa-receipt"></i> Tổng đơn hàng:</h3>
                        <div class="price-details">
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

    <?php require_once 'footer.php'; ?>

    <script>
        const unitPrice = <?php echo $product['price']; ?>;
        const maxStock = <?php echo $product['stock_quantity']; ?>;
        let currentQuantity = <?php echo $quantity; ?>;

        function updateQuantity(change) {
            const newQuantity = currentQuantity + change;
            
            if (newQuantity < 1) {
                alert('Số lượng tối thiểu là 1');
                return;
            }
            
            if (newQuantity > maxStock) {
                alert('Sản phẩm chỉ còn ' + maxStock + ' trong kho');
                return;
            }
            
            currentQuantity = newQuantity;
            document.getElementById('quantity').textContent = currentQuantity;
            document.getElementById('quantityInput').value = currentQuantity;
            
            const total = currentQuantity * unitPrice;
            document.getElementById('totalPrice').textContent = total.toLocaleString('vi-VN');
            document.getElementById('displayTotal').textContent = total.toLocaleString('vi-VN') + ' VNĐ';
            document.getElementById('modalTotal').textContent = total.toLocaleString('vi-VN') + ' VNĐ';
        }

        function toggleBankInfo() {
            const bankInfo = document.getElementById('bankTransferInfo');
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            bankInfo.style.display = paymentMethod === 'bank_transfer' ? 'block' : 'none';
        }

        function openOrderModal() {
            document.getElementById('orderModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeOrderModal() {
            document.getElementById('orderModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal khi click outside
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) {
                closeOrderModal();
            }
        }

        function handleBuyNowSubmit(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const totalAmount = currentQuantity * unitPrice;
            formData.append('total_amount', totalAmount);
            
            // Hiển thị loading
            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            submitBtn.disabled = true;
            
            fetch('process_buy_now.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = 'track_order.php';
                } else {
                    alert('Lỗi: ' + data.message);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại!');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
            
            return false;
        }
    </script>

    <?php 
    // Include footer với error handling
    if (file_exists('footer.php')) {
        @include 'footer.php';
    }
    ?>
</body>
</html>
