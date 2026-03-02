<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login_admin.php");
    exit();
}

require_once 'connect.php';

// Cập nhật trạng thái giao hàng
if (isset($_POST['update_shipping'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php?page=shipping");
    exit();
}

// Chỉ lấy các đơn đã xác nhận hoặc đang giao
$orders = $conn->query("SELECT * FROM orders WHERE status IN ('confirmed','shipping') ORDER BY created_at DESC");
?>

<div class="admin-content">
<h1 class="admin-page-title"><i class="fas fa-truck"></i> Quản lý giao hàng</h1>
<div class="admin-card">
<table class="admin-table">
<thead><tr>
<th>ID</th>
<th>Khách hàng</th>
<th>SĐT</th>
<th>Địa chỉ</th>
<th>Tổng tiền</th>
<th>Trạng thái</th>
<th>Ngày tạo</th>
<th>Hành động</th>
</tr></thead>
<tbody>
<?php while ($row = $orders->fetch_assoc()): ?>
<tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
    <td><?php echo htmlspecialchars($row['phone']); ?></td>
    <td><?php echo htmlspecialchars($row['address']); ?></td>
    <td><?php echo number_format($row['total_amount']); ?> ₫</td>
    <td><?php echo ucfirst($row['status']); ?></td>
    <td><?php echo $row['created_at']; ?></td>
    <td>
        <form method="post" class="status-form">
            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
            <select name="status">
                <option value="confirmed" <?php if($row['status']=='confirmed') echo 'selected'; ?>>Confirmed</option>
                <option value="shipping" <?php if($row['status']=='shipping') echo 'selected'; ?>>Shipping</option>
                <option value="delivered" <?php if($row['status']=='delivered') echo 'selected'; ?>>Delivered</option>
            </select>
            <button type="submit" name="update_shipping" class="admin-btn admin-btn-primary">Cập nhật</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>
