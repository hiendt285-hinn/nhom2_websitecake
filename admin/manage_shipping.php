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
    header("Location: manage_shipping.php");
    exit();
}

// Chỉ lấy các đơn đã xác nhận hoặc đang giao
$orders = $conn->query("SELECT * FROM orders WHERE status IN ('confirmed','shipping') ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Quản lý giao hàng</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { 
        font-family: 'Open Sans', sans-serif; 
        background: #F5F1E8; 
        margin:0;
        padding:20px;
    }
h2 { color:#8B6F47; margin-bottom:16px; }
table { width:100%; border-collapse: collapse;background:#ffffff; border-radius:8px; overflow:hidden; }
td, th { padding: 10px 12px; border-bottom: 1px solid #eee; font-size:14px; }
th { background: #f9f6f2; color: #333; font-weight:600; }
.status-form select { padding:6px 8px; border-radius:4px; border:1px solid #ccc; font-size:13px; }
.status-form input[type=submit] { padding:6px 12px; background:#8B6F47; color:white; border:none; border-radius:999px; cursor:pointer; font-size:13px; font-weight:600; }
.status-form input[type=submit]:hover { background:#A0826D; }
</style>
</head>
<body>

<h2>Quản lý giao hàng</h2>

<table>
<tr>
<th>ID</th>
<th>Khách hàng</th>
<th>SĐT</th>
<th>Địa chỉ</th>
<th>Tổng tiền</th>
<th>Trạng thái</th>
<th>Ngày tạo</th>
<th>Hành động</th>
</tr>

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
            <input type="submit" name="update_shipping" value="Cập nhật">
        </form>
    </td>
</tr>
<?php endwhile; ?>

</table>
</body>
</html>
