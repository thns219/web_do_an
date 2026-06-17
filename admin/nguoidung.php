<?php 
session_start();
require_once __DIR__ . '/../database/db.php';

$users = $conn->query("SELECT * FROM Users ORDER BY UID DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quản lý người dùng</title>
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
    <h2>Quản lý người dùng</h2>

    <table class="table">
        <tr>
            <th>ID</th><th>Họ tên</th><th>Tài khoản</th><th>Email</th><th>SĐT</th><th>Role</th><th>Hành động</th>
        </tr>

        <?php while ($u = $users->fetch_assoc()): ?>
        <tr>
            <td><?= $u['UID']; ?></td>
            <td><?= $u['Hoten']; ?></td>
            <td><?= $u['Taikhoan']; ?></td>
            <td><?= $u['Email']; ?></td>
            <td><?= $u['DienthoaiKH']; ?></td>
            <td><?= $u['Role']; ?></td>
            <td>
                <a href="nguoidung_edit.php?id=<?= $u['UID']; ?>" class="btn blue">Sửa</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</main>

</body>
</html>
