<?php
session_start();
include 'connect.php';
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

            <!-- PROMO ITEM -->
            <div class="promo-card">
                <div class="promo-badge">HOT</div>
                <div class="promo-image">🍰</div>
                <div class="promo-content">
                    <h3>Giảm 15% bánh sinh nhật</h3>
                    <p>
                        Áp dụng cho tất cả bánh sinh nhật size vừa & lớn.
                        Không áp dụng chung ưu đãi khác.
                    </p>
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
                    <p>
                        Miễn phí giao hàng nội thành Hà Nội
                        cho đơn từ 350.000đ.
                    </p>
                </div>
                <div class="promo-footer">
                    <a href="products.php">Đặt bánh ngay</a>
                </div>
            </div>

            <div class="promo-card">
                <div class="promo-badge">ƯU ĐÃI</div>
                <div class="promo-image">🍓</div>
                <div class="promo-content">
                    <h3>Tặng topping trái cây</h3>
                    <p>
                        Tặng topping hoa quả tươi cho bánh size lớn
                        trong khung giờ 9h–11h.
                    </p>
                </div>
                <div class="promo-footer">
                    <a href="products.php">Xem chi tiết</a>
                </div>
            </div>

            <div class="promo-card">
                <div class="promo-badge">VIP</div>
                <div class="promo-image">🎁</div>
                <div class="promo-content">
                    <h3>Khách thân thiết -10%</h3>
                    <p>
                        Áp dụng cho khách có từ 3 đơn hàng trở lên
                        trong vòng 30 ngày.
                    </p>
                </div>
                <div class="promo-footer">
                    <a href="products.php">Mua ngay</a>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
include 'footer.php';

if (isset($conn)) {
    mysqli_close($conn);
}
?>
