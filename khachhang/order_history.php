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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử đơn hàng - Sweet Cake</title>
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
        
        .order-history-page {
            max-width: 1200px;
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
        
        /* Back Button */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--main-brown);
            color: #fff;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 14px;
            box-shadow: 0 4px 10px rgba(139, 69, 19, 0.2);
        }
        
        .back-btn:hover {
            background: var(--brown-light);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(139, 69, 19, 0.3);
        }
        
        /* User Summary */
        .user-summary {
            background: #faf7f2;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(139, 69, 19, 0.1);
        }
        
        .user-summary h2 {
            margin: 0 0 15px 0;
            font-size: 18px;
            color: var(--main-brown);
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(139, 69, 19, 0.2);
        }
        
        .user-summary h2 i {
            font-size: 20px;
        }
        
        .user-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-item label {
            font-weight: 600;
            color: #777;
            margin-bottom: 4px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-item span {
            background: #fff;
            border: 1px solid rgba(139, 69, 19, 0.15);
            border-radius: 8px;
            padding: 10px 12px;
            color: var(--text-black);
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Table */
        .table-wrapper {
            overflow-x: auto;
            margin: 25px 0;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            background: white;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
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
            padding: 16px 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
            vertical-align: middle;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover td {
            background: #f9f6f2;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }
        
        .status-pending { 
            background: #fff3e0; 
            color: #e65100; 
            border-left: 3px solid #ff9800;
        }
        .status-confirmed { 
            background: #e3f2fd; 
            color: #0d47a1; 
            border-left: 3px solid #1976D2;
        }
        .status-shipping { 
            background: #f3e5f5; 
            color: #4a0072; 
            border-left: 3px solid #9C27B0;
        }
        .status-delivered { 
            background: #e8f5e9; 
            color: #1b5e20; 
            border-left: 3px solid #4CAF50;
        }
        .status-cancelled { 
            background: #ffebee; 
            color: #b71c1c; 
            border-left: 3px solid #f44336;
        }
        
        /* Order ID */
        .order-id {
            font-weight: 700;
            color: var(--main-brown);
            font-size: 15px;
        }
        
        /* Amount */
        .amount {
            font-weight: 700;
            color: var(--main-brown);
            font-size: 16px;
        }
        
        /* Date */
        .order-date {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 13px;
        }
        
        .order-date i {
            color: var(--main-brown);
            font-size: 12px;
        }
        
        /* View Button */
        .view-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--main-brown);
            color: white;
            padding: 8px 16px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s;
            white-space: nowrap;
            box-shadow: 0 2px 5px rgba(139, 69, 19, 0.2);
        }
        
        .view-btn:hover {
            background: var(--brown-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(139, 69, 19, 0.3);
        }
        
        .view-btn i {
            font-size: 12px;
        }
        
        /* No Orders */
        .no-orders {
            text-align: center;
            color: #777;
            font-style: italic;
            padding: 60px 20px;
            background: #faf7f2;
            border-radius: 12px;
            border: 1px dashed rgba(139, 69, 19, 0.3);
        }
        
        .no-orders i {
            font-size: 48px;
            color: rgba(139, 69, 19, 0.2);
            margin-bottom: 15px;
        }
        
        .no-orders p {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .shop-now-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--main-brown);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .shop-now-btn:hover {
            background: var(--brown-light);
            transform: translateY(-2px);
        }
        
        /* Footer Link */
        .footer-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(139, 69, 19, 0.1);
        }
        
        .footer-link a {
            color: var(--main-brown);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .footer-link a:hover {
            color: var(--brown-light);
            gap: 12px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .order-history-page {
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
            
            .order-header h1 {
                font-size: 24px;
            }
            
            .user-info-grid {
                grid-template-columns: 1fr;
            }
            
            td {
                padding: 12px 10px;
            }
            
            .status-badge {
                padding: 4px 8px;
                font-size: 11px;
            }
            
            .view-btn {
                padding: 6px 12px;
                font-size: 12px;
            }
        }
        
        @media (max-width: 480px) {
            .order-date {
                flex-direction: column;
                align-items: flex-start;
                gap: 2px;
            }
            
            .amount {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>

<?php include 'header.php'; ?>

<div class="order-history-page">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="index.php"><i class="fas fa-home"></i> Trang chủ</a>
        <i class="fas fa-chevron-right"></i>
        <a href="account.php">Tài khoản</a>
        <i class="fas fa-chevron-right"></i>
        <span>Lịch sử đơn hàng</span>
    </div>

    <div class="order-card">
        <div class="order-header">
            <h1>
                <i class="fas fa-history"></i>
                Lịch sử đơn hàng
            </h1>
            <a href="account.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại tài khoản
            </a>
        </div>

        <?php if ($user): ?>
        <div class="user-summary">
            <h2>
                <i class="fas fa-user-circle"></i>
                Thông tin cá nhân
            </h2>
            <div class="user-info-grid">
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
        </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="no-orders">
                <i class="fas fa-box-open"></i>
                <p>Bạn chưa có đơn hàng nào.</p>
                <a href="products.php" class="shop-now-btn">
                    <i class="fas fa-shopping-bag"></i> Mua sắm ngay
                </a>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
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
                            <td>
                                <span class="order-id">#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></span>
                            </td>
                            <td>
                                <div class="order-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                                </div>
                                <div style="font-size: 12px; color: #999; margin-top: 4px;">
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('H:i', strtotime($order['created_at'])); ?>
                                </div>
                            </td>
                            <td>
                                <span class="amount"><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> ₫</span>
                            </td>
                            <td>
                                <span class="status-badge <?php echo status_badge_class($order['status']); ?>">
                                    <i class="fas <?php echo status_icon($order['status']); ?>"></i>
                                    <?php echo status_text($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="view-btn">
                                    <i class="fas fa-eye"></i> Chi tiết
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="footer-link">
            <a href="index.php">
                <i class="fas fa-home"></i> Quay lại trang chủ
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" defer></script>
</body>
</html>