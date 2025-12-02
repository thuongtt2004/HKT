<header class="admin-header">
    <div class="admin-header-top">
        <div class="logo-section">
            <img src="./images/logoo.jpg" alt="Logo" class="admin-logo">
            <h1>HKT Store - Admin</h1>
        </div>
        
        <div class="admin-header-actions">
            <div class="admin-welcome">
                <i class="fas fa-user-shield"></i>
                <span>Quản trị viên</span>
            </div>
            
            <a href="logout.php" class="btn-admin-logout">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </a>
        </div>
    </div>
    
    <nav class="admin-nav">
        <ul class="admin-menu">
            <li>
                <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="admin_products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_products.php' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i> Sản phẩm
                </a>
            </li>
            <li>
                <a href="admin_orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_orders.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i> Đơn hàng
                </a>
            </li>
            <li>
                <a href="admin_returns.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_returns.php' ? 'active' : ''; ?>">
                    <i class="fas fa-undo"></i> Trả hàng
                    <?php
                    // Đếm yêu cầu trả hàng chờ duyệt
                    $return_pending_query = "SELECT COUNT(*) as count FROM orders WHERE return_request = 1 AND return_status = 'Chờ duyệt'";
                    $return_pending_result = $conn->query($return_pending_query);
                    if ($return_pending_result) {
                        $return_count = $return_pending_result->fetch_assoc()['count'];
                        if ($return_count > 0): ?>
                            <span class="admin-badge"><?php echo $return_count; ?></span>
                        <?php endif;
                    }
                    ?>
                </a>
            </li>
            <li>
                <a href="admin_payment_confirmation.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_payment_confirmation.php' ? 'active' : ''; ?>">
                    <i class="fas fa-check-circle"></i> Xác nhận thanh toán
                    <?php
                    // Đếm đơn chờ thanh toán
                    $pending_payment_query = "SELECT COUNT(*) as count FROM orders WHERE order_status = 'Chờ thanh toán' AND payment_method = 'bank_transfer'";
                    $pending_payment_result = $conn->query($pending_payment_query);
                    if ($pending_payment_result) {
                        $pending_count = $pending_payment_result->fetch_assoc()['count'];
                        if ($pending_count > 0): ?>
                            <span class="admin-badge"><?php echo $pending_count; ?></span>
                        <?php endif;
                    }
                    ?>
                </a>
            </li>
            <li>
                <a href="admin_users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Khách hàng
                </a>
            </li>
            <li>
                <a href="admin_reviews.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_reviews.php' ? 'active' : ''; ?>">
                    <i class="fas fa-star"></i> Đánh giá
                </a>
            </li>
            <li>
                <a href="admin_chat.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_chat.php' ? 'active' : ''; ?>">
                    <i class="fas fa-comments"></i> Chat
                    <?php
                    // Đếm tin nhắn chưa đọc
                    require_once 'config/connect.php';
                    $table_check = $conn->query("SHOW TABLES LIKE 'messages'");
                    if ($table_check && $table_check->num_rows > 0) {
                        $unread_query = "SELECT COUNT(*) as count FROM messages WHERE sender_type = 'user' AND is_read = 0";
                        $unread_result = $conn->query($unread_query);
                        if ($unread_result) {
                            $unread_count = $unread_result->fetch_assoc()['count'];
                            if ($unread_count > 0): ?>
                                <span class="admin-badge"><?php echo $unread_count; ?></span>
                            <?php endif;
                        }
                    }
                    ?>
                </a>
            </li>
            <li>
                <a href="admin_promotions.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_promotions.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tags"></i> Khuyến mãi
                </a>
            </li>
            <li>
                <a href="admin_revenue.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_revenue.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i> Doanh thu
                </a>
            </li>
            <li>
                <a href="admin_log.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_log.php' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i> Lịch sử
                </a>
            </li>
        </ul>
    </nav>
</header>
