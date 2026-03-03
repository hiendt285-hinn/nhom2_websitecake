<?php
session_start();
include 'connect.php';

// Tạo bảng contacts nếu chưa có
$conn->query("CREATE TABLE IF NOT EXISTS contacts (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) DEFAULT NULL,
  name varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  phone varchar(50) DEFAULT NULL,
  message text NOT NULL,
  status varchar(50) DEFAULT 'new',
  created_at datetime DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY user_id (user_id),
  CONSTRAINT contacts_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$contactSuccess = '';
$contactError = '';

// Lấy thông tin user đăng nhập để tự động điền form (nếu có)
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
        } else {
            $contactError = 'Gửi không thành công. Vui lòng thử lại.';
        }
        $stmt->close();
    } else {
        $contactError = 'Vui lòng điền đầy đủ Họ tên, Email và Nội dung.';
    }
}

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

            <div class="info-item">
                <strong>📍 Địa chỉ:</strong><br>
                15 Kim Chung Di Trạch, Hoài Đức, Hà Nội
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
            <?php if ($contactSuccess): ?>
                <p style="color:#2e7d32; margin-bottom:15px;"><?php echo htmlspecialchars($contactSuccess); ?></p>
            <?php endif; ?>
            <?php if ($contactError): ?>
                <p style="color:#d32f2f; margin-bottom:15px;"><?php echo htmlspecialchars($contactError); ?></p>
            <?php endif; ?>
            <form method="post" action="">
                <input type="text" name="name" placeholder="Họ và tên" value="<?php echo htmlspecialchars(isset($_POST['name']) ? $_POST['name'] : $contactDefaults['name']); ?>" required>
                <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : $contactDefaults['email']); ?>" required>
                <input type="text" name="phone" placeholder="Số điện thoại" value="<?php echo htmlspecialchars(isset($_POST['phone']) ? $_POST['phone'] : $contactDefaults['phone']); ?>">
                <textarea name="message" placeholder="Nội dung liên hệ..." required><?php echo htmlspecialchars(isset($_POST['message']) ? $_POST['message'] : ''); ?></textarea>
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
