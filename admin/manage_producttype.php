<?php
session_start();

require_once 'connect.php';
if (!isset($_SESSION["admin"])) {
    header("Location: login_admin.php");
    exit();
}

// Thêm danh mục mới
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description']);
    if ($name) {
        $stmt = $conn->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $slug, $description);
        $stmt->execute();
        $stmt->close();
    }
}

// Xóa danh mục
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Sửa danh mục
if (isset($_POST['edit_category'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description']);
    $stmt = $conn->prepare("UPDATE categories SET name=?, slug=?, description=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $slug, $description, $id);
    $stmt->execute();
    $stmt->close();
}

// Lấy danh sách danh mục
$result = $conn->query("SELECT * FROM categories ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý danh mục</title>
<style>
body{font-family:Poppins,sans-serif;background:#fffaf0;padding:20px;}
table{width:100%;border-collapse:collapse;margin-top:20px;background:#ffffff}
th,td{border:1px solid #ccc;padding:10px;text-align:left;}
th{background:#F5F1E8;color: #333;;}
form input, form textarea{width:95%;padding:8px;margin:5px 0;border-radius:5px;border:1px solid #ccc;}
form input[type=submit]{background:#8B6F47;color:#fff;border:none;padding:10px 15px;cursor:pointer;border-radius:5px;}
form input[type=submit]:hover{background:#A0826D;}
a{color:#2196F3;text-decoration:none;}
a:hover{color:#64B5F6;}
</style> 
</head>
<body>
<h2>Quản lý danh mục sản phẩm</h2>
<form method="post">
    <input type="text" name="name" placeholder="Tên danh mục" required>
    <input type="text" name="slug" placeholder="Slug">
    <textarea name="description" placeholder="Mô tả"></textarea>
    <input type="submit" name="add_category" value="Thêm danh mục">
</form>

<table>
<tr><th>ID</th><th>Tên danh mục</th><th>Slug</th><th>Mô tả</th><th>Hành động</th></tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo htmlspecialchars($row['name']); ?></td>
<td><?php echo htmlspecialchars($row['slug']); ?></td>
<td><?php echo htmlspecialchars($row['description']); ?></td>
<td>
    <a href="manage_producttype.php?edit=<?php echo $row['id']; ?>">Sửa</a> |
    <a href="manage_producttype.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
</td>
</tr>
<?php endwhile; ?>
</table>

<?php
// Form sửa
if(isset($_GET['edit'])){
    $id = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM categories WHERE id=$id LIMIT 1");
    $cat = $res->fetch_assoc();
?>
<h3>Sửa danh mục</h3>
<form method="post">
<input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
<input type="text" name="name" value="<?php echo htmlspecialchars($cat['name']); ?>" required>
<input type="text" name="slug" value="<?php echo htmlspecialchars($cat['slug']); ?>">
<textarea name="description"><?php echo htmlspecialchars($cat['description']); ?></textarea>
<input type="submit" name="edit_category" value="Cập nhật danh mục">
</form>
<?php } ?>
</body>
</html>
