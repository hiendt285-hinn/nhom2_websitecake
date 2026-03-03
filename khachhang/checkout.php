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

// Lấy thông tin người dùng từ database (điền sẵn form; nếu lỗi thì dùng mảng rỗng tránh trang trắng)
$userProfile = ['full_name' => '', 'phone' => '', 'address' => ''];
$stmtUser = $conn->prepare("SELECT full_name, phone, address FROM users WHERE id = ?");
if ($stmtUser) {
    $stmtUser->bind_param('i', $userId);
    $stmtUser->execute();
    $res = $stmtUser->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $userProfile['full_name'] = (string)($row['full_name'] ?? '');
        $userProfile['phone'] = (string)($row['phone'] ?? '');
        $userProfile['address'] = (string)($row['address'] ?? '');
    }
    $stmtUser->close();
}

// Đảm bảo bảng promotions và cột đơn hàng tồn tại
$conn->query("CREATE TABLE IF NOT EXISTS promotions (
  id int(11) NOT NULL AUTO_INCREMENT,
  code varchar(50) NOT NULL,
  title varchar(255) DEFAULT NULL,
  discount_type enum('percent','fixed') NOT NULL DEFAULT 'percent',
  discount_value decimal(10,2) NOT NULL DEFAULT 0,
  min_order_amount decimal(10,2) DEFAULT 0,
  valid_from datetime DEFAULT NULL,
  valid_to datetime DEFAULT NULL,
  is_active tinyint(1) DEFAULT 1,
  created_at datetime DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$chk = $conn->query("SHOW COLUMNS FROM orders LIKE 'promo_code'");
if ($chk && $chk->num_rows === 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN promo_code varchar(50) DEFAULT NULL, ADD COLUMN discount_amount decimal(10,2) DEFAULT 0");
}

// Danh sách mã khuyến mãi đang áp dụng (để hiển thị dropdown)
$promoList = [];
$promoRes = $conn->query("SELECT code, title, discount_type, discount_value, min_order_amount FROM promotions WHERE is_active = 1 AND (valid_from IS NULL OR valid_from <= NOW()) AND (valid_to IS NULL OR valid_to >= NOW()) ORDER BY code");
if ($promoRes && $promoRes->num_rows > 0) {
    while ($row = $promoRes->fetch_assoc()) {
        $promoList[] = $row;
    }
}

// Tính tổng tiền
$totalAmount = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalAmount += $item['price'] * $item['quantity'];
}
$discountAmount = 0;
$appliedPromo = null;
$promoError = '';
$finalAmount = $totalAmount;

// Xử lý đặt hàng (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $note = isset($_POST['note']) ? trim($_POST['note']) : null;
    $paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'cod';
    $promoCodeInput = isset($_POST['promo_code']) ? strtoupper(trim($_POST['promo_code'])) : '';

    $totalAmount = 0;
    foreach ($_SESSION['cart'] as $item) {
        $totalAmount += $item['price'] * $item['quantity'];
    }
    $discountAmount = 0;
    $appliedPromo = null;
    if ($promoCodeInput !== '') {
        $stmtP = $conn->prepare("SELECT id, code, discount_type, discount_value, min_order_amount, valid_from, valid_to FROM promotions WHERE code = ? AND is_active = 1 LIMIT 1");
        $stmtP->bind_param('s', $promoCodeInput);
        $stmtP->execute();
        $promo = $stmtP->get_result()->fetch_assoc();
        $stmtP->close();
        if ($promo) {
            $minOrder = (float)$promo['min_order_amount'];
            if ($totalAmount >= $minOrder) {
                $valid = true;
                if (!empty($promo['valid_from']) && strtotime($promo['valid_from']) > time()) $valid = false;
                if (!empty($promo['valid_to']) && strtotime($promo['valid_to']) < time()) $valid = false;
                if ($valid) {
                    if ($promo['discount_type'] === 'percent') {
                        $discountAmount = round($totalAmount * (float)$promo['discount_value'] / 100, 0);
                    } else {
                        $discountAmount = min((float)$promo['discount_value'], $totalAmount);
                    }
                    $appliedPromo = $promo;
                }
            }
        }
        if ($promoCodeInput !== '' && !$appliedPromo) {
            $promoError = 'Mã không hợp lệ, đã hết hạn hoặc chưa đủ điều kiện đơn hàng.';
        }
    }
    $finalAmount = max(0, $totalAmount - $discountAmount);

    $error = '';
    if ($fullName === '' || $phone === '' || $address === '') {
        $error = 'Vui lòng nhập đầy đủ Họ tên, SĐT và Địa chỉ.';
    } else {
        if ($error === '') {
            $conn->begin_transaction();
            try {
                $orderSql = "INSERT INTO orders (user_id, full_name, phone, address, note, total_amount, status, payment_method, promo_code, discount_amount, created_at)
                             VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, NOW())";
                $stmtOrder = $conn->prepare($orderSql);
                if (!$stmtOrder) {
                    throw new Exception('Lỗi hệ thống (orders): ' . $conn->error);
                }
                $promoCodeSave = $appliedPromo ? $appliedPromo['code'] : '';
                $stmtOrder->bind_param('issssdsds', $userId, $fullName, $phone, $address, $note, $finalAmount, $paymentMethod, $promoCodeSave, $discountAmount);
                if (!$stmtOrder->execute()) {
                    throw new Exception('Không thể lưu đơn hàng: ' . $stmtOrder->error);
                }
                $orderId = $conn->insert_id;
                $stmtOrder->close();

                // Lưu từng item và trừ tồn kho
                $itemSql = "INSERT INTO order_items (order_id, product_id, size, flavor, quantity, unit_price)
                            VALUES (?, ?, ?, ?, ?, ?)";
                $stmtItem = $conn->prepare($itemSql);
                if (!$stmtItem) {
                    throw new Exception('Lỗi hệ thống (order_items): ' . $conn->error);
                }
                foreach ($_SESSION['cart'] as $item) {
                    $productId = (int)$item['id'];
                    $size = isset($item['size']) ? (string)$item['size'] : '';
                    $flavor = isset($item['flavor']) ? (string)$item['flavor'] : '';
                    $quantity = (int)$item['quantity'];
                    $unitPrice = (float)$item['price'];
                    $stmtItem->bind_param('iissid', $orderId, $productId, $size, $flavor, $quantity, $unitPrice);
                    if (!$stmtItem->execute()) {
                        throw new Exception('Không thể lưu chi tiết đơn hàng: ' . $stmtItem->error);
                    }
                }
                $stmtItem->close();

                $conn->commit();
                unset($_SESSION['cart']);
                header('Location: order_detail.php?id=' . $orderId . '&success=1');
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $error = $e->getMessage();
            }
        }
    }
}

// Giá trị hiển thị trong form: ưu tiên POST (khi lỗi), không thì dùng thông tin tài khoản
$formFullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : ($userProfile['full_name'] ?? '');
$formPhone = isset($_POST['phone']) ? trim($_POST['phone']) : ($userProfile['phone'] ?? '');
$formAddress = isset($_POST['address']) ? trim($_POST['address']) : ($userProfile['address'] ?? '');
$formNote = isset($_POST['note']) ? trim($_POST['note']) : '';
$formPaymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'cod';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Sweet Cake</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .checkout-page { max-width: 1100px; margin: 30px auto; padding: 0 20px; font-family: 'Open Sans', sans-serif; }
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
        .btn-submit {
            background: #5D4037;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
        }
        .btn-submit:hover {
            background: white;
            color: #5D4037;
            border: 1px solid #5D4037;
        }
        .error { color: #d32f2f; margin-bottom: 12px; font-weight: 600; }
        .info-note {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        @media (max-width: 900px) { 
            .checkout-grid { grid-template-columns: 1fr; } 
            .form-row { grid-template-columns: 1fr; } 
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="checkout-page">
    <h1 style="text-align:center; margin-bottom:20px; color:#5D4037;">Thanh toán</h1>

    <?php if (isset($error) && $error !== ''): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="info-note">
        <i class="fas fa-info-circle"></i> 
        Thông tin nhận hàng được tự động lấy từ tài khoản của bạn. Bạn có thể chỉnh sửa nếu cần.
    </div>

    <form method="POST" class="checkout-grid">
        <div class="card">
            <h2>Thông tin nhận hàng</h2>
            
            <div class="form-group">
                <label>Họ và tên</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($formFullName); ?>" required placeholder="Nhập họ tên người nhận">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($formPhone); ?>" required placeholder="Nhập số điện thoại">
                </div>
                
                <div class="form-group">
                    <label>Phương thức thanh toán</label>
                    <select name="payment_method">
                        <option value="cod"<?php echo $formPaymentMethod === 'cod' ? ' selected' : ''; ?>>Thanh toán khi nhận hàng (COD)</option>
                        <option value="banking"<?php echo $formPaymentMethod === 'banking' ? ' selected' : ''; ?>>Chuyển khoản ngân hàng</option>
                        <option value="momo"<?php echo $formPaymentMethod === 'momo' ? ' selected' : ''; ?>>Ví MoMo</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Địa chỉ nhận hàng</label>
                <textarea name="address" required placeholder="Nhập địa chỉ cụ thể (số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố)"><?php echo htmlspecialchars($formAddress); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Ghi chú (tuỳ chọn)</label>
                <textarea name="note" placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi giao, để lại trước cửa..."><?php echo htmlspecialchars($formNote); ?></textarea>
            </div>
        </div>
        
        <div class="card order-summary">
            <h2>Đơn hàng của bạn</h2>
            
            <?php foreach ($_SESSION['cart'] as $item): ?>
                <div class="row">
                    <div>
                        <?php echo htmlspecialchars($item['name']); ?> 
                        <?php if (isset($item['size']) && $item['size']): ?>
                            <span style="font-size:12px; color:#666;">(Size: <?php echo htmlspecialchars($item['size']); ?>)</span>
                        <?php endif; ?>
                        <?php if (isset($item['flavor']) && $item['flavor']): ?>
                            <span style="font-size:12px; color:#666;">(Vị: <?php echo htmlspecialchars($item['flavor']); ?>)</span>
                        <?php endif; ?>
                        <span style="font-weight:600;"> x <?php echo (int)$item['quantity']; ?></span>
                    </div>
                    <div><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫</div>
                </div>
            <?php endforeach; ?>
            
            <hr style="margin:12px 0; border:none; border-top:1px solid #eee;">
            
            <div class="form-group" style="margin-bottom:12px;">
                <label>Mã khuyến mãi</label>
                <select name="promo_code" id="promo_code" style="text-transform:uppercase;">
                    <option value="">— Không dùng mã —</option>
                    <?php foreach ($promoList as $p):
                        $short = $p['code'];
                        $short .= $p['discount_type'] === 'percent' ? ' -' . (int)$p['discount_value'] . '%' : ' -' . number_format((float)$p['discount_value']/1000, 0, '', '') . 'k';
                        if ((float)$p['min_order_amount'] > 0) $short .= ' (từ ' . number_format((float)$p['min_order_amount']/1000, 0, '', '') . 'k)';
                        $selected = (isset($_POST['promo_code']) && $_POST['promo_code'] === $p['code']) ? ' selected' : '';
                    ?>
                    <option value="<?php echo htmlspecialchars($p['code']); ?>"<?php echo $selected; ?>><?php echo htmlspecialchars($short); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <?php if (isset($promoError) && $promoError !== ''): ?>
                    <span style="color:#d32f2f; font-size:12px; display:block; margin-top:5px;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($promoError); ?>
                    </span>
                <?php endif; ?>
                
                <?php if (isset($appliedPromo) && $appliedPromo): ?>
                    <span style="color:#2e7d32; font-size:12px; display:block; margin-top:5px;">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($appliedPromo['code']); ?>: -<?php echo number_format($discountAmount, 0, ',', '.'); ?>₫
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="row">
                <div>Tạm tính</div>
                <div><?php echo number_format($totalAmount, 0, ',', '.'); ?>₫</div>
            </div>
            
            <?php if ($discountAmount > 0): ?>
            <div class="row" style="color:#2e7d32;">
                <div>Giảm (<?php echo htmlspecialchars($appliedPromo['code'] ?? ''); ?>)</div>
                <div>-<?php echo number_format($discountAmount, 0, ',', '.'); ?>₫</div>
            </div>
            <?php endif; ?>
            
            <hr style="margin:12px 0; border:none; border-top:1px solid #eee;">
            
            <div class="row total" style="font-size:18px;">
                <div>Tổng cộng</div>
                <div><?php echo number_format($finalAmount, 0, ',', '.'); ?>₫</div>
            </div>
            
            <div style="margin-top:20px;">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i> Đặt hàng
                </button>
            </div>
            
            <p style="text-align:center; margin-top:10px; font-size:12px; color:#666;">
                <i class="fas fa-lock"></i> Thông tin của bạn được bảo mật
            </p>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
</body>
</html>