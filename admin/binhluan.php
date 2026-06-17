<?php
session_start();
require_once __DIR__ . '/../database/db.php';

$sql = "
    SELECT Binhluan.*, Users.Hoten, Monan.Tenmon
    FROM Binhluan
    LEFT JOIN Users ON Binhluan.UID = Users.UID
    LEFT JOIN Monan ON Binhluan.Mamon = Monan.Mamon
    ORDER BY MaBL DESC
";

$bl = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quản lý bình luận</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
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
    <h2>Quản lý bình luận</h2>

    <table class="table">
        <tr>
            <th>ID</th><th>Khách</th><th>Món</th><th>Nội dung</th><th>Ngày</th>
        </tr>

        <?php while ($b = $bl->fetch_assoc()): ?>
        <tr>
            <td><?= $b['MaBL']; ?></td>
            <td><?= $b['Hoten']; ?></td>
            <td><?= $b['Tenmon']; ?></td>
            <td><?= $b['Noidung']; ?></td>
            <td><?= $b['Ngaytao']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</main>

</body>
</html>
