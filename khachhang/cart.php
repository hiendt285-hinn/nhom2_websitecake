<?php
// cart.php - Trang hiển thị giỏ hàng

session_start();
include 'connect.php'; // Nếu cần cập nhật giá từ DB, nhưng hiện dùng data từ session

// Xử lý cập nhật giỏ hàng / xóa mục (hỗ trợ AJAX và non-AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['success' => false];

    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'update' && isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $itemKey => $qty) {
            $qty = max(1, (int)$qty);
            if (isset($_SESSION['cart'][$itemKey])) {
                $_SESSION['cart'][$itemKey]['quantity'] = $qty;
            }
        }
        $response['success'] = true;
        echo json_encode($response);
        exit;
    }

    if ($action === 'remove' && isset($_POST['item_key'])) {
        $itemKey = $_POST['item_key'];
        if (isset($_SESSION['cart'][$itemKey])) {
            unset($_SESSION['cart'][$itemKey]);
            $response['success'] = true;
        }
        echo json_encode($response);
        exit;
    }

    echo json_encode($response);
    exit;
}

// Tính tổng tiền
$total_amount = 0;
if (isset($_SESSION['cart'])) {
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
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* === TRANG GIỎ HÀNG === */
        .cart-page {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
            font-family: 'Poppins', sans-serif;
        }
        .alert-success { background: #e8f5e9; color: #256029; border: 1px solid #c8e6c9; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; }

        .page-header {
            text-align: center;
            margin-bottom: 16px;
        }

        .page-header h1 {
            font-size: 32px;
            color: #ff5f9e;
            font-weight: 700;
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
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .cart-table th, .cart-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .cart-table th {
            background: #f8f5f0;
            font-weight: 600;
            color: #5D4037;
        }

        .cart-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .item-name {
            font-weight: 600;
            color: #333;
        }

        .item-details {
            color: #666;
            font-size: 14px;
        }

        .quantity-input {
            width: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            text-align: center;
        }

        .btn-remove {
            background: none;
            border: none;
            color: #d32f2f;
            cursor: pointer;
            font-size: 18px;
        }

        .cart-summary {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: #f8f5f0;
            border-radius: 12px;
        }

        .total-amount {
            font-size: 20px;
            font-weight: bold;
            color: #FFCA28;
        }

        .btn-update, .btn-checkout {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-update {
            background: #5D4037;
            color: white;
        }

        .btn-update:hover {
            background: #4E342E;
        }

        .btn-checkout {
            background: #FFCA28;
            color: #5D4037;
        }

        .btn-checkout:hover {
            background: #FFB300;
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
                                <img src="../images/products/<?php echo htmlspecialchars($item['image']); ?>" 
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
                                <input type="number" name="quantities[<?php echo $item_key; ?>]" 
                                       class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1">
                            </td>
                            <td><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>₫</td>
                            <td>
                                <button type="button" class="btn-remove" 
                                        onclick="removeItem('<?php echo $item_key; ?>')">
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
            <input type="hidden" name="action" value="update">
        </form>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

<script>
    // Xóa item (AJAX)
    function removeItem(itemKey) {
        if (confirm('Xóa sản phẩm này khỏi giỏ?')) {
            fetch('cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'remove', item_key: itemKey })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Không thể xóa sản phẩm.');
                }
            })
            .catch(() => alert('Không thể xóa sản phẩm.'));
        }
    }

    // Cập nhật form submit (AJAX)
    document.getElementById('cart-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.set('action', 'update');
        fetch('cart.php', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    });
</script>

</body>
</html>