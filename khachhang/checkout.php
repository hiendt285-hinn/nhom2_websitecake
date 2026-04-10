<?php
session_start();
require_once 'connect.php';

$conn->query("CREATE TABLE IF NOT EXISTS promotion_products (
  promotion_id int(11) NOT NULL,
  product_id int(11) NOT NULL,
  PRIMARY KEY (promotion_id, product_id),
  KEY product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

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

// Lấy thông tin người dùng từ database (chỉ các cột thường có: full_name, phone, address)
$userProfile = ['full_name' => '', 'phone' => '', 'email' => '', 'address' => ''];
$stmtUser = $conn->prepare("SELECT full_name, phone, email, address FROM users WHERE id = ? LIMIT 1");
if ($stmtUser) {
    $stmtUser->bind_param('i', $userId);
    $stmtUser->execute();
    $res = $stmtUser->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $userProfile['full_name'] = (string)($row['full_name'] ?? '');
        $userProfile['phone'] = (string)($row['phone'] ?? '');
        $userProfile['email'] = (string)($row['email'] ?? '');
        $userProfile['address'] = (string)($row['address'] ?? '');
    }
    $stmtUser->close();
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
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $note = isset($_POST['note']) ? trim($_POST['note']) : null;
    $paymentMethod = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'cod';
    if (!in_array($paymentMethod, ['cod', 'banking'], true)) {
        $paymentMethod = 'cod';
    }
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
                if (!empty($promo['valid_from']) && strtotime($promo['valid_from']) > time()) {
                    $valid = false;
                }
                if (!empty($promo['valid_to']) && strtotime($promo['valid_to']) < time()) {
                    $valid = false;
                }
                $restrictedIds = [];
                $stPr = $conn->prepare('SELECT product_id FROM promotion_products WHERE promotion_id = ?');
                if ($stPr) {
                    $pid = (int)$promo['id'];
                    $stPr->bind_param('i', $pid);
                    $stPr->execute();
                    $rp = $stPr->get_result();
                    if ($rp) {
                        while ($row = $rp->fetch_assoc()) {
                            $restrictedIds[] = (int)$row['product_id'];
                        }
                    }
                    $stPr->close();
                } else {
                    // Khong fallback ve "ap dung toan bo gio" khi doc pham vi ma bi loi
                    $valid = false;
                }
                $hasProductScope = count($restrictedIds) > 0;
                $eligibleSubtotal = 0;
                foreach ($_SESSION['cart'] as $item) {
                    $cartPid = (int)$item['id'];
                    if (!$hasProductScope || in_array($cartPid, $restrictedIds, true)) {
                        $eligibleSubtotal += (float)$item['price'] * (int)$item['quantity'];
                    }
                }
                if ($valid && $hasProductScope && $eligibleSubtotal <= 0) {
                    $valid = false;
                }
                if ($valid) {
                    $discountBase = $hasProductScope ? $eligibleSubtotal : $totalAmount;
                    if ($promo['discount_type'] === 'percent') {
                        $discountAmount = round($discountBase * (float)$promo['discount_value'] / 100, 0);
                    } else {
                        $discountAmount = min((float)$promo['discount_value'], $discountBase);
                    }
                    $appliedPromo = $promo;
                }
            }
        }
        if ($promoCodeInput !== '' && !$appliedPromo) {
            $promoError = 'Mã không hợp lệ, đã hết hạn, chưa đủ điều kiện đơn hàng, hoặc giỏ không có sản phẩm áp dụng mã.';
        }
    }
    $finalAmount = max(0, $totalAmount - $discountAmount);

    $error = '';
    if ($promoCodeInput !== '' && !$appliedPromo) {
        $error = $promoError !== '' ? $promoError : 'Mã khuyến mãi không đủ điều kiện áp dụng.';
    } elseif ($fullName === '' || $phone === '' || $address === '') {
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
                $stmtOrder->bind_param('issssdssd', $userId, $fullName, $phone, $address, $note, $finalAmount, $paymentMethod, $promoCodeSave, $discountAmount);
                if (!$stmtOrder->execute()) {
                    throw new Exception('Không thể lưu đơn hàng: ' . $stmtOrder->error);
                }
                $orderId = $conn->insert_id;
                $stmtOrder->close();

                // Lưu từng item 
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

// Thông tin tài khoản ngân hàng
$bankInfo = [
    'bank_name' => 'Ngân hàng Quân đội MB (MB Bank)',
    'bank_branch' => 'Chi nhánh Hoàn Kiếm, Hà Nội',
    'account_name' => 'CÔNG TY CỔ PHẦN SWEET CAKE',
    'account_number' => '1234567890',
    'transfer_content' => 'SWEETCAKE ' . preg_replace('/\D/', '', $formPhone),
    'qr_code' => 'images/nganhng.jpg'
];
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
        :root {
            --main-brown: #8B4513;
            --brown-light: #A0522D;
            --light-beige: #FDF5E6;
            --text-black: #333333;
            --border-color: #e0e0e0;
            --success-green: #4CAF50;
            --warning-orange: #ff9800;
            --info-blue: #1976D2;
            --danger-red: #f44336;
            --gray-light: #f5f5f5;
        }
        
        .checkout-page { 
            max-width: 1200px; 
            margin: 40px auto; 
            padding: 0 20px; 
            font-family: 'Open Sans', sans-serif; 
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 30px;
            color: var(--main-brown);
            font-size: 32px;
            font-weight: 700;
            font-family: 'Playfair Display', serif;
        }
        
        .checkout-grid { 
            display: grid; 
            grid-template-columns: 1.5fr 1fr; 
            gap: 25px; 
        }
        
        .card { 
            background: white; 
            border-radius: 16px; 
            box-shadow: 0 8px 25px rgba(139, 69, 19, 0.08); 
            padding: 25px; 
            border: 1px solid rgba(139, 69, 19, 0.1); 
        }
        
        .card h2 { 
            margin: 0 0 20px 0; 
            color: var(--main-brown); 
            font-size: 20px; 
            display: flex; 
            align-items: center; 
            gap: 10px;
            padding-bottom: 15px;
            border-bottom: 2px dashed var(--border-color);
        }
        
        .card h2 i {
            font-size: 24px;
        }
        
        .form-row { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 15px; 
        }
        
        .form-group { 
            margin-bottom: 15px; 
        }
        
        label { 
            display: block; 
            font-weight: 600; 
            margin-bottom: 8px; 
            color: #555; 
            font-size: 14px; 
        }
        
        input, textarea, select { 
            width: 100%; 
            padding: 12px; 
            border: 2px solid var(--border-color); 
            border-radius: 10px; 
            font-size: 14px; 
            transition: all 0.3s; 
            background: white; 
        }
        
        input:focus, textarea:focus, select:focus { 
            border-color: var(--main-brown); 
            outline: none; 
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1); 
        }
        
        textarea { 
            min-height: 80px; 
            resize: vertical; 
        }
        
        .order-summary { 
            font-size: 14px; 
        }
        
        .order-summary .row { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 10px; 
            padding: 5px 0;
        }
        
        .total { 
            font-weight: 700; 
            color: var(--success-green); 
            font-size: 18px;
        }
        
        hr { 
            margin: 15px 0; 
            border: none; 
            border-top: 1px solid var(--border-color); 
        }
        
        /* Payment Methods */
        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin: 15px 0;
        }
        
        .payment-option {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .payment-option:hover {
            border-color: var(--main-brown);
        }
        
        .payment-option.selected {
            border-color: var(--main-brown);
            background: rgba(139, 69, 19, 0.02);
        }
        
        .payment-option label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            cursor: pointer;
            margin: 0;
            font-weight: 600;
            color: var(--text-black);
        }
        
        .payment-option input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: var(--main-brown);
            margin: 0;
        }
        
        .payment-option i {
            font-size: 24px;
            width: 30px;
            color: var(--main-brown);
        }
        
        /* Banking Details */
        .banking-details {
            background: #faf7f2;
            border-radius: 12px;
            padding: 20px;
            margin: 15px 0;
            border: 1px solid rgba(139, 69, 19, 0.15);
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .banking-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            color: var(--main-brown);
            font-weight: 700;
            font-size: 16px;
        }
        
        .banking-header i {
            font-size: 24px;
        }
        
        .banking-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .bank-info-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            border: 1px solid rgba(139, 69, 19, 0.1);
        }
        
        .bank-info-item .label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .bank-info-item .value {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-black);
            word-break: break-word;
        }
        
        .bank-info-item .value.highlight {
            color: var(--main-brown);
            font-size: 18px;
        }
        
        .bank-info-item .copy-btn {
            margin-top: 8px;
            display: inline-flex;
        }
        
        .qr-placeholder {
            background: var(--gray-light);
            padding: 24px;
            border-radius: 10px;
            text-align: center;
            color: var(--main-brown);
        }
        
        .qr-placeholder i {
            font-size: 64px;
            display: block;
            margin-bottom: 8px;
        }
        
        .qr-note .transfer-content {
            display: inline-block;
            margin-right: 8px;
            font-weight: 600;
            color: var(--main-brown);
        }
        
        .qr-note .copy-btn {
            vertical-align: middle;
        }
        
        .qr-code-section {
            text-align: center;
            margin-top: 15px;
            padding: 15px;
            background: white;
            border-radius: 10px;
            border: 1px dashed var(--main-brown);
        }
        
        .qr-code-section img {
            max-width: 200px;
            max-height: 200px;
            margin-bottom: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .qr-note {
            font-size: 13px;
            color: #666;
            margin-top: 10px;
        }
        
        .qr-note strong {
            color: var(--main-brown);
        }
        
        .copy-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: transparent;
            color: var(--main-brown);
            border: 1px solid var(--main-brown);
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
            margin-left: 10px;
        }
        
        .copy-btn:hover {
            background: var(--main-brown);
            color: white;
        }
        
        .btn-submit {
            background: var(--main-brown);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            font-size: 16px;
            width: 100%;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(139, 69, 19, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-submit:hover {
            background: var(--brown-light);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(139, 69, 19, 0.3);
        }
        
        .error { 
            background: #ffebee;
            border-left: 4px solid var(--danger-red);
            color: #b71c1c;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-note {
            background: #e8f5e9;
            border-left: 4px solid var(--success-green);
            color: #1b5e20;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .btn-copy-promo { 
            background: var(--main-brown); 
            color: #fff; 
            border: none; 
            padding: 12px; 
            border-radius: 10px; 
            cursor: pointer; 
            font-size: 14px; 
            white-space: nowrap;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-copy-promo:hover { 
            background: var(--brown-light); 
            transform: translateY(-2px);
        }
        
        .promo-chip { 
            background: #f5f5f5; 
            border: 1px solid var(--border-color); 
            padding: 8px 15px; 
            border-radius: 30px; 
            font-size: 13px; 
            cursor: pointer; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            transition: all 0.3s;
            color: var(--text-black);
        }
        
        .promo-chip:hover { 
            background: #e8f5e9; 
            border-color: var(--success-green); 
            color: var(--success-green); 
            transform: translateY(-2px);
        }
        
        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
            color: #999;
            font-size: 13px;
        }
        
        .secure-badge i {
            font-size: 18px;
            color: var(--success-green);
        }
        
        @media (max-width: 900px) { 
            .checkout-grid { 
                grid-template-columns: 1fr; 
            } 
            .form-row { 
                grid-template-columns: 1fr; 
            } 
            .banking-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .card {
                padding: 20px;
            }
            
            .payment-option label {
                padding: 12px;
                font-size: 13px;
            }
            
            .btn-copy-promo {
                width: 100%;
                justify-content: center;
            }
            
            .qr-code-section img {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="checkout-page">
    <h1 class="page-title">
        <i class="fas fa-shopping-bag"></i> Thanh toán
    </h1>

    <?php if (isset($error) && $error !== ''): ?>
        <div class="error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="info-note">
        <i class="fas fa-info-circle"></i> 
        Thông tin nhận hàng được tự động lấy từ tài khoản của bạn. Bạn có thể chỉnh sửa nếu cần.
    </div>

    <form method="POST" class="checkout-grid">
        <!-- LEFT COLUMN - Customer Info & Payment -->
        <div class="card">
            <h2>
                <i class="fas fa-user"></i>
                Thông tin nhận hàng
            </h2>
            
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
                    <label>Địa chỉ email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($userProfile['email'] ?? ''); ?>" readonly placeholder="Email từ tài khoản" title="Email lấy từ tài khoản đăng nhập">
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
            
            <h2 style="margin-top: 25px;">
                <i class="fas fa-credit-card"></i>
                Phương thức thanh toán
            </h2>
            
            <div class="payment-methods">
                <!-- COD -->
                <div class="payment-option <?php echo $formPaymentMethod === 'cod' ? 'selected' : ''; ?>">
                    <label>
                        <input type="radio" name="payment_method" value="cod" <?php echo $formPaymentMethod === 'cod' ? 'checked' : ''; ?>>
                        <i class="fas fa-truck"></i>
                        <div>
                            <strong>Thanh toán khi nhận hàng (COD)</strong>
                            <p style="font-size: 13px; color: #666; margin-top: 3px;">Chỉ thanh toán khi nhận được bánh</p>
                        </div>
                    </label>
                </div>
                
                <!-- Banking -->
                <div class="payment-option <?php echo $formPaymentMethod === 'banking' ? 'selected' : ''; ?>">
                    <label>
                        <input type="radio" name="payment_method" value="banking" <?php echo $formPaymentMethod === 'banking' ? 'checked' : ''; ?>>
                        <i class="fas fa-university"></i>
                        <div>
                            <strong>Chuyển khoản ngân hàng</strong>
                            <p style="font-size: 13px; color: #666; margin-top: 3px;">Thanh toán qua tài khoản ngân hàng</p>
                        </div>
                    </label>
                </div>
            </div>
            
            <!-- Banking Details (show when banking selected) -->
            <div id="bankingDetails" style="display: <?php echo $formPaymentMethod === 'banking' ? 'block' : 'none'; ?>;">
                <div class="banking-details">
                    <div class="banking-header">
                        <i class="fas fa-info-circle"></i>
                        Thông tin chuyển khoản
                    </div>
                    
                    <div class="banking-grid">
                        <div class="bank-info-item">
                            <div class="label">Ngân hàng</div>
                            <div class="value"><?php echo htmlspecialchars($bankInfo['bank_name']); ?></div>
                        </div>
                        
                        <div class="bank-info-item">
                            <div class="label">Chi nhánh</div>
                            <div class="value"><?php echo htmlspecialchars($bankInfo['bank_branch']); ?></div>
                        </div>
                        
                        <div class="bank-info-item">
                            <div class="label">Số tài khoản</div>
                            <div class="value highlight" id="accountNumber"><?php echo htmlspecialchars($bankInfo['account_number']); ?></div>
                            <button type="button" class="copy-btn" onclick="copyAccountNumber()" title="Sao chép số tài khoản">
                                <i class="fas fa-copy"></i> Sao chép
                            </button>
                        </div>
                        
                        <div class="bank-info-item">
                            <div class="label">Chủ tài khoản</div>
                            <div class="value"><?php echo htmlspecialchars($bankInfo['account_name']); ?></div>
                        </div>
                    </div>
                    
                    <div class="qr-code-section">
                        <?php 
                        $qrFullPath = __DIR__ . '/../' . $bankInfo['qr_code'];
                        if (!empty($bankInfo['qr_code']) && file_exists($qrFullPath)): 
                        ?>
                        <img src="../<?php echo htmlspecialchars($bankInfo['qr_code']); ?>" alt="QR Code thanh toán">
                        <?php else: ?>
                        <div class="qr-placeholder">
                            <i class="fas fa-qrcode"></i>
                            <p>Mã QR (cập nhật ảnh trong cấu hình)</p>
                        </div>
                        <?php endif; ?>
                        <div class="qr-note">
                            <strong>💡 Hướng dẫn:</strong> Quét mã QR bằng ứng dụng ngân hàng hoặc chuyển khoản theo thông tin trên.<br>
                            <strong>Nội dung chuyển khoản (bắt buộc):</strong>
                            <span id="transferContent"><?php echo htmlspecialchars($bankInfo['transfer_content']); ?></span>
                            <button type="button" class="copy-btn" onclick="copyTransferContent()" title="Sao chép nội dung">
                                <i class="fas fa-copy"></i> Sao chép
                            </button>
                        </div>
                    </div>
                    
                    <div style="margin-top: 15px; padding: 15px; background: #fff3e0; border-radius: 8px; font-size: 13px;">
                        <i class="fas fa-clock" style="color: var(--warning-orange);"></i>
                        <strong>Lưu ý:</strong> Đơn hàng sẽ được xác nhận sau khi chúng tôi nhận được thanh toán. Vui lòng giữ lại biên lai chuyển khoản.
                    </div>
                </div>
            </div>
        </div>
        
        <!-- RIGHT COLUMN - Order Summary -->
        <div class="card order-summary">
            <h2>
                <i class="fas fa-shopping-cart"></i>
                Đơn hàng của bạn
            </h2>
            
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
            
            <hr>
            
            <div class="row">
                <div>Tạm tính</div>
                <div><?php echo number_format($totalAmount, 0, ',', '.'); ?>₫</div>
            </div>
            
            <?php if ($discountAmount > 0): ?>
            <div class="row" style="color: var(--success-green);">
                <div>
                    <i class="fas fa-tag"></i> 
                    Giảm (<?php echo htmlspecialchars($appliedPromo['code'] ?? ''); ?>)
                </div>
                <div>-<?php echo number_format($discountAmount, 0, ',', '.'); ?>₫</div>
            </div>
            <?php endif; ?>
            
            <hr>
            
            <div class="row total">
                <div>Tổng cộng</div>
                <div><?php echo number_format($finalAmount, 0, ',', '.'); ?>₫</div>
            </div>
            
            <!-- Promo Code -->
            <div class="form-group" style="margin-top: 20px;">
                <label>
                    <i class="fas fa-ticket-alt"></i>
                    Mã khuyến mãi
                </label>
                <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                    <input type="text" name="promo_code" id="promo_code" value="<?php echo htmlspecialchars(isset($_POST['promo_code']) ? $_POST['promo_code'] : ''); ?>" placeholder="Nhập mã giảm giá" style="text-transform:uppercase; flex:1; min-width:160px; padding:12px; border:2px solid var(--border-color); border-radius:10px;">
                    <button type="button" class="btn-copy-promo" onclick="copyPromoFromInput()" title="Sao chép mã">
                        <i class="fas fa-copy"></i> Sao chép
                    </button>
                </div>
                
                <?php if (!empty($promoList)): ?>
                    <p style="font-size:12px; color:#666; margin-top:10px; margin-bottom:5px;">Mã có sẵn — bấm để sao chép:</p>
                    <div style="display:flex; flex-wrap:wrap; gap:8px;">
                        <?php foreach ($promoList as $p): ?>
                            <button type="button" class="promo-chip" onclick="copyPromoCode('<?php echo htmlspecialchars(addslashes($p['code'])); ?>')" title="Sao chép <?php echo htmlspecialchars($p['code']); ?>">
                                <?php echo htmlspecialchars($p['code']); ?> 
                                <i class="fas fa-copy" style="font-size:10px;"></i>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($promoError) && $promoError !== ''): ?>
                    <span style="color:var(--danger-red); font-size:12px; display:block; margin-top:5px;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($promoError); ?>
                    </span>
                <?php endif; ?>
                
                <?php if (isset($appliedPromo) && $appliedPromo): ?>
                    <span style="color:var(--success-green); font-size:12px; display:block; margin-top:5px;">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($appliedPromo['code']); ?>: -<?php echo number_format($discountAmount, 0, ',', '.'); ?>₫
                    </span>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-check"></i>
                Xác nhận đặt hàng
            </button>
        </div>
    </form>
</div>

<script>
(function() {
    var form = document.querySelector('form.checkout-grid');
    if (!form) return;
    var bankingDetails = document.getElementById('bankingDetails');
    var paymentRadios = form.querySelectorAll('input[name="payment_method"]');
    var options = form.querySelectorAll('.payment-option');

    function updatePaymentUI() {
        var method = form.querySelector('input[name="payment_method"]:checked');
        if (!method) return;
        var isBanking = method.value === 'banking';
        if (bankingDetails) bankingDetails.style.display = isBanking ? 'block' : 'none';
        options.forEach(function(el) {
            el.classList.toggle('selected', el.querySelector('input[name="payment_method"]').value === method.value);
        });
    }

    paymentRadios.forEach(function(radio) {
        radio.addEventListener('change', updatePaymentUI);
    });
    updatePaymentUI();
})();

function copyAccountNumber() {
    var el = document.getElementById('accountNumber');
    if (el) {
        navigator.clipboard.writeText(el.textContent.trim()).then(function() {
            alert('Đã sao chép số tài khoản!');
        }).catch(function() {
            prompt('Sao chép số tài khoản:', el.textContent.trim());
        });
    }
}

function copyTransferContent() {
    var el = document.getElementById('transferContent');
    if (el) {
        navigator.clipboard.writeText(el.textContent.trim()).then(function() {
            alert('Đã sao chép nội dung chuyển khoản!');
        }).catch(function() {
            prompt('Sao chép nội dung chuyển khoản:', el.textContent.trim());
        });
    }
}

function copyPromoCode(code) {
    navigator.clipboard.writeText(code).then(function() {
        document.getElementById('promo_code').value = code;
        alert('Đã sao chép mã: ' + code);
    }).catch(function() {
        document.getElementById('promo_code').value = code;
    });
}

function copyPromoFromInput() {
    var input = document.getElementById('promo_code');
    if (input && input.value.trim()) {
        navigator.clipboard.writeText(input.value.trim());
        alert('Đã sao chép mã từ ô nhập!');
    }
}
</script>

<?php include 'footer.php'; ?>
</body>
</html>