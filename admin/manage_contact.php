<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login_admin.php");
    exit();
}
require_once 'connect.php';

// Tạo bảng contacts nếu chưa có
$conn->query("CREATE TABLE IF NOT EXISTS contacts (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  phone varchar(50) DEFAULT NULL,
  message text NOT NULL,
  status varchar(50) DEFAULT 'new',
  created_at datetime DEFAULT current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Cập nhật trạng thái (đã xem / mới)
if (isset($_GET['status_id']) && isset($_GET['status'])) {
    $id = (int)$_GET['status_id'];
    $status = $_GET['status'] === 'read' ? 'read' : 'new';
    $stmt = $conn->prepare("UPDATE contacts SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php?page=contact");
    exit();
}
// Xóa
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM contacts WHERE id = $id");
    header("Location: admin_dashboard.php?page=contact");
    exit();
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where = ($filter === 'read') ? "WHERE status = 'read'" : (($filter === 'new') ? "WHERE status = 'new'" : "");
$contacts = $conn->query("SELECT * FROM contacts $where ORDER BY created_at DESC");
?>
<div class="admin-content">
    <div class="admin-page-header">
        <h1 class="admin-page-title"><i class="fas fa-envelope"></i> Quản lý liên hệ</h1>
        <div class="filter-actions">
            <a href="admin_dashboard.php?page=contact" class="admin-btn <?php echo $filter === 'all' ? 'admin-btn-primary' : 'admin-btn-secondary'; ?>">Tất cả</a>
            <a href="admin_dashboard.php?page=contact&filter=new" class="admin-btn <?php echo $filter === 'new' ? 'admin-btn-primary' : 'admin-btn-secondary'; ?>">Chưa xem</a>
            <a href="admin_dashboard.php?page=contact&filter=read" class="admin-btn <?php echo $filter === 'read' ? 'admin-btn-primary' : 'admin-btn-secondary'; ?>">Đã xem</a>
        </div>
    </div>
    <p class="muted" style="margin-bottom:16px;">Tin nhắn từ form Liên hệ trang khách hàng.</p>
    <div class="admin-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>SĐT</th>
                    <th>Nội dung</th>
                    <th>Trạng thái</th>
                    <th>Ngày gửi</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($contacts && $contacts->num_rows > 0): ?>
                    <?php while ($row = $contacts->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo (int)$row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td style="max-width: 280px;"><?php echo nl2br(htmlspecialchars(mb_substr($row['message'], 0, 150) . (mb_strlen($row['message']) > 150 ? '...' : ''))); ?></td>
                            <td><?php echo $row['status'] === 'read' ? 'Đã xem' : '<span style="color:#d32f2f;">Mới</span>'; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php if ($row['status'] !== 'read'): ?>
                                    <a href="admin_dashboard.php?page=contact&status_id=<?php echo $row['id']; ?>&status=read" class="admin-link">Đánh dấu đã xem</a>
                                <?php else: ?>
                                    <a href="admin_dashboard.php?page=contact&status_id=<?php echo $row['id']; ?>&status=new" class="admin-link">Đánh dấu mới</a>
                                <?php endif; ?>
                                |
                                <a href="admin_dashboard.php?page=contact&delete_id=<?php echo $row['id']; ?>" class="admin-link" style="color:#d32f2f;" onclick="return confirm('Xóa liên hệ này?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">Chưa có liên hệ nào</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
