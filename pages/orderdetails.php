<?php
session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../functions/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

$uid  = (int)$_SESSION['user_id'];
$madh = isset($_GET['madh']) ? (int)$_GET['madh'] : 0;

if ($madh <= 0) {
    header("Location: orders.php");
    exit();
}

$cancelMessage = "";

// Xử lý hủy đơn: chỉ được hủy khi đang xử lý
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $sqlCancel = "
        UPDATE Donhang
        SET TinhtrangDH = 'Đã hủy'
        WHERE MaDH = ? AND UID = ? AND TinhtrangDH = 'Đang xử lý'
        LIMIT 1
    ";
    if ($stmtC = $conn->prepare($sqlCancel)) {
        $stmtC->bind_param("ii", $madh, $uid);
        $stmtC->execute();
        if ($stmtC->affected_rows > 0) {
            // hủy thành công -> redirect để tránh F5 submit lại
            $stmtC->close();
            header("Location: orderdetails.php?madh=" . $madh . "&cancel=1");
            exit();
        } else {
            // không hủy được (có thể vì không phải 'Đang xử lý')
            $cancelMessage = "Không thể hủy đơn. Đơn có thể đã được xử lý hoặc không tồn tại.";
        }
        $stmtC->close();
    } else {
        $cancelMessage = "Lỗi hệ thống khi hủy đơn. Vui lòng thử lại.";
    }
}

// Lấy thông tin đơn hàng (kiểm tra thuộc về user)
$sqlOrder = "
SELECT 
    d.MaDH,
    d.TinhtrangDH,
    d.Ngaydat,
    d.Ngaygiao,
    u.Hoten,
    u.DienthoaiKH,
    u.DiachiKH
FROM Donhang d
JOIN Users u ON d.UID = u.UID
WHERE d.MaDH = ? AND d.UID = ?
LIMIT 1
";

$stmt = $conn->prepare($sqlOrder);
$stmt->bind_param("ii", $madh, $uid);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    // Đơn không tồn tại hoặc không thuộc về user
    header("Location: orders.php");
    exit();
}

// Nếu vừa hủy xong redirect quay lại
if (isset($_GET['cancel']) && $_GET['cancel'] == 1) {
    $cancelMessage = "Bạn đã hủy đơn hàng này thành công.";
}

// Lấy danh sách món trong đơn
$sqlItems = "
SELECT 
    m.Tenmon,
    m.Anh,
    c.Soluong,
    c.Dongia,
    (c.Soluong * c.Dongia) AS ThanhTien
FROM Chitietdonhang c
JOIN Monan m ON c.Mamon = m.Mamon
WHERE c.MaDH = ?
";

$stmt2 = $conn->prepare($sqlItems);
$stmt2->bind_param("i", $madh);
$stmt2->execute();
$itemsRs = $stmt2->get_result();

$items = [];
$totalAmount = 0;

while ($row = $itemsRs->fetch_assoc()) {
    $items[] = $row;
    $totalAmount += $row['ThanhTien'];
}

$stmt2->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Chi tiết đơn #<?php echo $order['MaDH']; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="/assets/css/orderdetails.css">
</head>

<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <div class="container">
    <a href="orders.php" class="back-link">← Quay lại đơn hàng</a>

    <?php if (!empty($cancelMessage)): ?>
      <div class="msg-box <?php echo (strpos($cancelMessage, 'thành công') !== false) ? 'msg-success' : 'msg-error'; ?>">
        <?php echo htmlspecialchars($cancelMessage); ?>
      </div>
    <?php endif; ?>

    <div class="card" style="margin-top: 12px;">
      <div class="card-header">
        <h2>Đơn hàng #<?php echo (int)$order['MaDH']; ?></h2>
        <span class="order-status">
          <?php echo htmlspecialchars($order['TinhtrangDH']); ?>
        </span>

        <?php if ($order['TinhtrangDH'] === 'Đang xử lý'): ?>
          <form
            method="POST"
            action="orderdetails.php?madh=<?php echo (int)$order['MaDH']; ?>"
            class="cancel-form"
            onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn này?');"
          >
            <input type="hidden" name="cancel_order" value="1">
            <button type="submit" class="btn-cancel">Hủy đơn</button>
          </form>
        <?php endif; ?>
      </div>
      <div class="muted">
        Ngày đặt: <?php echo htmlspecialchars($order['Ngaydat']); ?><br>
        <?php if (!empty($order['Ngaygiao'])): ?>
          Ngày giao: <?php echo htmlspecialchars($order['Ngaygiao']); ?><br>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <h3 style="margin-top:0;margin-bottom:8px;font-size:16px;">Thông tin nhận hàng</h3>
      <div class="muted">
        <?php echo htmlspecialchars($order['Hoten']); ?><br>
        SĐT: <?php echo htmlspecialchars($order['DienthoaiKH'] ?? ''); ?><br>
        Địa chỉ: <?php echo htmlspecialchars($order['DiachiKH'] ?? ''); ?>
      </div>
    </div>

    <div class="card">
      <h3 style="margin-top:0;margin-bottom:8px;font-size:16px;">Sản phẩm</h3>

      <div class="items-list">
        <?php foreach ($items as $it): ?>
          <div class="item-row">
            <img src="<?php echo "../../" . htmlspecialchars($it['Anh'] ?? 'assets/img/default.jpg'); ?>"
                 alt="Ảnh món"
                 class="item-img">
            <div class="item-info">
              <div class="item-name"><?php echo htmlspecialchars($it['Tenmon']); ?></div>
              <div class="item-meta">
                Số lượng: x<?php echo (int)$it['Soluong']; ?>
              </div>
            </div>
            <div class="item-price">
              <?php echo number_format($it['ThanhTien'], 0, ',', '.'); ?>₫
              <span>
                Đơn giá: <?php echo number_format($it['Dongia'], 0, ',', '.'); ?>₫
              </span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="total-box">
        <span class="total-label">Tổng thanh toán:</span>
        <div class="total-value">
          <?php echo number_format($totalAmount, 0, ',', '.'); ?>₫
        </div>
      </div>
    </div>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
