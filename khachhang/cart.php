<?php
session_start();
require_once __DIR__ . '/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_key'])) {
        $itemKey = $_POST['remove_key'];
        if (isset($_SESSION['cart'][$itemKey])) {
            unset($_SESSION['cart'][$itemKey]);
            if (empty($_SESSION['cart'])) {
                unset($_SESSION['cart']);
            }
        }
    } elseif (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $itemKey => $qty) {
            $qty = (int)$qty;
            if ($qty <= 0 && isset($_SESSION['cart'][$itemKey])) {
                unset($_SESSION['cart'][$itemKey]);
            } elseif ($qty >= 1 && isset($_SESSION['cart'][$itemKey])) {
                $_SESSION['cart'][$itemKey]['quantity'] = $qty;
            }
        }

        if (empty($_SESSION['cart'])) {
            unset($_SESSION['cart']);
        }
    }

    header('Location: cart.php');
    exit;
}

$total_amount = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }
}

$show_success = isset($_GET['ordered']) && (int)$_GET['ordered'] === 1;

$page_title = 'Giỏ hàng - Sweet Cake';
include 'header.php';
?>

<div class="cart-page">
    <div class="page-header">
        <h1>Giỏ hàng của bạn</h1>
    </div>

    <?php if ($show_success): ?>
        <div class="alert-success">
            Đặt hàng thành công! Bạn có thể xem đơn hàng trong <a href="order_history.php">Lịch sử đơn hàng</a>.
        </div>
    <?php endif; ?>

    <?php if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])): ?>
        <div class="cart-empty">
            <i class="fas fa-shopping-bag fa-3x" style="color: #ccc; margin-bottom: 20px; display:block;"></i>
            <p>Giỏ hàng đang trống.</p>
            <a href="products.php" class="btn-primary" style="margin-top:16px;">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <form id="cart-form" method="POST" action="cart.php" class="cart-layout">
            <div class="cart-table-wrap">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Tổng</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['cart'] as $item_key => $item): ?>
                            <tr>
                                <td>
                                    <img src="../images/<?php echo htmlspecialchars($item['image']); ?>"
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-img">
                                    <div>
                                        <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <div class="item-details">
                                            Size: <?php echo htmlspecialchars($item['size']); ?> ·
                                            Vị: <?php echo htmlspecialchars($item['flavor']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo number_format($item['price'], 0, ',', '.'); ?>₫</td>
                                <td>
                                    <input type="number" name="quantities[<?php echo $item_key; ?>]"
                                           class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1">
                                </td>
                                <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫</td>
                                <td>
                                    <button type="submit"
                                            name="remove_key"
                                            value="<?php echo htmlspecialchars($item_key, ENT_QUOTES, 'UTF-8'); ?>"
                                            class="btn-remove" title="Xóa sản phẩm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-summary-card">
                <h3>Tóm tắt đơn hàng</h3>
                <div class="cart-summary-row">
                    <span>Số sản phẩm</span>
                    <span><?php echo count($_SESSION['cart']); ?></span>
                </div>
                <div class="total-amount">
                    Tổng cộng: <?php echo number_format($total_amount, 0, ',', '.'); ?>₫
                </div>
                <div class="cart-summary-actions">
                    <button type="submit" class="btn-update">Cập nhật giỏ hàng</button>
                    <a href="checkout.php" class="btn-checkout">Tiến hành thanh toán</a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
