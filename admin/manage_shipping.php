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
        'pending'   => 'order-badge-pending',
        'confirmed' => 'order-badge-confirmed',
        'shipping'  => 'order-badge-shipping',
        'delivered' => 'order-badge-delivered',
        'cancelled' => 'order-badge-cancelled',
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
/* Status Badges */
.order-badge { 
    display: inline-block; 
    padding: 4px 10px; 
    border-radius: 20px; 
    font-size: 11px; 
    font-weight: 600; 
    white-space: nowrap;
}
.order-badge-pending   { background: #f39c12; color: #fff; }
.order-badge-confirmed { background: #3498db; color: #fff; }
.order-badge-shipping  { background: #9b59b6; color: #fff; }
.order-badge-delivered { background: #27ae60; color: #fff; }
.order-badge-cancelled { background: #e74c3c; color: #fff; }

/* Filter Section */
.order-filter { 
    display: flex; 
    align-items: center; 
    gap: 10px; 
    flex-wrap: wrap; 
    margin-bottom: 16px; 
}
.order-filter a { 
    padding: 6px 14px; 
    border-radius: 8px; 
    text-decoration: none; 
    font-size: 13px; 
    font-weight: 500; 
    background: #f0f0f0; 
    color: #2c3e50; 
    transition: all 0.3s ease;
}
.order-filter a:hover { background: #e0e0e0; }
.order-filter a.active { background: #9a7b5a; color: #fff; }

.order-address { 
    max-width: 150px; 
    overflow: hidden; 
    text-overflow: ellipsis; 
    white-space: nowrap; 
}

/* Action Container */
.order-actions { 
    display: flex; 
    align-items: center; 
    gap: 6px; 
    flex-wrap: nowrap; 
    white-space: nowrap;
}

/* Button Styles - Đồng bộ với manage_orders */
.admin-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 70px;
    height: 32px;
    white-space: nowrap;
}

.admin-btn i {
    font-size: 12px;
}

.admin-btn-sm {
    min-width: 60px;
    height: 30px;
    padding: 4px 8px;
    font-size: 11px;
}

.admin-btn-primary {
    background: #9a7b5a;
    color: white;
}

.admin-btn-primary:hover {
    background: #A0522D;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(139,69,19,0.2);
}

.admin-btn-secondary {
    background: #95a5a6;
    color: white;
}

.admin-btn-secondary:hover {
    background: #7f8c8d;
    transform: translateY(-2px);
}

/* Select Box - Cân đối với button */
.order-status-select {
    padding: 5px 8px;
    border-radius: 6px;
    border: 2px solid #e0e0e0;
    font-size: 12px;
    min-width: 110px;
    width: 110px;
    height: 32px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.order-status-select:focus {
    outline: none;
    border-color: #9a7b5a;
    box-shadow: 0 0 0 3px rgba(139,69,19,0.1);
}

/* Form Actions */
.order-actions form {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin: 0;
}

/* Page Header */
.admin-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.admin-page-title {
    font-size: 24px;
    color: #9a7b5a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Table */
.admin-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
}

.admin-table th {
    background: #9a7b5a;
    color: white;
    font-weight: 600;
    padding: 12px 8px;
    font-size: 13px;
    text-align: left;
    white-space: nowrap;
}

.admin-table td {
    padding: 12px 8px;
    border-bottom: 1px solid #e0e0e0;
    font-size: 13px;
    vertical-align: middle;
}

.admin-table tbody tr:hover td {
    background: #f9f6f2;
}

.admin-link {
    color: #8B4513;
    text-decoration: none;
    font-weight: 600;
}

.admin-link:hover {
    text-decoration: underline;
}

/* Card */
.admin-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 20px;
    overflow-x: auto;
}

/* Payment method icon + text */
.payment-method {
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}

.payment-method i {
    color: #8B4513;
    font-size: 12px;
    width: 16px;
}

/* Amount */
.amount {
    font-weight: 600;
    color: #8B4513;
    white-space: nowrap;
}

/* Date */
.order-date {
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
    font-size: 12px;
}

.order-date i {
    color: #8B4513;
    font-size: 12px;
}

/* Responsive */
@media (max-width: 1200px) {
    .admin-table {
        display: block;
        overflow-x: auto;
    }
    
    .order-address { 
        max-width: 120px; 
    }
    
    .order-status-select {
        min-width: 100px;
        width: 100px;
    }
    
    .admin-btn {
        min-width: 60px;
        padding: 6px 8px;
    }
}

@media (max-width: 992px) {
    .order-actions {
        flex-direction: column;
        align-items: stretch;
        gap: 4px;
    }
    
    .order-actions form {
        flex-direction: column;
        width: 100%;
    }
    
    .admin-btn-sm {
        width: 100%;
        min-width: 100%;
    }
    
    .order-status-select {
        width: 100%;
        min-width: 100%;
    }
}

@media (max-width: 768px) { 
    .order-address { max-width: 100px; } 
    
    .admin-page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .order-filter {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .order-filter a {
        width: 100%;
        text-align: center;
    }
}
</style>

<div class="admin-content">
    <div class="admin-page-header">
        <h1 class="admin-page-title">
            <i class="fas fa-truck"></i> 
            Quản lý giao hàng
        </h1>
        <a href="admin_dashboard.php?page=orders" class="admin-btn admin-btn-secondary">
            <i class="fas fa-shopping-cart"></i> Xem tất cả đơn hàng
        </a>
    </div>

    <div class="admin-card">
        <div class="order-filter">
            <span style="font-weight: 600; color: #2c3e50;">Lọc theo trạng thái:</span>
            <a href="admin_dashboard.php?page=shipping" class="<?php echo $filterStatus === '' ? 'active' : ''; ?>">Tất cả</a>
            <a href="admin_dashboard.php?page=shipping&filter_status=confirmed" class="<?php echo $filterStatus === 'confirmed' ? 'active' : ''; ?>">Đã xác nhận</a>
            <a href="admin_dashboard.php?page=shipping&filter_status=shipping" class="<?php echo $filterStatus === 'shipping' ? 'active' : ''; ?>">Đang giao</a>
        </div>

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
                    <th style="min-width: 250px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders && $orders->num_rows > 0): while ($row = $orders->fetch_assoc()): ?>
                <tr>
                    <td><a href="admin_dashboard.php?page=order_detail&id=<?php echo (int)$row['id']; ?>" class="admin-link">#<?php echo (int)$row['id']; ?></a></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td class="order-address" title="<?php echo htmlspecialchars($row['address']); ?>"><?php echo htmlspecialchars($row['address']); ?></td>
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
                        <span class="order-badge <?php echo orderStatusClass($row['status']); ?>">
                            <?php echo orderStatusLabel($row['status']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="order-date">
                            <i class="far fa-calendar-alt"></i>
                            <?php echo date('d/m/Y', strtotime($row['created_at'])); ?>
                            <i class="far fa-clock" style="margin-left: 2px;"></i>
                            <?php echo date('H:i', strtotime($row['created_at'])); ?>
                        </span>
                    </td>
                    <td>
                        <div class="order-actions">
                            <a href="admin_dashboard.php?page=order_detail&id=<?php echo (int)$row['id']; ?>" 
                               class="admin-btn admin-btn-secondary"
                               title="Xem chi tiết đơn hàng">
                                <i class="fas fa-eye"></i> Chi tiết
                            </a>
                            <form method="post" class="order-actions">
                                <input type="hidden" name="order_id" value="<?php echo (int)$row['id']; ?>">
                                <select name="status" class="order-status-select" title="Chọn trạng thái">
                                    <option value="confirmed" <?php if ($row['status'] === 'confirmed') echo 'selected'; ?>><?php echo orderStatusLabel('confirmed'); ?></option>
                                    <option value="shipping"  <?php if ($row['status'] === 'shipping')  echo 'selected'; ?>><?php echo orderStatusLabel('shipping'); ?></option>
                                    <option value="delivered" <?php if ($row['status'] === 'delivered') echo 'selected'; ?>><?php echo orderStatusLabel('delivered'); ?></option>
                                </select>
                                <button type="submit" name="update_status" class="admin-btn admin-btn-primary"
                                        title="Cập nhật trạng thái đơn hàng"
                                        onclick="return confirm('Xác nhận cập nhật trạng thái đơn hàng?')">
                                    <i class="fas fa-sync-alt"></i> Cập nhật
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 40px; color: #7f8c8d;">
                        <i class="fas fa-truck" style="font-size: 48px; margin-bottom: 15px; display: block; opacity: 0.3;"></i>
                        <p style="margin: 0; font-size: 16px;">
                            <?php echo $filterStatus ? 'Không có đơn hàng nào với trạng thái "' . orderStatusLabel($filterStatus) . '"' : 'Chưa có đơn hàng nào đang chờ giao (Đã xác nhận / Đang giao)'; ?>
                        </p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>