<?php
session_start();
require_once 'connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = (int)$_SESSION['user_id'];

// Lấy thông tin đơn hàng (mysqli)
$order = null;
$orderSql = "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ? AND o.user_id = ? LIMIT 1";
if ($stmt = $conn->prepare($orderSql)) {
    $stmt->bind_param('ii', $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result ? $result->fetch_assoc() : null;
    $stmt->close();
}

if (!$order) {
    die('Đơn hàng không tồn tại hoặc bạn không có quyền xem.');
}

// Lấy chi tiết sản phẩm từ order_items
$items = [];
$detailSql = "SELECT oi.product_id, oi.size, oi.flavor, oi.quantity, oi.unit_price, p.name 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = ?";
if ($stmt = $conn->prepare($detailSql)) {
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt->close();
}

// Map trạng thái theo schema csdl.sql: pending, confirmed, shipping, delivered, cancelled
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

function status_color($status) {
    switch ($status) {
        case 'delivered': return '#4CAF50';
        case 'pending': return '#ff9800';
        case 'confirmed': return '#1976D2';
        case 'shipping': return '#9C27B0';
        case 'cancelled': return '#f44336';
        default: return '#555';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng #<?php echo $order_id; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f7f7f7; }
        .container { max-width: 900px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        .info { display: flex; justify-content: space-between; margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 6px; }
        .info div { flex: 1; }
        .info strong { color: #555; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #4CAF50; color: white; }
        .muted { color: #666; font-size: 12px; }
        .total { font-weight: bold; font-size: 18px; text-align: right; color: #2e7d32; }
        .back-btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px; }
        .back-btn:hover { background: #1976D2; }
    </style>
</head>
<body>
<div class="container">
    <h1>Chi tiết đơn hàng #<?php echo $order['id']; ?></h1>

    <div class="info">
        <div>
            <p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
            <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i:s', strtotime($order['created_at'])); ?></p>
            <p><strong>Thanh toán:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
        </div>
        <div style="text-align: right;">
            <p><strong>Trạng thái:</strong>
                <span style="color: <?php echo status_color($order['status']); ?>; font-weight: bold;">
                    <?php echo status_text($order['status']); ?>
                </span>
            </p>
            <p><strong>Tổng cộng:</strong> <?php echo number_format($order['total_amount'], 0, ',', '.'); ?> ₫</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Biến thể</th>
                <th>Số lượng</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td class="muted">
                    <?php if (!empty($item['size'])): ?>Size: <?php echo htmlspecialchars($item['size']); ?><?php endif; ?>
                    <?php if (!empty($item['flavor'])): ?><?php echo !empty($item['size']) ? ' · ' : ''; ?>Vị: <?php echo htmlspecialchars($item['flavor']); ?><?php endif; ?>
                </td>
                <td><?php echo (int)$item['quantity']; ?></td>
                <td><?php echo number_format($item['unit_price'], 0, ',', '.'); ?> ₫</td>
                <td><?php echo number_format($item['unit_price'] * $item['quantity'], 0, ',', '.'); ?> ₫</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p class="total">
        Tổng cộng: <strong><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> ₫</strong>
    </p>

    <a href="order_history.php" class="back-btn">Quay lại danh sách đơn hàng</a>
</div>
</body>
</html>