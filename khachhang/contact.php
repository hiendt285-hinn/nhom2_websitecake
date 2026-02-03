<?php
session_start();
include 'connect.php';
include 'header.php';
?>

<style>
.contact-wrapper {
    background: #F5F1E8;
    padding: 60px 20px;
}

.contact-container {
    max-width: 1100px;
    margin: auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
}

.contact-info h2,
.contact-form h2 {
    color: #8B6F47;
    margin-bottom: 15px;
}

.contact-info p {
    line-height: 1.7;
    margin-bottom: 10px;
    color: #333;
}

.contact-box {
    background: #fff;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
}

.contact-info .info-item {
    margin-bottom: 15px;
}

.contact-info strong {
    color: #8B6F47;
}

.contact-form input,
.contact-form textarea {
    width: 100%;
    padding: 12px 14px;
    margin-bottom: 15px;
    border-radius: 10px;
    border: 1px solid #ddd;
    font-size: 14px;
}

.contact-form textarea {
    resize: none;
    height: 120px;
}

.contact-form button {
    background: #8B6F47;
    color: #fff;
    border: none;
    padding: 12px 30px;
    border-radius: 999px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: .3s;
}

.contact-form button:hover {
    background: #735c3a;
}

.map-section {
    margin-top: 50px;
    border-radius: 16px;
    overflow: hidden;
}

@media(max-width: 900px) {
    .contact-container {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="contact-wrapper">
    <div class="contact-container">

        <!-- THÔNG TIN LIÊN HỆ -->
        <div class="contact-info contact-box">
            <h2>Liên hệ Sweet Cake 🍰</h2>
            <p>
                Sweet Cake luôn sẵn sàng lắng nghe và hỗ trợ bạn trong mọi thắc mắc,
                từ đặt bánh, tư vấn mẫu cho đến phản hồi dịch vụ.
            </p>

            <div class="#store-card">
            </div>

            <div class="info-item">
                <strong>📞 Hotline:</strong><br>
                0912 353 558 (8h – 21h)
            </div>

            <div class="info-item">
                <strong>📧 Email:</strong><br>
                sweetcakebakery@gmail.com
            </div>

            <div class="info-item">
                <strong>⏰ Giờ mở cửa:</strong><br>
                Thứ 2 – Chủ nhật | 8:00 – 21:00
            </div>
        </div>

        <!-- FORM LIÊN HỆ -->
        <div class="contact-form contact-box">
            <h2>Gửi lời nhắn cho chúng tôi 💌</h2>

            <form method="post" action="#">
                <input type="text" name="name" placeholder="Họ và tên" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="phone" placeholder="Số điện thoại">
                <textarea name="message" placeholder="Nội dung liên hệ..." required></textarea>
                <button type="submit">Gửi liên hệ</button>
            </form>
        </div>

    </div>

    <!-- GOOGLE MAP -->
    <div class="map-section">
        <iframe 
            src="https://www.google.com/maps?q=Kim%20Chung%20Di%20Trach%20Hoai%20Duc%20Ha%20Noi&output=embed"
            width="100%" 
            height="380" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy">
        </iframe>
    </div>
</div>

<?php
include 'footer.php';

if (isset($conn)) {
    mysqli_close($conn);
}
?>
