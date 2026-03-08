<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login_admin.php");
    exit();
}

require_once 'connect.php';

function orderStatusLabel($status) {
    $map = [
        'pending'   => 'Chờ xử lý',
        'confirmed' => 'Đã xác nhận',
        'shipping'  => 'Đang giao',
        'delivered' => 'Đã giao',
        'cancelled' => 'Đã hủy',
    ];
    return $map[$status] ?? $status;
}

function orderStatusClass($status) {
    $map = [
        'pending'   => 'status-pending',
        'confirmed' => 'status-confirmed',
        'shipping'  => 'status-shipping',
        'delivered' => 'status-delivered',
        'cancelled' => 'status-cancelled',
    ];
    return $map[$status] ?? '';
}

function paymentMethodLabel($method) {
    $map = [
        'cod'     => 'COD',
        'banking' => 'Chuyển khoản',
        'momo'    => 'MoMo',
    ];
    return $map[$method] ?? strtoupper($method);
}

// Cập nhật trạng thái giao hàng
if (isset($_POST['update_status'])) {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $allowed = ['confirmed', 'shipping', 'delivered'];
    if ($orderId && in_array($status, $allowed, true)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $orderId);
        $stmt->execute();
        $stmt->close();
    }
    $filterStatus = isset($_GET['filter_status']) ? '&filter_status=' . urlencode($_GET['filter_status']) : '';
    header("Location: admin_dashboard.php?page=shipping" . $filterStatus);
    exit();
}

// Bộ lọc: chỉ đơn đã xác nhận hoặc đang giao
$filterStatus = isset($_GET['filter_status']) && in_array($_GET['filter_status'], ['confirmed', 'shipping'], true)
    ? $_GET['filter_status'] : '';

if ($filterStatus !== '') {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE status = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $filterStatus);
    $stmt->execute();
    $orders = $stmt->get_result();
    $stmt->close();
} else {
    $orders = $conn->query("SELECT * FROM orders WHERE status IN ('confirmed', 'shipping') ORDER BY created_at DESC");
}
?>

<style>
    /* Variables */
    :root {
        --primary-color: #9a7b5a;
        --primary-light: #A0522D;
        --success-color: #27ae60;
        --warning-color: #f39c12;
        --info-color: #3498db;
        --shipping-color: #9b59b6;
        --danger-color: #e74c3c;
        --dark-color: #9a7b5a;
        --border-color: #e0e0e0;
        --bg-light: #f8f9fa;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-align: center;
        min-width: 100px;
    }

    .status-pending {
        background: #fff3e0;
        color: #e65100;
        border-left: 3px solid var(--warning-color);
    }

    .status-confirmed {
        background: #e3f2fd;
        color: #0d47a1;
        border-left: 3px solid var(--info-color);
    }

    .status-shipping {
        background: #f3e5f5;
        color: #4a0072;
        border-left: 3px solid var(--shipping-color);
    }

    .status-delivered {
        background: #e8f5e9;
        color: #1b5e20;
        border-left: 3px solid var(--success-color);
    }

    .status-cancelled {
        background: #ffebee;
        color: #b71c1c;
        border-left: 3px solid var(--danger-color);
    }

    /* Filter Section */
    .filter-section {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 20px;
        padding: 15px;
        background: var(--bg-light);
        border-radius: 10px;
        border: 1px solid var(--border-color);
    }

    .filter-label {
        font-weight: 600;
        color: var(--dark-color);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-btn {
        padding: 8px 20px;
        border-radius: 25px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        background: white;
        color: var(--dark-color);
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .filter-btn:hover {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .filter-btn.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    /* Table Styles */
    .table-responsive {
        overflow-x: auto;
        border-radius: 10px;
        border: 1px solid var(--border-color);
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        min-width: 1200px;
    }

    .admin-table th {
        background: var(--primary-color);
        color: white;
        font-weight: 600;
        padding: 15px 12px;
        font-size: 14px;
        text-align: left;
        white-space: nowrap;
    }

    .admin-table td {
        padding: 15px 12px;
        border-bottom: 1px solid var(--border-color);
        font-size: 14px;
        vertical-align: middle;
    }

    .admin-table tbody tr:hover td {
        background: #f9f6f2;
    }

    /* Order Info */
    .order-link {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .order-link:hover {
        text-decoration: underline;
    }

    .order-address {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #666;
    }

    .payment-method {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 8px;
        background: #f5f5f5;
        border-radius: 4px;
        font-size: 12px;
    }

    .amount {
        font-weight: 600;
        color: var(--primary-color);
    }

    /* Action Buttons - Tất cả button có kích thước bằng nhau */
    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 100px;
        height: 38px;
        white-space: nowrap;
    }

    .btn i {
        font-size: 14px;
    }

    .btn-sm {
        min-width: 100px;
        height: 38px;
        padding: 8px 12px;
    }

    .btn-primary {
        background: var(--primary-color);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-light);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(139, 69, 19, 0.2);
    }

    .btn-secondary {
        background: #95a5a6;
        color: white;
    }

    .btn-secondary:hover {
        background: #7f8c8d;
        transform: translateY(-2px);
    }

    .btn-success {
        background: var(--success-color);
        color: white;
    }

    .btn-success:hover {
        background: #219a52;
        transform: translateY(-2px);
    }

    .btn-info {
        background: var(--info-color);
        color: white;
    }

    .btn-info:hover {
        background: #2980b9;
        transform: translateY(-2px);
    }

    /* Status Form */
    .status-form {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .status-select {
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid var(--border-color);
        font-size: 13px;
        min-width: 140px;
        height: 38px;
        background: white;
        cursor: pointer;
    }

    .status-select:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #7f8c8d;
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 15px;
        color: var(--border-color);
    }

    .empty-state p {
        margin: 0;
        font-size: 16px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .filter-section {
            flex-direction: column;
            align-items: flex-start;
        }

        .filter-buttons {
            width: 100%;
        }

        .filter-btn {
            flex: 1;
            text-align: center;
            justify-content: center;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }

        .status-form {
            width: 100%;
        }

        .status-select {
            width: 100%;
        }
    }
</style>

<div class="admin-content">
    <div class="admin-page-header">
        <h1 class="admin-page-title">
            <i class="fas fa-truck"></i> 
            Quản lý giao hàng
        </h1>
        <a href="admin_dashboard.php?page=orders" class="btn btn-secondary">
            <i class="fas fa-shopping-cart"></i> 
            Xem tất cả đơn hàng
        </a>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="filter-label">
            <i class="fas fa-filter"></i>
            Lọc theo trạng thái:
        </div>
        <div class="filter-buttons">
            <a href="admin_dashboard.php?page=shipping" class="filter-btn <?php echo $filterStatus === '' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> Tất cả
            </a>
            <a href="admin_dashboard.php?page=shipping&filter_status=confirmed" class="filter-btn <?php echo $filterStatus === 'confirmed' ? 'active' : ''; ?>">
                <i class="fas fa-check-circle"></i> Đã xác nhận
            </a>
            <a href="admin_dashboard.php?page=shipping&filter_status=shipping" class="filter-btn <?php echo $filterStatus === 'shipping' ? 'active' : ''; ?>">
                <i class="fas fa-truck"></i> Đang giao
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Mã ĐH</th>
                    <th>Khách hàng</th>
                    <th>SĐT</th>
                    <th>Địa chỉ</th>
                    <th>Thanh toán</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders && $orders->num_rows > 0): ?>
                    <?php while ($row = $orders->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <a href="admin_dashboard.php?page=order_detail&id=<?php echo (int)$row['id']; ?>" class="order-link">
                                <i class="fas fa-hashtag"></i>
                                <?php echo (int)$row['id']; ?>
                            </a>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                        </td>
                        <td>
                            <span class="payment-method">
                                <i class="fas fa-phone"></i>
                                <?php echo htmlspecialchars($row['phone']); ?>
                            </span>
                        </td>
                        <td class="order-address" title="<?php echo htmlspecialchars($row['address']); ?>">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($row['address']); ?>
                        </td>
                        <td>
                            <span class="payment-method">
                                <i class="fas <?php 
                                    echo $row['payment_method'] == 'cod' ? 'fa-money-bill' : 
                                        ($row['payment_method'] == 'banking' ? 'fa-university' : 'fa-mobile-alt'); 
                                ?>"></i>
                                <?php echo paymentMethodLabel($row['payment_method']); ?>
                            </span>
                        </td>
                        <td class="amount">
                            <?php echo number_format((float)$row['total_amount'], 0, ',', '.'); ?> ₫
                        </td>
                        <td>
                            <span class="status-badge <?php echo orderStatusClass($row['status']); ?>">
                                <i class="fas <?php 
                                    echo $row['status'] == 'shipping' ? 'fa-truck' : 
                                        ($row['status'] == 'delivered' ? 'fa-check-circle' : 'fa-clock'); 
                                ?>"></i>
                                <?php echo orderStatusLabel($row['status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="payment-method">
                                <i class="far fa-calendar-alt"></i>
                                <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <!-- Button Xem chi tiết -->
                                <a href="admin_dashboard.php?page=order_detail&id=<?php echo (int)$row['id']; ?>" 
                                   class="btn btn-info btn-sm" 
                                   title="Xem chi tiết đơn hàng">
                                    <i class="fas fa-eye"></i>
                                    <span>Chi tiết</span>
                                </a>

                                <!-- Form cập nhật trạng thái -->
                                <form method="post" class="status-form" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo (int)$row['id']; ?>">
                                    <select name="status" class="status-select" title="Chọn trạng thái">
                                        <option value="confirmed" <?php if ($row['status'] === 'confirmed') echo 'selected'; ?>>
                                            <i class="fas fa-check-circle"></i> Đã xác nhận
                                        </option>
                                        <option value="shipping"  <?php if ($row['status'] === 'shipping')  echo 'selected'; ?>>
                                            <i class="fas fa-truck"></i> Đang giao
                                        </option>
                                        <option value="delivered" <?php if ($row['status'] === 'delivered') echo 'selected'; ?>>
                                            <i class="fas fa-check-double"></i> Đã giao
                                        </option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-primary btn-sm" 
                                            title="Cập nhật trạng thái đơn hàng"
                                            onclick="return confirm('Xác nhận cập nhật trạng thái đơn hàng?')">
                                        <i class="fas fa-sync-alt"></i>
                                        <span>Cập nhật</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="fas <?php echo $filterStatus ? 'fa-filter' : 'fa-truck'; ?>"></i>
                                <p>
                                    <?php 
                                    if ($filterStatus) {
                                        echo 'Không có đơn hàng nào với trạng thái "' . orderStatusLabel($filterStatus) . '"';
                                    } else {
                                        echo 'Chưa có đơn hàng nào đang chờ giao (Đã xác nhận / Đang giao)';
                                    }
                                    ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>