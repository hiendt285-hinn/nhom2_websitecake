<?php
// login.php - Trang đăng nhập người dùng với CSS inline

// Bắt đầu session nếu chưa có
if (!isset($_SESSION)) {
    session_start();
}

// Kết nối database (sử dụng mysqli từ connect.php)
require_once 'connect.php';

// Xử lý form đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Truy vấn bằng mysqli prepared statements
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            // Đăng nhập thành công
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit();
        } else {
            $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
        }
    } else {
        $error = "Không thể chuẩn bị truy vấn đăng nhập.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Savor Cake</title>
    <style>
    /* CSS inline cho trang login, dựa trên style.css mới với tông màu pink, green, kem */

    /* Reset cơ bản */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif; /* Sử dụng font từ file mới */
    }

    /* Tông màu chính từ file style.css mới */
    :root {
        --primary-color: #ff5f9e; /* Màu pink chính cho nút, link */
        --primary-hover: #ff90c2; /* Hover pink */
        --accent-color: #4CAF50; /* Màu xanh green cho accent */
        --accent-hover: #388e3c; /* Hover green */
        --background-color: #fffaf0; /* Nền kem nhạt */
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

    /* Form chung cho login */
    .login-form {
        max-width: 400px;
        margin: 50px auto;
        padding: 20px;
        background-color: var(--white); /* Nền trắng cho form để nổi bật */
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .login-form h2 {
        color: var(--primary-color); /* Pink cho tiêu đề */
        margin-bottom: 20px;
    }

    .login-form label {
        display: block;
        text-align: left;
        margin-bottom: 5px;
        color: var(--text-color);
    }

    .login-form input {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
    }

    .login-form button {
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

    .login-form button:hover {
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

<div class="login-form">
    <h2>Đăng nhập</h2>
    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <label for="username">Tên đăng nhập:</label>
        <input type="text" id="username" name="username" required>
        
        <label for="password">Mật khẩu:</label>
        <input type="password" id="password" name="password" required>
        
        <button type="submit">Đăng nhập</button>
    </form>
    <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
</div>

<footer>
    <!-- Nội dung footer nếu cần -->
</footer>
</body>
</html>