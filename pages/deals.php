<?php
session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../functions/functions.php';

// Lấy danh sách món đang khuyến mãi: Giaban < Giagoc
$sql = "
SELECT 
    Mamon,
    Tenmon,
    Giaban,
    Giagoc,
    Noidung,
    Anh,
    (Giagoc - Giaban) AS GiamTien,
    CASE 
        WHEN Giagoc > 0 THEN ROUND((Giagoc - Giaban) / Giagoc * 100)
        ELSE 0
    END AS GiamPT
FROM Monan
WHERE Giaban < Giagoc
ORDER BY GiamPT DESC, Giaban ASC
";

$promos = [];
$rs = $conn->query($sql);
if ($rs) {
    while ($row = $rs->fetch_assoc()) {
        $promos[] = $row;
    }
    $rs->free();
}

// URL hiện tại (dùng cho return_url khi thêm giỏ)
$currentUrl = $_SERVER['REQUEST_URI'] ?? '/pages/deals.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Khuyến mãi món ăn</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="/assets/css/deals.css">
</head>




<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>

    
 <main>
  <div class="page-wrapper">
    <div class="page-header">
      <h1 class="page-title">
        <span class="icon">🔥</span>
        Khuyến mãi cực cháy
      </h1>
      <div class="badge-hot">
        Siêu
        <span>giảm giá</span>
      </div>
    </div>
    <div class="page-subtitle">
      Các món đang có <strong>mức giá</strong> siêu ưu đãi so với <strong>giá gốc</strong>!
    </div>

    <?php if (empty($promos)): ?>
      <div class="no-promo">
        Hiện tại chưa có món nào đang khuyến mãi. Bạn quay lại sau nhé! 🥲
      </div>
    <?php else: ?>
      <div class="promo-grid">
        <?php foreach ($promos as $p): ?>
          <?php
            $giaban   = (float)$p['Giaban'];
            $giagoc   = (float)$p['Giagoc'];
            $giamTien = (float)$p['GiamTien'];
            $giamPT   = (int)$p['GiamPT'];
          ?>
          <!-- Click cả card -> sang trang chi tiết khuyến mãi -->
          <div
            class="promo-card"
            onclick="window.location.href='chitietmonan.php?mamon=<?php echo (int)$p['Mamon']; ?>'">
            
            <div class="promo-img-wrap">
              <img src="<?php echo "../../" . htmlspecialchars($p['Anh'] ?? 'assets/img/default.jpg'); ?>"
                   alt="<?php echo htmlspecialchars($p['Tenmon']); ?>">
              <?php if ($giamPT > 0): ?>
                <div class="discount-tag">
                  -<?php echo $giamPT; ?>%
                  <span>GIẢM</span>
                </div>
              <?php endif; ?>
            </div>

            <div class="promo-body">
              <h2 class="promo-name">
                <?php echo htmlspecialchars($p['Tenmon']); ?>
              </h2>

              <?php if (!empty($p['Noidung'])): ?>
                <div class="promo-desc">
                  <?php echo htmlspecialchars($p['Noidung']); ?>
                </div>
              <?php endif; ?>

              <div class="price-row">
                <div class="price-current">
                  <?php echo number_format($giaban, 0, ',', '.'); ?>₫
                </div>
                <div class="price-old">
                  <?php echo number_format($giagoc, 0, ',', '.'); ?>₫
                </div>
              </div>

              <?php if ($giamTien > 0): ?>
                <div class="saved">
                  Tiết kiệm: <?php echo number_format($giamTien, 0, ',', '.'); ?>₫
                </div>
              <?php endif; ?>

              <div class="promo-footer">
                <div class="code-label">
                  Mã món: #<?php echo (int)$p['Mamon']; ?>
                </div>

                <!-- Nút thêm giỏ hàng: chặn click lan lên card -->
                <a
                  href="/pages/cart.php?add=<?php echo (int)$p['Mamon']; ?>&return_url=<?php echo urlencode($currentUrl); ?>"
                  class="btn-order btn-add-cart"
                  onclick="event.stopPropagation();">
                  Thêm giỏ
                  <span class="icon">🛒</span>
                </a>
              </div>

            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>


    <div id="toastAddedDeals" class="toast-added">
      Đã thêm vào giỏ hàng 🛒
    </div> 

</main>

<script src="../../assets/js/deals.js"> </script>




  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
