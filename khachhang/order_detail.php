<?php
session_start();

require_once 'connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$order_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = (int)$_SESSION['user_id'];

if ($order_id <= 0) {
    die('ID đơn hàng không hợp lệ.');
}

// Lấy thông tin đơn hàng
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

// Lấy chi tiết sản phẩm từ order_items
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
    <title>Chi tiết đơn hàng #<?php echo htmlspecialchars($order_id); ?></title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Open Sans', Arial, sans-serif;
            background: var(--light-beige);
            margin: 0;
            padding: 0;
        }
        .order-detail-page {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px 40px;
        }
        .order-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            padding: 24px 20px;
        }
        .order-card h1 {
            font-size: 26px;
            margin-bottom: 20px;
            color: var(--main-brown);
        }
        .info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .info div {
            min-width: 220px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px 8px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
            font-size: 14px;
        }
        th {
            background: #f9f6f2;
            color: var(--text-black);
            font-weight: 600;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .muted {
            color: #888;
            font-size: 0.95em;
        }
        .total {
            font-size: 16px;
            text-align: right;
            margin-bottom: 16px;
            font-weight: 600;
        }
        .total strong {
            color: var(--main-brown);
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: var(--main-brown);
            color: #fff;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.2s;
        }
        .back-btn:hover {
            background: var(--brown-light);
        }
        @media (max-width: 600px) {
            .order-card {
                padding: 18px 12px;
            }
            .info {
                flex-direction: column;
            }
            table, th, td {
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>

<div class="order-detail-page">
    <div class="order-card">
        <h1>Chi tiết đơn hàng #<?php echo htmlspecialchars($order['id']); ?></h1>

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

        <a href="order_history.php" class="back-btn"><i class="fas fa-arrow-left"></i> Quay lại danh sách đơn hàng</a>
    </div>
</div>
</body>
</html>