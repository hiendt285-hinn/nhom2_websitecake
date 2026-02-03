<?php
// update_cart.php - Xử lý cập nhật quantity các item trong giỏ (giới hạn theo tồn kho)

session_start();
require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['quantities'])) {
    $quantities = $_POST['quantities']; // Array [item_key => new_qty]

    foreach ($quantities as $item_key => $new_qty) {
        $new_qty = (int)$new_qty;
        if ($new_qty <= 0) {
            unset($_SESSION['cart'][$item_key]);
        } elseif (isset($_SESSION['cart'][$item_key])) {
            $productId = (int)$_SESSION['cart'][$item_key]['id'];
            $stmt = $conn->prepare('SELECT stock FROM products WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $productId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $stock = $row ? (int)$row['stock'] : 0;
            $otherQty = 0;
            foreach ($_SESSION['cart'] as $k => $item) {
                if ($k !== $item_key && (int)$item['id'] === $productId) {
                    $otherQty += (int)$item['quantity'];
                }
            }
            $maxQty = max(0, $stock - $otherQty);
            $new_qty = min($new_qty, $maxQty > 0 ? $maxQty : 0);
            if ($new_qty <= 0) {
                unset($_SESSION['cart'][$item_key]);
            } else {
                $_SESSION['cart'][$item_key]['quantity'] = $new_qty;
            }
        }
    }

    if (empty($_SESSION['cart'])) {
        unset($_SESSION['cart']);
    }

    echo json_encode([
        'success' => true,
        'cart_count' => isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0
    ]);
} else {
    header('Location: cart.php');
}
?>