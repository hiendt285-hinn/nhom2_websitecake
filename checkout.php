<?php
session_start();
require_once 'connect.php';

// Yêu cầu đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Kiểm tra giỏ hàng
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

$userId = (int)$_SESSION['user_id'];

// Tính tổng tiền
$totalAmount = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalAmount += $item['price'] * $item['quantity'];
}

// Xử lý đặt hàng (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $note = isset($_POST['note']) ? trim($_POST['note']) : null;
    $paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'cod';

    if ($fullName === '' || $phone === '' || $address === '') {
        $error = 'Vui lòng nhập đầy đủ Họ tên, SĐT và Địa chỉ.';
    } else {
        $conn->begin_transaction();
        try {
            // Tạo đơn hàng
            $orderSql = "INSERT INTO orders (user_id, session_id, full_name, phone, address, note, total_amount, status, payment_method, created_at)
                          VALUES (?, NULL, ?, ?, ?, ?, ?, 'pending', ?, NOW())";
            $stmtOrder = $conn->prepare($orderSql);
            if (!$stmtOrder) {
                throw new Exception('Không thể chuẩn bị truy vấn đơn hàng.');
            }
            $stmtOrder->bind_param('issssds', $userId, $fullName, $phone, $address, $note, $totalAmount, $paymentMethod);
            if (!$stmtOrder->execute()) {
                throw new Exception('Không thể lưu đơn hàng.');
            }
            $orderId = $conn->insert_id;
            $stmtOrder->close();

            // Lưu từng item
            $itemSql = "INSERT INTO order_items (order_id, product_id, size, flavor, quantity, unit_price) VALUES (?, ?, ?, ?, ?, ?)";
            $stmtItem = $conn->prepare($itemSql);
            if (!$stmtItem) {
                throw new Exception('Không thể chuẩn bị truy vấn chi tiết đơn hàng.');
            }
    foreach ($_SESSION['cart'] as $item) {
                $productId = (int)$item['id'];
                $size = isset($item['size']) ? (string)$item['size'] : null;
                $flavor = isset($item['flavor']) ? (string)$item['flavor'] : null;
                $quantity = (int)$item['quantity'];
                $unitPrice = (float)$item['price'];
                $stmtItem->bind_param('iissid', $orderId, $productId, $size, $flavor, $quantity, $unitPrice);
                if (!$stmtItem->execute()) {
                    throw new Exception('Không thể lưu chi tiết đơn hàng.');
                }
            }
            $stmtItem->close();

            $conn->commit();

            // Xóa giỏ hàng
    unset($_SESSION['cart']);

            // Chuyển đến trang chi tiết đơn hàng
            header('Location: order_detail.php?id=' . $orderId);
    exit();
} catch (Exception $e) {
            $conn->rollback();
            $error = 'Lỗi thanh toán: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Savor Cake</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .checkout-page { max-width: 1100px; margin: 30px auto; padding: 0 20px; font-family: 'Poppins', sans-serif; }
        .checkout-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .card { background: #fffaf0; border: 1px solid #e0e0e0; border-radius: 12px; padding: 20px; }
        .card h2 { margin-bottom: 15px; color: #5D4037; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .form-group { margin-bottom: 12px; }
        label { display: block; font-weight: 600; margin-bottom: 6px; color: #333; }
        input, textarea, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        textarea { min-height: 80px; }
        .order-summary { font-size: 14px; }
        .order-summary .row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .total { font-weight: 700; color: #2e7d32; }
        .btn-submit { background: #FFCA28; color: #5D4037; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .btn-submit:hover { background: #FFB300; }
        .error { color: #d32f2f; margin-bottom: 12px; font-weight: 600; }
        @media (max-width: 900px) { .checkout-grid { grid-template-columns: 1fr; } .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="checkout-page">
    <h1 style="text-align:center; margin-bottom:20px; color:#5D4037;">Thanh toán</h1>

    <?php if (isset($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" class="checkout-grid">
        <div class="card">
            <h2>Thông tin nhận hàng</h2>
            <div class="form-group">
                <label>Họ và tên</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" required>
                </div>
                <div class="form-group">
                    <label>Phương thức thanh toán</label>
                    <select name="payment_method">
                        <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                        <option value="banking">Chuyển khoản</option>
                        <option value="momo">Momo</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Địa chỉ</label>
                <textarea name="address" required></textarea>
            </div>
            <div class="form-group">
                <label>Ghi chú (tuỳ chọn)</label>
                <textarea name="note" placeholder="Ví dụ: Giao giờ hành chính..."></textarea>
            </div>
        </div>
        <div class="card order-summary">
            <h2>Đơn hàng</h2>
            <?php foreach ($_SESSION['cart'] as $item): ?>
                <div class="row">
                    <div><?php echo htmlspecialchars($item['name']); ?> x <?php echo (int)$item['quantity']; ?></div>
                    <div><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫</div>
                </div>
            <?php endforeach; ?>
            <hr style="margin:12px 0; border:none; border-top:1px solid #eee;">
            <div class="row total">
                <div>Tổng cộng</div>
                <div><?php echo number_format($totalAmount, 0, ',', '.'); ?>₫</div>
            </div>
            <div style="margin-top:16px; text-align:right;">
                <button type="submit" class="btn-submit">Đặt hàng</button>
            </div>
        </div>
    </form>
</div>
<?php include 'footer.php'; ?>
</body>
</html>