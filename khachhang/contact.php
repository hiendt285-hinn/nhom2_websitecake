<?php
session_start();
include 'connect.php';

$contactSuccess = '';
$contactError = '';

$contactDefaults = ['name' => '', 'email' => '', 'phone' => ''];
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $stmtUser = $conn->prepare("SELECT full_name, email, phone FROM users WHERE id = ? LIMIT 1");
    if ($stmtUser) {
        $stmtUser->bind_param('i', $uid);
        $stmtUser->execute();
        $resUser = $stmtUser->get_result();
        if ($resUser && $row = $resUser->fetch_assoc()) {
            $contactDefaults['name'] = $row['full_name'] ?? '';
            $contactDefaults['email'] = $row['email'] ?? '';
            $contactDefaults['phone'] = $row['phone'] ?? '';
        }
        $stmtUser->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['email'], $_POST['message'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message']);
    $userId = isset($_SESSION['user_id']) && $_SESSION['user_id'] !== '' ? (int)$_SESSION['user_id'] : null;

    if ($name !== '' && $email !== '' && $message !== '') {
        $stmt = $conn->prepare("INSERT INTO contacts (user_id, name, email, phone, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('issss', $userId, $name, $email, $phone, $message);
        if ($stmt->execute()) {
            $contactSuccess = 'Cảm ơn bạn! Chúng tôi đã nhận được liên hệ và sẽ phản hồi sớm.';
            $_POST = array();
        } else {
            $contactError = 'Gửi không thành công. Vui lòng thử lại.';
        }
        $stmt->close();
    } else {
        $contactError = 'Vui lòng điền đầy đủ Họ tên, Email và Nội dung.';
    }
}

$page_title = 'Liên hệ - Sweet Cake';
include 'header.php';
?>

<div class="content-page">
    <nav class="content-breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <span>Liên hệ</span>
    </nav>

    <div class="content-page-header">
        <h1>Liên hệ Sweet Cake</h1>
        <p>Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn — từ đặt bánh, tư vấn mẫu đến phản hồi dịch vụ</p>
    </div>

    <div class="contact-layout">
        <aside class="contact-info-card">
            <h2><i class="fas fa-store"></i> Thông tin liên hệ</h2>
            <p>Gọi hotline hoặc ghé cửa hàng gần nhất. Đội ngũ Sweet Cake phản hồi trong giờ hành chính.</p>

            <div class="contact-info-list">
                <div class="contact-info-item">
                    <i class="fas fa-phone-alt"></i>
                    <div>
                        <strong>Hotline</strong>
                        <a href="tel:1900636302">1900 636 302</a>
                    </div>
                </div>
                <div class="contact-info-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <strong>Email</strong>
                        <a href="mailto:sweetcake05@gmail.com">sweetcake05@gmail.com</a>
                    </div>
                </div>
                <div class="contact-info-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>Giờ mở cửa</strong>
                        <span>Thứ 2 – Chủ nhật | 8:00 – 21:00</span>
                    </div>
                </div>
            </div>

            <p class="contact-stores-title">Hệ thống cửa hàng</p>

            <div class="contact-store-item">
                <h3>Sweet Cake Hinnode</h3>
                <p><strong>Điện thoại:</strong> 0912 353 558 (Tư vấn)</p>
                <p><strong>Địa chỉ:</strong> 15 Kim Chung Di Trạch, Hoài Đức, Hà Nội</p>
                <a href="https://maps.app.goo.gl/gJgrzAVwzTYXMNsY9" target="_blank" rel="noopener" class="map-link">
                    <i class="fas fa-map-marker-alt"></i> Xem trên Google Maps
                </a>
            </div>

            <div class="contact-store-item">
                <h3>Sweet Cake An Bình City</h3>
                <p><strong>Điện thoại:</strong> 0385 215 962 (Tư vấn)</p>
                <p><strong>Địa chỉ:</strong> 232 Phạm Văn Đồng, Cổ Nhuế 1, Bắc Từ Liêm, Hà Nội</p>
                <a href="https://maps.app.goo.gl/EVWWVXXMaqsmYJSc9" target="_blank" rel="noopener" class="map-link">
                    <i class="fas fa-map-marker-alt"></i> Xem trên Google Maps
                </a>
            </div>
        </aside>

        <div class="contact-form-card">
            <h2><i class="fas fa-paper-plane"></i> Gửi lời nhắn</h2>

            <?php if ($contactSuccess): ?>
                <div class="content-alert-success" id="contactAlert">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($contactSuccess); ?>
                </div>
            <?php endif; ?>

            <?php if ($contactError): ?>
                <div class="content-alert-error" id="contactAlert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($contactError); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="" class="contact-form" id="contactForm">
                <div class="form-group">
                    <label for="name">Họ và tên</label>
                    <input type="text" id="name" name="name" placeholder="Nhập họ và tên" required
                           value="<?php echo htmlspecialchars(isset($_POST['name']) ? $_POST['name'] : ($contactSuccess ? '' : $contactDefaults['name'])); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Nhập email" required
                           value="<?php echo htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : ($contactSuccess ? '' : $contactDefaults['email'])); ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" placeholder="VD: 0901234567"
                           value="<?php echo htmlspecialchars(isset($_POST['phone']) ? $_POST['phone'] : ($contactSuccess ? '' : $contactDefaults['phone'])); ?>">
                </div>

                <div class="form-group">
                    <label for="message">Nội dung</label>
                    <textarea id="message" name="message" placeholder="Nhập nội dung liên hệ..." required><?php echo htmlspecialchars(isset($_POST['message']) ? $_POST['message'] : ($contactSuccess ? '' : '')); ?></textarea>
                </div>

                <button type="submit" class="btn-primary btn-submit" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> Gửi liên hệ
                </button>
            </form>
        </div>
    </div>

    <div class="contact-map">
        <iframe
            src="https://www.google.com/maps?q=Kim%20Chung%20Di%20Trach%20Hoai%20Duc%20Ha%20Noi&output=embed"
            allowfullscreen=""
            loading="lazy"
            title="Bản đồ Sweet Cake Hinnode">
        </iframe>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('contactForm');
    var submitBtn = document.getElementById('submitBtn');

    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner"></i> Đang gửi...';
        });
    }

    var alert = document.getElementById('contactAlert');
    if (alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function() { alert.style.display = 'none'; }, 500);
        }, 5000);
    }

    <?php if ($contactError): ?>
    var errAlert = document.getElementById('contactAlert');
    if (errAlert) {
        errAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    <?php endif; ?>
});
</script>

<?php
include 'footer.php';

if (isset($conn)) {
    mysqli_close($conn);
}
?>
