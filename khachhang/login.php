<?php
// login.php - Trang đăng nhập người dùng với CSS inline

if (!isset($_SESSION)) {
    session_start();
}

require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
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
    /* Theme adjusted to light brown / light beige / black text */
    :root {
        --main-brown: #8B6F47;
        --brown-light: #A0826D;
        --light-beige: #F5F1E8;
        --white: #FFFFFF;
        --text-black: #000000;
        --error-red: #721c24;
    }

    body {
        background-color: var(--light-beige);
        font-family: 'Open Sans', sans-serif;
        margin: 0;
        padding: 0;
        color: var(--text-black);
    }

    .login-form {
        max-width: 400px;
        margin: 60px auto;
        background: var(--white);
        padding: 30px;
        border-radius: 4px;
        border: 1px solid #e8e8e8;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        text-align: center;
        color: var(--text-black);
    }

    .login-form h2 {
        background: linear-gradient(to right, var(--main-brown), var(--brown-light));
        color: white;
        padding: 16px;
        margin: -30px -30px 20px -30px;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
        font-size: 20px;
        font-weight: 600;
    }

    label {
        display: block;
        text-align: left;
        margin-bottom: 6px;
        font-weight: 500;
        font-size: 14px;
        color: var(--text-black);
    }

    input {
        width: 100%;
        padding: 10px 12px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        transition: 0.3s;
        box-sizing: border-box;
    }

    input:hover {
        border-color: var(--main-brown);
        background-color: var(--white);
    }

    input:focus {
        border-color: var(--main-brown);
        outline: none;
    }

    button {
        width: 100%;
        padding: 12px;
        background: var(--main-brown);
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
    }

    button:hover {
        background: var(--brown-light);
    }

    .error {
        background: #f8d7da;
        color: var(--error-red);
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 15px;
        font-weight: bold;
        font-size: 14px;
    }

    a {
        color: var(--main-brown);
        font-weight: 600;
        text-decoration: none;
        font-size: 14px;
    }

    a:hover {
        color: var(--brown-light);
        text-decoration: underline;
    }

    p {
        margin-top: 15px;
        font-size: 14px;
        color: #666;
    }

    footer {
        text-align: center;
        padding: 20px;
        background: var(--light-beige);
        color: var(--text-black);
        margin-top: 50px;
        font-size: 13px;
    }
    </style>
</head>

<body>

<div class="login-form">
    <h2>Đăng nhập</h2>

    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <label for="username">Tên đăng nhập:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Mật khẩu:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Đăng nhập</button>
    </form>

    <p style="margin-top: 10px;">
        Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
    </p>
</div>

<footer>
    © 2025 Savor Cake – All rights reserved.
</footer>

</body>
</html>
