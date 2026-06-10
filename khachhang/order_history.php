<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$user = null;
$userSql = "SELECT username, full_name, email, phone, address FROM users WHERE id = ? LIMIT 1";
if ($stmt = $conn->prepare($userSql)) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();
}

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

function status_badge_class($status) {
    switch ($status) {
        case 'delivered': return 'status-delivered';
        case 'pending': return 'status-pending';
        case 'confirmed': return 'status-confirmed';
        case 'shipping': return 'status-shipping';
        case 'cancelled': return 'status-cancelled';
        default: return 'status-default';
    }
}

function status_icon($status) {
    switch ($status) {
        case 'delivered': return 'fa-check-circle';
        case 'pending': return 'fa-clock';
        case 'confirmed': return 'fa-check';
        case 'shipping': return 'fa-truck';
        case 'cancelled': return 'fa-times-circle';
        default: return 'fa-circle';
    }
}

$page_title = 'Lịch sử đơn hàng - Sweet Cake';
include 'header.php';
?>

<div class="content-page">
    <nav class="content-breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <a href="account.php">Tài khoản</a>
        <span>/</span>
        <span>Lịch sử đơn hàng</span>
    </nav>

    <div class="content-page-header">
        <h1>Lịch sử đơn hàng</h1>
        <p>Theo dõi tất cả đơn hàng bạn đã đặt tại Sweet Cake</p>
    </div>

    <nav class="account-nav" aria-label="Tài khoản">
        <a href="account.php"><i class="fas fa-user"></i> Thông tin</a>
        <a href="edit-profile.php"><i class="fas fa-user-edit"></i> Chỉnh sửa</a>
        <a href="order_history.php" class="is-active"><i class="fas fa-history"></i> Lịch sử đơn hàng</a>
    </nav>

    <?php if ($user): ?>
    <div class="account-card">
        <h2><i class="fas fa-id-card"></i> Thông tin cá nhân</h2>
        <div class="account-profile-grid">
            <div class="account-field">
                <label>Họ và tên</label>
                <div class="value"><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></div>
            </div>
            <div class="account-field">
                <label>Email</label>
                <div class="value"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <div class="account-field">
                <label>Số điện thoại</label>
                <div class="value"><?php echo htmlspecialchars($user['phone'] ?: 'Chưa cập nhật'); ?></div>
            </div>
            <div class="account-field">
                <label>Địa chỉ</label>
                <div class="value"><?php echo htmlspecialchars($user['address'] ?: 'Chưa cập nhật'); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="account-card">
        <h2><i class="fas fa-list"></i> Danh sách đơn hàng</h2>

        <?php if (empty($orders)): ?>
        <div class="account-empty">
            <i class="fas fa-box-open"></i>
            <p>Bạn chưa có đơn hàng nào.</p>
            <a href="products.php" class="btn-primary"><i class="fas fa-shopping-bag"></i> Mua sắm ngay</a>
        </div>
        <?php else: ?>
        <div class="account-table-wrap">
            <table class="account-table">
                <thead>
                    <tr>
                        <th>Mã đơn hàng</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td data-label="Mã đơn hàng">
                            <span class="order-id-badge">#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></span>
                        </td>
                        <td data-label="Ngày đặt">
                            <span class="order-date-text">
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                            </span>
                            <span class="order-date-text" style="display:block; margin-top:4px;">
                                <i class="fas fa-clock"></i>
                                <?php echo date('H:i', strtotime($order['created_at'])); ?>
                            </span>
                        </td>
                        <td data-label="Tổng tiền">
                            <span class="order-amount"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>₫</span>
                        </td>
                        <td data-label="Trạng thái">
                            <span class="status-badge <?php echo status_badge_class($order['status']); ?>">
                                <i class="fas <?php echo status_icon($order['status']); ?>"></i>
                                <?php echo status_text($order['status']); ?>
                            </span>
                        </td>
                        <td data-label="Thao tác">
                            <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn-account btn-account-view">
                                <i class="fas fa-eye"></i> Chi tiết
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div class="account-actions" style="margin-top:20px;">
            <a href="account.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại tài khoản</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
