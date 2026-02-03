<?php
// add-to-cart.php - Thêm sản phẩm vào giỏ hàng (SESSION)

session_start();
require_once 'connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
    exit;
}

$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
$size = trim($_POST['size'] ?? ''); 
$flavor = trim($_POST['flavor'] ?? '');

if (empty($size) || empty($flavor)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn Kích thước và Hương vị hợp lệ.']);
    exit();
}

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Thiếu product_id']);
    exit;
}

// Lấy thông tin sản phẩm từ DB (kèm stock)
$stmt = $conn->prepare('SELECT id, name, price, image, stock FROM products WHERE id = ? AND is_active = 1 LIMIT 1');
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Không thể chuẩn bị truy vấn']);
    exit;
}
$stmt->bind_param('i', $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại hoặc ngừng bán']);
    exit;
}

// Khởi tạo giỏ nếu chưa có
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Tổng số lượng đã có trong giỏ cho sản phẩm này (cùng product_id)
$alreadyInCart = 0;
foreach ($_SESSION['cart'] as $key => $item) {
    if ((int)$item['id'] === (int)$product['id']) {
        $alreadyInCart += (int)$item['quantity'];
    }
}
$stock = (int)($product['stock'] ?? 0);
$available = $stock - $alreadyInCart;
if ($available <= 0) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm "' . $product['name'] . '" đã hết tồn kho hoặc đã đủ số lượng trong giỏ (tồn kho: ' . $stock . ').']);
    exit;
}
$quantity = min($quantity, $available);

// Tạo key duy nhất theo sản phẩm + biến thể (size, flavor)
$itemKey = $product['id'] . '|' . $size . '|' . $flavor;

if (isset($_SESSION['cart'][$itemKey])) {
    $_SESSION['cart'][$itemKey]['quantity'] += $quantity;
} else {
    $_SESSION['cart'][$itemKey] = [
        'id' => (int)$product['id'],
        'name' => (string)$product['name'],
        'price' => (float)$product['price'],
        'image' => (string)$product['image'],
        'quantity' => $quantity,
        'size' => $size,
        'flavor' => $flavor,
    ];
}

// Đếm số dòng item trong giỏ
$cartCount = count($_SESSION['cart']);

echo json_encode(['success' => true, 'cart_count' => $cartCount]);
