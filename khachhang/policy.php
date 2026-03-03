<?php
// policy.php - Trang chính sách, dùng chung giao diện với các trang khác
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chính sách - Sweet Cake</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .policy-page {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px 40px;
            font-family: 'Open Sans', sans-serif;
        }
        .policy-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .policy-title {
            font-size: 32px;
            font-weight: 800;
            color: var(--text-black);
        }
        .policy-line {
            width: 120px;
            height: 4px;
            border-radius: 4px;
            background: var(--main-brown);
            margin: 12px auto 20px;
        }
        .policy-desc {
            max-width: 800px;
            margin: 0 auto;
            color: #666;
            line-height: 1.7;
            font-size: 16px;
        }
        .policy-block {
            margin-top: 40px;
        }
        .policy-block h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: var(--text-black);
        }
        .inner-policy-section {
            display: flex;
            flex-direction: row;
            gap: 30px;
            align-items: center;
            justify-content: center;
            text-align: left;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .policy-img {
            width: 50%;
            max-width: 500px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .policy-text {
            width: 50%;
            font-size: 15px;
            color: #444;
        }
        .policy-text ul {
            list-style: none;
            padding: 0;
            margin: 0;
            line-height: 1.6;
        }
        .policy-text ul ul {
            margin-left: 18px;
        }
        .policy-text li {
            margin-bottom: 8px;
        }
        .highlight {
            margin-top: 10px;
            font-weight: 600;
            color: #ff6b6b;
        }
        @media (max-width: 768px) {
            .inner-policy-section {
                flex-direction: column;
                text-align: center;
            }
            .policy-img,
            .policy-text {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="policy-page">
    <div class="policy-header">
        <h1 class="policy-title">Chính sách Sweet Cake</h1>
        <div class="policy-line"></div>
        <p class="policy-desc">
            Dưới đây là các chính sách về ship, thanh toán, hoàn tiền, chiết khấu và voucher feedback của Sweet Cake.
            Chúng tôi cam kết mang đến dịch vụ tốt nhất cho khách hàng.
        </p>
    </div>

    <div class="policy-block">
        <h2>1️⃣ Chính sách ship</h2>
        <div class="inner-policy-section">
            <img src="../images/Ship-COD-2025-02-01.webp" class="policy-img" alt="Chính sách ship">
            <div class="policy-text">
                <ul>
                    <li><b>Đơn ≥ 350.000đ:</b> Freeship nội thành</li>
                    <li><b>Đơn &lt; 350.000đ:</b> Phí ship 30.000đ</li>
                    <li><b>Nhận tại An Bình (Cầu Giấy – Hà Đông):</b> Freeship</li>
                    <li><b>Cơ sở khác:</b>
                        <ul>
                            <li>Đơn ≥ 350.000đ hoặc đặt trước 24h: Freeship</li>
                            <li>Còn lại: 30.000đ</li>
                        </ul>
                    </li>
                </ul>
                <p class="highlight">🎀 Lưu ý: Savor hỗ trợ tối đa 60k phí ship.</p>
            </div>
        </div>
    </div>

    <div class="policy-block">
        <h2>2️⃣ Hình thức thanh toán</h2>
        <div class="inner-policy-section">
            <img src="../images/shipperRow2.webp" class="policy-img" alt="Thanh toán">
            <div class="policy-text">
                <ul>
                    <li><b>Chuyển khoản:</b> Thanh toán 100% – không phát sinh thêm phí</li>
                    <li><b>COD:</b> Trả tiền sau khi nhận bánh – không cần đặt cọc</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="policy-block">
        <h2>3️⃣ Chính sách hoàn tiền</h2>
        <div class="inner-policy-section">
            <img src="../images/485796463_1056335373182140_6080756415570266761_n.jpg" class="policy-img" alt="Hoàn tiền">
            <div class="policy-text">
                <ul>
                    <li>Khiếu nại được xử lý trong <b>2 giờ</b> (9h–19h hàng ngày)</li>
                    <li>Hoàn tiền trong <b>48h</b> cho các trường hợp:
                        <ul>
                            <li>Thanh toán thừa hoặc sai</li>
                            <li>Chuyển nhầm tài khoản</li>
                            <li>Hủy gấp: hoàn 65%</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="policy-block">
        <h2>4️⃣ Chiết khấu mua số lượng lớn</h2>
        <div class="inner-policy-section">
            <img src="../images/discountPolicy.webp" class="policy-img" alt="Chiết khấu">
            <div class="policy-text">
                <ul>
                    <li>≥ 2.000.000đ → giảm 10%</li>
                    <li>≥ 5.000.000đ → giảm 15%</li>
                    <li>≥ 10.000.000đ → giảm 20%</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="policy-block">
        <h2>5️⃣ Voucher feedback</h2>
        <div class="inner-policy-section">
            <img src="../images/cake-feedback-voucher-15.webp" class="policy-img" alt="Feedback">
            <div class="policy-text">
                <ul>
                    <li>Gửi ảnh + review sản phẩm → nhận <b>Voucher 10% đơn tiếp theo</b>.</li>
                </ul>
                <p>⏳ Hạn sử dụng: 7 ngày.</p>
                <p>Có thể chuyển voucher cho bạn bè.</p>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>