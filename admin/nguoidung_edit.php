<?php
session_start();
require_once __DIR__ . '/../database/db.php';

if (!isset($_GET['id'])) die("Không có người dùng");

// Lấy thông tin người dùng
$maKH = intval($_GET['id']);
$res = $conn->query("SELECT * FROM Users WHERE UID=$maKH LIMIT 1");
if (!$res || $res->num_rows==0) die("Người dùng không tồn tại");

$user = $res->fetch_assoc();

// Cập nhật thông tin người dùng
if (isset($_POST['update'])) {
    $hoten = $conn->real_escape_string($_POST['Hoten']);
    $email = $conn->real_escape_string($_POST['Email']);
    $sdt = $conn->real_escape_string($_POST['DienthoaiKH']);
    $role = $conn->real_escape_string($_POST['Role']);

    // Nếu nhập mật khẩu mới thì update
    $password_sql = "";
    if (!empty($_POST['Matkhau'])) {
        $matkhau = password_hash($_POST['Matkhau'], PASSWORD_DEFAULT);
        $password_sql = ", Matkhau='$matkhau'";
    }

    $conn->query("
        UPDATE Users SET 
        Hoten='$hoten',
        Email='$email',
        DienthoaiKH='$sdt',
        Role='$role'
        $password_sql
        WHERE UID=$maKH
    ");

    header("Location: nguoidung.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa người dùng</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/sidebar.php'; ?>

<main class="admin-main">
    <h2>Sửa người dùng #<?= $maKH; ?></h2>

    <form method="POST" class="form-box">
        <input type="text" name="Hoten" value="<?= htmlspecialchars($user['Hoten']); ?>" placeholder="Họ tên" required>
        <input type="email" name="Email" value="<?= htmlspecialchars($user['Email']); ?>" placeholder="Email">
        <input type="text" name="DienthoaiKH" value="<?= htmlspecialchars($user['DienthoaiKH']); ?>" placeholder="Số điện thoại">

        <label>Mật khẩu (để trống nếu không muốn thay đổi)</label>
        <input type="password" name="Matkhau" placeholder="Mật khẩu mới">

        <label>Role</label>
        <select name="Role" required>
            <option value="khach" <?= $user['Role']=='khach'?'selected':''; ?>>Khách</option>
            <option value="nhanvien" <?= $user['Role']=='nhanvien'?'selected':''; ?>>Nhân viên</option>
            <option value="admin" <?= $user['Role']=='admin'?'selected':''; ?>>Admin</option>
        </select>

        <button type="submit" name="update" class="btn blue">Cập nhật</button>
        <a href="nguoidung.php" class="btn">Hủy</a>
    </form>
</main>

</body>
</html>
