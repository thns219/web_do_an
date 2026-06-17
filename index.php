<?php
// Load DB and helper functions so we can fetch featured items
require_once __DIR__ . '/database/db.php';
require_once __DIR__ . '/functions/functions.php';

$totalItems = countMenuItems($conn);
$featuredItems = [];
if ($totalItems > 0) {
    // load all items (if DB is large, consider pagination instead)
    $featuredItems = getMenuItems($conn, ['limit' => $totalItems, 'offset' => 0]);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ Ăn húp hội</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/index.css">
   <link rel="icon" type="image/jpg" sizes="16x16" href="../images/logo.jpg">
   <link rel="stylesheet" href="assets/css/spin.css">

</head>
<body>
    <?php include 'includes/header.php'?>

    <div class="main">
        <div class="row bordered">
            <h1>Chào mừng bạn đến với Ăn húp hội</h1>
            <div class="video-container">
                <video autoplay muted loop>
                    <source src="assets/video/food.mp4" type="video/mp4">
                    Trình duyệt của bạn không hỗ trợ video.
                </video>
            </div>
        </div>
    </div>

    <div class="fooddishes">
        <h2>Món ăn nổi bật</h2>
        <div class="food-grid">
            <?php if (!empty($featuredItems)): ?>
                <?php foreach ($featuredItems as $it): ?>
                    <div class="food-item">
                        <a href="/pages/chitietmonan.php?mamon=<?php echo urlencode($it['Mamon']); ?>" class="food-link">
                            <img src="<?php echo htmlspecialchars(resolveImagePath($it['Anh'] ?? '')); ?>" alt="<?php echo htmlspecialchars($it['Tenmon']); ?>">
                            <h3><?php echo htmlspecialchars($it['Tenmon']); ?></h3>
                            <p><?php echo number_format($it['Giaban'],0,',','.'); ?> VNĐ</p>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="food-item">Chưa có sản phẩm nổi bật.</div>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'includes/footer.php'?>
    <script src="assets/js/spin.js"></script>

</body>
</html>
