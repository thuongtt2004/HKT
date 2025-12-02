<?php
session_start();
require_once '../config/connect.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Lấy danh sách đơn hàng với phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Lọc theo trạng thái nếu có
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$where_clause = $status_filter ? "WHERE o.order_status = ?" : "";

$sql = "SELECT o.*, u.username, u.email, u.phone 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.user_id 
        $where_clause
        ORDER BY o.created_at DESC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if($status_filter) {
    $stmt->bind_param("sii", $status_filter, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$orders = $stmt->get_result();

// Tổng số đơn hàng
$total = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_pages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng - HKT Store</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="admin-main">
            <h1>Quản Lý Đơn Hàng</h1>
            
            <div class="filters">
                <select onchange="filterOrders(this.value)">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Chờ xác nhận">Chờ xác nhận</option>
                    <option value="Đã xác nhận">Đã xác nhận</option>
                    <option value="Đang giao">Đang giao</option>
                    <option value="Đã giao">Đã giao</option>
                    <option value="Đã hủy">Đã hủy</option>
                </select>
            </div>

            <div class="order-list">
                <table>
                    <thead>
                        <tr>
                            <th>Mã ĐH</th>
                            <th>Khách hàng</th>
                            <th>Thông tin liên hệ</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($order['username']); ?><br>
                                <small><?php echo htmlspecialchars($order['full_name']); ?></small>
                            </td>
                            <td>
                                Email: <?php echo htmlspecialchars($order['email']); ?><br>
                                SĐT: <?php echo htmlspecialchars($order['phone']); ?><br>
                                Địa chỉ: <?php echo htmlspecialchars($order['address']); ?>
                            </td>
                            <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</td>
                            <td>
                                <select onchange="updateOrderStatus(<?php echo $order['order_id']; ?>, this.value)"
                                        class="status-select status-<?php echo strtolower(str_replace(' ', '-', $order['order_status'])); ?>">
                                    <option value="Chờ xác nhận" <?php echo $order['order_status'] == 'Chờ xác nhận' ? 'selected' : ''; ?>>
                                        Chờ xác nhận
                                    </option>
                                    <option value="Đã xác nhận" <?php echo $order['order_status'] == 'Đã xác nhận' ? 'selected' : ''; ?>>
                                        Đã xác nhận
                                    </option>
                                    <option value="Đang giao" <?php echo $order['order_status'] == 'Đang giao' ? 'selected' : ''; ?>>
                                        Đang giao
                                    </option>
                                    <option value="Đã giao" <?php echo $order['order_status'] == 'Đã giao' ? 'selected' : ''; ?>>
                                        Đã giao
                                    </option>
                                    <option value="Đã hủy" <?php echo $order['order_status'] == 'Đã hủy' ? 'selected' : ''; ?>>
                                        Đã hủy
                                    </option>
                                </select>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td>
    <button onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)" 
            class="btn-view">Chi tiết</button>
    <button onclick="printOrder(<?php echo $order['order_id']; ?>)" 
            class="btn-print">In</button>
    <button onclick="openOrderModal(<?php echo $order['order_id']; ?>)"
            class="btn-order">Đặt hàng</button>
</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Phân trang -->
                <div class="pagination">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status='.$status_filter : ''; ?>" 
                           class="<?php echo $page == $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Modal xem chi tiết đơn hàng -->
            <div id="orderDetailModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <div id="orderDetailContent"></div>
                </div>
            </div>
        </main>
    </div>

    <script>
       function openOrderModal(orderId) {
    document.getElementById('orderModal').style.display = 'block';
    // Lưu order ID vào form để xử lý
    document.getElementById('orderForm').dataset.orderId = orderId;
}

function closeOrderModal() {
    document.getElementById('orderModal').style.display = 'none';
}

// Xử lý submit form đặt hàng
document.getElementById('orderForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const orderId = this.dataset.orderId;
    const formData = new FormData(this);
    formData.append('order_id', orderId);

    try {
        const response = await fetch('xu_ly_dat_hang.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if(data.success) {
            alert('Đặt hàng thành công!');
            closeOrderModal();
            location.reload();
        } else {
            alert(data.message || 'Có lỗi xảy ra!');
        }
    } catch (error) {
        alert('Có lỗi xảy ra khi xử lý đơn hàng!');
    }
});

// Đóng modal khi click bên ngoài
window.onclick = function(event) {
    const modal = document.getElementById('orderModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
    </script>
</body>
</html> 