<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

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
    session_destroy();
    header('Location: login.php');
    exit;
}

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

$page_title = 'Tài khoản của tôi - Sweet Cake';
include 'header.php';
?>

<div class="content-page">
    <nav class="content-breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <span>Tài khoản</span>
    </nav>

    <div class="content-page-header">
        <h1>Tài khoản của tôi</h1>
        <p>Quản lý thông tin cá nhân và theo dõi đơn hàng của bạn</p>
    </div>

    <nav class="account-nav" aria-label="Tài khoản">
        <a href="account.php" class="is-active"><i class="fas fa-user"></i> Thông tin</a>
        <a href="edit-profile.php"><i class="fas fa-user-edit"></i> Chỉnh sửa</a>
        <a href="order_history.php"><i class="fas fa-history"></i> Lịch sử đơn hàng</a>
    </nav>

    <div class="account-card">
        <h2><i class="fas fa-id-card"></i> Thông tin cá nhân</h2>

        <?php if (isset($_GET['updated']) && $_GET['updated'] === '1'): ?>
        <div class="content-alert-success" role="alert">
            <i class="fas fa-check-circle"></i> Cập nhật thông tin thành công!
        </div>
        <?php endif; ?>

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

        <div class="account-actions">
            <a href="edit-profile.php" class="btn-primary"><i class="fas fa-edit"></i> Chỉnh sửa thông tin</a>
        </div>
    </div>

    <div class="account-card">
        <h2><i class="fas fa-shopping-bag"></i> Đơn hàng gần đây</h2>

        <?php if (isset($_GET['received']) && $_GET['received'] === '1'): ?>
        <div class="content-alert-success" role="alert">
            <i class="fas fa-check-circle"></i> Đã xác nhận nhận hàng. Trạng thái đơn đã cập nhật thành Đã giao.
        </div>
        <?php endif; ?>

        <?php if (!empty($orders)): ?>
        <div class="account-table-wrap">
            <table class="account-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): $status = status_label($order['status']); ?>
                    <tr>
                        <td data-label="Mã đơn"><span class="order-id-badge">#<?php echo $order['id']; ?></span></td>
                        <td data-label="Ngày đặt">
                            <span class="order-date-text">
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                            </span>
                        </td>
                        <td data-label="Tổng tiền"><span class="order-amount"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>₫</span></td>
                        <td data-label="Trạng thái">
                            <span class="status-badge <?php echo $status['class']; ?>"><?php echo $status['label']; ?></span>
                        </td>
                        <td data-label="Thao tác">
                            <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn-account btn-account-view">
                                <i class="fas fa-eye"></i> Chi tiết
                            </a>
                            <form method="post" action="confirm_received.php" style="display:inline; margin-left:6px;" onsubmit="return <?php echo $order['status'] === 'shipping' ? "confirm('Bạn đã nhận được hàng?');" : "false;"; ?>">
                                <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                                <input type="hidden" name="redirect" value="account">
                                <button type="submit" class="btn-account btn-account-success" <?php if ($order['status'] !== 'shipping') echo ' disabled title="Chỉ kích hoạt khi đơn ở trạng thái Đang giao."'; ?>>
                                    <i class="fas fa-box-open"></i> Đã nhận hàng
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="account-link-more">
            <a href="order_history.php">Xem toàn bộ lịch sử đơn hàng <i class="fas fa-arrow-right"></i></a>
        </div>
        <?php else: ?>
        <div class="account-empty">
            <i class="fas fa-box-open"></i>
            <p>Bạn chưa có đơn hàng nào.</p>
            <a href="products.php" class="btn-primary"><i class="fas fa-shopping-bag"></i> Mua sắm ngay</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
