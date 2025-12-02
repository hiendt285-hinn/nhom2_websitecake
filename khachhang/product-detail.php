<?php
// detail_products.php - Trang chi tiết sản phẩm

session_start();
require_once 'connect.php'; // Kết nối DB

// 1. Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id === 0) {
    // Nếu không có ID hợp lệ, chuyển hướng
    header('Location: products.php');
    exit;
}

$stmt = $conn->prepare("SELECT id, name, price, description, image FROM products WHERE id = ? AND is_active = 1 LIMIT 1");
if (!$stmt) {
    die("Lỗi chuẩn bị truy vấn: " . $conn->error);
}
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

// 3. Xử lý nếu sản phẩm không tồn tại
if (!$product) {
    header('Location: products.php'); 
    exit;
}

// 4. Lấy danh sách tùy chọn (Size và Flavor) từ DB
// Phải đảm bảo bạn đã tạo bảng 'sizes' và 'flavors'
$sizes_result = $conn->query("SELECT name FROM sizes ORDER BY name ASC");
$available_sizes = $sizes_result ? $sizes_result->fetch_all(MYSQLI_ASSOC) : [];

$flavors_result = $conn->query("SELECT name FROM flavors ORDER BY name ASC");
$available_flavors = $flavors_result ? $flavors_result->fetch_all(MYSQLI_ASSOC) : [];


// 5. Xử lý thông báo sau khi thêm vào giỏ hàng
$message = '';
if (isset($_GET['added']) && (int)$_GET['added'] === 1) {
    $message = '<div class="alert-success" style="margin-bottom: 20px; padding: 15px; background: #d4edda; color: #155724; border-radius: 5px;">Sản phẩm đã được thêm vào giỏ hàng thành công!</div>';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Chi tiết sản phẩm</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="container product-detail-page">
    <?php echo $message; ?>

    <div class="product-detail-wrapper">
        <div class="product-image-area">
        <img src="../images/<?php echo htmlspecialchars($product['image']); ?>" 
            alt="<?php echo htmlspecialchars($product['name']); ?>" class="main-product-img">
        </div>
        
        <div class="product-info-area">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="product-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>₫</p>
            
            <div class="product-description">
                <h2>Mô tả sản phẩm</h2>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
            
            <hr>

            <form id="add-to-cart-form" method="POST" action="add-to-cart.php">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                
                <div class="options-group">
                    <label for="size">Chọn Kích thước:</label>
                    <select name="size" id="size" required>
                        <?php if (empty($available_sizes)): ?>
                            <option value="">Không có tùy chọn</option>
                        <?php endif; ?>
                        <?php foreach ($available_sizes as $size_row): ?>
                            <option value="<?php echo htmlspecialchars($size_row['name']); ?>">
                                <?php echo htmlspecialchars($size_row['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="options-group">
                    <label for="flavor">Chọn Hương vị:</label>
                    <select name="flavor" id="flavor" required>
                        <?php if (empty($available_flavors)): ?>
                            <option value="">Không có tùy chọn</option>
                        <?php endif; ?>
                        <?php foreach ($available_flavors as $flavor_row): ?>
                            <option value="<?php echo htmlspecialchars($flavor_row['name']); ?>">
                                <?php echo htmlspecialchars($flavor_row['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="quantity-group">
                    <label for="quantity">Số lượng:</label>
                    <input type="number" name="quantity" id="quantity" value="1" min="1" required class="quantity-input">
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn-add-to-cart">
                        <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                    </button>
                    
                </div>
            </form>
            
            <div class="delivery-info">
                <i class="fas fa-truck"></i> Giao hàng trong vòng 2 giờ tại Hà Nội.
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
document.getElementById('add-to-cart-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch('add-to-cart.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Tải lại trang với tham số 'added=1' để hiển thị thông báo thành công
            window.location.href = 'product-detail.php?id=' + <?php echo $product_id; ?> + '&added=1';
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể thêm sản phẩm vào giỏ hàng.'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Đã xảy ra lỗi khi giao tiếp với máy chủ.');
    });
});
</script>

<style>
.product-detail-page {
    padding: 40px 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.product-detail-wrapper {
    display: flex;
    gap: 40px;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.product-image-area {
    flex: 0 0 500px;
    max-width: 50%;
}

.main-product-img {
    width: 100%;
    height: 500px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.product-info-area {
    flex-grow: 1;
}

.product-info-area h1 {
    font-size: 36px;
    font-weight: 800;
    margin-bottom: 10px;
    color: var(--text-black);
    font-family: 'Playfair Display', serif;
}

.product-price {
    font-size: 30px;
    font-weight: 800;
    color: var(--main-brown);
    margin-bottom: 20px;
}

.product-description h2 {
    font-size: 20px;
    font-weight: 700;
    margin-top: 20px;
    margin-bottom: 10px;
    color: #333;
}

.product-description p {
    line-height: 1.7;
    color: #555;
    font-size: 16px;
}

/* Form Options */
.options-group, .quantity-group {
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.options-group label, .quantity-group label {
    font-weight: 600;
    flex-basis: 120px;
    text-align: right;
}

select, .quantity-input {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
    flex-grow: 1;
    max-width: 200px;
}

.quantity-input {
    width: 80px;
    text-align: center;
}

.action-buttons {
    display: flex;
    gap: 12px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.btn-add-to-cart {
    background-color: var(--main-brown);
    color: var(--white);
    padding: 15px 30px;
    border: none;
    border-radius: 6px;
    font-weight: 700;
    font-size: 18px;
    cursor: pointer;
    transition: background-color 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-add-to-cart:hover {
    background-color: var(--brown-light);
}

.btn-checkout {
    background-color: #ffffff;
    color: var(--main-brown);
    padding: 15px 26px;
    border-radius: 6px;
    border: 1px solid var(--main-brown);
    font-weight: 700;
    font-size: 16px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background-color 0.3s, color 0.3s;
}

.btn-go-checkout:hover {
    background-color: var(--main-brown);
    color: var(--white);
}

.delivery-info {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px dashed #eee;
    color: #666;
    font-size: 14px;
    font-style: italic;
}
.delivery-info i {
    color: var(--main-brown);
}

/* Responsive */
@media (max-width: 992px) {
    .product-detail-wrapper {
        flex-direction: column;
    }
    .product-image-area {
        max-width: 100%;
        flex: 1 1 100%;
    }
    .main-product-img {
        height: auto;
    }
    .options-group label, .quantity-group label {
        text-align: left;
        flex-basis: auto;
    }
}
</style>

</body>
</html>