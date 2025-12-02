<?php
// register.php - Trang đăng ký người dùng với CSS inline

// Bắt đầu session nếu chưa có
if (!isset($_SESSION)) {
    session_start();
}

// Kết nối database (sử dụng mysqli từ connect.php)
require_once 'connect.php';

// Xử lý form đăng ký
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp!";
    } else {
        // Kiểm tra username đã tồn tại
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        if ($checkStmt) {
            $checkStmt->bind_param('s', $username);
            $checkStmt->execute();
            $checkStmt->store_result();
            $exists = $checkStmt->num_rows > 0;
            $checkStmt->close();

            if ($exists) {
                $error = "Tên đăng nhập đã tồn tại!";
            } else {
                // Hash mật khẩu
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert vào DB
                $insStmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                if ($insStmt) {
                    $insStmt->bind_param('sss', $username, $email, $hashed_password);
                    if ($insStmt->execute()) {
                        // Đăng ký thành công → chuyển đến đăng nhập
                        header('Location: login.php');
                        exit();
                    } else {
                        $error = "Đăng ký thất bại, vui lòng thử lại!";
                    }
                    $insStmt->close();
                } else {
                    $error = "Không thể chuẩn bị truy vấn đăng ký.";
                }
            }
        } else {
            $error = "Không thể kiểm tra tên đăng nhập.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Savor Cake</title>
    <style>
    /* CSS inline cho trang register, dựa trên style.css mới với tông màu pink, green, kem */

    /* Reset cơ bản */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Open Sans', sans-serif; /* Sử dụng font từ file mới */
    }

    /* Tông màu chính từ file style.css mới */
    :root {
        --primary-color: #8B6F47; /* Màu brown chính cho nút, link */
        --primary-hover: #A0826D; /* Hover light brown */
        --accent-color: #F5F1E8; /* Màu beige light cho accent */
        --accent-hover: #D4C5B5; /* Hover light brown */
        --background-color: #F5F1E8; /* Nền kem nhạt */
        --text-color: #333; /* Màu chữ chính */
        --error-color: #d32f2f; /* Màu lỗi đỏ */
        --border-color: #e0e0e0; /* Viền nhạt */
        --white: #FFFFFF;
    }

    /* Body */
    body {
        background-color: var(--background-color);
        color: var(--text-color);
        line-height: 1.6;
    }

    /* Form chung cho register */
    .register-form {
        max-width: 400px;
        margin: 50px auto;
        padding: 20px;
        background-color: var(--white); /* Nền trắng cho form để nổi bật */
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .register-form h2 {
        color: var(--primary-color); /* Pink cho tiêu đề */
        margin-bottom: 20px;
    }

    .register-form label {
        display: block;
        text-align: left;
        margin-bottom: 5px;
        color: var(--text-color);
    }

    .register-form input {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
    }

    .register-form button {
        width: 100%;
        padding: 10px;
        background-color: var(--primary-color);
        color: var(--white);
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        transition: background-color 0.3s;
    }

    .register-form button:hover {
        background-color: var(--primary-hover);
    }

    .error {
        color: var(--error-color);
        margin-bottom: 15px;
        font-weight: bold;
    }

    /* Footer (nếu có) */
    footer {
        text-align: center;
        padding: 20px;
        background-color: var(--primary-color);
        color: var(--white);
        margin-top: 50px;
    }
    </style>
</head>
<body>

<div class="register-form">
    <h2>Đăng ký tài khoản</h2>
    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <form action="register.php" method="POST">
        <label for="username">Tên đăng nhập:</label>
        <input type="text" id="username" name="username" required>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        
        <label for="password">Mật khẩu:</label>
        <input type="password" id="password" name="password" required>
        
        <label for="confirm_password">Xác nhận mật khẩu:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
        
        <button type="submit">Đăng ký</button>
    </form>
    <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
</div>

<footer>
    <!-- Nội dung footer nếu cần -->
</footer>
</body>
</html>