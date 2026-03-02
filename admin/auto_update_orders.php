<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login_admin.php");
    exit();
}

require_once 'connect.php';

$message = '';
$messageType = '';

// Tự động chạy cập nhật trạng thái đơn khi vào trang (không cần bấm tay)
if (!isset($_POST['auto_update'])) {
    $total = 0;
    $conn->query("UPDATE orders SET status = 'delivered' WHERE status = 'shipping' AND created_at <= DATE_SUB(NOW(), INTERVAL 3 DAY)");
    $total += $conn->affected_rows;
    $conn->query("UPDATE orders SET status = 'shipping' WHERE status = 'confirmed' AND created_at <= DATE_SUB(NOW(), INTERVAL 12 HOUR)");
    $total += $conn->affected_rows;
    $conn->query("UPDATE orders SET status = 'confirmed' WHERE status = 'pending' AND created_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $total += $conn->affected_rows;
    if ($total > 0) {
        $message = "Đã tự động cập nhật $total đơn hàng (trạng thái: pending → confirmed → shipping → delivered).";
        $messageType = 'success';
    }
}

// Nếu bấm nút "Chạy" thủ công thì vẫn xử lý như cũ
if (isset($_POST['auto_update']) && isset($_POST['rule'])) {
    $rule = $_POST['rule'];
    
    if ($rule === 'pending_to_confirmed') {
        // Đơn pending >= 24 giờ → confirmed
        $stmt = $conn->prepare("UPDATE orders SET status = 'confirmed' 
            WHERE status = 'pending' 
            AND created_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        if ($stmt && $stmt->execute()) {
            $updatedCount = $conn->affected_rows;
            $message = "Đã cập nhật $updatedCount đơn hàng từ Pending → Confirmed (đơn cũ hơn 24h)";
            $messageType = 'success';
        } else {
            $message = 'Lỗi khi cập nhật.';
            $messageType = 'error';
        }
        if ($stmt) $stmt->close();
    } elseif ($rule === 'confirmed_to_shipping') {
        // Đơn confirmed >= 12 giờ → shipping
        $stmt = $conn->prepare("UPDATE orders SET status = 'shipping' 
            WHERE status = 'confirmed' 
            AND created_at <= DATE_SUB(NOW(), INTERVAL 12 HOUR)");
        if ($stmt && $stmt->execute()) {
            $updatedCount = $conn->affected_rows;
            $message = "Đã cập nhật $updatedCount đơn hàng từ Confirmed → Shipping (đơn cũ hơn 12h)";
            $messageType = 'success';
        } else {
            $message = 'Lỗi khi cập nhật.';
            $messageType = 'error';
        }
        if ($stmt) $stmt->close();
    } elseif ($rule === 'shipping_to_delivered') {
        // Đơn shipping >= 3 ngày → delivered
        $stmt = $conn->prepare("UPDATE orders SET status = 'delivered' 
            WHERE status = 'shipping' 
            AND created_at <= DATE_SUB(NOW(), INTERVAL 3 DAY)");
        if ($stmt && $stmt->execute()) {
            $updatedCount = $conn->affected_rows;
            $message = "Đã cập nhật $updatedCount đơn hàng từ Shipping → Delivered (đơn cũ hơn 3 ngày)";
            $messageType = 'success';
        } else {
            $message = 'Lỗi khi cập nhật.';
            $messageType = 'error';
        }
        if ($stmt) $stmt->close();
    } elseif ($rule === 'run_all') {
        $total = 0;
        // 1. shipping -> delivered
        $s1 = $conn->query("UPDATE orders SET status = 'delivered' 
            WHERE status = 'shipping' AND created_at <= DATE_SUB(NOW(), INTERVAL 3 DAY)");
        $total += $conn->affected_rows;
        // 2. confirmed -> shipping
        $s2 = $conn->query("UPDATE orders SET status = 'shipping' 
            WHERE status = 'confirmed' AND created_at <= DATE_SUB(NOW(), INTERVAL 12 HOUR)");
        $total += $conn->affected_rows;
        // 3. pending -> confirmed
        $s3 = $conn->query("UPDATE orders SET status = 'confirmed' 
            WHERE status = 'pending' AND created_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $total += $conn->affected_rows;
        
        $message = "Đã tự động cập nhật tổng $total đơn hàng theo quy tắc (delivered ← shipping ← confirmed ← pending)";
        $messageType = 'success';
    }
}

// Thống kê đơn cần cập nhật (sau khi đã chạy auto)
$pendingOld = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'pending' 
    AND created_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetch_assoc()['c'];
$confirmedOld = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'confirmed' 
    AND created_at <= DATE_SUB(NOW(), INTERVAL 12 HOUR)")->fetch_assoc()['c'];
$shippingOld = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'shipping' 
    AND created_at <= DATE_SUB(NOW(), INTERVAL 3 DAY)")->fetch_assoc()['c'];
?>
<div class="admin-content">
    <h1 class="admin-page-title"><i class="fas fa-sync-alt"></i> Tự động cập nhật đơn hàng</h1>

    <?php if ($message): ?>
        <div class="admin-message admin-message-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="admin-card">
        <h2>Quy tắc cập nhật</h2>
        <p class="muted">Trạng thái đơn hàng được <strong>tự động cập nhật</strong> mỗi khi bạn mở trang này. Bạn vẫn có thể bấm nút bên dưới để chạy thủ công.</p>

        <table class="admin-table" style="margin-top: 16px;">
            <thead>
                <tr>
                    <th>Quy tắc</th>
                    <th>Điều kiện</th>
                    <th>Số đơn đang chờ</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Pending → Confirmed</td>
                    <td>Đơn pending cũ hơn 24 giờ</td>
                    <td><?php echo (int)$pendingOld; ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="rule" value="pending_to_confirmed">
                            <button type="submit" name="auto_update" class="admin-btn admin-btn-primary">Chạy</button>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>Confirmed → Shipping</td>
                    <td>Đơn confirmed cũ hơn 12 giờ</td>
                    <td><?php echo (int)$confirmedOld; ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="rule" value="confirmed_to_shipping">
                            <button type="submit" name="auto_update" class="admin-btn admin-btn-primary">Chạy</button>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>Shipping → Delivered</td>
                    <td>Đơn shipping cũ hơn 3 ngày</td>
                    <td><?php echo (int)$shippingOld; ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="rule" value="shipping_to_delivered">
                            <button type="submit" name="auto_update" class="admin-btn admin-btn-primary">Chạy</button>
                        </form>
                    </td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #eee;">
            <form method="post">
                <input type="hidden" name="rule" value="run_all">
                <button type="submit" name="auto_update" class="admin-btn admin-btn-primary admin-btn-lg">
                    <i class="fas fa-play"></i> Chạy tất cả quy tắc
                </button>
            </form>
        </div>
    </div>
</div>
