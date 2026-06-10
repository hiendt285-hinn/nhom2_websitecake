<?php
session_start();
require_once 'connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: news.php');
    exit;
}

$item = null;
$stmt = $conn->prepare("SELECT id, title, slug, summary, content, image, created_at, updated_at FROM news WHERE id = ? AND is_active = 1 LIMIT 1");
if ($stmt) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $item = $row;
    }
    $stmt->close();
}

if (!$item) {
    header('Location: news.php');
    exit;
}

$page_title = htmlspecialchars($item['title']) . ' - Sweet Cake';
include 'header.php';
?>

<div class="news-detail-page">
    <nav class="content-breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <a href="news.php">Tin tức</a>
        <span>/</span>
        <span><?php echo htmlspecialchars($item['title']); ?></span>
    </nav>

    <article class="news-detail-article">
        <header class="news-detail-header">
            <span class="news-badge"><i class="fas fa-newspaper"></i> Sweet Cake Journal</span>
            <h1 class="news-detail-title"><?php echo htmlspecialchars($item['title']); ?></h1>
            <div class="news-detail-meta">
                <span><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($item['created_at'])); ?></span>
                <span><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($item['created_at'])); ?></span>
            </div>
        </header>

        <?php if (!empty($item['image'])): ?>
        <div class="news-detail-media">
            <img src="<?php echo htmlspecialchars(strpos($item['image'], 'http') === 0 ? $item['image'] : '../images/' . $item['image']); ?>"
                 alt="<?php echo htmlspecialchars($item['title']); ?>" class="news-detail-img">
        </div>
        <?php endif; ?>

        <?php if (!empty($item['summary'])): ?>
            <p class="news-detail-summary"><?php echo nl2br(htmlspecialchars($item['summary'])); ?></p>
        <?php endif; ?>

        <div class="news-detail-content">
            <?php echo nl2br(htmlspecialchars($item['content'] ?? '')); ?>
        </div>

        <footer class="news-detail-footer">
            <a href="news.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại tin tức</a>
            <a href="products.php" class="btn-primary"><i class="fas fa-cake-candles"></i> Xem sản phẩm</a>
        </footer>
    </article>
</div>

<?php include 'footer.php'; ?>
