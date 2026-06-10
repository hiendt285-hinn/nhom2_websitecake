<?php

session_start();
require_once 'connect.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id === 0) {
    header('Location: products.php');
    exit;
}

$stmt = $conn->prepare("SELECT p.id, p.name, p.price, p.description, p.image, p.category_id, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.is_active = 1 LIMIT 1");
if (!$stmt) {
    die("Lỗi chuẩn bị truy vấn: " . $conn->error);
}
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: products.php');
    exit;
}

$sold_stmt = $conn->prepare("SELECT COALESCE(SUM(oi.quantity), 0) AS total_sold FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE oi.product_id = ? AND o.status IN ('completed', 'delivered')");
$total_sold = 0;
if ($sold_stmt) {
    $sold_stmt->bind_param("i", $product_id);
    $sold_stmt->execute();
    $sold_row = $sold_stmt->get_result()->fetch_assoc();
    $total_sold = (int)($sold_row['total_sold'] ?? 0);
    $sold_stmt->close();
}

$sizes_result = $conn->query("SELECT name FROM sizes ORDER BY name ASC");
$available_sizes = $sizes_result ? $sizes_result->fetch_all(MYSQLI_ASSOC) : [];

$flavors_result = $conn->query("SELECT name FROM flavors ORDER BY name ASC");
$available_flavors = $flavors_result ? $flavors_result->fetch_all(MYSQLI_ASSOC) : [];

$related_products = [];
if (!empty($product['category_id'])) {
    $related_stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE category_id = ? AND id != ? AND is_active = 1 ORDER BY id DESC LIMIT 4");
    if ($related_stmt) {
        $related_stmt->bind_param('ii', $product['category_id'], $product_id);
        $related_stmt->execute();
        $related_result = $related_stmt->get_result();
        while ($row = $related_result->fetch_assoc()) {
            $related_products[] = $row;
        }
        $related_stmt->close();
    }
}

$message = '';
if (isset($_GET['added']) && (int)$_GET['added'] === 1) {
    $message = 'Sản phẩm đã được thêm vào giỏ hàng thành công!';
}

$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$loginReturnUrl = 'product-detail.php?id=' . $product_id;

$page_title = htmlspecialchars($product['name']) . ' - Sweet Cake';
include 'header.php';
?>

<div class="content-page product-detail-page">
    <nav class="content-breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <a href="products.php">Sản phẩm</a>
        <?php if (!empty($product['category_name'])): ?>
        <span>/</span>
        <a href="products.php?category=<?php echo (int)$product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a>
        <?php endif; ?>
        <span>/</span>
        <span><?php echo htmlspecialchars($product['name']); ?></span>
    </nav>

    <?php if ($message): ?>
    <div class="content-alert-success" role="alert">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <div class="pd-layout">
        <aside class="pd-gallery">
            <div class="pd-image-frame">
                <img src="../images/<?php echo htmlspecialchars($product['image']); ?>"
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     class="pd-main-image">
            </div>
            <div class="pd-gallery-note">
                <i class="fas fa-camera"></i> Hình ảnh minh họa — bánh được làm tươi theo đơn
            </div>
        </aside>

        <div class="pd-main">
            <?php if (!empty($product['category_name'])): ?>
            <span class="pd-category-tag"><?php echo htmlspecialchars($product['category_name']); ?></span>
            <?php endif; ?>

            <h1 class="pd-title"><?php echo htmlspecialchars($product['name']); ?></h1>

            <div class="pd-price-row">
                <span class="pd-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>₫</span>
                <span class="pd-sold"><i class="fas fa-shopping-bag"></i> Đã bán <?php echo number_format($total_sold); ?></span>
            </div>

            <?php if (!empty(trim($product['description'] ?? ''))): ?>
            <p class="pd-description-compact"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            <?php endif; ?>

            <div class="pd-meta-tags">
                <span class="pd-meta-tag"><i class="fas fa-truck"></i> Giao 2 giờ</span>
                <span class="pd-meta-tag"><i class="fas fa-leaf"></i> Làm tươi</span>
                <span class="pd-meta-tag"><i class="fas fa-wallet"></i> COD / CK</span>
            </div>

            <div class="pd-purchase-card">
                <form id="add-to-cart-form" method="POST" action="add-to-cart.php" class="pd-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                    <div class="pd-form-grid">
                        <div class="pd-field">
                            <label for="size"><i class="fas fa-ruler"></i> Kích thước</label>
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

                        <div class="pd-field">
                            <label for="flavor"><i class="fas fa-ice-cream"></i> Hương vị</label>
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

                        <div class="pd-field pd-field-qty">
                            <label for="quantity"><i class="fas fa-hashtag"></i> SL</label>
                            <div class="pd-qty-stepper">
                                <button type="button" class="pd-qty-btn" id="qty-minus" aria-label="Giảm số lượng">−</button>
                                <input type="number" name="quantity" id="quantity" value="1" min="1" required class="pd-qty-input" aria-label="Số lượng">
                                <button type="button" class="pd-qty-btn" id="qty-plus" aria-label="Tăng số lượng">+</button>
                            </div>
                        </div>
                    </div>

                    <div class="pd-actions">
                        <?php if ($isLoggedIn): ?>
                        <button type="submit" class="btn-primary" name="action" value="cart">
                            <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                        </button>
                        <button type="button" class="btn-secondary" id="btn-order-now" title="Thêm vào giỏ và chuyển đến thanh toán">
                            <i class="fas fa-bolt"></i> Đặt hàng ngay
                        </button>
                        <?php else: ?>
                        <a href="login.php?return=<?php echo urlencode($loginReturnUrl); ?>" class="btn-primary">
                            <i class="fas fa-lock"></i> Đăng nhập để mua hàng
                        </a>
                        <a href="register.php" class="btn-secondary">
                            <i class="fas fa-user-plus"></i> Tạo tài khoản
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <p class="pd-delivery-strip">
        <i class="fas fa-shipping-fast"></i>
        Giao nhanh Hà Nội trong 2 giờ · Đặt trước 17h · COD hoặc chuyển khoản
    </p>

    <?php if (!empty(trim($product['description'] ?? ''))): ?>
    <section class="pd-description-full">
        <h2>Mô tả chi tiết</h2>
        <div class="pd-description-body"><?php echo nl2br(htmlspecialchars($product['description'])); ?></div>
    </section>
    <?php endif; ?>

    <?php if (!empty($related_products)): ?>
    <section class="pd-related">
        <div class="pd-related-header">
            <h2>Sản phẩm liên quan</h2>
            <?php if (!empty($product['category_name'])): ?>
            <a href="products.php?category=<?php echo (int)$product['category_id']; ?>">Xem thêm <?php echo htmlspecialchars($product['category_name']); ?> <i class="fas fa-arrow-right"></i></a>
            <?php endif; ?>
        </div>
        <div class="products-grid pd-related-grid">
            <?php foreach ($related_products as $rel): ?>
            <div class="product-card">
                <a href="product-detail.php?id=<?php echo $rel['id']; ?>">
                    <img src="../images/<?php echo htmlspecialchars($rel['image']); ?>" alt="<?php echo htmlspecialchars($rel['name']); ?>">
                </a>
                <div class="product-info">
                    <h3>
                        <a href="product-detail.php?id=<?php echo $rel['id']; ?>" style="color:inherit;text-decoration:none;">
                            <?php echo htmlspecialchars($rel['name']); ?>
                        </a>
                    </h3>
                    <div class="product-price"><?php echo number_format($rel['price'], 0, ',', '.'); ?>₫</div>
                    <div class="delivery-time"><i class="fas fa-truck"></i> Giao nhanh trong <span>2 giờ</span></div>
                    <div class="product-actions">
                        <a href="product-detail.php?id=<?php echo $rel['id']; ?>" class="btn-view"><i class="fas fa-eye"></i> Xem chi tiết</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<script>
(function() {
    var qtyInput = document.getElementById('quantity');
    var minusBtn = document.getElementById('qty-minus');
    var plusBtn = document.getElementById('qty-plus');

    minusBtn?.addEventListener('click', function() {
        var val = parseInt(qtyInput.value, 10) || 1;
        if (val > 1) qtyInput.value = val - 1;
    });

    plusBtn?.addEventListener('click', function() {
        var val = parseInt(qtyInput.value, 10) || 1;
        qtyInput.value = val + 1;
    });

    document.getElementById('add-to-cart-form')?.addEventListener('submit', function(e) {
        if (e.submitter && e.submitter.getAttribute('value') === 'checkout') return;
        e.preventDefault();
        var form = e.target;
        var formData = new FormData(form);

        fetch('add-to-cart.php', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.href = 'product-detail.php?id=' + <?php echo $product_id; ?> + '&added=1';
            } else if (data.login_url) {
                window.location.href = data.login_url;
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể thêm sản phẩm vào giỏ hàng.'));
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi giao tiếp với máy chủ.');
        });
    });

    document.getElementById('btn-order-now')?.addEventListener('click', function() {
        var form = document.getElementById('add-to-cart-form');
        if (!form) return;
        var formData = new FormData(form);
        formData.set('action', 'checkout');

        fetch('add-to-cart.php', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.href = 'checkout.php';
            } else if (data.login_url) {
                window.location.href = data.login_url;
            } else {
                alert('Lỗi: ' + (data.message || 'Không thể thêm sản phẩm vào giỏ hàng.'));
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi giao tiếp với máy chủ.');
        });
    });
})();
</script>

<?php include 'footer.php'; ?>
