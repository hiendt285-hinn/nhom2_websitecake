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

function status_badge_class($status) {
    switch ($status) {
        case 'delivered': return 'status-delivered';
        case 'pending': return 'status-pending';
        case 'confirmed': return 'status-confirmed';
        case 'shipping': return 'status-shipping';
        case 'cancelled': return 'status-cancelled';
        default: return '';
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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng #<?php echo htmlspecialchars($order_id); ?></title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --main-brown: #8B4513;
            --brown-light: #A0522D;
            --light-beige: #FDF5E6;
            --text-black: #333333;
            --border-color: #e0e0e0;
            --success-green: #4CAF50;
            --warning-orange: #ff9800;
            --info-blue: #1976D2;
            --shipping-purple: #9C27B0;
            --danger-red: #f44336;
            --gray-light: #f5f5f5;
        }
        
        body {
            font-family: 'Open Sans', Arial, sans-serif;
            background: var(--light-beige);
            margin: 0;
            padding: 0;
        }
        
        .order-detail-page {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px 40px;
        }
        
        /* Breadcrumb */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .breadcrumb a {
            color: var(--main-brown);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .breadcrumb i {
            font-size: 12px;
            color: #999;
        }
        
        /* Order Card */
        .order-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(139, 69, 19, 0.08);
            padding: 30px;
            border: 1px solid rgba(139, 69, 19, 0.1);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px dashed var(--border-color);
        }
        
        .order-header h1 {
            font-size: 28px;
            margin: 0;
            color: var(--main-brown);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .order-header h1 i {
            font-size: 32px;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .status-pending { background: #fff3e0; color: #e65100; border-left: 4px solid #ff9800; }
        .status-confirmed { background: #e3f2fd; color: #0d47a1; border-left: 4px solid #1976D2; }
        .status-shipping { background: #f3e5f5; color: #4a0072; border-left: 4px solid #9C27B0; }
        .status-delivered { background: #e8f5e9; color: #1b5e20; border-left: 4px solid #4CAF50; }
        .status-cancelled { background: #ffebee; color: #b71c1c; border-left: 4px solid #f44336; }
        
        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-box {
            background: #faf7f2;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid rgba(139, 69, 19, 0.1);
        }
        
        .info-box h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: var(--main-brown);
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(139, 69, 19, 0.2);
        }
        
        .info-box h3 i {
            font-size: 18px;
        }
        
        .info-content p {
            margin: 8px 0;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .info-content strong {
            min-width: 100px;
            display: inline-block;
            color: var(--text-black);
        }
        
        /* Table */
        .table-wrapper {
            overflow-x: auto;
            margin: 25px 0;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        th {
            background: var(--main-brown);
            color: white;
            font-weight: 600;
            padding: 14px 12px;
            font-size: 14px;
            text-align: left;
        }
        
        td {
            padding: 14px 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover td {
            background: #f9f6f2;
        }
        
        .product-name {
            font-weight: 600;
            color: var(--main-brown);
        }
        
        .variant-info {
            color: #666;
            font-size: 0.95em;
            background: #f5f5f5;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }
        
        /* Summary Section */
        .summary-section {
            background: #faf7f2;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            border: 1px solid rgba(139, 69, 19, 0.1);
        }
        
        .summary-rows {
            max-width: 400px;
            margin-left: auto;
        }
        
        .summary-rows .row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 15px;
        }
        
        .summary-rows .row:not(:last-child) {
            border-bottom: 1px dashed #ddd;
        }
        
        .summary-rows .row.discount {
            color: var(--success-green);
        }
        
        .summary-rows .row.total {
            font-size: 18px;
            font-weight: 700;
            color: var(--main-brown);
            padding-top: 15px;
            margin-top: 5px;
            border-top: 2px solid var(--border-color);
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            min-width: 180px;
            height: 45px;
        }
        
        .btn i {
            font-size: 16px;
        }
        
        .btn-primary {
            background: var(--main-brown);
            color: white;
            box-shadow: 0 4px 10px rgba(139, 69, 19, 0.2);
        }
        
        .btn-primary:hover:not(:disabled) {
            background: var(--brown-light);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(139, 69, 19, 0.3);
        }
        
        .btn-success {
            background: var(--success-green);
            color: white;
            box-shadow: 0 4px 10px rgba(76, 175, 80, 0.2);
        }
        
        .btn-success:hover:not(:disabled) {
            background: #45a049;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6d4c41;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5d3e34;
            transform: translateY(-2px);
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Success Banner */
        .success-banner {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            border-left: 5px solid #2e7d32;
            color: #2e7d32;
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 10px rgba(46, 125, 50, 0.1);
        }
        
        .success-banner i {
            font-size: 28px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .order-detail-page {
                padding: 0 15px 30px;
            }
            
            .order-card {
                padding: 20px;
            }
            
            .order-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .summary-rows {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="order-detail-page">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="index.php"><i class="fas fa-home"></i> Trang chủ</a>
        <i class="fas fa-chevron-right"></i>
        <a href="order_history.php">Lịch sử đơn hàng</a>
        <i class="fas fa-chevron-right"></i>
        <span>Chi tiết đơn hàng #<?php echo $order['id']; ?></span>
    </div>

    <?php if ($isSuccess): ?>
    <div class="success-banner">
        <i class="fas fa-check-circle"></i>
        <span>Đặt hàng thành công! Đơn hàng #<?php echo $order['id']; ?> đã được ghi nhận. Chúng tôi sẽ liên hệ bạn sớm.</span>
    </div>
    <?php endif; ?>
    
    <?php if ($isReceived): ?>
    <div class="success-banner">
        <i class="fas fa-check-circle"></i>
        <span>Bạn đã xác nhận đã nhận hàng. Cảm ơn bạn đã sử dụng dịch vụ!</span>
    </div>
    <?php endif; ?>

    <div class="order-card">
        <div class="order-header">
            <h1>
                <i class="fas fa-receipt"></i>
                Chi tiết đơn hàng #<?php echo htmlspecialchars($order['id']); ?>
            </h1>
            <span class="status-badge <?php echo status_badge_class($order['status']); ?>">
                <i class="fas <?php 
                    echo $order['status'] == 'delivered' ? 'fa-check-circle' : 
                        ($order['status'] == 'shipping' ? 'fa-truck' : 
                        ($order['status'] == 'cancelled' ? 'fa-times-circle' : 'fa-clock')); 
                ?>"></i>
                <?php echo status_text($order['status']); ?>
            </span>
        </div>

        <!-- Customer & Order Info Grid -->
        <div class="info-grid">
            <div class="info-box">
                <h3><i class="fas fa-user"></i> Thông tin khách hàng</h3>
                <div class="info-content">
                    <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                    <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <p><strong>Địa chỉ:</strong> <?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                    <?php if (!empty(trim($order['note'] ?? ''))): ?>
                    <p><strong>Ghi chú:</strong> <?php echo nl2br(htmlspecialchars($order['note'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="info-box">
                <h3><i class="fas fa-shopping-cart"></i> Thông tin đơn hàng</h3>
                <div class="info-content">
                    <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                    <p><strong>Thanh toán:</strong> <?php echo htmlspecialchars($paymentLabel[$order['payment_method']] ?? $order['payment_method']); ?></p>
                    <p><strong>Phương thức:</strong> <?php echo $order['payment_method'] == 'cod' ? 'Thanh toán khi nhận hàng' : 'Chuyển khoản'; ?></p>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <h3 style="margin: 20px 0 15px; color: var(--main-brown); display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-cake-candles"></i> Sản phẩm đã đặt
        </h3>
        
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Size / Hương vị</th>
                        <th style="text-align: center;">SL</th>
                        <th style="text-align: right;">Đơn giá</th>
                        <th style="text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="product-name"><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>
                            <?php if (!empty($item['size']) || !empty($item['flavor'])): ?>
                                <span class="variant-info">
                                    <?php if (!empty($item['size'])): ?>
                                        <i class="fas fa-ruler"></i> <?php echo htmlspecialchars($item['size']); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($item['flavor'])): ?>
                                        <?php if (!empty($item['size'])): ?> · <?php endif; ?>
                                        <i class="fas fa-ice-cream"></i> <?php echo htmlspecialchars($item['flavor']); ?>
                                    <?php endif; ?>
                                </span>
                            <?php else: ?>
                                <span class="variant-info">Mặc định</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;"><?php echo (int)$item['quantity']; ?></td>
                        <td style="text-align: right;"><?php echo number_format($item['unit_price'], 0, ',', '.'); ?>₫</td>
                        <td style="text-align: right; font-weight: 600;"><?php echo number_format($item['unit_price'] * $item['quantity'], 0, ',', '.'); ?>₫</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="summary-section">
            <div class="summary-rows">
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
        </div>

        <!-- Action Buttons (Equal size) -->
        <div class="action-buttons">
            <!-- Đã nhận hàng button -->
            <form method="post" action="confirm_received.php" style="display: inline;" onsubmit="return <?php echo $order['status'] === 'shipping' ? "confirm('Bạn đã nhận được hàng? Xác nhận sẽ chuyển trạng thái đơn sang Đã giao.');" : "false;"; ?>">
                <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                <input type="hidden" name="redirect" value="detail">
                <button type="submit" class="btn btn-success" <?php if ($order['status'] !== 'shipping') echo 'disabled'; ?>>
                    <i class="fas fa-check-circle"></i> 
                    <?php echo $order['status'] === 'delivered' ? 'Đã giao hàng' : 'Đã nhận hàng'; ?>
                </button>
            </form>

            <!-- Quay lại danh sách -->
            <a href="order_history.php" class="btn btn-primary">
                <i class="fas fa-history"></i> Lịch sử đơn hàng
            </a>

            <!-- Tiếp tục mua sắm -->
            <a href="products.php" class="btn btn-secondary">
                <i class="fas fa-shopping-bag"></i> Mua thêm
            </a>

            <!-- Hủy đơn hàng (chỉ khi đang chờ xử lý) -->
            <?php if ($order['status'] === 'pending'): ?>
            <a href="cancel_order.php?id=<?php echo $order['id']; ?>" 
               class="btn btn-primary" 
               style="background: var(--danger-red);"
               onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?');">
                <i class="fas fa-times-circle"></i> Hủy đơn hàng
            </a>
            <?php endif; ?>
        </div>

        <!-- Admin link (visible to admin) -->
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <div style="margin-top: 25px; padding-top: 20px; border-top: 1px dashed var(--border-color); text-align: center;">
            <a href="admin/order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-primary" style="background: #333; min-width: auto;">
                <i class="fas fa-cog"></i> Quản lý đơn hàng (Admin)
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>