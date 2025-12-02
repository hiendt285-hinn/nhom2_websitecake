<?php
session_start();
require_once 'connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Lấy thông tin người dùng
$user = null;
$userSql = "SELECT username, full_name, email, phone, address FROM users WHERE id = ? LIMIT 1";
if ($stmt = $conn->prepare($userSql)) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();
}

// Lấy danh sách đơn hàng của người dùng (mysqli)
$orders = [];
$sql = "SELECT id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt->close();
}

function status_text($status) {
    $map = [
        'pending' => 'Chờ xử lý',
        'confirmed' => 'Đã xác nhận',
        'shipping' => 'Đang giao',
        'delivered' => 'Đã giao',
        'cancelled' => 'Đã hủy',
    ];
    return $map[$status] ?? $status;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử đơn hàng</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Open Sans', Arial, sans-serif; margin: 0; background: var(--light-beige); }
        .order-history-page { max-width: 1000px; margin: 40px auto; padding: 0 20px 40px; }
        .container { background: #fff; padding: 24px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); }
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .back-btn { display: inline-flex; align-items: center; gap: 8px; background: var(--main-brown); color: #fff; padding: 10px 18px; border-radius: 999px; text-decoration: none; font-weight: 600; transition: 0.3s; font-size: 14px; }
        .back-btn:hover { background: var(--brown-light); }
        h1 { text-align: center; color: var(--text-black); margin-bottom: 20px; font-size: 24px; }
        .user-summary { background: #fffaf0; border: 1px solid #f3e0c7; border-radius: 12px; padding: 18px; margin-bottom: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
        .user-summary h2 { grid-column: 1 / -1; margin: 0 0 10px 0; font-size: 18px; color: #5D4037; }
        .info-item { display: flex; flex-direction: column; }
        .info-item label { font-weight: 600; color: #777; margin-bottom: 4px; font-size: 13px; }
        .info-item span { background: #fff; border: 1px solid #f0d5da; border-radius: 8px; padding: 8px 10px; color: #333; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f9f6f2; color: var(--text-black); font-weight: 600; }
        tr:hover { background-color: #fdf7f0; }
        .status { font-weight: bold; }
        .pending { color: #ff9800; }
        .confirmed { color: #1976D2; }
        .shipping { color: #9C27B0; }
        .delivered { color: #2e7d32; }
        .cancelled { color: #f44336; }
        .view-btn { display: inline-block; padding: 6px 12px; background: var(--main-brown); color: white; text-decoration: none; border-radius: 999px; font-size: 13px; font-weight: 600; }
        .view-btn:hover { background: var(--brown-light); }
        .no-orders { text-align: center; color: #777; font-style: italic; margin: 40px 0; }
        .footer-link { text-align: center; margin-top: 24px; }
        .footer-link a { color: var(--main-brown); text-decoration: none; font-weight: 600; }
    </style>
</head>

<body>
<div class="order-history-page">
<div class="container">
    <div class="header-actions">
        <a href="account.php" class="back-btn"><i class="fas fa-arrow-left"></i> Quay lại tài khoản</a>
    </div>
    <h1>Lịch sử đơn hàng</h1>

    <?php if ($user): ?>
    <div class="user-summary">
        <h2>Thông tin cá nhân</h2>
        <div class="info-item">
            <label>Họ và tên</label>
            <span><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></span>
        </div>
        <div class="info-item">
            <label>Email</label>
            <span><?php echo htmlspecialchars($user['email']); ?></span>
        </div>
        <div class="info-item">
            <label>Số điện thoại</label>
            <span><?php echo htmlspecialchars($user['phone'] ?: 'Chưa cập nhật'); ?></span>
        </div>
        <div class="info-item">
            <label>Địa chỉ</label>
            <span><?php echo htmlspecialchars($user['address'] ?: 'Chưa cập nhật'); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <p class="no-orders">Bạn chưa có đơn hàng nào.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                    <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> ₫</td>
                    <td class="status <?php echo htmlspecialchars($order['status']); ?>">
                        <?php echo status_text($order['status']); ?>
                    </td>
                    <td>
                        <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="view-btn">Xem chi tiết</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p class="footer-link">
        <a href="index.php">Quay lại trang chủ</a>
    </p>
</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
</body>
</html>