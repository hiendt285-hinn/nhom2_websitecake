<?php include 'header.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chính sách</title>

    <style>

body {
    font-family: "Poppins", sans-serif;
    margin: 0;
    background: #faf6ef; 
    color: #333;
}


.policy-section {
    padding: 60px 20px;
    text-align: center;
}


.policy-title {
    font-size: 42px;
    font-weight: 700;
    color: #5d9159;
}


.line {
    width: 140px;
    height: 6px;
    border-radius: 6px;
    background: #5d9159;
    margin: 10px auto 35px;
}


.policy-desc {
    max-width: 900px;
    font-size: 20px;
    margin: 0 auto 50px;
    color: #6d6d6d;
    line-height: 1.6;
}

/* Khung chính sách */
.policy-container {
    max-width: 1200px;
    margin: 0 auto;
}

/* Tiêu đề nhỏ */
.policy-section h2 {
    font-size: 32px;
    color: #5d9159;
    margin-bottom: 25px;
}


.policy-img {
    width: 100%;
    max-width: 520px;
    border-radius: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}


.inner-policy-section {
    display: flex;
    flex-direction: row;
    gap: 40px;
    align-items: center;
    justify-content: center;
    text-align: left;
    margin-bottom: 50px;
}

.policy-text {
    width: 50%;
    font-size: 18px;
    color: #444;
}

.inner-policy-section .policy-img {
    width: 50%;
}


.policy-section ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
    line-height: 1.6;
}

.policy-section ul li {
    font-size: 18px;
    margin-bottom: 12px;
}

.policy-section ul ul {
    margin-left: 20px;
}

.highlight {
    font-size: 18px;
    font-weight: bold;
    color: #ff6b6b;
    margin-top: 15px;
}


@media (max-width: 768px) {
    .inner-policy-section {
        flex-direction: column;
        text-align: center;
    }
    .inner-policy-section .policy-img,
    .policy-text {
        width: 100%;
    }
}

    </style>
</head>
<body>
    <section class="policy-section" id="policy">
        <h2 class="policy-title">Chính sách Sweet Cake</h2>
        <div class="line"></div>
        <p class="policy-desc">
            Dưới đây là các chính sách về ship, thanh toán, hoàn tiền, chiết khấu và voucher feedback của Sweet Cake. Chúng tôi cam kết mang đến dịch vụ tốt nhất cho khách hàng.
        </p>
        <div class="policy-container">
             <!-- Chính sách Ship -->
            <div class="policy-section">
                <h2>1️⃣ CHÍNH SÁCH SHIP</h2>
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
            <!-- Thanh toán -->
            <div class="policy-section">
                <h2>2️⃣ HÌNH THỨC THANH TOÁN</h2>
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
            <!-- Hoàn tiền -->
            <div class="policy-section">
                <h2>3️⃣ CHÍNH SÁCH HOÀN TIỀN</h2>
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
            <!-- Chiết khấu -->
            <div class="policy-section">
                <h2>4️⃣ CHIẾT KHẤU MUA SỐ LƯỢNG LỚN</h2>
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
            <!-- Feedback -->
            <div class="policy-section">
                <h2>5️⃣ VOUCHER FEEDBACK</h2>
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
    </section>
    <?php include 'footer.php'; ?>
</body>
</html>