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

$page_title = 'Thanh toán - Sweet Cake';
include 'header.php';
?>

<div class="checkout-page">
    <nav class="content-breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <a href="cart.php">Giỏ hàng</a>
        <span>/</span>
        <span>Thanh toán</span>
    </nav>

    <?php if (isset($error) && $error !== ''): ?>
        <div class="content-alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="checkout-grid">
        <div class="checkout-main">
            <section class="checkout-card">
                <div class="checkout-card-header">
                    <span class="checkout-step">01</span>
                    <div>
                        <h2>Thông tin nhận hàng</h2>
                        <p>Sweet Cake sẽ liên hệ theo thông tin bên dưới trước khi giao bánh.</p>
                    </div>
                </div>

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
            </section>
            
            <section class="checkout-card">
                <div class="checkout-card-header">
                    <span class="checkout-step">02</span>
                    <div>
                        <h2>Phương thức thanh toán</h2>
                        <p>Chọn cách thanh toán phù hợp với bạn.</p>
                    </div>
                </div>

                <div class="payment-methods">
                    <div class="payment-option <?php echo $formPaymentMethod === 'cod' ? 'selected' : ''; ?>">
                        <label>
                            <input type="radio" name="payment_method" value="cod" <?php echo $formPaymentMethod === 'cod' ? 'checked' : ''; ?>>
                            <span class="payment-icon"><i class="fas fa-truck"></i></span>
                            <div>
                                <strong>Thanh toán khi nhận hàng (COD)</strong>
                                <p>Chỉ thanh toán khi nhận được bánh.</p>
                            </div>
                        </label>
                    </div>
                    
                    <div class="payment-option <?php echo $formPaymentMethod === 'banking' ? 'selected' : ''; ?>">
                        <label>
                            <input type="radio" name="payment_method" value="banking" <?php echo $formPaymentMethod === 'banking' ? 'checked' : ''; ?>>
                            <span class="payment-icon"><i class="fas fa-university"></i></span>
                            <div>
                                <strong>Chuyển khoản ngân hàng</strong>
                                <p>Quét QR hoặc chuyển khoản theo thông tin bên dưới.</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <div id="bankingDetails" style="display: <?php echo $formPaymentMethod === 'banking' ? 'block' : 'none'; ?>;">
                    <div class="banking-details">
                        <div class="banking-header">
                            <i class="fas fa-info-circle"></i>
                            Thông tin chuyển khoản
                        </div>
                        
                        <div class="banking-layout">
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
                                    <strong>Hướng dẫn:</strong> Quét mã QR hoặc chuyển khoản theo thông tin trên.
                                    <span>Nội dung CK: <b id="transferContent"><?php echo htmlspecialchars($bankInfo['transfer_content']); ?></b></span>
                                    <button type="button" class="copy-btn" onclick="copyTransferContent()" title="Sao chép nội dung">
                                        <i class="fas fa-copy"></i> Sao chép
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="banking-warning">
                            <i class="fas fa-clock"></i>
                            <span><strong>Lưu ý:</strong> Đơn hàng sẽ được xác nhận sau khi chúng tôi nhận được thanh toán. Vui lòng giữ lại biên lai chuyển khoản.</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        
        <aside class="checkout-sidebar">
            <div class="checkout-card order-summary">
                <div class="checkout-card-header">
                    <span class="checkout-step">03</span>
                    <div>
                        <h2>Đơn hàng của bạn</h2>
                        <p><?php echo count($_SESSION['cart']); ?> sản phẩm trong giỏ hàng</p>
                    </div>
                </div>

                <div class="checkout-items">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="checkout-item">
                        <div class="checkout-item-main">
                            <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                            <span>
                                <?php if (isset($item['size']) && $item['size']): ?>
                                    Size: <?php echo htmlspecialchars($item['size']); ?>
                                <?php endif; ?>
                                <?php if (isset($item['flavor']) && $item['flavor']): ?>
                                    <?php echo (isset($item['size']) && $item['size']) ? ' · ' : ''; ?>Vị: <?php echo htmlspecialchars($item['flavor']); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="checkout-item-price">
                            <span>x<?php echo (int)$item['quantity']; ?></span>
                            <strong><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫</strong>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="checkout-promo">
                    <label><i class="fas fa-ticket-alt"></i> Mã khuyến mãi</label>
                    <div class="promo-input-row">
                        <input type="text" name="promo_code" id="promo_code" value="<?php echo htmlspecialchars(isset($_POST['promo_code']) ? $_POST['promo_code'] : ''); ?>" placeholder="Nhập mã giảm giá">
                        <button type="button" class="btn-copy-promo" onclick="copyPromoFromInput()" title="Sao chép mã">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    
                    <?php if (!empty($promoList)): ?>
                        <p>Mã có sẵn — bấm để sao chép:</p>
                        <div class="promo-chip-list">
                            <?php foreach ($promoList as $p): ?>
                                <button type="button" class="promo-chip" onclick="copyPromoCode('<?php echo htmlspecialchars(addslashes($p['code'])); ?>')" title="Sao chép <?php echo htmlspecialchars($p['code']); ?>">
                                    <?php echo htmlspecialchars($p['code']); ?> 
                                    <i class="fas fa-copy"></i>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($promoError) && $promoError !== ''): ?>
                        <span class="promo-message error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($promoError); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if (isset($appliedPromo) && $appliedPromo): ?>
                        <span class="promo-message success">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($appliedPromo['code']); ?>: -<?php echo number_format($discountAmount, 0, ',', '.'); ?>₫
                        </span>
                    <?php endif; ?>
                </div>

                <div class="checkout-total-box">
                    <div class="row">
                        <span>Tạm tính</span>
                        <strong><?php echo number_format($totalAmount, 0, ',', '.'); ?>₫</strong>
                    </div>
                    
                    <?php if ($discountAmount > 0): ?>
                    <div class="row discount">
                        <span><i class="fas fa-tag"></i> Giảm (<?php echo htmlspecialchars($appliedPromo['code'] ?? ''); ?>)</span>
                        <strong>-<?php echo number_format($discountAmount, 0, ',', '.'); ?>₫</strong>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row total">
                        <span>Tổng cộng</span>
                        <strong><?php echo number_format($finalAmount, 0, ',', '.'); ?>₫</strong>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i>
                    Xác nhận đặt hàng
                </button>
            </div>
        </aside>
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