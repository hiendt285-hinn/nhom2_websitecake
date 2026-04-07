<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION["admin"])) {
    header("Location: login_admin.php");
    exit();
}

require_once 'connect.php';

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($orderId <= 0) {
    header("Location: admin_dashboard.php?page=orders");
    exit();
}

// Lấy thông tin đơn hàng (cần sớm để kiểm tra khi xử lý POST)
$order = null;
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $row = $result->fetch_assoc()) {
    $order = $row;
}
$stmt->close();

// Cập nhật trạng thái hoặc hủy đơn (chỉ khi đơn chưa giao và chưa hủy)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $order) {
    $action = $_POST['action'] ?? '';
    $status = trim($_POST['status'] ?? '');
    $allowedStatus = ['pending', 'confirmed', 'shipping', 'delivered', 'cancelled'];
    $readOnlyStatus = ['delivered', 'cancelled'];
    $canUpdate = !in_array($order['status'], $readOnlyStatus, true);
    
    if ($canUpdate && $action === 'update_status' && in_array($status, $allowedStatus, true)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $orderId);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_dashboard.php?page=order_detail&id=" . $orderId . "&updated=1");
        exit();
    }
    
    if ($canUpdate && $action === 'cancel_order') {
        $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $stmt->close();
        header("Location: admin_dashboard.php?page=order_detail&id=" . $orderId . "&cancelled=1");
        exit();
    }
}

if (!$order) {
    header("Location: admin_dashboard.php?page=orders");
    exit();
}

// Lấy chi tiết sản phẩm
$items = [];
$stmt = $conn->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

// Hàm helper
function getStatusText($status) {
    $statuses = [
        'pending' => 'Chờ xử lý',
        'confirmed' => 'Đã xác nhận',
        'shipping' => 'Đang giao',
        'delivered' => 'Đã giao',
        'cancelled' => 'Đã hủy'
    ];
    return $statuses[$status] ?? $status;
}

function getStatusColor($status) {
    $colors = [
        'pending' => '#f39c12',
        'confirmed' => '#3498db',
        'shipping' => '#9b59b6',
        'delivered' => '#27ae60',
        'cancelled' => '#e74c3c'
    ];
    return $colors[$status] ?? '#95a5a6';
}

function getPaymentMethodText($method) {
    $methods = [
        'cod' => 'COD (Thanh toán khi nhận hàng)',
        'banking' => 'Chuyển khoản ngân hàng',
        'momo' => 'Ví MoMo'
    ];
    return $methods[$method] ?? $method;
}

$subtotal = $order['total_amount'] + ($order['discount_amount'] ?? 0);
?>
<style>
/* Chỉ áp dụng cho nội dung chi tiết đơn - không ảnh hưởng sidebar/layout admin */
.order-detail-admin .od-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .order-detail-admin .od-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .order-detail-admin .od-header h1 {
            font-size: 28px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Button Styles - Tất cả button có kích thước bằng nhau */
        .order-detail-admin .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            min-width: 160px;
            height: 45px;
            white-space: nowrap;
        }

        .order-detail-admin .btn i {
            font-size: 16px;
        }

        .order-detail-admin .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .order-detail-admin .btn-secondary:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .order-detail-admin .btn-primary {
            background: #9a7b5a;
            color: white;
        }

        .order-detail-admin .btn-primary:hover {
            background: #A0522D;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(139, 69, 19, 0.3);
        }

        .order-detail-admin .btn-success {
            background: #27ae60;
            color: white;
        }

        .order-detail-admin .btn-success:hover {
            background: #219a52;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(39, 174, 96, 0.3);
        }

        .order-detail-admin .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .order-detail-admin .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(231, 76, 60, 0.3);
        }

        .order-detail-admin .btn-warning {
            background: #27ae60;
            color: white;
        }

        .order-detail-admin .btn-warning:hover {
            background:rgb(7, 75, 35);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(243, 156, 18, 0.3);
        }

        /* Disabled button */
        .order-detail-admin .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Messages */
        .order-detail-admin .od-message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Card */
        .order-detail-admin .od-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 20px;
        }

        /* Info Grid */
        .order-detail-admin .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .order-detail-admin .info-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #dee2e6;
        }

        .order-detail-admin .info-box h3 {
            margin-bottom: 15px;
            color: #8B4513;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }

        .order-detail-admin .info-item {
            margin-bottom: 10px;
        }

        .order-detail-admin .info-item strong {
            display: inline-block;
            min-width: 100px;
            color: #555;
        }

        /* Status Badge */
        .order-detail-admin .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            color: white;
        }

        /* Table */
        .order-detail-admin .table-responsive {
            overflow-x: auto;
            margin: 20px 0;
        }

        .order-detail-admin table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-detail-admin th {
            background: #9a7b5a;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }

        .order-detail-admin td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }

        .order-detail-admin tr:hover td {
            background: #f8f9fa;
        }

        .order-detail-admin .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }

        /* Summary */
        .order-detail-admin .summary {
            text-align: right;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px dashed #dee2e6;
        }

        .order-detail-admin .summary-row {
            margin-bottom: 10px;
        }

        .order-detail-admin .summary-row.total {
            font-size: 18px;
            font-weight: bold;
            color: #9a7b5a;
        }

        /* Actions Container */
        .order-detail-admin .actions-container {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }

        .order-detail-admin .actions-title {
            font-size: 16px;
            font-weight: 600;
            color: #8B4513;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Button Groups */
        .order-detail-admin .button-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .order-detail-admin .status-form {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            flex: 1;
            min-width: 400px;
        }

        .order-detail-admin .status-form label {
            font-weight: 500;
            color: #555;
            min-width: 120px;
        }

        .order-detail-admin .status-form select {
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            min-width: 180px;
            height: 42px;
            font-size: 14px;
        }

        .order-detail-admin .status-form select:focus {
            outline: none;
            border-color: #8B4513;
        }

        .order-detail-admin .variant-info {
            color: #666;
            font-size: 13px;
        }

        /* Cancelled Message */
        .order-detail-admin .cancelled-message {
            margin-top: 20px;
            padding: 15px;
            background: #f8d7da;
            color: #9a7b5a;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .order-detail-admin .status-form {
                min-width: 100%;
            }
            
            .order-detail-admin .button-group {
                flex-direction: column;
                width: 100%;
            }
            
            .order-detail-admin .btn {
                width: 100%;
                min-width: 100%;
            }
            
            .order-detail-admin .status-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .order-detail-admin .status-form select {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .order-detail-admin .od-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-detail-admin .info-grid {
                grid-template-columns: 1fr;
            }
            
            .order-detail-admin .info-item {
                flex-direction: column;
            }
            
            .order-detail-admin .info-item strong {
                min-width: auto;
                margin-bottom: 5px;
            }
        }

        /* Small screens */
        @media (max-width: 480px) {
            .order-detail-admin .btn {
                height: 40px;
                font-size: 13px;
                padding: 10px 16px;
            }
            
            .order-detail-admin .status-form select {
                height: 38px;
            }
        }
    </style>
<div class="admin-content order-detail-admin">
    <div class="od-container">
        <!-- Header -->
        <div class="od-header">
            <h1>
                <i class="fas fa-file-invoice"></i>
                Chi tiết đơn hàng #<?php echo $order['id']; ?>
            </h1>
            <a href="admin_dashboard.php?page=orders" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>

        <!-- Messages -->
        <?php if (isset($_GET['updated'])): ?>
        <div class="od-message">
            <i class="fas fa-check-circle"></i>
            Đã cập nhật trạng thái đơn hàng thành công!
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['cancelled'])): ?>
        <div class="od-message">
            <i class="fas fa-check-circle"></i>
            Đã hủy đơn hàng thành công!
        </div>
        <?php endif; ?>

        <!-- Main Card -->
        <div class="od-card">
            <!-- Info Grid -->
            <div class="info-grid">
                <!-- Customer Info -->
                <div class="info-box">
                    <h3><i class="fas fa-user"></i> Thông tin khách hàng</h3>
                    <div class="info-item">
                        <strong>Người nhận:</strong>
                        <span><?php echo htmlspecialchars($order['full_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>SĐT:</strong>
                        <span><?php echo htmlspecialchars($order['phone']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Địa chỉ:</strong>
                        <span><?php echo nl2br(htmlspecialchars($order['address'])); ?></span>
                    </div>
                    <?php if (!empty($order['note'])): ?>
                    <div class="info-item">
                        <strong>Ghi chú:</strong>
                        <span><?php echo nl2br(htmlspecialchars($order['note'])); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Order Info -->
                <div class="info-box">
                    <h3><i class="fas fa-shopping-cart"></i> Thông tin đơn hàng</h3>
                    <div class="info-item">
                        <strong>Ngày đặt:</strong>
                        <span><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Thanh toán:</strong>
                        <span><?php echo getPaymentMethodText($order['payment_method']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Trạng thái:</strong>
                        <span class="status-badge" style="background: <?php echo getStatusColor($order['status']); ?>">
                            <?php echo getStatusText($order['status']); ?>
                        </span>
                    </div>
                    <?php if (!empty($order['promo_code'])): ?>
                    <div class="info-item">
                        <strong>Mã giảm giá:</strong>
                        <span><?php echo htmlspecialchars($order['promo_code']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Products Table -->
            <h3 style="margin-bottom: 15px; color: #9a7b5a;">
                <i class="fas fa-box"></i> Sản phẩm đã đặt
            </h3>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Biến thể</th>
                            <th style="text-align: center">SL</th>
                            <th style="text-align: right">Đơn giá</th>
                            <th style="text-align: right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <?php if (!empty($item['image'])): ?>
                                    <img src="../images/<?php echo htmlspecialchars($item['image']); ?>" 
                                         alt="" 
                                         class="product-img"
                                         onerror="this.style.display='none'">
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($item['size']) || !empty($item['flavor'])): ?>
                                    <span class="variant-info">
                                        <?php 
                                        $variants = [];
                                        if (!empty($item['size'])) $variants[] = 'Size: ' . $item['size'];
                                        if (!empty($item['flavor'])) $variants[] = 'Vị: ' . $item['flavor'];
                                        echo implode(' - ', $variants);
                                        ?>
                                    </span>
                                <?php else: ?>
                                    <span class="variant-info">Mặc định</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center"><?php echo (int)$item['quantity']; ?></td>
                            <td style="text-align: right"><?php echo number_format($item['unit_price'], 0, ',', '.'); ?> ₫</td>
                            <td style="text-align: right"><?php echo number_format($item['unit_price'] * $item['quantity'], 0, ',', '.'); ?> ₫</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Summary -->
            <div class="summary">
                <?php if ($order['discount_amount'] > 0): ?>
                <div class="summary-row">
                    Tạm tính: <?php echo number_format($subtotal, 0, ',', '.'); ?> ₫
                </div>
                <div class="summary-row" style="color: #27ae60;">
                    Giảm giá: -<?php echo number_format($order['discount_amount'], 0, ',', '.'); ?> ₫
                </div>
                <?php endif; ?>
                <div class="summary-row total">
                    Tổng cộng: <?php echo number_format($order['total_amount'], 0, ',', '.'); ?> ₫
                </div>
            </div>

            <!-- Actions với các button có kích thước bằng nhau -->
            <?php if ($order['status'] === 'cancelled'): ?>
            <div class="cancelled-message">
                <i class="fas fa-exclamation-triangle"></i>
                Đơn hàng này đã bị hủy. Không thể thực hiện thao tác nào khác.
            </div>
            <?php elseif ($order['status'] === 'delivered'): ?>
            <div class="actions-container">
                <div class="delivered-message" style="padding: 15px 20px; background: #e8f5e9; color: #2e7d32; border-radius: 8px; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                    <span>Đơn hàng đã giao. Không thể cập nhật trạng thái hoặc hủy đơn.</span>
                </div>
                <div class="button-group">
                    <button type="button" class="btn btn-warning" onclick="window.print()">
                        <i class="fas fa-print"></i> In đơn hàng
                    </button>
                </div>
            </div>
            <?php else: ?>
            <div class="actions-container">
                <div class="actions-title">
                    <i class="fas fa-tools"></i>
                    Quản lý đơn hàng
                </div>
                
                <div class="button-group">
                    <!-- Form cập nhật trạng thái -->
                    <form method="post" class="status-form">
                        <input type="hidden" name="action" value="update_status">
                        <label for="status"><i class="fas fa-sync-alt"></i> Cập nhật trạng thái:</label>
                        <select name="status" id="status">
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                            <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                            <option value="shipping" <?php echo $order['status'] == 'shipping' ? 'selected' : ''; ?>>Đang giao</option>
                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Đã giao</option>
                        </select>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Cập nhật
                        </button>
                    </form>

                    <!-- Button Xác nhận đã giao hàng (chỉ hiện khi đang giao) -->
                    <?php if ($order['status'] == 'shipping'): ?>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="status" value="delivered">
                        <button type="submit" class="btn btn-success" onclick="return confirm('Xác nhận đã giao hàng thành công?')">
                            <i class="fas fa-check-circle"></i> Đã giao hàng
                        </button>
                    </form>
                    <?php endif; ?>

                    <!-- Button Hủy đơn hàng -->
                    <form method="post" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này? Hành động này không thể hoàn tác.');" style="display: inline;">
                        <input type="hidden" name="action" value="cancel_order">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times-circle"></i> Hủy đơn hàng
                        </button>
                    </form>

                    <!-- Button In đơn hàng -->
                    <button type="button" class="btn btn-warning" onclick="window.print()">
                        <i class="fas fa-print"></i> In đơn hàng
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>