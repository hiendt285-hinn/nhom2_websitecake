<?php
session_start();
include 'connect.php';
include 'header.php';
?>
<section class="hero">
  <div class="hero-content">
    <h1>Mỗi miếng bánh, một câu chuyện hạnh phúc</h1>
    <p>BST Bánh Sinh Nhật</p>
    <a href="products.php" class="btn">Xem BST ngay</a>
  </div>
  <div class="hero-imgs">
    <img src="../images/AE2CDC01-6F2C-4BE5-AF72-3C24605224B9.png" alt="Bánh">
  </div>
</section>

<section class="why-choose">
  <div class="why-left">
    <h2>Tại sao bạn nên lựa chọn<br>bánh Sweet Cake</h2>
    <p>Hãy cùng tìm hiểu những đặc điểm nổi bật của Sweet Cake nhé!</p>
  </div>
  <div class="why-right">
    <div class="why-card">
      <div class="emoji">🍓🍇🥝</div>
      <p><strong>Đa dạng hoa quả tươi nhất HN – 10 loại:</strong> nhãn, vải, nho, dâu, bơ, xoài, cherry, kiwi, chanh leo, việt quất</p>
    </div>
    <div class="why-card">
      <div class="emoji">🛵</div>
      <p>Làm và ship hỏa tốc chỉ 1h từ khi đặt bánh. COD không cần cọc. Freeship từ 350k</p>
    </div>
    <div class="why-card">
      <div class="emoji">🎂</div>
      <p>Nhiều kích thước bánh cho 2-20 người. 150+ mẫu bánh sinh nhật, sự kiện, hộp thiếc</p>
    </div>
    <div class="why-card">
      <div class="emoji">✅</div>
      <p>Chứng nhận <strong>ISO 22000:2018</strong>, đảm bảo VSATTP. Tổng đài xử lý mọi vấn đề 7-23h</p>
    </div>
  </div>
</section>

<section class="box-collection">
  <h2 class="collection-title">Premium Box Collection<br><span>Open The Delight</span></h2>
  <p class="collection-desc">
    Khám phá bộ sưu tập bánh hộp cao cấp độc đáo từ Savor Cake với những tuyệt phẩm Tiramisu, Matcha và Chocolate. 
    Mỗi chiếc hộp tinh tế là lời mời gọi "open the delight" – mở ra niềm vui với từng tầng hương vị đậm đà, nơi rượu rum 
    Captain Morgan hòa quyện cùng các nguyên liệu thượng hạng, mang đến một trải nghiệm ẩm thực xa xỉ và đậm chất nghệ thuật.
  </p>

  <h3 class="box-subtitle">| Bánh hộp thiếc</h3>

  <div class="product-grid">
    <div class="product-card">
      <img src="../images/tiramisu-matcha-250g.webp" alt="Tiramisu Matcha">
      <h4>Tiramisu Matcha 250g</h4>
      <p>Bánh Tiramisu Matcha, bản giao hưởng tinh tế giữa lớp bánh lady finger nhúng nước trà xanh đậm vị kết hợp cùng rượu dark rum Captain Morgan, xen kẽ với lớp kem tiramisu mượt mà, phô mai mascarpone, phía trên phủ lớp bột matcha Haru Nhật Bản, mang đến hậu vị thanh mát, nhẹ nhàng nhưng đầy lôi cuốn</p>
      <div class="delivery-time">Giao được từ <span>15 giờ 30 hôm nay</span></div>
      <p class="price">189.000₫</p>
      <div class="actions">
      <a href="checkout.php" class="btn-order"> Đặt hàng </a>
      <button class="btn-cart" onclick="addToCart(<?php echo $row['id']; ?>)">
        <i class="fas fa-cart-plus"></i></button>
      </div>
    </div>

    <div class="product-card">
      <img src="../images/tiramisu-classic-250g.webp" alt="Tiramisu Classic">
      <h4>Tiramisu Classic 250g</h4>
      <p>Bánh Tiramisu Classic là sự kết hợp hài hòa giữa lớp bánh lady finger thấm đẫm cà phê và rượu dark rum Captain Morgan, xen kẽ với lớp kem tiramisu mượt mà làm từ trứng gà, phô mai mascarpone chuẩn Ý</p>
      <div class="delivery-time">Giao được từ <span>15 giờ 30 hôm nay</span></div>
      <p class="price">169.000₫</p>
      <div class="actions">
        <a href="checkout.php" class="btn-order">Đặt ngay</a>
        <a href="cart.php" class="btn-cart"><i class="fas fa-cart-plus"></i></a>
      </div>
    </div>

    <div class="product-card">
      <img src="../images/choco-dream-cake.webp" alt="Choco Dream Cake">
      <h4>Choco Dream Cake 315g</h4>
      <p>Choco Dream Cake là bản hòa tấu ngọt ngào của 5 tầng hương vị: cốt bánh chocolate ẩm mịn thấm đẫm nước cacao và rượu dark rum Captain Morgan, xen kẽ lớp caramel giòn cùng hạt óc chó và hạnh nhân, mousse chocolate mượt mà và ganache đậm vị. Phía trên là lớp chocolate nguyên chất phủ nhẹ bột cacao cao cấp – dùng thìa đập vỡ và xắn một miếng từ đáy lên để cảm nhận sự bùng nổ của từng tầng hương vị</p>
      <div class="delivery-time">Giao được từ <span>16 giờ 30 hôm nay</span></div>
      <p class="price">259.000₫</p>
      <div class="actions">
        <a href="checkout.php" class="btn-order">Đặt ngay</a>
        <a href="cart.php" class="btn-cart"><i class="fas fa-cart-plus"></i></a>
      </div>
    </div>

    <div class="product-card">
      <img src="../images/berry-lover-cake-500g-no-cherry.webp" alt="Berry Love">
      <h4>Berry Love 400g</h4>
      <p>Chiếc bánh là bản tình ca mùa hè gửi đến những tâm hồn yêu trái cây đỏ mọng. Với 5 tầng hương vị đan xen, Berry Lover Cake chinh phục vị giác bằng sự cân bằng tinh tế giữa vị ngọt, vị chua dịu và độ béo mịn hoàn hảo</p>
      <div class="delivery-time">Giao được từ <span>16 giờ 30 hôm nay</span></div>
      <p class="price">Giá: <strong>259.000 ₫</strong></p>
      <div class="actions">
        <a href="checkout.php" class="btn-order">Đặt ngay</a>
        <a href="cart.php" class="btn-cart"><i class="fas fa-cart-plus"></i></a>
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

<h3 class="box-subtitle">| Bánh kem hỏa tốc 1H</h3>

<div class="product-grid">
  <div class="product-card">
    <img src="../images/banh-red-velvet-sua-chua-viet-quat-201023.webp" alt="Bánh red velvet sữa chua việt quất">
    <h4>Bánh red velvet sữa chua việt quất</h4>
    <p>Bánh kem cốt red velvet tròn, kem sữa chua, bên trên trang trí 3 quả việt quất.</p>
    <div class="delivery-time">Giao được từ <span>15 giờ 30 hôm nay</span></div>
    <p class="price">189.000₫</p>
    <div class="actions">
      <a href="checkout.php" class="btn-order">Đặt ngay</a>
      <a href="cart.php" class="btn-cart"><i class="fas fa-cart-plus"></i></a>
    </div>
  </div>

  <div class="product-card">
    <img src="../images/banh-kem-bo-xoai-viet-quat.webp" alt="Bánh kem bơ xoài việt quất">
    <h4>Bánh kem bơ xoài việt quất</h4>
    <p>Cốt vani và kem bơ, trang trí thêm hoa quả tươi mát gồm xoài và việt quất trên mặt bánh, xen kẽ các bông kem bơ béo ngậy.</p>
    <div class="delivery-time">Giao được từ <span>15 giờ 30 hôm nay</span></div>
    <p class="price">169.000₫</p>
    <div class="actions">
      <a href="checkout.php" class="btn-order">Đặt ngay</a>
      <a href="cart.php" class="btn-cart"><i class="fas fa-cart-plus"></i></a>
    </div>
  </div>

  <div class="product-card">
    <img src="../images/banh-kem-triple-choco.webp" alt="Triple choco cake">
    <h4>Triple choco cake</h4>
    <p>Bánh 3 lần vị socola: cốt bánh socola, kem tươi vị socola, trang trí socola chip.</p>
    <div class="delivery-time">Giao được từ <span>16 giờ 30 hôm nay</span></div>
    <p class="price">150.000₫</p>
    <div class="actions">
      <a href="checkout.php" class="btn-order">Đặt ngay</a>
      <a href="cart.php" class="btn-cart"><i class="fas fa-cart-plus"></i></a>
    </div>
  </div>
</div>

<section class="mousse-section">
  <div class="mousse-container">
    <div class="mousse-images">
      <div class="green-circle"></div>
      <img src="../images/z7140806120150_8c57454f6c66ebc70683090fb1ada3d2.jpg" alt="Bánh mousse vàng" class="cake cake3">
    </div>

    <div class="mousse-content">
      <h2>
        <span class="title-green">Bộ sưu tập bánh lạnh</span><br>
        <span class="title-orange">Mousse</span>
      </h2>
      <p>
        Sweet cake ra mắt bộ sưu tập bánh mousse ngọt mềm, thơm lừng vị hoa quả/cà phê. 
        Bánh sử dụng nguyên liệu xịn xò, 100% kem tươi whipping nhập khẩu và hoa quả tươi, 
        phù hợp với những người sành ăn nhất.
      </p>
    </div>
  </div>
</section>

<h3 class="box-subtitle">| Bánh lạnh Mousse</h3>
<div class="product-grid">
  <div class="product-card">
    <img src="../images/mousse-sua-chua-viet-quat.webp" alt="Mousse sữa chua việt quất">
    <h4>Mousse sữa chua việt quất</h4>
    <p>Cốt vani xen kẽ các tầng bánh. Tầng dưới cùng là lớp mousse việt quất, tiếp theo là tầng mousse sữa chua và trên cùng là lớp thạch gelatin việt quất. Trang trí bằng việt quất, dâu tươi Đà Lạt, socola trắng và lá hương thảo</p>
    <div class="delivery-time">Giao được từ <span>15 giờ 30 hôm nay</span></div>
    <p class="price">220.000₫</p>
    <div class="actions">
      <a href="checkout.php" class="btn-order">Đặt ngay</a>
      <a href="cart.php" class="btn-cart"><i class="fas fa-cart-plus"></i></a>
    </div>
  </div>

  <div class="product-card">
    <img src="../images/mousse-xoai-original.webp" alt="Mousse Xoài">
    <h4>Mousse Xoài</h4>
    <p>Bánh mousse mang đậm hương vị xoài ngọt mát dễ chịu, kem tươi whipping cream kết hợp cùng sữa chua cốt vani, trang trí thêm xoài trên mặt bánh để thêm đậm vị cùng socola</p>
    <div class="delivery-time">Giao được từ <span>15 giờ 30 hôm nay</span></div>
    <p class="price">Giá: <strong>220.000 ₫</strong></p>
    <div class="actions">
      <a href="checkout.php" class="btn-order">Đặt ngay</a>
      <a href="cart.php" class="btn-cart"><i class="fas fa-cart-plus"></i></a>
    </div>
  </div>

  <div class="product-card">
    <img src="../images/mousse-bo-sua-dua.webp" alt="Mousse bơ sữa dừa">
    <h4>Mousse bơ sữa dừa</h4>
    <p>Chất bánh mousse mềm, mịn, ngọt bùi, kết hợp với 2 lớp mousse thơm ngậy của bơ và sữa dừa, bồng bềnh nhẹ nhàng như tan trong miệng. Trang trí thêm hoa quả bên trên, cùng một bông hoa nhỏ xinh ở giữa.</p>
    <div class="delivery-time">Giao được từ <span>16 giờ 30 hôm nay</span></div>
    <p class="price">230.000₫</p>
    <div class="actions">
      <a href="checkout.php" class="btn-order">Đặt ngay</a>
      <a href="cart.php" class="btn-cart"><i class="fas fa-cart-plus"></i></a>
  </div>
</div>

<!-- Phần giới thiệu cửa hàng -->
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
      <a href="#contact" class="btn-visit">Ghé thăm cửa hàng</a>
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
      Nếu trước đây Savor chỉ có nhân xoài tươi cho cả nhà lựa chọn, thì hiện tại Savor đã bổ sung thêm các loại nhân hoa quả khác, 
      đặc biệt phải kể: <strong>NHÂN XOÀI DỨA</strong> – Sự kết hợp hoàn hảo giữa vị chua của dứa cân bằng với vị ngọt của xoài 
      cùng hương thơm tươi mát, dịu nhẹ
      <br>~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      <br>Chỉ cần thêm một chút phí nho nhỏ tùy theo size bánh
      <br>Size mini: 10k/60g
      <br>Size nhỏ: 25k/150g
      <br>Size vừa: 40k/240g
      <br>~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      <br><em>Lưu ý:</em> Các mẫu bánh Mousse không được áp dụng thêm nhân hoa quả, 
      cả nhà nhắn Savor để được tư vấn các mẫu bánh nha 💛
    </p>
  </div>

  <button class="btn-order">ĐẶT BÁNH NGAY</button>
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

  <button class="order-btn">INBOX ĐẶT BÁNH</button>
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

<section class="store-system">
  <h2>Hệ thống cửa hàng</h2>
  <p class="subtitle">Cơ sở sẵn bánh</p>

  <div class="store-wrapper">
    <div class="store-card">
      <h3>Sweet Cake Hinnode</h3>
      <p><strong>Giờ mở cửa:</strong> 8h - 21h T2-CN</p>
      <p><strong>Điện thoại:</strong> 091235355887 (Tư vấn)</p>
      <p><strong>Địa chỉ:</strong> lk06.15,Kim Chung Di Trạch,Hoài Đức,Hà Nội</p>
    </div>

    <div class="store-card">
      <h3>Sweet Cake An Bình City</h3>
      <p><strong>Giờ mở cửa:</strong> 8h - 21h T2-CN</p>
      <p><strong>Điện thoại:</strong> 038521596256 (Tư vấn)</p>
      <p><strong>Địa chỉ:</strong> 232 Phạm Văn Đồng,Cổ Nhuế 1,BẮc Từ Liêm,Hà Nội </p>
    </div>
  </div>
</section>

<?php
// Include footer (đã điều chỉnh để phù hợp)
include 'footer.php';

// Đóng kết nối DB nếu dùng
if (isset($conn)) {
    mysqli_close($conn);
}
?>