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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($item['title']); ?> - Sweet Cake</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .news-detail-page { max-width: 800px; margin: 30px auto; padding: 0 20px 40px; font-family: 'Open Sans', sans-serif; }
        .news-detail-header { margin-bottom: 24px; }
        .news-detail-title { font-size: 28px; font-weight: 800; color: #333; line-height: 1.3; margin-bottom: 12px; }
        .news-detail-meta { font-size: 14px; color: #888; }
        .news-detail-img { width: 100%; height: auto; max-height: 70vh; object-fit: contain; border-radius: 12px; margin-bottom: 24px; background: #f9f9f9; }
        .news-detail-content { font-size: 16px; line-height: 1.8; color: #444; }
        .news-detail-content p { margin-bottom: 16px; }
        .back-link { display: inline-flex; align-items: center; gap: 8px; margin-top: 24px; color: var(--main-brown, #5D4037); font-weight: 600; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="news-detail-page">
    <article>
        <header class="news-detail-header">
            <h1 class="news-detail-title"><?php echo htmlspecialchars($item['title']); ?></h1>
            <p class="news-detail-meta">
                <i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?>
            </p>
        </header>

        <?php if (!empty($item['image'])): ?>
            <img src="<?php echo htmlspecialchars(strpos($item['image'], 'http') === 0 ? $item['image'] : '../images/' . $item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="news-detail-img">
        <?php endif; ?>

        <?php if (!empty($item['summary'])): ?>
            <p class="news-detail-summary" style="font-size:18px; color:#555; margin-bottom:20px;"><?php echo nl2br(htmlspecialchars($item['summary'])); ?></p>
        <?php endif; ?>

        <div class="news-detail-content">
            <?php echo nl2br(htmlspecialchars($item['content'] ?? '')); ?>
        </div>

        <a href="news.php" class="back-link"><i class="fas fa-arrow-left"></i> Quay lại tin tức</a>
    </article>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
