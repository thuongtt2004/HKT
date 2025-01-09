<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: dangnhapAdmin.php');
    exit();
}

require_once('config/connect.php');

// Lấy thống kê từ CSDL
$stats = [
    'total_products' => $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'],
    'total_orders' => $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'],
    'total_users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'total_revenue' => $conn->query("SELECT SUM(total_amount) as sum FROM orders WHERE order_status = 'completed'")->fetch_assoc()['sum']
];

// Lấy đơn hàng gần đây
$recentOrders = $conn->query("
    SELECT o.*, u.username 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.user_id 
    ORDER BY o.created_at DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị - TTHUONG STORE</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
   
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo-section">
                <img src="./images/logoo.jpg" alt="Logo" class="admin-logo">
                <h1>TTHUONG STORE - Trang Quản Trị</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="QTVindex.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="admin_products.php"><i class="fas fa-box"></i> Sản phẩm</a></li>
                    <li><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Đơn hàng</a></li>
                    <li><a href="admin_users.php"><i class="fas fa-users"></i> Khách hàng</a></li>
                    <li><a href="admin_log.php"><i class="fas fa-list"></i> Lịch sử đăng nhập </a></li>
                    <li><a href="trangchu.php"><i class ="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="welcome-section">
            <h2>Xin chào, <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin'; ?></h2>
            <p>Chào mừng bạn đến với trang quản trị TTHUONG STORE</p>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <i class="fas fa-box stat-icon"></i>
                <div class="stat-info">
                    <h3>Tổng sản phẩm</h3>
                    <p><?php echo number_format($stats['total_products']); ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-shopping-cart stat-icon"></i>
                <div class="stat-info">
                    <h3>Tổng đơn hàng</h3>
                    <p><?php echo number_format($stats['total_orders']); ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-users stat-icon"></i>
                <div class="stat-info">
                    <h3>Khách hàng</h3>
                    <p><?php echo number_format($stats['total_users']); ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-money-bill-wave stat-icon"></i>
                <div class="stat-info">
                    <h3>Doanh thu</h3>
                    <p><?php echo number_format($stats['total_revenue']); ?>đ</p>
                </div>
            </div>
        </div>

        <div class="recent-orders">
            <div class="section-header">
                <h2><i class="fas fa-clock"></i> Đơn hàng gần đây</h2>
                <a href="admin_orders.php" class="view-all">Xem tất cả</a>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày đặt</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                            <td><?php echo number_format($order['total_amount']); ?>đ</td>
                            <td><span class="status-badge <?php echo $order['order_status']; ?>"><?php echo $order['order_status']; ?></span></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td>
                                <a href="admin_order_detail.php?id=<?php echo $order['order_id']; ?>" class="action-btn">
                                    <i class="fas fa-eye"></i> Chi tiết
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer>
        <p><i class="far fa-copyright"></i> 2024 TTHUONG STORE. All rights reserved.</p>
    </footer>
</body>
</html>