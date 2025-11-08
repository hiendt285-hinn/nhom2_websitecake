<?php
// footer.php - Phần footer chung cho website (dựa trên HTML cung cấp)
?>

<footer class="footer">
    <div class="footer-container">
        <!-- Cột 1: Thông tin liên hệ -->
        <div class="footer-column">
            <h3>Thông tin liên hệ</h3>
            <p>Hotline tư vấn: <a href="tel:1900779907">1900 779 907</a></p>
            <p>Hotline khiếu nại: <a href="tel:0364907773">0364 907 773</a></p>
            <p>Tài trợ thiện nguyện: <a href="tel:0934664262">093 466 4262</a></p>
            <p>Email: <a href="mailto:support@sweetcake.vn">support@sweetcake.vn</a></p>
        </div>

        <!-- Cột 2: Our brands -->
        <div class="footer-column center">
            <h3>Our brands</h3>
            <div class="brand-logos">
                <img src="images/35-mau-thiet-ke-logo-tiem-banh-dep-5.jpg" alt="Abby logo">
            </div>
            <p class="press-text">Báo chí nói gì về Savor Cake ?</p>
        </div>

        <img src="images/bo-cong-thuong.webp" alt="Đã thông báo Bộ Công Thương" class="gov-logo">
    </div>
</footer>

<footer>
    <p>© <?php echo date("Y"); ?> Sweet Cake | Thiết kế bởi bạn 🍰</p> <!-- Sử dụng date("Y") cho năm động -->
</footer>

<!-- Link đến file JS nếu cần -->
<script src="assets/script.js"></script> <!-- Nếu có JS, thêm vào đây -->

</body>
</html>