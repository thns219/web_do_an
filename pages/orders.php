<?php
session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../functions/functions.php';

// Bắt buộc đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = (int)$_SESSION['user_id'];

// Lấy type filter
$type = isset($_GET['type']) ? (int)$_GET['type'] : 0;

// Lấy danh sách đơn hàng của user, 1 dòng / 1 đơn
$sql = "
SELECT 
    d.MaDH,
    d.TinhtrangDH,
    d.Ngaydat,
    d.Ngaygiao,
    m.Tenmon,
    SUM(c.Soluong * c.Dongia)       AS TongTien,
    SUM(c.Soluong)                  AS TongSoLuong,
    MIN(m.Anh)                      AS AnhDaiDien
FROM Donhang d
JOIN Chitietdonhang c ON d.MaDH = c.MaDH
JOIN Monan m ON c.Mamon = m.Mamon
WHERE d.UID = {$uid}
GROUP BY d.MaDH, d.TinhtrangDH, d.Ngaydat, d.Ngaygiao
ORDER BY d.Ngaydat DESC
";

$dhang = [];
$rs = $conn->query($sql);
if ($rs) {
    while ($r = $rs->fetch_assoc()) {
        $dhang[] = $r;
    }
    $rs->free();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đơn hàng</title>
    <link rel="stylesheet" href="/assets/css/orders.css">

</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    
<main>
    <!-- Filter trạng thái -->
    <div class="select-oder-status">
        <a href="?type=0" class="<?php echo ($type === 0 ? 'active' : ''); ?>">Tất cả</a>
        <a href="?type=1" class="<?php echo ($type === 1 ? 'active' : ''); ?>">Đang xử lý</a>
        <a href="?type=2" class="<?php echo ($type === 2 ? 'active' : ''); ?>">Đang giao</a>
        <a href="?type=3" class="<?php echo ($type === 3 ? 'active' : ''); ?>">Đã giao</a>
        <a href="?type=4" class="<?php echo ($type === 4 ? 'active' : ''); ?>">Đã hủy</a>
    </div>

    <?php $hasOrder = false; ?>

    <div class="list-oders">
    <?php foreach ($dhang as $rdh): ?>
        <?php
            $show = false;

            if ($type === 0 || $type > 4) $show = true;
            if ($type === 1 && $rdh['TinhtrangDH'] === "Đang xử lý") $show = true;
            if ($type === 2 && $rdh['TinhtrangDH'] === "Đang giao")   $show = true;
            if ($type === 3 && $rdh['TinhtrangDH'] === "Đã giao")     $show = true;
            if ($type === 4 && $rdh['TinhtrangDH'] === "Đã hủy")      $show = true;

            if ($show) $hasOrder = true;
        ?>

        <?php if ($show): ?>
        <div class="oder">
            <div class="card-food">
                <div class="food">
                    <div>
                        <img class="img-food" 
                             src="<?php echo "../../" . htmlspecialchars($rdh['AnhDaiDien'] ?? 'assets/img/default.jpg'); ?>" 
                             alt="Ảnh món">
                    </div>
                    <div class="food-text">
                        <div>Đơn #<?php echo (int)$rdh['MaDH']; ?></div>
                        <div> <?php echo $rdh['Tenmon'] . "..."; ?></div>
                        <div><?php echo (int)$rdh['TongSoLuong']; ?> món</div>
                    </div>
                </div>
                <div class="oder-status">
                    <?php echo htmlspecialchars($rdh['TinhtrangDH']); ?>
                </div>
            </div>

            <div>
                <div>Ngày đặt: <?php echo htmlspecialchars($rdh['Ngaydat']); ?></div>
                <?php if (!empty($rdh['Ngaygiao'])): ?>
                    <div>Ngày giao: <?php echo htmlspecialchars($rdh['Ngaygiao']); ?></div>
                <?php endif; ?>
            </div>
            
            <div>
                Thanh toán:
                <span class="total">
                    <?php echo number_format($rdh['TongTien'], 0, ',', '.'); ?>₫
                </span>
            </div>

            <div>
                <a href="orderdetails.php?madh=<?php echo (int)$rdh['MaDH']; ?>" class="detail-oder">
                    Chi tiết
                </a>
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if (!$hasOrder): ?>
        <div class="no-order">Không có đơn hàng</div>
    <?php endif; ?>
    </div>
  </main>
    
            <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
    
</html>
