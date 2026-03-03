<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login_admin.php');
    exit();
}
require_once 'connect.php';

$conn->query("CREATE TABLE IF NOT EXISTS promotions (
  id int(11) NOT NULL AUTO_INCREMENT,
  code varchar(50) NOT NULL,
  title varchar(255) DEFAULT NULL,
  discount_type enum('percent','fixed') NOT NULL DEFAULT 'percent',
  discount_value decimal(10,2) NOT NULL DEFAULT 0,
  min_order_amount decimal(10,2) DEFAULT 0,
  valid_from datetime DEFAULT NULL,
  valid_to datetime DEFAULT NULL,
  is_active tinyint(1) DEFAULT 1,
  created_at datetime DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $title = trim($_POST['title'] ?? '');
    $discountType = in_array($_POST['discount_type'] ?? '', ['percent', 'fixed']) ? $_POST['discount_type'] : 'percent';
    $discountValue = (float)($_POST['discount_value'] ?? 0);
    $minOrder = (float)($_POST['min_order_amount'] ?? 0);
    $validFrom = !empty($_POST['valid_from']) ? $_POST['valid_from'] : null;
    $validTo = !empty($_POST['valid_to']) ? $_POST['valid_to'] : null;
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($code === '') {
        $error = 'Vui lòng nhập mã.';
    } else {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE promotions SET code=?, title=?, discount_type=?, discount_value=?, min_order_amount=?, valid_from=?, valid_to=?, is_active=? WHERE id=?");
            $stmt->bind_param('sssddsssi', $code, $title, $discountType, $discountValue, $minOrder, $validFrom, $validTo, $isActive, $id);
            if ($stmt->execute()) {
                $message = 'Cập nhật mã giảm giá thành công.';
            } else {
                $error = 'Lỗi cập nhật (có thể trùng mã).';
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO promotions (code, title, discount_type, discount_value, min_order_amount, valid_from, valid_to, is_active) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->bind_param('sssddssi', $code, $title, $discountType, $discountValue, $minOrder, $validFrom, $validTo, $isActive);
            if ($stmt->execute()) {
                $message = 'Thêm mã giảm giá thành công.';
            } else {
                $error = 'Lỗi thêm (có thể trùng mã).';
            }
            $stmt->close();
        }
    }
}

if (isset($_GET['delete_id'])) {
    $did = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM promotions WHERE id = $did");
    header('Location: admin_dashboard.php?page=promotions');
    exit();
}

$list = $conn->query("SELECT * FROM promotions ORDER BY id DESC");
?>
<div class="admin-content">
    <div class="admin-page-header">
        <h1 class="admin-page-title"><i class="fas fa-tag"></i> Quản lý mã giảm giá</h1>
        <button type="button" class="admin-btn admin-btn-primary" onclick="openForm()">Thêm mã</button>
    </div>
    <?php if ($message): ?><div class="admin-message admin-message-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="admin-message admin-message-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <div class="admin-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Tiêu đề</th>
                    <th>Loại</th>
                    <th>Giá trị</th>
                    <th>Đơn tối thiểu</th>
                    <th>Hiệu lực</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($list && $list->num_rows > 0): ?>
                    <?php while ($r = $list->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($r['code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($r['title'] ?: '—'); ?></td>
                        <td><?php echo $r['discount_type'] === 'percent' ? '%' : '₫'; ?></td>
                        <td><?php echo $r['discount_type'] === 'percent' ? (int)$r['discount_value'] . '%' : number_format((float)$r['discount_value'], 0, ',', '.'); ?></td>
                        <td><?php echo (float)$r['min_order_amount'] > 0 ? number_format((float)$r['min_order_amount'], 0, ',', '.') . '₫' : '—'; ?></td>
                        <td><?php echo $r['valid_from'] ? date('d/m/Y', strtotime($r['valid_from'])) : '—'; ?> → <?php echo $r['valid_to'] ? date('d/m/Y', strtotime($r['valid_to'])) : '—'; ?></td>
                        <td><?php echo $r['is_active'] ? 'Bật' : 'Tắt'; ?></td>
                        <td class="admin-action-cell">
                            <button type="button" class="admin-btn admin-btn-primary admin-btn-sm" onclick='editPromo(<?php echo json_encode($r); ?>)'><i class="fas fa-edit"></i> Sửa</button>
                            <a href="admin_dashboard.php?page=promotions&delete_id=<?php echo (int)$r['id']; ?>" class="admin-btn admin-btn-danger admin-btn-sm" style="text-decoration:none;" onclick="return confirm('Xóa mã này?');"><i class="fas fa-trash-alt"></i> Xóa</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8">Chưa có mã giảm giá.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="promoModal" class="edit-modal" style="display:none;">
    <div class="admin-modal-box" style="max-width:480px;">
        <div class="admin-modal-header">
            <h2 class="admin-modal-title" id="promoModalTitle">Thêm mã giảm giá</h2>
            <button type="button" class="admin-modal-close" onclick="closeForm()">&times;</button>
        </div>
        <form method="post">
            <div class="admin-modal-body">
                <input type="hidden" name="id" id="promo_id">
                <div class="admin-form-group">
                    <label>Mã *</label>
                    <input type="text" name="code" id="promo_code" required style="text-transform:uppercase;">
                </div>
                <div class="admin-form-group">
                    <label>Tiêu đề</label>
                    <input type="text" name="title" id="promo_title">
                </div>
                <div class="admin-form-group">
                    <label>Loại giảm</label>
                    <select name="discount_type" id="promo_discount_type">
                        <option value="percent">Theo %</option>
                        <option value="fixed">Số tiền cố định</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label>Giá trị *</label>
                    <input type="number" name="discount_value" id="promo_discount_value" step="0.01" min="0" required>
                </div>
                <div class="admin-form-group">
                    <label>Đơn tối thiểu (₫)</label>
                    <input type="number" name="min_order_amount" id="promo_min_order" step="1000" min="0" value="0">
                </div>
                <div class="admin-form-group">
                    <label>Từ ngày</label>
                    <input type="datetime-local" name="valid_from" id="promo_valid_from">
                </div>
                <div class="admin-form-group">
                    <label>Đến ngày</label>
                    <input type="datetime-local" name="valid_to" id="promo_valid_to">
                </div>
                <div class="admin-form-group">
                    <label><input type="checkbox" name="is_active" id="promo_is_active" value="1" checked> Đang áp dụng</label>
                </div>
                <div class="admin-modal-actions">
                    <button type="button" class="admin-btn admin-btn-secondary" onclick="closeForm()">Hủy</button>
                    <button type="submit" class="admin-btn admin-btn-primary">Lưu</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
function openForm() {
    document.getElementById('promoModalTitle').textContent = 'Thêm mã giảm giá';
    document.getElementById('promo_id').value = '';
    document.getElementById('promo_code').value = '';
    document.getElementById('promo_title').value = '';
    document.getElementById('promo_discount_type').value = 'percent';
    document.getElementById('promo_discount_value').value = '';
    document.getElementById('promo_min_order').value = '0';
    document.getElementById('promo_valid_from').value = '';
    document.getElementById('promo_valid_to').value = '';
    document.getElementById('promo_is_active').checked = true;
    document.getElementById('promoModal').style.display = 'flex';
}
function closeForm() { document.getElementById('promoModal').style.display = 'none'; }
function editPromo(r) {
    document.getElementById('promoModalTitle').textContent = 'Sửa mã giảm giá';
    document.getElementById('promo_id').value = r.id;
    document.getElementById('promo_code').value = r.code;
    document.getElementById('promo_title').value = r.title || '';
    document.getElementById('promo_discount_type').value = r.discount_type || 'percent';
    document.getElementById('promo_discount_value').value = r.discount_value;
    document.getElementById('promo_min_order').value = r.min_order_amount || 0;
    document.getElementById('promo_valid_from').value = r.valid_from ? r.valid_from.replace(' ', 'T').slice(0, 16) : '';
    document.getElementById('promo_valid_to').value = r.valid_to ? r.valid_to.replace(' ', 'T').slice(0, 16) : '';
    document.getElementById('promo_is_active').checked = !!parseInt(r.is_active, 10);
    document.getElementById('promoModal').style.display = 'flex';
}
window.onclick = function(e) { if (e.target.id === 'promoModal') closeForm(); };
</script>
