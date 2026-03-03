<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login_admin.php");
    exit();
}

require_once 'connect.php';

$message = '';
$messageType = '';

// Tự động chạy cập nhật trạng thái khi vào trang (có điều kiện thời gian)
$autoUpdated = 0;
$conn->query("UPDATE orders SET status = 'delivered' WHERE status = 'shipping' AND created_at <= DATE_SUB(NOW(), INTERVAL 3 DAY)");
$autoUpdated += $conn->affected_rows;
$conn->query("UPDATE orders SET status = 'shipping' WHERE status = 'confirmed' AND created_at <= DATE_SUB(NOW(), INTERVAL 12 HOUR)");
$autoUpdated += $conn->affected_rows;
$conn->query("UPDATE orders SET status = 'confirmed' WHERE status = 'pending' AND created_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$autoUpdated += $conn->affected_rows;

if ($autoUpdated > 0) {
    $message = "Đã tự động cập nhật $autoUpdated đơn hàng theo điều kiện thời gian.";
    $messageType = 'info';
}

// Xử lý cập nhật thủ công (chạy ngay lập tức)
if (isset($_POST['manual_update'])) {
    $rule = $_POST['rule'];
    $updatedCount = 0;
    
    if ($rule === 'pending_to_confirmed') {
        // Cập nhật TẤT CẢ đơn pending thành confirmed (không cần điều kiện)
        $conn->query("UPDATE orders SET status = 'confirmed' WHERE status = 'pending'");
        $updatedCount = $conn->affected_rows;
        $message = "Đã chuyển $updatedCount đơn từ Pending → Confirmed (thủ công)";
        $messageType = 'success';
    } 
    elseif ($rule === 'confirmed_to_shipping') {
        $conn->query("UPDATE orders SET status = 'shipping' WHERE status = 'confirmed'");
        $updatedCount = $conn->affected_rows;
        $message = "Đã chuyển $updatedCount đơn từ Confirmed → Shipping (thủ công)";
        $messageType = 'success';
    } 
    elseif ($rule === 'shipping_to_delivered') {
        $conn->query("UPDATE orders SET status = 'delivered' WHERE status = 'shipping'");
        $updatedCount = $conn->affected_rows;
        $message = "Đã chuyển $updatedCount đơn từ Shipping → Delivered (thủ công)";
        $messageType = 'success';
    } 
    elseif ($rule === 'run_all') {
        // Chạy tất cả các bước
        $total = 0;
        $conn->query("UPDATE orders SET status = 'confirmed' WHERE status = 'pending'");
        $total += $conn->affected_rows;
        $conn->query("UPDATE orders SET status = 'shipping' WHERE status = 'confirmed'");
        $total += $conn->affected_rows;
        $conn->query("UPDATE orders SET status = 'delivered' WHERE status = 'shipping'");
        $total += $conn->affected_rows;
        
        $message = "Đã cập nhật thủ công tổng $total đơn hàng (tất cả chuyển lên 1 bậc)";
        $messageType = 'success';
    }
}

// Đếm số đơn theo trạng thái
$totalPending = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'pending'")->fetch_assoc()['c'];
$totalConfirmed = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'confirmed'")->fetch_assoc()['c'];
$totalShipping = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'shipping'")->fetch_assoc()['c'];
$totalDelivered = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'delivered'")->fetch_assoc()['c'];
$totalCancelled = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'cancelled'")->fetch_assoc()['c'];

// Đếm số đơn đủ điều kiện tự động
$pendingReady = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'pending' AND created_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetch_assoc()['c'];
$confirmedReady = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'confirmed' AND created_at <= DATE_SUB(NOW(), INTERVAL 12 HOUR)")->fetch_assoc()['c'];
$shippingReady = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'shipping' AND created_at <= DATE_SUB(NOW(), INTERVAL 3 DAY)")->fetch_assoc()['c'];

// Lấy danh sách đơn hàng để hiển thị
$recentOrders = $conn->query("
    SELECT id, full_name, status, created_at 
    FROM orders 
    WHERE status IN ('pending', 'confirmed', 'shipping')
    ORDER BY created_at DESC 
    LIMIT 10
");
?>
<div class="admin-content">
    <div class="admin-page-header">
        <h1 class="admin-page-title"><i class="fas fa-sync-alt"></i> Cập nhật trạng thái đơn hàng</h1>
    </div>

    <?php if ($message): ?>
        <div class="admin-message admin-message-<?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'info-circle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="admin-card" style="margin-bottom: 20px;">
        <p class="muted" style="margin: 0;">
            <i class="fas fa-info-circle" style="color: #9a7b5a;"></i>
            <strong>Tự động:</strong> Hệ thống tự động cập nhật khi bạn vào trang (theo điều kiện thời gian).
            <strong>Thủ công:</strong> Bạn có thể ấn nút để chuyển trạng thái NGAY LẬP TỨC, không cần đợi đủ thời gian.
        </p>
    </div>

    <style>
        .auto-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .auto-stat-item { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.08); text-align: center; border-left: 4px solid #9a7b5a; }
        .auto-stat-item h4 { font-size: 13px; color: #7f8c8d; margin-bottom: 6px; }
        .auto-stat-item .number { font-size: 24px; font-weight: 700; color: #2c3e50; }
        .auto-stat-item .muted { font-size: 12px; color: #95a5a6; }
        .auto-stat-item.pending { border-left-color: #f39c12; }
        .auto-stat-item.confirmed { border-left-color: #3498db; }
        .auto-stat-item.shipping { border-left-color: #9b59b6; }
        .auto-stat-item.delivered { border-left-color: #27ae60; }
        .auto-stat-item.cancelled { border-left-color: #e74c3c; }
        .auto-rule-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin: 20px 0; }
        .auto-rule-card { background: #f8f9fa; border-radius: 8px; padding: 18px; border: 1px solid #ecf0f1; }
        .auto-rule-card h3 { font-size: 15px; margin-bottom: 12px; color: #2c3e50; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .auto-rule-badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; }
        .auto-rule-badge.warning { background: #f39c12; color: #fff; }
        .auto-rule-badge.info { background: #3498db; color: #fff; }
        .auto-rule-badge.primary { background: #9b59b6; color: #fff; }
        .auto-rule-badge.success { background: #27ae60; color: #fff; }
        .auto-count-box { font-size: 26px; font-weight: 700; color: #2c3e50; margin: 10px 0; }
        .auto-condition { background: #fff; padding: 10px 12px; border-radius: 5px; margin: 12px 0; font-size: 13px; border-left: 3px solid #9a7b5a; }
        .auto-ready-badge { background: #27ae60; color: #fff; padding: 2px 8px; border-radius: 12px; font-size: 11px; margin-left: 6px; }
        .auto-button-group { display: flex; gap: 10px; margin-top: 12px; }
        .auto-button-group form { flex: 1; }
    </style>

    <!-- Thống kê tổng quan -->
    <div class="auto-stats-grid">
        <div class="auto-stat-item pending">
            <h4><i class="fas fa-clock"></i> Chờ xử lý</h4>
            <div class="number"><?php echo $totalPending; ?></div>
            <div class="muted"><?php echo $pendingReady; ?> đơn đủ điều kiện</div>
        </div>
        <div class="auto-stat-item confirmed">
            <h4><i class="fas fa-check-circle"></i> Đã xác nhận</h4>
            <div class="number"><?php echo $totalConfirmed; ?></div>
            <div class="muted"><?php echo $confirmedReady; ?> đơn đủ điều kiện</div>
        </div>
        <div class="auto-stat-item shipping">
            <h4><i class="fas fa-truck"></i> Đang giao</h4>
            <div class="number"><?php echo $totalShipping; ?></div>
            <div class="muted"><?php echo $shippingReady; ?> đơn đủ điều kiện</div>
        </div>
        <div class="auto-stat-item delivered">
            <h4><i class="fas fa-check-double"></i> Đã giao</h4>
            <div class="number"><?php echo $totalDelivered; ?></div>
        </div>
        <div class="auto-stat-item cancelled">
            <h4><i class="fas fa-times-circle"></i> Đã hủy</h4>
            <div class="number"><?php echo $totalCancelled; ?></div>
        </div>
    </div>

    <!-- Các quy tắc cập nhật -->
    <div class="admin-card">
        <h2><i class="fas fa-tasks"></i> Quy tắc cập nhật trạng thái</h2>
        
        <div class="auto-rule-grid">
            <div class="auto-rule-card">
                <h3>
                    <span class="auto-rule-badge warning">Pending</span>
                    <i class="fas fa-arrow-right"></i>
                    <span class="auto-rule-badge info">Confirmed</span>
                </h3>
                <div class="auto-count-box"><?php echo $totalPending; ?> đơn</div>
                <div class="auto-condition">
                    <i class="fas fa-clock"></i> Tự động: Pending ≥ 24 giờ
                    <?php if ($pendingReady > 0): ?>
                        <span class="auto-ready-badge"><?php echo $pendingReady; ?> đơn sẵn sàng</span>
                    <?php endif; ?>
                </div>
                <div class="auto-button-group">
                    <form method="post">
                        <input type="hidden" name="rule" value="pending_to_confirmed">
                        <button type="submit" name="manual_update" class="admin-btn admin-btn-primary" style="width:100%;">
                            <i class="fas fa-play"></i> Chạy thủ công
                        </button>
                    </form>
                </div>
            </div>

            <div class="auto-rule-card">
                <h3>
                    <span class="auto-rule-badge info">Confirmed</span>
                    <i class="fas fa-arrow-right"></i>
                    <span class="auto-rule-badge primary">Shipping</span>
                </h3>
                <div class="auto-count-box"><?php echo $totalConfirmed; ?> đơn</div>
                <div class="auto-condition">
                    <i class="fas fa-clock"></i> Tự động: Confirmed ≥ 12 giờ
                    <?php if ($confirmedReady > 0): ?>
                        <span class="auto-ready-badge"><?php echo $confirmedReady; ?> đơn sẵn sàng</span>
                    <?php endif; ?>
                </div>
                <div class="auto-button-group">
                    <form method="post">
                        <input type="hidden" name="rule" value="confirmed_to_shipping">
                        <button type="submit" name="manual_update" class="admin-btn admin-btn-primary" style="width:100%;">
                            <i class="fas fa-play"></i> Chạy thủ công
                        </button>
                    </form>
                </div>
            </div>

            <div class="auto-rule-card">
                <h3>
                    <span class="auto-rule-badge primary">Shipping</span>
                    <i class="fas fa-arrow-right"></i>
                    <span class="auto-rule-badge success">Delivered</span>
                </h3>
                <div class="auto-count-box"><?php echo $totalShipping; ?> đơn</div>
                <div class="auto-condition">
                    <i class="fas fa-clock"></i> Tự động: Shipping ≥ 3 ngày
                    <?php if ($shippingReady > 0): ?>
                        <span class="auto-ready-badge"><?php echo $shippingReady; ?> đơn sẵn sàng</span>
                    <?php endif; ?>
                </div>
                <div class="auto-button-group">
                    <form method="post">
                        <input type="hidden" name="rule" value="shipping_to_delivered">
                        <button type="submit" name="manual_update" class="admin-btn admin-btn-primary" style="width:100%;">
                            <i class="fas fa-play"></i> Chạy thủ công
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #eee; text-align: center;">
            <form method="post">
                <input type="hidden" name="rule" value="run_all">
                <button type="submit" name="manual_update" class="admin-btn admin-btn-primary" style="padding: 12px 24px; font-size: 15px;">
                    <i class="fas fa-forward"></i> Chạy tất cả thủ công (Pending → Confirmed → Shipping → Delivered)
                </button>
            </form>
            <p class="muted" style="margin-top: 10px; font-size: 13px;">
                <i class="fas fa-exclamation-triangle" style="color: #9a7b5a;"></i>
                Nút này sẽ chuyển TẤT CẢ đơn hàng lên trạng thái tiếp theo, không phân biệt thời gian tạo.
            </p>
        </div>
    </div>

    <!-- Danh sách đơn hàng đang chờ -->
    <div class="admin-card">
        <h2><i class="fas fa-list"></i> Đơn hàng đang chờ xử lý</h2>
        
        <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thời gian chờ</th>
                        <th>Sẵn sàng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $recentOrders->fetch_assoc()): 
                        $created = new DateTime($order['created_at']);
                        $now = new DateTime();
                        $diff = $now->diff($created);
                        $hours = $diff->days * 24 + $diff->h;
                        
                        $statusClass = '';
                        $statusText = '';
                        $readyStatus = '';
                        
                        if ($order['status'] == 'pending') {
                            $statusClass = 'badge-warning';
                            $statusText = 'Chờ xử lý';
                            $readyStatus = ($hours >= 24) ? 'Sẵn sàng tự động' : 'Chưa đủ 24h';
                        } elseif ($order['status'] == 'confirmed') {
                            $statusClass = 'badge-info';
                            $statusText = 'Đã xác nhận';
                            $readyStatus = ($hours >= 12) ? 'Sẵn sàng tự động' : 'Chưa đủ 12h';
                        } elseif ($order['status'] == 'shipping') {
                            $statusClass = 'badge-primary';
                            $statusText = 'Đang giao';
                            $readyStatus = ($hours >= 72) ? 'Sẵn sàng tự động' : 'Chưa đủ 3 ngày';
                        }
                    ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                        <td><span class="badge" style="background:#f39c12;color:#fff;padding:4px 10px;border-radius:20px;font-size:12px;"><?php echo $statusText; ?></span></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td><?php echo $hours; ?> giờ</td>
                        <td>
                            <?php if (($order['status'] == 'pending' && $hours >= 24) || ($order['status'] == 'confirmed' && $hours >= 12) || ($order['status'] == 'shipping' && $hours >= 72)): ?>
                                <span class="badge" style="background:#27ae60;color:#fff;padding:4px 8px;border-radius:20px;font-size:11px;">✓ <?php echo $readyStatus; ?></span>
                            <?php else: ?>
                                <span class="badge" style="background:#95a5a6;color:#fff;padding:4px 8px;border-radius:20px;font-size:11px;">⏳ <?php echo $readyStatus; ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="muted">Không có đơn hàng nào đang chờ xử lý.</p>
        <?php endif; ?>
    </div>

    <!-- Thống kê chi tiết -->
    <div class="admin-card">
        <h2><i class="fas fa-chart-pie"></i> Thống kê chi tiết</h2>
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Trạng thái</th>
                    <th>Tổng số</th>
                    <th>Đủ điều kiện tự động</th>
                    <th>Chưa đủ điều kiện</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge" style="background:#f39c12;color:#fff;padding:4px 10px;border-radius:20px;font-size:12px;">Pending</span></td>
                    <td><?php echo $totalPending; ?></td>
                    <td class="text-success"><?php echo $pendingReady; ?> đơn</td>
                    <td class="text-muted"><?php echo $totalPending - $pendingReady; ?> đơn</td>
                </tr>
                <tr>
                    <td><span class="badge" style="background:#3498db;color:#fff;padding:4px 10px;border-radius:20px;font-size:12px;">Confirmed</span></td>
                    <td><?php echo $totalConfirmed; ?></td>
                    <td class="text-success"><?php echo $confirmedReady; ?> đơn</td>
                    <td class="text-muted"><?php echo $totalConfirmed - $confirmedReady; ?> đơn</td>
                </tr>
                <tr>
                    <td><span class="badge" style="background:#9b59b6;color:#fff;padding:4px 10px;border-radius:20px;font-size:12px;">Shipping</span></td>
                    <td><?php echo $totalShipping; ?></td>
                    <td class="text-success"><?php echo $shippingReady; ?> đơn</td>
                    <td class="text-muted"><?php echo $totalShipping - $shippingReady; ?> đơn</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>