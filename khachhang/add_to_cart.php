<?php
// add_to_cart.php - Xử lý thêm sản phẩm vào giỏ hàng qua session

session_start();
include 'connect.php'; // Kết nối DB để lấy thông tin sản phẩm nếu cần

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $size = $_POST['size'] ?? '20cm'; // Default nếu không có
    $flavor = $_POST['flavor'] ?? 'Vani'; // Default nếu không có

    // Kiểm tra sản phẩm tồn tại trong DB
    $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit();
    }
    $product = $result->fetch_assoc();
    $stmt->close();

    // Khóa unique cho item: product_id_size_flavor
    $item_key = $product_id . '_' . $size . '_' . $flavor;

    // Khởi tạo giỏ nếu chưa có
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Nếu item đã tồn tại, cộng quantity
    if (isset($_SESSION['cart'][$item_key])) {
        $_SESSION['cart'][$item_key]['quantity'] += $quantity;
    } else {
        // Thêm mới
        $_SESSION['cart'][$item_key] = [
            'product_id' => $product_id,
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'],
            'quantity' => $quantity,
            'size' => $size,
            'flavor' => $flavor
        ];
    }

    // Trả về JSON cho fetch (nếu dùng AJAX)
    echo json_encode(['success' => true, 'cart_count' => count($_SESSION['cart'])]);
} else {
    header('Location: products.php'); // Redirect nếu không phải POST
}
?>