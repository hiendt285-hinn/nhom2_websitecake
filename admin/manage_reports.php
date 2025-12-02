<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login_admin.php");
    exit();
}
include 'admin_header.php';
require_once 'connect.php';

// Xử lý lọc theo ngày
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$where = "1=1";
if ($start_date && $end_date) {
    $where .= " AND created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
}

// Tổng doanh thu
$result_total = $conn->query("SELECT SUM(total_amount) as total_revenue FROM orders WHERE $where");
$total_revenue = $result_total->fetch_assoc()['total_revenue'];

// Số lượng đơn hàng theo trạng thái
$status_count = $conn->query("SELECT status, COUNT(*) as count FROM orders WHERE $where GROUP BY status");

// Chi tiết đơn hàng
$orders = $conn->query("SELECT * FROM orders WHERE $where ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Báo cáo doanh thu - Savor Cake</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body {font-family:'Open Sans',sans-serif; background:#F5F1E8; margin:0; padding:20px;}
table {width:100%; border-collapse: collapse; margin-top:10px;background:#ffffff; border-radius:8px; overflow:hidden;}
th, td {border-bottom:1px solid #eee; padding:10px 12px; text-align:left; font-size:14px;}
th {background:#f9f6f2; color:#333; font-weight:600;}
.message {padding:10px; margin-bottom:10px; border-radius:5px;}
.filter-form input[type=date] {padding:6px 8px; margin-right:10px; border-radius:4px; border:1px solid #ccc;}
.filter-form input[type=submit] {padding:6px 12px; background:#8B6F47; color:white; border:none; border-radius:999px; cursor:pointer; font-size:13px; font-weight:600;}
</style>
</head>
<body>
<div style="padding:0 0 20px 0;">
    <h2 style="color:#8B6F47;">Báo cáo doanh thu</h2>

    <form method="get" class="filter-form">
        <label>Ngày bắt đầu:</label>
        <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
        <label>Ngày kết thúc:</label>
        <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
        <input type="submit" value="Lọc">
    </form>

    <h3>Tổng doanh thu: <?php echo number_format($total_revenue ?? 0, 0, ',', '.'); ?> VND</h3>

    <h3>Số lượng đơn hàng theo trạng thái:</h3>
    <table>
        <tr><th>Trạng thái</th><th>Số lượng</th></tr>
        <?php while($row = $status_count->fetch_assoc()): ?>
            <tr>
                <td><?php echo ucfirst($row['status']); ?></td>
                <td><?php echo $row['count']; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h3>Chi tiết đơn hàng:</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Khách hàng</th>
            <th>Số điện thoại</th>
            <th>Địa chỉ</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th>Ngày đặt</th>
        </tr>
        <?php if($orders && $orders->num_rows>0): ?>
            <?php while($row=$orders->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                    <td><?php echo number_format($row['total_amount'],0,',','.'); ?></td>
                    <td><?php echo ucfirst($row['status']); ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">Không có đơn hàng nào</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
