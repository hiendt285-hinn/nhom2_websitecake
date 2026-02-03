<?php
// cart.php - Trang hiển thị giỏ hàng
session_start();
require_once __DIR__ . '/connect.php';

// Xử lý cập nhật giỏ hàng / xóa mục (dạng form thông thường, không phụ thuộc JS)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Xóa 1 item nếu bấm nút thùng rác
    if (isset($_POST['remove_key'])) {
        $itemKey = $_POST['remove_key'];
        if (isset($_SESSION['cart'][$itemKey])) {
            unset($_SESSION['cart'][$itemKey]);
            if (empty($_SESSION['cart'])) {
                unset($_SESSION['cart']);
            }
        }
    }
    // Cập nhật toàn bộ số lượng (giới hạn theo tồn kho)
    elseif (isset($_POST['quantities']) && is_array($_POST['quantities']) && isset($conn)) {
        foreach ($_POST['quantities'] as $itemKey => $qty) {
            $qty = (int)$qty;
            if ($qty <= 0) {
                unset($_SESSION['cart'][$itemKey]);
            } elseif (isset($_SESSION['cart'][$itemKey])) {
                $productId = (int)$_SESSION['cart'][$itemKey]['id'];
                $stock = 999;
                $stmt = $conn->prepare('SELECT stock FROM products WHERE id = ? LIMIT 1');
                if ($stmt) {
                    $stmt->bind_param('i', $productId);
                    $stmt->execute();
                    $row = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    if ($row !== null) {
                        $stock = (int)$row['stock'];
                    }
                }
                $otherQty = 0;
                foreach ($_SESSION['cart'] as $k => $item) {
                    if ($k !== $itemKey && (int)$item['id'] === $productId) {
                        $otherQty += (int)$item['quantity'];
                    }
                }
                $maxQty = max(0, $stock - $otherQty);
                $qty = min($qty, $maxQty > 0 ? $maxQty : 0);
                if ($qty <= 0) {
                    unset($_SESSION['cart'][$itemKey]);
                } else {
                    $_SESSION['cart'][$itemKey]['quantity'] = $qty;
                }
            }
        }

        if (empty($_SESSION['cart'])) {
            unset($_SESSION['cart']);
        }
    }

    // Sau khi xử lý, quay lại trang giỏ hàng (PRG pattern)
    header('Location: cart.php');
    exit;
}

// Tính tổng tiền và lấy tồn kho theo product_id (để giới hạn số lượng trong giỏ)
$total_amount = 0;
$stockByProduct = [];
if (isset($_SESSION['cart']) && !empty($_SESSION['cart']) && isset($conn)) {
    $productIds = array_values(array_unique(array_map(function ($item) { return (int)$item['id']; }, $_SESSION['cart'])));
    foreach ($_SESSION['cart'] as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }
    if (!empty($productIds)) {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $types = str_repeat('i', count($productIds));
        $stmtStock = $conn->prepare("SELECT id, stock FROM products WHERE id IN ($placeholders)");
        if ($stmtStock) {
            $bindParams = array_merge([$types], $productIds);
            $refs = [];
            foreach ($bindParams as $key => $val) {
                $refs[$key] = &$bindParams[$key];
            }
            call_user_func_array([$stmtStock, 'bind_param'], $refs);
            $stmtStock->execute();
            $res = $stmtStock->get_result();
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $stockByProduct[(int)$row['id']] = (int)$row['stock'];
                }
            }
            $stmtStock->close();
        }
    }
} elseif (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }
}

$show_success = isset($_GET['ordered']) && (int)$_GET['ordered'] === 1;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - Savor Cake</title>
    <link rel="stylesheet" href="style.css?v=<?php echo (file_exists(__DIR__ . '/style.css') ? filemtime(__DIR__ . '/style.css') : time()); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* === TRANG GIỎ HÀNG === */
        .cart-page {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
            font-family: 'Open Sans', sans-serif;
        }
        .alert-success { background: var(--light-beige); color: var(--text-black); border: 1px solid rgba(0,0,0,0.06); padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; }

        .page-header {
            text-align: center;
            margin-bottom: 20px;
            padding: 30px 0 20px;
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }

        .page-header h1 {
            font-size: 28px;
            color: var(--text-black);
            font-weight: 700;
            margin: 0;
            font-family: 'Open Sans', sans-serif;
        }

        .cart-empty {
            text-align: center;
            color: #666;
            font-size: 18px;
            padding: 50px 0;
        }

        /* === TABLE GIỎ HÀNG === */
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            /* Glassmorphism frame */
            background: rgba(255,255,255,0.55);
            -webkit-backdrop-filter: blur(8px);
            backdrop-filter: blur(8px);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.35);
        }

        .cart-table th, .cart-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        .cart-table th {
            background: #f9f6f2;
            font-weight: 600;
            color: var(--text-black);
        }

        .cart-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .item-name {
            font-weight: 600;
            color: var(--text-black);
        }

        .item-details {
            color: #666;
            font-size: 14px;
        }

        .quantity-input {
            width: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
            font-size: 14px;
        }

        .btn-remove {
            background: none;
            border: none;
            color: #d32f2f;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
        }

        .btn-remove:hover {
            color: #ff5f5f;
        }

        .cart-summary {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 20px;
            padding: 20px;
            /* Glass footer */
            background: rgba(255,255,255,0.45);
            -webkit-backdrop-filter: blur(6px);
            backdrop-filter: blur(6px);
            border-radius: 10px;
            border-top: 1px solid rgba(255,255,255,0.25);
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
        }

        .total-amount {
            font-size: 20px;
            font-weight: 700;
            color: var(--main-brown);
        }

        .btn-update, .btn-checkout {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
            font-size: 14px;
        }

        .btn-update {
            background: var(--white);
            color: var(--text-black);
            border: 1px solid #ddd;
        }

        .btn-update:hover {
            background: #f9f6f2;
            border-color: var(--main-brown);
        }

        .btn-checkout {
            background: var(--main-brown);
            color: var(--white);
        }

        .btn-checkout:hover {
            background: var(--brown-light);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .cart-table {
                font-size: 14px;
            }
            .cart-item-img {
                width: 60px;
                height: 60px;
            }
            .cart-summary {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

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
            <i class="fas fa-shopping-cart fa-3x" style="color: #ccc; margin-bottom: 20px;"></i>
            <p>Giỏ hàng đang trống. <a href="products.php">Tiếp tục mua sắm</a></p>
        </div>
    <?php else: ?>
        <form id="cart-form" method="POST" action="cart.php">
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
                                        Size: <?php echo htmlspecialchars($item['size']); ?><br>
                                        Vị: <?php echo htmlspecialchars($item['flavor']); ?>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo number_format($item['price'], 0, ',', '.'); ?>₫</td>
                            <td>
                                <?php
                                $pid = (int)$item['id'];
                                $stock = $stockByProduct[$pid] ?? 0;
                                $otherQty = 0;
                                foreach ($_SESSION['cart'] as $k => $i) {
                                    if ($k !== $item_key && (int)$i['id'] === $pid) { $otherQty += (int)$i['quantity']; }
                                }
                                $maxQty = max(1, $stock - $otherQty);
                                ?>
                                <input type="number" name="quantities[<?php echo $item_key; ?>]" 
                                       class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $maxQty; ?>">
                            </td>
                            <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫</td>
                            <td>
                                <button type="submit"
                                        name="remove_key"
                                        value="<?php echo htmlspecialchars($item_key, ENT_QUOTES, 'UTF-8'); ?>"
                                        class="btn-remove">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <div class="total-amount">
                    Tổng cộng: <?php echo number_format($total_amount, 0, ',', '.'); ?>₫
                </div>
                <button type="submit" class="btn-update">Cập nhật giỏ hàng</button>
                <a href="checkout.php" class="btn-checkout">Tiến hành thanh toán</a> <!-- Link đến checkout nếu có -->
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

</body>
</html>