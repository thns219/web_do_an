<?php
session_start();
require_once __DIR__ . '/../database/db.php';

if (!isset($_GET['id'])) {
    die("Không có mã đơn hàng");
}

$maDH = intval($_GET['id']);

// ---------------------------
// CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG
// ---------------------------
if (isset($_POST['update_status'])) {
    $tinhtrang = $conn->real_escape_string($_POST['TinhtrangDH']);
    $conn->query("UPDATE Donhang SET TinhtrangDH='$tinhtrang' WHERE MaDH=$maDH");
    header("Location: donhang_view.php?id=$maDH");
    exit;
}

// Lấy thông tin đơn hàng
$sql = "
    SELECT Donhang.*, Users.Hoten, Users.DienthoaiKH, Users.Email
    FROM Donhang
    LEFT JOIN Users ON Donhang.UID = Users.UID
    WHERE MaDH=$maDH
    LIMIT 1
";
$res = $conn->query($sql);
if (!$res || $res->num_rows==0) die("Đơn hàng không tồn tại");
$order = $res->fetch_assoc();

// Lấy chi tiết món ăn
$sql_items = "
    SELECT Chitietdonhang.*, Monan.Tenmon
    FROM Chitietdonhang
    LEFT JOIN Monan ON Chitietdonhang.Mamon = Monan.Mamon
    WHERE MaDH=$maDH
";
$items = $conn->query($sql_items);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng #<?= $maDH; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>

<main class="admin-main">
    <h2>Chi tiết đơn hàng #<?= $maDH; ?></h2>

    <div class="form-box" style="margin-bottom:30px;">
        <h3>Thông tin khách hàng</h3>
        <p><strong>Khách:</strong> <?= $order['Hoten']; ?></p>
        <p><strong>Email:</strong> <?= $order['Email']; ?></p>
        <p><strong>Điện thoại:</strong> <?= $order['DienthoaiKH']; ?></p>
        <p><strong>Ngày đặt:</strong> <?= $order['Ngaydat']; ?></p>
        <p><strong>Ngày giao:</strong> <?= $order['Ngaygiao'] ?? '-'; ?></p>

        <form method="POST" style="margin-top:10px; display:flex; align-items:center; gap:10px;">
            <label><strong>Trạng thái:</strong></label>
            <select name="TinhtrangDH" style="padding:6px 12px; border-radius:8px;">
                <?php
                $statuses = ['Đang xử lý','Đang giao','Đã giao','Đã hủy'];
                foreach($statuses as $s):
                ?>
                    <option value="<?= $s; ?>" <?= $order['TinhtrangDH']==$s?'selected':''; ?>><?= $s; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="update_status" class="btn blue">Cập nhật</button>
        </form>
    </div>

    <h3>Chi tiết món ăn</h3>
    <table class="table">
        <tr>
            <th>STT</th>
            <th>Tên món</th>
            <th>Số lượng</th>
            <th>Đơn giá</th>
            <th>Thành tiền</th>
        </tr>

        <?php 
        $stt = 1; 
        $total = 0;
        while ($item = $items->fetch_assoc()):
            $thanhTien = $item['Soluong'] * $item['Dongia'];
            $total += $thanhTien;
        ?>
        <tr>
            <td><?= $stt++; ?></td>
            <td><?= $item['Tenmon']; ?></td>
            <td><?= $item['Soluong']; ?></td>
            <td><?= number_format($item['Dongia']); ?>đ</td>
            <td><?= number_format($thanhTien); ?>đ</td>
        </tr>
        <?php endwhile; ?>

        <tr>
            <td colspan="4" style="text-align:right;font-weight:600;">Tổng tiền:</td>
            <td style="font-weight:700; color:#4f46e5;"><?= number_format($total); ?>đ</td>
        </tr>
    </table>

    <a href="donhang.php" class="btn back-btn" style="margin-top:20px;">← Quay về danh sách</a>

</main>

</body>
</html>
