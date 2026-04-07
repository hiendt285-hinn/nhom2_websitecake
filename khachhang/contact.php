<?php
session_start();
include 'connect.php';

// Khởi tạo biến
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

// Xử lý form khi submit
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
            // Reset form bằng cách không gán giá trị POST
            $_POST = array();
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
/* Main Styles */
.contact-wrapper {
    background: linear-gradient(135deg, #F5F1E8 0%, #fef9f0 100%);
    padding: 60px 20px;
    min-height: 600px;
}

.contact-container {
    max-width: 1100px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
}

/* Card Styles */
.contact-box {
    background: #fff;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(139, 69, 19, 0.08);
    border: 1px solid rgba(139, 69, 19, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.contact-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(139, 69, 19, 0.12);
}

/* Headers */
.contact-info h2,
.contact-form h2 {
    color: #8B4513;
    margin-bottom: 20px;
    font-size: 28px;
    font-weight: 700;
    font-family: 'Playfair Display', serif;
    position: relative;
    padding-bottom: 10px;
}

.contact-info h2:after,
.contact-form h2:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 3px;
    background: linear-gradient(to right, #8B4513, #D2691E);
    border-radius: 3px;
}

.contact-info p {
    line-height: 1.7;
    margin-bottom: 25px;
    color: #555;
    font-size: 15px;
}

/* Info Items */
.info-item {
    margin-bottom: 20px;
    padding: 15px;
    background: #faf7f2;
    border-radius: 12px;
    border: 1px solid rgba(139, 69, 19, 0.08);
    transition: all 0.3s ease;
}

.info-item:hover {
    background: #f5eee5;
    border-color: rgba(139, 69, 19, 0.2);
}

.info-item strong {
    color: #8B4513;
    display: block;
    margin-bottom: 5px;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-item strong i {
    font-size: 18px;
}

.info-item div {
    color: #333;
    font-size: 15px;
    padding-left: 26px;
}

/* Form Styles */
.contact-form input,
.contact-form textarea {
    width: 100%;
    padding: 14px 16px;
    margin-bottom: 15px;
    border-radius: 12px;
    border: 2px solid #e0e0e0;
    font-size: 14px;
    transition: all 0.3s ease;
    background: white;
    font-family: 'Open Sans', sans-serif;
}

.contact-form input:focus,
.contact-form textarea:focus {
    border-color: #8B4513;
    outline: none;
    box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
}

.contact-form input:hover,
.contact-form textarea:hover {
    border-color: #A0522D;
}

.contact-form textarea {
    resize: vertical;
    min-height: 120px;
    max-height: 200px;
}

/* Messages */
.success-message {
    background: #e8f5e9;
    border-left: 4px solid #2e7d32;
    color: #1b5e20;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
    animation: slideDown 0.3s ease;
}

.error-message {
    background: #ffebee;
    border-left: 4px solid #d32f2f;
    color: #b71c1c;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.success-message i,
.error-message i {
    font-size: 20px;
}

/* Button */
.contact-form button {
    background: #8B4513;
    color: white;
    border: none;
    padding: 14px 35px;
    border-radius: 50px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
    box-shadow: 0 4px 10px rgba(139, 69, 19, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.contact-form button:hover {
    background: #A0522D;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(139, 69, 19, 0.3);
}

.contact-form button:active {
    transform: translateY(0);
}

.contact-form button i {
    font-size: 18px;
}

/* Map Section */
.map-section {
    max-width: 1100px;
    margin: 40px auto 0;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(139, 69, 19, 0.1);
}

.map-section iframe {
    display: block;
    transition: all 0.3s ease;
}

.map-section:hover iframe {
    filter: brightness(1.02);
}

/* Responsive */
@media(max-width: 900px) {
    .contact-container {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .contact-box {
        padding: 25px;
    }
    
    .contact-info h2,
    .contact-form h2 {
        font-size: 24px;
    }
    
    .map-section {
        margin-top: 30px;
    }
    
    .map-section iframe {
        height: 300px;
    }
}

@media(max-width: 480px) {
    .contact-wrapper {
        padding: 40px 15px;
    }
    
    .contact-box {
        padding: 20px;
    }
    
    .contact-form button {
        padding: 12px 25px;
    }
    
    .info-item div {
        font-size: 14px;
    }
}

/* Loading state */
.contact-form button.loading {
    opacity: 0.7;
    cursor: not-allowed;
}

.contact-form button.loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<div class="contact-wrapper">
    <div class="contact-container">

        <!-- THÔNG TIN LIÊN HỆ -->
        <div class="contact-info contact-box">
            <h2>
                <i class="fas fa-heart" style="color: #8B4513; margin-right: 10px;"></i>
                Liên hệ Sweet Cake
            </h2>
            <p>
                Sweet Cake luôn sẵn sàng lắng nghe và hỗ trợ bạn trong mọi thắc mắc,
                từ đặt bánh, tư vấn mẫu cho đến phản hồi dịch vụ.
            </p>

            <div class="info-item">
                <strong><i class="fas fa-map-marker-alt"></i> Địa chỉ:</strong>
                <div>15 Kim Chung Di Trạch, Hoài Đức, Hà Nội</div>
            </div>

            <div class="info-item">
                <strong><i class="fas fa-phone-alt"></i> Hotline:</strong>
                <div>0912 353 558 (8h – 21h)</div>
            </div>

            <div class="info-item">
                <strong><i class="fas fa-envelope"></i> Email:</strong>
                <div>sweetcakebakery@gmail.com</div>
            </div>

            <div class="info-item">
                <strong><i class="fas fa-clock"></i> Giờ mở cửa:</strong>
                <div>Thứ 2 – Chủ nhật | 8:00 – 21:00</div>
            </div>
        </div>

        <!-- FORM LIÊN HỆ -->
        <div class="contact-form contact-box">
            <h2>
                <i class="fas fa-paper-plane" style="color: #8B4513; margin-right: 10px;"></i>
                Gửi lời nhắn cho chúng tôi
            </h2>
            
            <?php if ($contactSuccess): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($contactSuccess); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($contactError): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($contactError); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="" id="contactForm">
                <input type="text" 
                       name="name" 
                       placeholder="Họ và tên *" 
                       value="<?php echo htmlspecialchars(isset($_POST['name']) ? $_POST['name'] : ($contactSuccess ? '' : $contactDefaults['name'])); ?>" 
                       required>
                
                <input type="email" 
                       name="email" 
                       placeholder="Email *" 
                       value="<?php echo htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : ($contactSuccess ? '' : $contactDefaults['email'])); ?>" 
                       required>
                
                <input type="text" 
                       name="phone" 
                       placeholder="Số điện thoại" 
                       value="<?php echo htmlspecialchars(isset($_POST['phone']) ? $_POST['phone'] : ($contactSuccess ? '' : $contactDefaults['phone'])); ?>">
                
                <textarea name="message" 
                          placeholder="Nội dung liên hệ *" 
                          required><?php echo htmlspecialchars(isset($_POST['message']) ? $_POST['message'] : ($contactSuccess ? '' : '')); ?></textarea>
                
                <button type="submit" id="submitBtn">
                    <i class="fas fa-paper-plane"></i>
                    Gửi liên hệ
                </button>
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
            loading="lazy"
            title="Bản đồ Sweet Cake">
        </iframe>
    </div>
</div>

<script>
// Animation và validation cho form
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const submitBtn = document.getElementById('submitBtn');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Disable button để tránh submit nhiều lần
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner"></i> Đang gửi...';
            
            // Cho phép form submit bình thường
            return true;
        });
    }
    
    // Auto-hide messages after 5 seconds
    const messages = document.querySelectorAll('.success-message, .error-message');
    messages.forEach(function(message) {
        setTimeout(function() {
            message.style.transition = 'opacity 0.5s ease';
            message.style.opacity = '0';
            setTimeout(function() {
                message.style.display = 'none';
            }, 500);
        }, 5000);
    });
});

// Validation phone number (optional)
function validatePhone(phone) {
    const phoneRegex = /(84|0[3|5|7|8|9])+([0-9]{8})\b/;
    return phone === '' || phoneRegex.test(phone);
}

// Smooth scroll to form if there's error
<?php if ($contactError): ?>
window.onload = function() {
    document.querySelector('.error-message').scrollIntoView({
        behavior: 'smooth',
        block: 'center'
    });
}
<?php endif; ?>
</script>

<?php
include 'footer.php';

if (isset($conn)) {
    mysqli_close($conn);
}
?>