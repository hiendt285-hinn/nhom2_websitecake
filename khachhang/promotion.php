<?php
session_start();
include 'connect.php';

// Tạo bảng promotions nếu chưa có và lấy danh sách mã khuyến mãi đang áp dụng
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
$res = $conn->query("SELECT code, title, discount_type, discount_value, min_order_amount FROM promotions WHERE is_active = 1 AND (valid_from IS NULL OR valid_from <= NOW()) AND (valid_to IS NULL OR valid_to >= NOW()) ORDER BY id");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $promos[] = $row;
    }
}
// Nếu chưa có mã nào, thêm vài mã mặc định
if (empty($promos)) {
    $conn->query("INSERT IGNORE INTO promotions (code, title, discount_type, discount_value, min_order_amount, is_active) VALUES
    ('SINHNHAT15', 'Giảm 15% bánh sinh nhật', 'percent', 15, 0, 1),
    ('FREESHIP350', 'Freeship đơn từ 350K', 'fixed', 30000, 350000, 1),
    ('SWEET10', 'Giảm 10% đơn hàng', 'percent', 10, 200000, 1)");
    $res = $conn->query("SELECT code, title, discount_type, discount_value, min_order_amount FROM promotions WHERE is_active = 1 ORDER BY id");
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) { $promos[] = $row; }
    }
}

include 'header.php';
?>

<style>
.promo-wrapper {
    background: #F5F1E8;
    padding: 60px 20px;
}

.promo-container {
    max-width: 1200px;
    margin: auto;
}

.promo-header {
    text-align: center;
    margin-bottom: 50px;
}

.promo-header h1 {
    color: #8B6F47;
    font-size: 36px;
    margin-bottom: 10px;
}

.promo-header p {
    color: #555;
    font-size: 16px;
}

.promo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
}

.promo-card {
    background: #fff;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    transition: transform .3s;
}

.promo-card:hover {
    transform: translateY(-6px);
}

.promo-badge {
    background: #d32f2f;
    color: #fff;
    padding: 6px 14px;
    position: absolute;
    top: 15px;
    left: 15px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
}

.promo-image {
    height: 200px;
    background: #eee;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 60px;
}

.promo-content {
    padding: 20px;
}

.promo-content h3 {
    color: #8B6F47;
    margin-bottom: 10px;
}

.promo-content p {
    font-size: 14px;
    color: #555;
    line-height: 1.6;
}

.promo-footer {
    padding: 15px 20px 25px;
}

.promo-footer a {
    display: inline-block;
    background: #8B6F47;
    color: #fff;
    padding: 10px 26px;
    border-radius: 999px;
    text-decoration: none;
    font-weight: 600;
    transition: .3s;
}

.promo-footer a:hover {
    background: #735c3a;
}

@media(max-width:768px){
    .promo-header h1 { font-size: 28px; }
}
</style>

<div class="promo-wrapper">
    <div class="promo-container">

        <!-- HEADER -->
        <div class="promo-header">
            <h1>🎉 Khuyến mãi Sweet Cake</h1>
            <p>Ưu đãi ngọt ngào – Trao yêu thương trọn vẹn</p>
        </div>

        <!-- PROMO LIST -->
        <div class="promo-grid">
            <?php foreach ($promos as $index => $p):
                $badges = ['HOT', 'NEW', 'ƯU ĐÃI', 'VIP'];
                $icons = ['🍰', '🎂', '🍓', '🎁'];
                $badge = $badges[$index % 4];
                $icon = $icons[$index % 4];
                $desc = $p['discount_type'] === 'percent'
                    ? 'Giảm ' . (int)$p['discount_value'] . '% đơn hàng.'
                    : 'Giảm ' . number_format((float)$p['discount_value'], 0, ',', '.') . '₫.';
                if ((float)$p['min_order_amount'] > 0) {
                    $desc .= ' Đơn tối thiểu ' . number_format((float)$p['min_order_amount'], 0, ',', '.') . '₫.';
                }
            ?>
            <div class="promo-card">
                <div class="promo-badge"><?php echo $badge; ?></div>
                <div class="promo-image"><?php echo $icon; ?></div>
                <div class="promo-content">
                    <h3><?php echo htmlspecialchars($p['title'] ?: $p['code']); ?></h3>
                    <p><?php echo htmlspecialchars($desc); ?></p>
                    <p style="margin-top:10px;"><strong>Mã: <code class="promo-code" style="background:#f0f0f0; padding:4px 10px; border-radius:6px;"><?php echo htmlspecialchars($p['code']); ?></code></strong> — Nhập mã khi thanh toán để áp dụng.</p>
                </div>
                <div class="promo-footer">
                    <a href="products.php">Xem sản phẩm</a>
                    <a href="cart.php" style="margin-left:10px;">Đặt hàng ngay</a>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($promos)): ?>
            <!-- Fallback khi chưa có mã trong DB -->
            <div class="promo-card">
                <div class="promo-badge">HOT</div>
                <div class="promo-image">🍰</div>
                <div class="promo-content">
                    <h3>Giảm 15% bánh sinh nhật</h3>
                    <p>Áp dụng cho tất cả bánh sinh nhật size vừa & lớn. Nhập mã khi thanh toán.</p>
                </div>
                <div class="promo-footer">
                    <a href="products.php">Xem sản phẩm</a>
                </div>
            </div>
            <div class="promo-card">
                <div class="promo-badge">NEW</div>
                <div class="promo-image">🎂</div>
                <div class="promo-content">
                    <h3>Freeship đơn từ 350K</h3>
                    <p>Miễn phí giao hàng nội thành Hà Nội cho đơn từ 350.000đ.</p>
                </div>
                <div class="promo-footer">
                    <a href="products.php">Đặt bánh ngay</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
include 'footer.php';

if (isset($conn)) {
    mysqli_close($conn);
}
?>
