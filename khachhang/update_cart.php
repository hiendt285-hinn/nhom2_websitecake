<?php
// update_cart.php - Xử lý cập nhật quantity các item trong giỏ

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['quantities'])) {
    $quantities = $_POST['quantities']; // Array [item_key => new_qty]

    foreach ($quantities as $item_key => $new_qty) {
        $new_qty = (int)$new_qty;
        if ($new_qty <= 0) {
            unset($_SESSION['cart'][$item_key]); // Xóa nếu qty <= 0
        } elseif (isset($_SESSION['cart'][$item_key])) {
            $_SESSION['cart'][$item_key]['quantity'] = $new_qty;
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