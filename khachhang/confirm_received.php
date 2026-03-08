<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$userId = (int)$_SESSION['user_id'];
$redirect = isset($_POST['redirect']) ? trim($_POST['redirect']) : 'account';

if ($orderId <= 0) {
    header('Location: account.php');
    exit();
}

// Chỉ cập nhật nếu đơn thuộc user và đang ở trạng thái shipping
$stmt = $conn->prepare("UPDATE orders SET status = 'delivered' WHERE id = ? AND user_id = ? AND status = 'shipping'");
$stmt->bind_param('ii', $orderId, $userId);
$stmt->execute();
$stmt->close();

if ($redirect === 'detail') {
    header('Location: order_detail.php?id=' . $orderId . '&received=1');
} else {
    header('Location: account.php?received=1');
}
exit();
