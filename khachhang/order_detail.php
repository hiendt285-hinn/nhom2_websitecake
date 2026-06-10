<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$order_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = (int)$_SESSION['user_id'];

if ($order_id <= 0) {
    die('ID đơn hàng không hợp lệ.');
}

$order = null;
$orderSql = "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ? AND o.user_id = ? LIMIT 1";
if ($stmt = $conn->prepare($orderSql)) {
    $stmt->bind_param('ii', $order_id, $user_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $order = $result ? $result->fetch_assoc() : null;
    }
    $stmt->close();
} else {
    die('Lỗi truy vấn đơn hàng.');
}

if (!$order) {
    die('Đơn hàng không tồn tại hoặc bạn không có quyền xem.');
}

$items = [];
$detailSql = "SELECT oi.product_id, oi.size, oi.flavor, oi.quantity, oi.unit_price, p.name 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = ?";
if ($stmt = $conn->prepare($detailSql)) {
    $stmt->bind_param('i', $order_id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    $stmt->close();
} else {
    die('Lỗi truy vấn chi tiết đơn hàng.');
}

function status_text($status) {
    $map = [
        'pending' => 'Chờ xử lý',
        'confirmed' => 'Đã xác nhận',
        'shipping' => 'Đang giao',
        'delivered' => 'Đã giao',
        'cancelled' => 'Đã hủy',
    ];
    return $map[$status] ?? htmlspecialchars($status);
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

$isSuccess = isset($_GET['success']) && $_GET['success'] === '1';
$isReceived = isset($_GET['received']) && $_GET['received'] === '1';
$discountAmount = (float)($order['discount_amount'] ?? 0);
$subtotal = $order['total_amount'] + $discountAmount;
$paymentLabel = [
    'cod' => 'Thanh toán khi nhận hàng (COD)',
    'banking' => 'Chuyển khoản ngân hàng',
    'momo' => 'Ví MoMo',
];

$page_title = 'Chi tiết đơn hàng #' . $order_id . ' - Sweet Cake';
include 'header.php';
?>

<div class="content-page">
    <nav class="content-breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <a href="account.php">Tài khoản</a>
        <span>/</span>
        <a href="order_history.php">Lịch sử đơn hàng</a>
        <span>/</span>
        <span>Đơn #<?php echo $order['id']; ?></span>
    </nav>

    <?php if ($isSuccess): ?>
    <div class="content-alert-success" role="alert">
        <i class="fas fa-check-circle"></i>
        Đặt hàng thành công! Đơn hàng #<?php echo $order['id']; ?> đã được ghi nhận. Chúng tôi sẽ liên hệ bạn sớm.
    </div>
    <?php endif; ?>

    <?php if ($isReceived): ?>
    <div class="content-alert-success" role="alert">
        <i class="fas fa-check-circle"></i>
        Bạn đã xác nhận đã nhận hàng. Cảm ơn bạn đã sử dụng dịch vụ!
    </div>
    <?php endif; ?>

    <div class="account-card">
        <div class="order-detail-header">
            <h1><i class="fas fa-receipt"></i> Chi tiết đơn hàng #<?php echo htmlspecialchars($order['id']); ?></h1>
            <span class="status-badge <?php echo status_badge_class($order['status']); ?>">
                <i class="fas <?php echo status_icon($order['status']); ?>"></i>
                <?php echo status_text($order['status']); ?>
            </span>
        </div>

        <div class="order-info-grid">
            <div class="order-info-box">
                <h3><i class="fas fa-user"></i> Thông tin khách hàng</h3>
                <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                <p><strong>Địa chỉ:</strong> <?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                <?php if (!empty(trim($order['note'] ?? ''))): ?>
                <p><strong>Ghi chú:</strong> <?php echo nl2br(htmlspecialchars($order['note'])); ?></p>
                <?php endif; ?>
            </div>

            <div class="order-info-box">
                <h3><i class="fas fa-shopping-cart"></i> Thông tin đơn hàng</h3>
                <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                <p><strong>Thanh toán:</strong> <?php echo htmlspecialchars($paymentLabel[$order['payment_method']] ?? $order['payment_method']); ?></p>
            </div>
        </div>

        <h2 style="font-family:'Playfair Display',serif; font-size:18px; color:var(--brown-dark-text); margin-bottom:16px; display:flex; align-items:center; gap:8px;">
            <i class="fas fa-cake-candles" style="color:var(--main-brown);"></i> Sản phẩm đã đặt
        </h2>

        <div class="account-table-wrap">
            <table class="account-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Size / Hương vị</th>
                        <th style="text-align:center;">SL</th>
                        <th style="text-align:right;">Đơn giá</th>
                        <th style="text-align:right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td data-label="Sản phẩm" style="font-weight:600; color:var(--main-brown);"><?php echo htmlspecialchars($item['name']); ?></td>
                        <td data-label="Size / Hương vị">
                            <?php if (!empty($item['size']) || !empty($item['flavor'])): ?>
                            <span class="variant-tag">
                                <?php if (!empty($item['size'])): ?>
                                <i class="fas fa-ruler"></i> <?php echo htmlspecialchars($item['size']); ?>
                                <?php endif; ?>
                                <?php if (!empty($item['flavor'])): ?>
                                <?php if (!empty($item['size'])): ?> · <?php endif; ?>
                                <i class="fas fa-ice-cream"></i> <?php echo htmlspecialchars($item['flavor']); ?>
                                <?php endif; ?>
                            </span>
                            <?php else: ?>
                            <span class="variant-tag">Mặc định</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="SL" style="text-align:center;"><?php echo (int)$item['quantity']; ?></td>
                        <td data-label="Đơn giá" style="text-align:right;"><?php echo number_format($item['unit_price'], 0, ',', '.'); ?>₫</td>
                        <td data-label="Thành tiền" style="text-align:right; font-weight:600;"><?php echo number_format($item['unit_price'] * $item['quantity'], 0, ',', '.'); ?>₫</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="order-summary-box">
            <?php if ($discountAmount > 0): ?>
            <div class="row">
                <span>Tạm tính</span>
                <span><?php echo number_format($subtotal, 0, ',', '.'); ?>₫</span>
            </div>
            <div class="row discount">
                <span>
                    <i class="fas fa-tag"></i>
                    Giảm giá<?php if (!empty($order['promo_code'])): ?> (<?php echo htmlspecialchars($order['promo_code']); ?>)<?php endif; ?>
                </span>
                <span>-<?php echo number_format($discountAmount, 0, ',', '.'); ?>₫</span>
            </div>
            <?php endif; ?>
            <div class="row total">
                <span>Tổng cộng</span>
                <span><?php echo number_format($order['total_amount'], 0, ',', '.'); ?>₫</span>
            </div>
        </div>

        <div class="order-action-bar">
            <form method="post" action="confirm_received.php" onsubmit="return <?php echo $order['status'] === 'shipping' ? "confirm('Bạn đã nhận được hàng? Xác nhận sẽ chuyển trạng thái đơn sang Đã giao.');" : "false;"; ?>">
                <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                <input type="hidden" name="redirect" value="detail">
                <button type="submit" class="btn-account btn-account-success" <?php if ($order['status'] !== 'shipping') echo 'disabled'; ?>>
                    <i class="fas fa-check-circle"></i>
                    <?php echo $order['status'] === 'delivered' ? 'Đã giao hàng' : 'Đã nhận hàng'; ?>
                </button>
            </form>

            <a href="order_history.php" class="btn-primary"><i class="fas fa-history"></i> Lịch sử đơn hàng</a>
            <a href="products.php" class="btn-secondary"><i class="fas fa-shopping-bag"></i> Mua thêm</a>

            <?php if ($order['status'] === 'pending'): ?>
            <a href="cancel_order.php?id=<?php echo $order['id']; ?>" class="btn-account btn-account-danger" onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?');">
                <i class="fas fa-times-circle"></i> Hủy đơn hàng
            </a>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <div style="margin-top:24px; padding-top:20px; border-top:1px dashed var(--border-soft); text-align:center;">
            <a href="admin/order_detail.php?id=<?php echo $order['id']; ?>" class="btn-secondary">
                <i class="fas fa-cog"></i> Quản lý đơn hàng (Admin)
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
