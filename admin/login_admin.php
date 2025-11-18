<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>login_admin</title>
<style>
    body {
        background: linear-gradient(to right, #f0f4f8, #e8eaf6);
        font-family: 'Segoe UI', sans-serif;
        margin: 0;
        padding: 0;
    }

    table {
        background-color: #ffffff;
        font-family: Arial, sans-serif;
        border-radius: 20px;
        padding: 20px;
        width: 450px;
        margin: 80px auto;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        border: none;
    }

    th {
        background: linear-gradient(to right, #39F, #5ab2ff);
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
        background-color: #f0f0f0;
        border-color: #39F;
    }

    input[type="submit"] {
        background: #39F;
        color: white;
        padding: 10px 25px;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        cursor: pointer;
        transition: 0.3s;
    }

    input[type="submit"]:hover {
        background: #ff5252;
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
	$txt_username = isset($_POST["txt_username"]) ? $_POST["txt_username"] : "";
	$txt_pass = isset($_POST["txt_pass"]) ? $_POST["txt_pass"] : "";

	if (isset($_POST["btn_login"])) {
		if ($txt_username == "admin" && $txt_pass == "123") {
		// lưu session
		session_start();
		$_SESSION["admin"] = $txt_username;
	
		// chuyển hướng
		header("Location: admin_dashboard.php");
		exit;
		} else if ($txt_username == "admin" && $txt_pass != "123") {
			echo "Sai mật khẩu!";
		} else {
			echo "Sai tên đăng nhập!";
		}
	
	}
?>

<form action="" method="post">
    <table width="200" border="1">
        <tr>
            <th colspan="2">Đăng nhập</th>
        </tr>
        <tr>
            <td>Tên đăng nhập:</td>
            <td><input name="txt_username" type="text" required="required"></td>
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