<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng nhập Admin - Savor Cake</title>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<style>
    body {
        background: #F5F1E8;
        font-family: 'Open Sans', sans-serif;
        margin: 0;
        padding: 0;
    }

    table {
        background-color: #ffffff;
        font-family: 'Open Sans', sans-serif;
        border-radius: 16px;
        padding: 20px;
        width: 450px;
        margin: 80px auto;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        border: 1px solid #e0e0e0;
    }

    th {
        background: #8B6F47;
        color: white;
        padding: 16px;
        font-size: 20px;
        border-top-left-radius: 20px;
        border-top-right-radius: 20px;
        border: none;
    }

    td {
        padding: 12px 16px;
        border: none;
        font-size: 16px;
    }

    input[type="text"],
    input[type="password"] {
        width: 95%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.3s ease;
    }

    input[type="text"]:hover,
    input[type="password"]:hover {
        background-color: #fffaf0;
        border-color: #ff5f9e;
    }

    input[type="submit"] {
        background: #8B6F47;
        color: white;
        padding: 10px 25px;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        cursor: pointer;
        transition: 0.3s;
        font-weight: 600;
    }

    input[type="submit"]:hover {
        background: #A0826D;
    }

    .message {
        text-align: center;
        font-weight: bold;
        padding: 10px;
        width: 450px;
        margin: 20px auto;
        border-radius: 10px;
    }

    .success {
        background-color: #d4edda;
        color: #155724;
    }

    .error {
        background-color: #f8d7da;
        color: #721c24;
    }
</style></head>

<body>
<?php
session_start();
require_once 'connect.php';

$error = '';

if (isset($_POST["btn_login"])) {
    $txt_username = isset($_POST["txt_username"]) ? trim($_POST["txt_username"]) : "";
    $txt_pass = isset($_POST["txt_pass"]) ? $_POST["txt_pass"] : "";

    if ($txt_username && $txt_pass) {
        // Kiểm tra trong database với role='admin'
        $sql = "SELECT id, username, password, role FROM users WHERE username = ? AND role = 'admin' LIMIT 1";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('s', $txt_username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            // Nếu user tồn tại và password đúng
            if ($user && password_verify($txt_pass, $user['password'])) {
                $_SESSION['admin_id'] = (int)$user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin'] = $user['username'];
                header("Location: admin_dashboard.php");
                exit;
            } 
            // Nếu username là "admin" và password là "123", tự động tạo/cập nhật admin user
            else if ($txt_username === 'admin' && $txt_pass === '123') {
                $hashed_password = password_hash('123', PASSWORD_DEFAULT);
                
                // Kiểm tra xem admin user đã tồn tại chưa
                if ($user) {
                    // Cập nhật password cho admin user hiện có
                    $updateSql = "UPDATE users SET password = ? WHERE username = 'admin' AND role = 'admin'";
                    if ($updateStmt = $conn->prepare($updateSql)) {
                        $updateStmt->bind_param('s', $hashed_password);
                        $updateStmt->execute();
                        $updateStmt->close();
                    }
                    $admin_id = $user['id'];
                } else {
                    // Tạo admin user mới nếu chưa tồn tại
                    $insertSql = "INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@savorcake.com', ?, 'admin')";
                    if ($insertStmt = $conn->prepare($insertSql)) {
                        $insertStmt->bind_param('s', $hashed_password);
                        $insertStmt->execute();
                        $admin_id = $conn->insert_id;
                        $insertStmt->close();
                    } else {
                        $error = "Không thể tạo tài khoản admin!";
                    }
                }
                
                // Đăng nhập sau khi tạo/cập nhật
                if (!isset($error)) {
                    $_SESSION['admin_id'] = (int)$admin_id;
                    $_SESSION['admin_username'] = 'admin';
                    $_SESSION['admin'] = 'admin';
                    header("Location: admin_dashboard.php");
                    exit;
                }
            } else {
                $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
            }
        } else {
            $error = "Lỗi kết nối database!";
        }
    } else {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    }
}
?>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form action="" method="post">
        <table width="200" border="1">
            <tr>
                <th colspan="2">Đăng nhập Admin</th>
            </tr>
            <tr>
                <td>Tên đăng nhập:</td>
                <td><input name="txt_username" type="text" required="required" value="<?php echo isset($_POST['txt_username']) ? htmlspecialchars($_POST['txt_username']) : ''; ?>"></td>
            </tr>
            <tr>
                <td>Mật khẩu:</td>
                <td><input name="txt_pass" type="password" required="required"></td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <input type="submit" name="btn_login" value="Đăng nhập">
                </td>
            </tr>
        </table>
    </form>
</body>
</html>