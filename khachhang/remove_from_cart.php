<?php
// remove_from_cart.php - Xử lý xóa item khỏi giỏ hàng

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['item_key'])) {
    $item_key = $_POST['item_key'];

    if (isset($_SESSION['cart'][$item_key])) {
        unset($_SESSION['cart'][$item_key]);
        echo json_encode(['success' => true, 'cart_count' => count($_SESSION['cart'])]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item không tồn tại']);
    }
} else {
    header('Location: cart.php');
}
?>