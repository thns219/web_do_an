<?php 
session_start();
require_once __DIR__ . '/../database/db.php';

// ---------------------------
// CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG
// ---------------------------
if (isset($_POST['update_status'])) {
    foreach ($_POST['MaDH'] as $id => $value) {
        $maDH = intval($id);
        $tinhtrang = $conn->real_escape_string($_POST['TinhtrangDH'][$id]);
        $conn->query("UPDATE Donhang SET TinhtrangDH='$tinhtrang' WHERE MaDH=$maDH");
    }
    header("Location: donhang.php");
    exit;
}

// Lấy danh sách đơn hàng
$sql = "
    SELECT Donhang.*, Users.Hoten 
    FROM Donhang
    LEFT JOIN Users ON Donhang.UID = Users.UID
    ORDER BY MaDH DESC
";
$dh = $conn->query($sql);
if (!$dh) die("Lỗi SQL: " . $conn->error);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/donhang.css"> <!-- CSS tách riêng -->
</head>
<body>
    <header class="admin-header">
    <h1>
        <img  style="with:35px; height: 35px; " src="../assets/img/logo.jpg" alt="Logo"> Ăn Húp Hội
    </h1>
    <div>
        <a href="../index.php" class="btn">Trang chủ</a>
        <a href="../pages/auth/logout.php" class="btn">Đăng xuất</a>
    </div>
</header>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>

<main class="admin-main">
    <h2>Quản lý đơn hàng</h2>

    <!-- Form lớn bao toàn bộ bảng -->
    <form method="POST">

        <button type="submit" name="update_status" class="update-all">Cập nhật</button>

        <table class="table">
            <tr>
                <th>Mã</th>
                <th>Khách</th>
                <th>Ngày đặt</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>

            <?php while ($d = $dh->fetch_assoc()): ?>
            <tr>
                <td><?= $d['MaDH']; ?></td>
                <td><?= $d['Hoten']; ?></td>
                <td><?= $d['Ngaydat']; ?></td>
                <td style="min-width:180px;">
                    <input type="hidden" name="MaDH[<?= $d['MaDH']; ?>]" value="<?= $d['MaDH']; ?>">

                    <select name="TinhtrangDH[<?= $d['MaDH']; ?>]" class="select-status">
                        <?php
                        $statuses = ['Đang xử lý','Đang giao','Đã giao','Đã hủy'];
                        foreach($statuses as $s): 
                        ?>
                            <option value="<?= $s; ?>" <?= $d['TinhtrangDH']==$s?'selected':''; ?>><?= $s; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Nút cũ ẩn đi -->
                    <button type="submit" name="update_status" class="hide-btn">Cập nhật</button>
                </td>
                <td>
                    <a href="donhang_view.php?id=<?= $d['MaDH']; ?>" class="btn">Xem chi tiết</a>
                </td>
                
            </tr>
            
            <?php endwhile; ?>
        </table>

    </form>
</main>

</body>
</html>
