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
        while ($row = $res->fetch_assoc()) { $promos[] = $row; }
    }
}

include 'header.php';
?>
<link rel="stylesheet" href="style.css">
<style>
.promo-page { max-width: 900px; margin: 30px auto; padding: 0 20px 50px; }
.promo-page h1 { font-size: 28px; color: #5D4037; margin-bottom: 8px; }
.promo-page .subtitle { color: #666; font-size: 15px; margin-bottom: 28px; }
.promo-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); overflow: hidden; }
.promo-table th, .promo-table td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #eee; }
.promo-table th { background: #f8f6f2; font-weight: 600; color: #5D4037; font-size: 13px; }
.promo-table tr:last-child td { border-bottom: none; }
.promo-table tr:hover td { background: #fafaf8; }
.promo-code-cell { font-family: 'Consolas', monospace; font-weight: 700; font-size: 15px; color: #5D4037; }
.promo-desc { font-size: 14px; color: #555; }
.promo-desc small { color: #888; }
.btn-copy { padding: 6px 12px; font-size: 12px; border-radius: 6px; border: 1px solid #5D4037; background: #fff; color: #5D4037; cursor: pointer; font-weight: 600; }
.btn-copy:hover { background: #5D4037; color: #fff; }
.promo-empty { text-align: center; padding: 48px 20px; color: #888; background: #fafaf8; border-radius: 12px; }
.promo-cta { margin-top: 24px; text-align: center; }
.promo-cta a { display: inline-block; padding: 12px 24px; background: #5D4037; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; }
.promo-cta a:hover { background: #4a3329; }
</style>

<div class="promo-page">
    <h1><i class="fas fa-tag"></i> Mã giảm giá</h1>
    <p class="subtitle">Chọn mã khi thanh toán để được ưu đãi</p>

    <?php if (!empty($promos)): ?>
        <table class="promo-table">
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Ưu đãi</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($promos as $p):
                    $short = $p['discount_type'] === 'percent'
                        ? 'Giảm ' . (int)$p['discount_value'] . '%'
                        : 'Giảm ' . number_format((float)$p['discount_value'], 0, ',', '.') . '₫';
                    if ((float)$p['min_order_amount'] > 0) {
                        $short .= ' (đơn từ ' . number_format((float)$p['min_order_amount'], 0, ',', '.') . '₫)';
                    }
                ?>
                <tr>
                    <td class="promo-code-cell"><?php echo htmlspecialchars($p['code']); ?></td>
                    <td class="promo-desc">
                        <?php echo htmlspecialchars($p['title'] ?: $short); ?>
                        <?php if ($p['title']): ?><br><small><?php echo htmlspecialchars($short); ?></small><?php endif; ?>
                    </td>
                    <td>
                        <button type="button" class="btn-copy" onclick="copyCode('<?php echo htmlspecialchars($p['code'], ENT_QUOTES); ?>')"><i class="fas fa-copy"></i> Sao chép</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="promo-empty">
            <p>Hiện chưa có mã khuyến mãi. Vui lòng quay lại sau.</p>
        </div>
    <?php endif; ?>

    <div class="promo-cta">
        <a href="products.php"><i class="fas fa-cake-candles"></i> Xem sản phẩm</a>
        <a href="cart.php" style="margin-left:12px; background:#8B7355;"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a>
    </div>
</div>

<script>
function copyCode(code) {
    navigator.clipboard.writeText(code).then(function() {
        var btn = event.target.closest('.btn-copy');
        if (btn) { btn.textContent = 'Đã copy!'; setTimeout(function(){ btn.innerHTML = '<i class="fas fa-copy"></i> Sao chép'; }, 1500); }
    });
}
</script>

<?php include 'footer.php'; ?>
