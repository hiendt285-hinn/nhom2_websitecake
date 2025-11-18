<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once 'connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Lấy thông tin người dùng theo schema hiện tại
$user_sql = "SELECT id, username, email, full_name, phone, address FROM users WHERE id = ? LIMIT 1";
$user = null;
if ($stmt = $conn->prepare($user_sql)) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();
}

if (!$user) {
    // Nếu không tìm thấy user, logout và redirect
    session_destroy();
    header('Location: login.php');
    exit;
}

// Lấy danh sách đơn hàng
$orders_sql = "SELECT id, created_at, total_amount, status FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$orders = [];
if ($stmt = $conn->prepare($orders_sql)) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt->close();
}

function status_label($status) {
    $map = [
        'pending' => ['label' => 'Chờ xử lý', 'class' => 'status-pending'],
        'confirmed' => ['label' => 'Đã xác nhận', 'class' => 'status-confirmed'],
        'shipping' => ['label' => 'Đang giao', 'class' => 'status-shipping'],
        'delivered' => ['label' => 'Đã giao', 'class' => 'status-delivered'],
        'cancelled' => ['label' => 'Đã hủy', 'class' => 'status-cancelled'],
    ];
    return $map[$status] ?? ['label' => $status, 'class' => 'status-default'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản của tôi - Savor Cake</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .account-page { max-width: 1200px; margin: 30px auto; padding: 0 20px; font-family: 'Poppins', sans-serif; }
        .page-header { text-align: center; margin-bottom: 40px; }
        .page-header h1 { font-size: 32px; color: #5D4037; font-weight: 700; margin-bottom: 10px; }
        .page-header p { color: #666; font-size: 16px; }
        .user-info, .orders-section { background: #fffaf0; padding: 30px; border-radius: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .user-info { margin-bottom: 40px; }
        .user-info h2, .orders-section h2 { font-size: 24px; color: #5D4037; margin-bottom: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .info-item { display: flex; flex-direction: column; }
        .info-item label { font-weight: 600; color: #5D4037; font-size: 14px; margin-bottom: 5px; }
        .info-item p { color: #333; font-size: 16px; background: #fff; padding: 10px; border-radius: 8px; border: 1px solid #ddd; }
        .btn-edit { background: #4caf50; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; margin-top: 20px; width: fit-content; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .btn-edit:hover { background: #388e3c; }
        .orders-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .orders-table th, .orders-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .orders-table th { background: #f8f5f0; font-weight: 600; color: #5D4037; }
        .status { padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; display: inline-block; }
        .status-pending { background: #fff3e0; color: #ef6c00; }
        .status-confirmed { background: #e3f2fd; color: #1565C0; }
        .status-shipping { background: #ede7f6; color: #6a1b9a; }
        .status-delivered { background: #e8f5e9; color: #2e7d32; }
        .status-cancelled { background: #ffebee; color: #c62828; }
        .status-default { background: #ececec; color: #333; }
        .btn-view-order { background: #2196f3; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: 0.3s; }
        .btn-view-order:hover { background: #1976d2; }
        .orders-empty { text-align: center; color: #999; font-size: 18px; margin: 30px 0; }
        @media (max-width: 768px) { .info-grid { grid-template-columns: 1fr; } .orders-table { font-size: 14px; } }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="account-page">
    <div class="page-header">
        <h1>Tài khoản của tôi</h1>
        <p>Quản lý thông tin cá nhân và xem lịch sử đơn hàng</p>
    </div>

    <div class="user-info">
        <h2>Thông tin cá nhân</h2>
        <div class="info-grid">
            <div class="info-item">
                <label>Họ và tên</label>
                <p><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></p>
            </div>
            <div class="info-item">
                <label>Email</label>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <div class="info-item">
                <label>Số điện thoại</label>
                <p><?php echo htmlspecialchars($user['phone'] ?: 'Chưa cập nhật'); ?></p>
            </div>
            <div class="info-item">
                <label>Địa chỉ</label>
                <p><?php echo htmlspecialchars($user['address'] ?: 'Chưa cập nhật'); ?></p>
            </div>
        </div>
        <a href="edit-profile.php" class="btn-edit"><i class="fas fa-edit"></i> Chỉnh sửa thông tin</a>
    </div>

    <div class="orders-section">
        <h2>Lịch sử đơn hàng</h2>
        <?php if (!empty($orders)): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Mã đơn hàng</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): $status = status_label($order['status']); ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>₫</td>
                        <td><span class="status <?php echo $status['class']; ?>"><?php echo $status['label']; ?></span></td>
                            <td>
                            <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn-view-order">
                                    <i class="fas fa-eye"></i> Xem chi tiết
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="text-align:right;">
                <a href="order_history.php" style="color:#2196f3; font-weight:600;">Xem toàn bộ lịch sử đơn hàng</a>
            </div>
        <?php else: ?>
            <p class="orders-empty">Bạn chưa có đơn hàng nào.</p>
        <?php endif; ?>
    </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>