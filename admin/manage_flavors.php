<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login_admin.php');
    exit();
}

include 'admin_header.php';
require_once 'connect.php';

$table_name = 'flavors';
$attribute_name = 'Hương vị';

$message = '';
$error = '';

// Xử lý Thêm mới và Chỉnh sửa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_name'])) {
        // --- Xử lý Thêm mới ---
        $new_name = trim($_POST['new_name']);
        if (!empty($new_name)) {
            $check_stmt = $conn->prepare("SELECT id FROM $table_name WHERE name = ?");
            $check_stmt->bind_param('s', $new_name);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows == 0) {
                $insert_stmt = $conn->prepare("INSERT INTO $table_name (name) VALUES (?)");
                $insert_stmt->bind_param('s', $new_name);
                if ($insert_stmt->execute()) {
                    $message = "Thêm $attribute_name thành công!";
                } else {
                    $error = "Lỗi khi thêm $attribute_name.";
                }
                $insert_stmt->close();
            } else {
                $error = "$attribute_name đã tồn tại!";
            }
            $check_stmt->close();
        }
    } elseif (isset($_POST['edit_id'], $_POST['edit_name'])) {
        // --- Xử lý Cập nhật ---
        $edit_id = intval($_POST['edit_id']);
        $edit_name = trim($_POST['edit_name']);

        if (!empty($edit_name) && $edit_id > 0) {
            // Kiểm tra trùng lặp (trừ bản ghi đang chỉnh sửa)
            $check_stmt = $conn->prepare("SELECT id FROM $table_name WHERE name = ? AND id != ?");
            $check_stmt->bind_param('si', $edit_name, $edit_id);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows == 0) {
                $update_stmt = $conn->prepare("UPDATE $table_name SET name = ? WHERE id = ?");
                $update_stmt->bind_param('si', $edit_name, $edit_id);
                if ($update_stmt->execute()) {
                    $message = "Cập nhật $attribute_name thành công!";
                } else {
                    $error = "Lỗi khi cập nhật $attribute_name.";
                }
                $update_stmt->close();
            } else {
                $error = "$attribute_name '$edit_name' đã tồn tại với ID khác.";
            }
            $check_stmt->close();
        }
    }
}

// Xử lý Xóa
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // Chú ý: Việc xóa có thể thất bại do ràng buộc khóa ngoại nếu thuộc tính đang được sử dụng.
    $stmt = $conn->prepare("DELETE FROM $table_name WHERE id = ?");
    $stmt->bind_param('i', $delete_id);
    if ($stmt->execute()) {
        header("Location: manage_sizes.php?success=deleted");
        exit();
    } else {
        $error = "Không thể xóa $attribute_name. Có thể đang được sử dụng (liên kết với sản phẩm, đơn hàng, hoặc giỏ hàng).";
    }
    $stmt->close();
}

// Lấy danh sách
$sql = "SELECT id, name FROM $table_name ORDER BY id ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8" />
<title>Quản lý <?php echo $attribute_name; ?></title>
<style>
    body { font-family: 'Open Sans', sans-serif; background: #F5F1E8; margin:0; padding:20px;}
    h1 { color: #8B6F47; margin-bottom:16px; }
    .form-container { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .form-container input[type="text"] { padding: 10px; border: 1px solid #ccc; border-radius: 4px; width: 300px; margin-right: 10px; }
    .form-container button { padding: 10px 15px; background: #8B6F47; color: white; border: none; border-radius: 4px; cursor: pointer; }
    .form-container button:hover { background: #A0826D; }
    
    table { width: 100%; border-collapse: collapse; background: white; border-radius:8px; overflow:hidden; }
    th, td { border-bottom: 1px solid #eee; padding: 10px 12px; text-align: left; font-size:14px; }
    th { background: #f9f6f2; color: #333; font-weight:600; }
    .btn-edit { color: #2196F3; cursor: pointer; margin-right: 10px; }
    .btn-delete { color: #d32f2f; cursor: pointer; }
    
    /* Style cho Modal/Form chỉnh sửa */
    .edit-modal {
        display: none; 
        position: fixed; 
        z-index: 1000; 
        left: 0; 
        top: 0;
        width: 100%; 
        height: 100%; 
        overflow: auto; 
        background-color: rgba(0,0,0,0.4); 
    }
    .modal-content {
        background-color: #fefefe;
        margin: 10% auto; 
        padding: 20px;
        border: 1px solid #888;
        width: 80%; 
        max-width: 500px;
        border-radius: 10px;
        position: relative;
    }
    .close-btn {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }
    .close-btn:hover,
    .close-btn:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
    .modal-content input[type="text"] {
        width: 100%;
        padding: 10px;
        margin: 8px 0;
        display: inline-block;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }
    .modal-content button {
        width: 100%;
        background-color: #8B6F47;
        color: white;
        padding: 14px 20px;
        margin: 8px 0;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
</style>
</head>
<body>

<h1>Quản lý <?php echo $attribute_name; ?></h1>

<?php if (isset($message) && $message) echo "<p style='color: green;'>$message</p>"; ?>
<?php if (isset($error) && $error) echo "<p style='color: red;'>$error</p>"; ?>

<div class="form-container">
    <h2>Thêm <?php echo $attribute_name; ?> mới</h2>
    <form method="POST">
        <input type="text" name="new_name" placeholder="Ví dụ: 20cm" required>
        <button type="submit">Thêm</button>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên <?php echo $attribute_name; ?></th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id'] ?></td>
                    <td><?php echo htmlspecialchars($row['name']) ?></td>
                    <td>
                        <span class="btn-edit" onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')">Chỉnh sửa</span>
                        <span class="btn-delete" onclick="if(confirm('Bạn có chắc muốn xóa <?php echo $row['name']; ?>?')) { window.location.href='manage_sizes.php?delete_id=<?php echo $row['id']; ?>'; }">Xóa</span>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3">Chưa có <?php echo $attribute_name; ?> nào được thêm.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<div id="editModal" class="edit-modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Chỉnh sửa <?php echo $attribute_name; ?></h2>
        <form method="POST">
            <input type="hidden" id="edit_id" name="edit_id">
            <label for="edit_name">Tên <?php echo $attribute_name; ?>:</label>
            <input type="text" id="edit_name" name="edit_name" required>
            <button type="submit">Lưu Thay đổi</button>
        </form>
    </div>
</div>

<script>
    // Lấy các phần tử modal
    var modal = document.getElementById("editModal");
    var btnClose = document.getElementsByClassName("close-btn")[0];

    // Hàm mở modal và điền dữ liệu
    function openEditModal(id, name) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        modal.style.display = "block";
    }

    // Đóng modal khi nhấp vào (x)
    btnClose.onclick = function() {
        modal.style.display = "none";
    }

    // Đóng modal khi nhấp ra ngoài
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>

</body>
</html>