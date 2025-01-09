<?php
session_start();
require_once 'config/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: dangnhap.php');
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
    <title>Đặt Hàng - TTHUONG Store</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
     <style>
       .order-form-container {
            margin-top: 30px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .order-form-container h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        .submit-btn:hover {
            background-color: #45a049;
        }

        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .cart-items {
            margin-bottom: 30px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: white;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }

        .item-details {
            flex: 1;
            padding: 0 20px;
        }

        .item-name {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .item-price {
            color: #666;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .quantity-btn {
            background: #eee;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        .quantity-display {
            font-weight: bold;
        }

        .item-actions {
            text-align: right;
        }

        .item-total {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        .cart-summary {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .cart-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 20px;
            font-weight: bold;
        }

        .empty-cart {
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .empty-cart i {
            font-size: 48px;
            color: #666;
            margin-bottom: 20px;
        }

        .customer-info {
            background: #EBE9E5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #333;
            border-radius: 4px;
            background: white;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-save, .btn-order {
            flex: 1;
            padding: 12px;
            border: 1px solid #333;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-save {
            background: #EBE9E5;
            color: #333;
        }

        .btn-order {
            background: #333;
            color: #EBE9E5;
        }

        .btn-save:hover, .btn-order:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
    </style>
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
                <div class="cart-total">
                    <h3>Tổng cộng:</h3>
                    <span class="total-amount"><?php echo number_format($total, 0, ',', '.'); ?> VNĐ</span>
                </div>
            </div>

            <!-- Form đặt hàng hiện trực tiếp -->
            <div class="order-container">
                <div class="order-form">
                    <h2><i class="fas fa-user"></i> Thông tin đặt hàng</h2>
                    <form id="orderForm" onsubmit="return handleSubmit(event)">
                        <div class="customer-info">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Họ và tên:</label>
                                <input type="text" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Email:</label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> Số điện thoại:</label>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-map-marker-alt"></i> Địa chỉ:</label>
                                <textarea id="address" name="address" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label><i class="fas fa-sticky-note"></i> Ghi chú:</label>
                                <textarea id="notes" name="notes"></textarea>
                            </div>
                        </div>

                        <div class="button-group">
                            <button type="button" class="btn-save" onclick="saveCustomerInfo()">
                                <i class="fas fa-save"></i> Lưu thông tin
                            </button>
                            <button type="submit" class="btn-order">
                                <i class="fas fa-check"></i> Xác nhận đặt hàng
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <p>Giỏ hàng của bạn đang trống</p>
            <a href="sanpham.php" class="submit-btn">
                <i class="fas fa-store"></i> Tiếp tục mua sắm
            </a>
        </div>
    <?php endif; ?>

    <script>
        function validateForm() {
            const fullName = document.querySelector('input[name="full_name"]').value;
            const phone = document.querySelector('input[name="phone"]').value;
            const email = document.querySelector('input[name="email"]').value;
            const address = document.querySelector('textarea[name="address"]').value;

            if (!fullName || !phone || !email || !address) {
                alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
                return false;
            }

            const phoneRegex = /(84|0[3|5|7|8|9])+([0-9]{8})\b/;
            if (!phoneRegex.test(phone)) {
                alert('Số điện thoại không hợp lệ!');
                return false;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Email không hợp lệ!');
                return false;
            }

            return confirm('Xác nhận đặt hàng?');
        }

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

        function removeFromCart(cartId) {
            if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
                fetch('xoa_sp.php', {
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

        // Hàm lưu thông tin khách hàng
        function saveCustomerInfo() {
            const formData = {
                full_name: document.getElementById('full_name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                address: document.getElementById('address').value,
                notes: document.getElementById('notes').value
            };

            fetch('save_order_info.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Đã lưu thông tin thành công!');
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi lưu thông tin');
            });
        }

        // Hàm xử lý đặt hàng
        function handleSubmit(event) {
            event.preventDefault();
            
            if (confirm('Xác nhận đặt hàng?')) {
                const formData = {
                    full_name: document.getElementById('full_name').value,
                    email: document.getElementById('email').value,
                    phone: document.getElementById('phone').value,
                    address: document.getElementById('address').value,
                    notes: document.getElementById('notes').value
                };

                fetch('save_order_info.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Đặt hàng thành công! Mã đơn hàng: ' + data.order_id);
                        window.location.href = 'trangchu.php';
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi đặt hàng');
                });
            }
            return false;
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