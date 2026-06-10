<?php
session_start();
require_once 'connect.php';

$conn->query("CREATE TABLE IF NOT EXISTS promotions (
  id int(11) NOT NULL AUTO_INCREMENT,
  code varchar(50) NOT NULL,
  title varchar(255) DEFAULT NULL,
  discount_type enum('percent','fixed') NOT NULL DEFAULT 'percent',
  discount_value decimal(10,2) NOT NULL DEFAULT 0,
  min_order_amount decimal(10,2) DEFAULT 0,
  valid_from datetime DEFAULT NULL,
  valid_to datetime DEFAULT NULL,
  is_active tinyint(1) DEFAULT 1,
  created_at datetime DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS promotion_products (
  promotion_id int(11) NOT NULL,
  product_id int(11) NOT NULL,
  PRIMARY KEY (promotion_id, product_id),
  KEY product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$promos = [];
$res = $conn->query("SELECT code, title, discount_type, discount_value, min_order_amount, valid_from, valid_to FROM promotions WHERE is_active = 1 AND (valid_from IS NULL OR valid_from <= NOW()) AND (valid_to IS NULL OR valid_to >= NOW()) ORDER BY id");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $promos[] = $row;
    }
}
if (empty($promos)) {
    $conn->query("INSERT IGNORE INTO promotions (code, title, discount_type, discount_value, min_order_amount, is_active) VALUES
    ('SINHNHAT15', 'Giảm 15% đơn bánh sinh nhật', 'percent', 15, 0, 1),
    ('FREESHIP350', 'Freeship đơn từ 350K', 'fixed', 30000, 350000, 1),
    ('SWEET10', 'Giảm 10% đơn từ 200K', 'percent', 10, 200000, 1)");
    $res = $conn->query("SELECT code, title, discount_type, discount_value, min_order_amount FROM promotions WHERE is_active = 1 ORDER BY id");
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $promos[] = $row;
        }
    }
}

$page_title = 'Khuyến mãi - Sweet Cake';
include 'header.php';
?>

<div class="content-page">
    <nav class="content-breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <span>Khuyến mãi</span>
    </nav>

    <div class="content-page-header">
        <h1><i class="fas fa-tag"></i> Mã giảm giá</h1>
        <p>Sao chép mã và nhập khi thanh toán để nhận ưu đãi từ Sweet Cake</p>
    </div>

    <?php if (!empty($promos)): ?>
        <div class="promo-grid">
            <?php foreach ($promos as $p):
                $short = $p['discount_type'] === 'percent'
                    ? 'Giảm ' . (int)$p['discount_value'] . '%'
                    : 'Giảm ' . number_format((float)$p['discount_value'], 0, ',', '.') . '₫';
                if ((float)$p['min_order_amount'] > 0) {
                    $short .= ' · Đơn từ ' . number_format((float)$p['min_order_amount'], 0, ',', '.') . '₫';
                }
            ?>
            <article class="promo-card">
                <div class="promo-card-code">
                    <i class="fas fa-ticket-alt"></i>
                    <?php echo htmlspecialchars($p['code']); ?>
                </div>
                <h2 class="promo-card-title"><?php echo htmlspecialchars($p['title'] ?: $short); ?></h2>
                <?php if ($p['title']): ?>
                    <p class="promo-card-desc"><?php echo htmlspecialchars($short); ?></p>
                <?php endif; ?>
                <div class="promo-card-meta">
                    <span class="promo-badge"><i class="fas fa-percent"></i> <?php echo htmlspecialchars($short); ?></span>
                    <?php if (!empty($p['valid_to'])): ?>
                        <span class="promo-badge"><i class="fas fa-clock"></i> HSD: <?php echo date('d/m/Y', strtotime($p['valid_to'])); ?></span>
                    <?php endif; ?>
                </div>
                <div class="promo-card-actions">
                    <button type="button" class="btn-copy-promo-card" data-code="<?php echo htmlspecialchars($p['code'], ENT_QUOTES); ?>">
                        <i class="fas fa-copy"></i> Sao chép mã
                    </button>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="promo-empty">
            <i class="fas fa-tag"></i>
            <p>Hiện chưa có mã khuyến mãi. Vui lòng quay lại sau.</p>
        </div>
    <?php endif; ?>

    <div class="content-cta">
        <a href="products.php" class="btn-primary"><i class="fas fa-cake-candles"></i> Xem sản phẩm</a>
        <a href="cart.php" class="btn-secondary"><i class="fas fa-shopping-bag"></i> Giỏ hàng</a>
    </div>
</div>

<script>
document.querySelectorAll('.btn-copy-promo-card').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var code = this.getAttribute('data-code');
        if (!code) return;
        navigator.clipboard.writeText(code).then(function() {
            btn.classList.add('copied');
            btn.innerHTML = '<i class="fas fa-check"></i> Đã sao chép!';
            setTimeout(function() {
                btn.classList.remove('copied');
                btn.innerHTML = '<i class="fas fa-copy"></i> Sao chép mã';
            }, 1800);
        });
    });
});
</script>

<?php include 'footer.php'; ?>
