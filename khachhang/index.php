<?php
session_start();
include 'connect.php';

$page_title = 'Sweet Cake - Bánh ngọt tươi mỗi ngày';
include 'header.php';

$category_icons = [
    'Bánh kem' => 'fa-birthday-cake',
    'Bánh ngọt' => 'fa-cookie-bite',
    'Bánh mousse' => 'fa-ice-cream',
    'Bánh hộp' => 'fa-box-open',
    'Bánh sinh nhật' => 'fa-gift',
    'default' => 'fa-cake-candles',
];

$categories_quick = [];
$cat_result = $conn->query("SELECT id, name FROM categories ORDER BY name LIMIT 8");
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories_quick[] = $row;
    }
}

function getProductsByCategory($conn, $category_id) {
    // Chỉ lấy các trường cần thiết cho card sản phẩm
    $stmt = $conn->prepare("SELECT id, name, price, image, short_description FROM products WHERE category_id = ? ORDER BY id DESC LIMIT 4");
    if (!$stmt) {
        // Xử lý lỗi prepare
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    return $stmt->get_result();
}


$box_collection_id = 7; 
$hoatoc_id = 3;          
$mousse_id = 5;          

// Sản phẩm nổi bật (is_featured = 1)
$featured_products = null;
$featured_stmt = $conn->prepare("SELECT id, name, price, image, short_description FROM products WHERE is_featured = 1 AND is_active = 1 ORDER BY id DESC LIMIT 8");
if ($featured_stmt) {
    $featured_stmt->execute();
    $featured_products = $featured_stmt->get_result();
    $featured_stmt->close();
}

// === THỰC HIỆN TRUY VẤN DỮ LIỆU ===
$box_products = getProductsByCategory($conn, $box_collection_id);
$hoatoc_products = getProductsByCategory($conn, $hoatoc_id);
$mousse_products = getProductsByCategory($conn, $mousse_id);

?>
<div class="content-wrapper page-container">

<section class="hero">
  <div class="hero-content">
    <span class="hero-badge"><i class="fas fa-star"></i> Bánh tươi mỗi ngày</span>
    <h1>Mỗi miếng bánh, một câu chuyện hạnh phúc</h1>
    <p>Khám phá bộ sưu tập bánh kem, mousse và bánh hộp cao cấp — đặt online nhanh chóng, giao tận nơi trong vòng 2 giờ tại Hà Nội.</p>
    <div class="hero-actions">
      <a href="products.php" class="btn-primary"><i class="fas fa-shopping-bag"></i> Mua ngay</a>
      <a href="promotion.php" class="btn-secondary">Xem khuyến mãi</a>
    </div>
  </div>
  <div class="hero-imgs">
    <img src="../images/AE2CDC01-6F2C-4BE5-AF72-3C24605224B9.png" alt="Bánh Sweet Cake">
  </div>
</section>

<?php if (!empty($categories_quick)): ?>
<section class="category-strip">
  <h2 class="section-heading">Danh mục sản phẩm</h2>
  <p class="section-subheading">Chọn loại bánh bạn yêu thích</p>
  <div class="category-grid">
    <?php foreach ($categories_quick as $cat):
        $icon = $category_icons['default'];
        foreach ($category_icons as $key => $fa) {
            if ($key !== 'default' && stripos($cat['name'], $key) !== false) {
                $icon = $fa;
                break;
            }
        }
    ?>
    <a href="products.php?category=<?php echo (int)$cat['id']; ?>" class="category-card">
      <span class="category-card-icon"><i class="fas <?php echo $icon; ?>"></i></span>
      <span><?php echo htmlspecialchars($cat['name']); ?></span>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="section-featured product-category-block">
    <div class="product-category-inner">
        <h2 class="product-category-title">Sản phẩm nổi bật</h2>
        <p class="section-featured-desc">Những món bánh được yêu thích nhất tại Sweet Cake</p>
        <div class="products-grid">
            <?php if ($featured_products && $featured_products->num_rows > 0): ?>
                <?php while($row = $featured_products->fetch_assoc()): ?>
                <div class="product-card">
                    <a href="product-detail.php?id=<?php echo $row['id']; ?>">
                        <img src="../images/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    </a>
                    <div class="product-info">
                        <h3>
                            <a href="product-detail.php?id=<?php echo $row['id']; ?>" style="color:inherit;text-decoration:none;">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </a>
                        </h3>
                        <div class="product-price"><?php echo number_format($row['price'], 0, ',', '.'); ?>₫</div>
                        <div class="delivery-time"><i class="fas fa-truck"></i> Giao nhanh trong <span>2 giờ</span></div>
                        <div class="product-actions">
                            <a href="product-detail.php?id=<?php echo $row['id']; ?>" class="btn-view"><i class="fas fa-eye"></i> Xem chi tiết</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="product-category-empty">Chưa có sản phẩm nổi bật. <a href="products.php">Xem tất cả sản phẩm</a></p>
            <?php endif; ?>
        </div>
        <?php if ($featured_products && $featured_products->num_rows > 0): ?>
        <div class="section-featured-cta">
            <a href="products.php" class="btn-outline">Xem tất cả sản phẩm</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="why-choose">
  <div class="why-header">
    <h2>Tại sao bạn nên lựa chọn bánh<br>Sweet Cake</h2>
    <p>Chúng tôi không chỉ bán bánh, chúng tôi mang đến trải nghiệm ngọt ngào được chăm chút từ nguyên liệu đến dịch vụ.</p>
  </div>
  <div class="why-grid">
    <div class="why-card">
      <div class="why-icon"><i class="fas fa-seedling"></i></div>
      <h3>Đa dạng hoa quả tươi nhất HN</h3>
      <p>Tuyển chọn hoa quả theo mùa từ những trang trại organic uy tín nhất.</p>
    </div>
    <div class="why-card">
      <div class="why-icon"><i class="fas fa-tachometer-alt"></i></div>
      <h3>Làm và ship hỏa tốc chỉ 1h</h3>
      <p>Quy trình chuyên nghiệp giúp bạn nhận bánh tươi mới trong thời gian ngắn nhất.</p>
    </div>
    <div class="why-card">
      <div class="why-icon"><i class="fas fa-users"></i></div>
      <h3>Nhiều kích thước bánh cho 2-20 người</h3>
      <p>Từ những bữa tiệc nhỏ ấm cúng đến các sự kiện lớn đông người tham dự.</p>
    </div>
    <div class="why-card">
      <div class="why-icon"><i class="fas fa-certificate"></i></div>
      <h3>Chứng nhận ISO 22000:2018</h3>
      <p>Cam kết tuyệt đối về an toàn vệ sinh thực phẩm cho sức khỏe gia đình bạn.</p>
    </div>
  </div>
</section>

<section class="box-collection">
    <h2 class="collection-title">Premium Box Collection<br><span>Open The Delight</span></h2>
    <p class="collection-desc">
        Khám phá bộ sưu tập bánh hộp cao cấp độc đáo từ Sweet Cake với những tuyệt phẩm Tiramisu, Matcha và Chocolate. 
        Mỗi chiếc hộp tinh tế là lời mời gọi "open the delight" – mở ra niềm vui với từng tầng hương vị đậm đà.
    </p>

    <div class="product-category-block">
        <div class="product-category-inner">
            <h2 class="product-category-title">Bánh hộp thiếc</h2>
            <div class="products-grid">
                <?php if ($box_products && $box_products->num_rows > 0): ?>
                    <?php while($row = $box_products->fetch_assoc()): ?>
                    <div class="product-card">
                        <a href="product-detail.php?id=<?php echo $row['id']; ?>">
                            <img src="../images/<?php echo htmlspecialchars($row['image']) ?>" alt="<?php echo htmlspecialchars($row['name']) ?>">
                        </a>
                        <div class="product-info">
                            <h3>
                                <a href="product-detail.php?id=<?php echo $row['id']; ?>" style="color:inherit;text-decoration:none;">
                                    <?php echo htmlspecialchars($row['name']) ?>
                                </a>
                            </h3>
                            <div class="product-price"><?php echo number_format($row['price'], 0, ',', '.') ?>₫</div>
                            <div class="delivery-time"><i class="fas fa-truck"></i> Giao nhanh trong <span>2 giờ</span></div>
                            <div class="product-actions">
                                <a href="product-detail.php?id=<?php echo $row['id']; ?>" class="btn-view"><i class="fas fa-eye"></i> Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="product-category-empty">Hiện không có sản phẩm trong bộ sưu tập này.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<section class="ship-fast">
    <h2>“Biệt đội” <span>Ship hỏa tốc</span></h2>
    <p class="ship-desc">
        Sweet Cake xây dựng đội ngũ Shipper chuyên nghiệp & thân thiện, 
        giao hàng nhanh chóng đến tay khách yêu trong vòng 1H
    </p>

    <div class="ship-gallery">
        <img src="../images/shipperRow1.webp" alt="Shipper giao bánh">
        <img src="../images/shipperRow4.webp" alt="Shipper tại tiệm bánh">
        <img src="../images/shipperRow3.webp" alt="Shipper tại tiệm bánh">
        <img src="../images/shipperRow2 (1).webp" alt="Shipper giao bánh cho khách">
    </div>
</section>

<section class="product-category-block">
    <div class="product-category-inner">
        <h2 class="product-category-title">Bánh kem hỏa tốc 1H</h2>
        <div class="products-grid">
            <?php if ($hoatoc_products && $hoatoc_products->num_rows > 0): ?>
                <?php while($row = $hoatoc_products->fetch_assoc()): ?>
                <div class="product-card">
                    <a href="product-detail.php?id=<?php echo $row['id']; ?>">
                        <img src="../images/<?php echo htmlspecialchars($row['image']) ?>" alt="<?php echo htmlspecialchars($row['name']) ?>">
                    </a>
                    <div class="product-info">
                        <h3>
                            <a href="product-detail.php?id=<?php echo $row['id']; ?>" style="color:inherit;text-decoration:none;">
                                <?php echo htmlspecialchars($row['name']) ?>
                            </a>
                        </h3>
                        <div class="product-price"><?php echo number_format($row['price'], 0, ',', '.') ?>₫</div>
                        <div class="delivery-time"><i class="fas fa-truck"></i> Giao nhanh trong <span>2 giờ</span></div>
                        <div class="product-actions">
                            <a href="product-detail.php?id=<?php echo $row['id']; ?>" class="btn-view"><i class="fas fa-eye"></i> Xem chi tiết</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="product-category-empty">Hiện không có sản phẩm bánh kem hỏa tốc nào.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="product-category-block">
    <div class="product-category-inner">
        <h2 class="product-category-title">Bánh lạnh Mousse</h2>
        <div class="products-grid">
            <?php if ($mousse_products && $mousse_products->num_rows > 0): ?>
                <?php while($row = $mousse_products->fetch_assoc()): ?>
                <div class="product-card">
                    <a href="product-detail.php?id=<?php echo $row['id']; ?>">
                        <img src="../images/<?php echo htmlspecialchars($row['image']) ?>" alt="<?php echo htmlspecialchars($row['name']) ?>">
                    </a>
                    <div class="product-info">
                        <h3>
                            <a href="product-detail.php?id=<?php echo $row['id']; ?>" style="color:inherit;text-decoration:none;">
                                <?php echo htmlspecialchars($row['name']) ?>
                            </a>
                        </h3>
                        <div class="product-price"><?php echo number_format($row['price'], 0, ',', '.') ?>₫</div>
                        <div class="delivery-time"><i class="fas fa-truck"></i> Giao nhanh trong <span>2 giờ</span></div>
                        <div class="product-actions">
                            <a href="product-detail.php?id=<?php echo $row['id']; ?>" class="btn-view"><i class="fas fa-eye"></i> Xem chi tiết</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="product-category-empty">Hiện không có sản phẩm bánh Mousse nào.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
<section class="store-intro">
  <div class="intro-container">
    <div class="intro-text">
      <h2>Chào mừng đến với <span>Sweet Cake Bakery</span> 🎂</h2>
      <p>
        Sweet Cake là tiệm bánh ngọt thủ công ra đời với mong muốn mang đến những chiếc bánh 
        tươi ngon nhất, được làm từ nguyên liệu tự nhiên, an toàn và tràn đầy yêu thương. 
        Chúng tôi tự hào là nơi lưu giữ hương vị ngọt ngào trong từng dịp đặc biệt của bạn – 
        từ sinh nhật, lễ kỷ niệm đến tiệc cưới sang trọng.
      </p>
      <p>
        Hãy ghé thăm cửa hàng của chúng tôi để tận mắt cảm nhận không gian ấm cúng, 
        phong cách trang trí ngọt ngào và đội ngũ nhân viên luôn sẵn sàng phục vụ bạn tận tâm nhất.
      </p>
      <a href="#store" class="btn-visit">Ghé thăm cửa hàng</a>
    </div>

    <div class="intro-images">
      <img src="../images/B42A1653-CA9F-4F0F-8883-A926AA4EFC7B.png" alt="Cửa hàng Sweet Cake bên ngoài">
      <img src="../images/87576454-E314-493D-A5C4-B98D9CE04B92.png" alt="Không gian bên trong cửa hàng">
    </div>
  </div>
</section>



<section class="fruit-section">
  <h2>Thêm nhân hoa quả</h2>

  <div class="fruit-images">
    <img src="../images/nhan_xoai_dua2.webp" alt="Nhân xoài dứa">
    <img src="../images/nhan_xoai_dua3.webp" alt="Bánh nhân hoa quả">
    <img src="../images/nhan_xoai_dua1.webp" alt="Cắt bánh hoa quả">
  </div>

  <div class="fruit-text">
    <p>
      Sweet Cake bổ sung thêm các loại nhân hoa quả tươi, đặc biệt <strong>Nhân Xoài Dứa</strong> — 
      sự kết hợp hoàn hảo giữa vị chua của dứa và vị ngọt của xoài cùng hương thơm tươi mát.
    </p>
    <ul class="fruit-pricing">
      <li>Size mini: 10.000₫ / 60g</li>
      <li>Size nhỏ: 25.000₫ / 150g</li>
      <li>Size vừa: 40.000₫ / 240g</li>
    </ul>
    <p><em>Lưu ý:</em> Các mẫu bánh Mousse không áp dụng thêm nhân hoa quả. Liên hệ Sweet Cake để được tư vấn.</p>
  </div>

  <a href="products.php" class="btn-order">Đặt bánh ngay</a>
</section>


<section class="cake-options">
  <h2>Lựa chọn kiểu bánh</h2>
  <div class="cake-type">
    <div class="type-card">
      <img src="../images/quoc-te-gia-dinh-1 (1).webp" alt="Bánh Vẽ">
      <p class="type-number">01</p>
      <h3>Bánh Vẽ</h3>
    </div>
    <div class="type-card">
      <img src="../images/quoc-te-gia-dinh-2.webp" alt="Bánh Order">
      <p class="type-number">02</p>
      <h3>Bánh Order</h3>
    </div>
  </div>
   
  <h2>Lựa chọn vị bánh</h2>
  <div class="cake-flavors">
    <div class="flavor">
      🫐<p>Cốt Vani + Mứt Việt Quất</p>
    </div>
    <div class="flavor">
      🍓<p>Cốt Vani + Mứt Dâu Tây</p>
    </div>
    <div class="flavor">
      🥭<p>Cốt Vani + Mứt Xoài (kèm xoài tươi)</p>
    </div>
    <div class="flavor">
      🍒<p>Cốt Vani + Mứt Cherry</p>
    </div>
    <div class="flavor">
      🍫<p>Cốt Socola + Kem Socola</p>
    </div>
    <div class="flavor">
      ☕<p>Cốt Cà Phê + Kem Cà Phê</p>
    </div>
    <div class="flavor">
      🍃<p>Cốt Trà Xanh + Kem Trà Xanh</p>
    </div>
  </div>
</section>

<section class="cake-size">
  <h2>Lựa chọn size bánh</h2>
  <div class="sizes">
    <div class="size-card">
      <img src="../images/mini-sz.webp" alt="Size mini">
      <p><strong>SIZE MINI</strong><br>13cm x 6cm</p>
    </div>
    <div class="size-card">
      <img src="../images/nho-sz.webp" alt="Size nhỏ">
      <p><strong>SIZE NHỎ</strong><br>17cm x 8cm</p>
    </div>
    <div class="size-card">
      <img src="../images/vua-sz.webp" alt="Size vừa">
      <p><strong>SIZE VỪA</strong><br>21cm x 8cm</p>
    </div>
  </div>

  <a href="contact.php" class="order-btn btn-primary">Liên hệ đặt bánh</a>
</section>

<section class="policy-section">
  <h2>Chính sách ship & bán hàng</h2>
  <p class="subtext">Bấm để xem thêm chi tiết <a href="policy.php">TẠI ĐÂY</a></p>

  <div class="policy-container">
    <div class="policy-card">
      <img src="../images/cake-feedback-voucher-15.webp" alt="Đặt hàng COD">
    </div>
    <div class="policy-card">
      <img src="../images/Ship-COD-2025-02-01.webp" alt="Chính sách chiết khấu">
    </div>
  </div>
</section>

<section class="store-system" id="store">
  <h2>Hệ thống cửa hàng</h2>
  <p class="subtitle">Cơ sở sẵn bánh</p>

  <div class="store-wrapper" >
    <div class="store-card">
      <h3>Sweet Cake Hinnode</h3>
      <p><strong>Giờ mở cửa:</strong> 8h - 21h T2-CN</p>
      <p><strong>Điện thoại:</strong> 091235355887 (Tư vấn)</p>
      <p><strong>Địa chỉ:</strong> 15,Kim Chung Di Trạch,Hoài Đức,Hà Nội</p>
      <p class="map-link-container">
        <a href="https://maps.app.goo.gl/gJgrzAVwzTYXMNsY9" target="_blank" class="map-link">
            📍 Xem trên Google Maps
        </a>
      </p>
    </div>

    <div class="store-card">
      <h3>Sweet Cake An Bình City</h3>
      <p><strong>Giờ mở cửa:</strong> 8h - 21h T2-CN</p>
      <p><strong>Điện thoại:</strong> 038521596256 (Tư vấn)</p>
      <p><strong>Địa chỉ:</strong> 232 Phạm Văn Đồng,Cổ Nhuế 1,BẮc Từ Liêm,Hà Nội </p>
      <p class="map-link-container">
        <a href="https://maps.app.goo.gl/EVWWVXXMaqsmYJSc9" target="_blank" class="map-link">
            📍 Xem trên Google Maps
        </a>
      </p>
    </div>
  </div>
</section>

<section class="cta-banner">
  <h2>Sẵn sàng đặt bánh cho dịp đặc biệt?</h2>
  <p>Chọn sản phẩm yêu thích, thêm vào giỏ và thanh toán chỉ vài bước — Sweet Cake giao tận nơi cho bạn.</p>
  <a href="products.php" class="btn-secondary"><i class="fas fa-arrow-right"></i> Bắt đầu mua sắm</a>
</section>

</div>

<script>
    function addToCart(productId) { 
        window.location.href = 'product-detail.php?id=' + productId;
    }
</script>

<?php 
include 'footer.php'; 

// Đóng kết nối DB
if (isset($conn)) {
    mysqli_close($conn);
}
?>