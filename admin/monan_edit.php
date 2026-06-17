<?php
session_start();
require_once __DIR__ . '/../database/db.php';

// Lấy ID món
if (!isset($_GET['id'])) {
    header("Location: monan.php");
    exit;
}
$id = intval($_GET['id']);

// Lấy thông tin món
$qr = $conn->query("SELECT * FROM Monan WHERE Mamon=$id");
$mon = $qr->fetch_assoc();

// Lấy danh sách loại
$loai = $conn->query("SELECT * FROM Loaimonan");

// Xử lý cập nhật
if (isset($_POST['update'])) {
    $ten = $_POST['Tenmon'];
    $gia = $_POST['Giaban'];
    $goc = $_POST['Giagoc'];
    $nd  = $_POST['Noidung'];
    $maloai = $_POST['Maloai'];

    // Ảnh
    $anh = $mon['Anh']; // giữ ảnh cũ mặc định

    // Nếu upload ảnh mới
    if (!empty($_FILES['Anh']['name'])) {
        $targetDir = "../assets/img/";
        $fileName = time() . "_" . basename($_FILES["Anh"]["name"]);
        $targetFile = $targetDir . $fileName;

        $allowed = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            move_uploaded_file($_FILES['Anh']['tmp_name'], $targetFile);
            $anh = "assets/img/" . $fileName;

            // Xóa ảnh cũ để tránh rác
            if (file_exists("../" . $mon['Anh'])) {
                @unlink("../" . $mon['Anh']);
            }
        }
    }

    // Nếu người dùng nhập link ảnh mới
    if (!empty($_POST['Anh_text'])) {
        $anh = $_POST['Anh_text'];
    }

    // Cập nhật món
    $sql = "
        UPDATE Monan SET 
            Tenmon='$ten', 
            Giaban='$gia', 
            Giagoc='$goc',
            Noidung='$nd',
            Anh='$anh',
            Maloai='$maloai'
        WHERE Mamon=$id
    ";

    $conn->query($sql);
    header("Location: monan.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa món ăn</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT']. '/includes/sidebar.php'; ?>

<main class="admin-main">
    <h2>Sửa món ăn</h2>

    <form method="POST" enctype="multipart/form-data" class="form-box">
        <input type="text" name="Tenmon" value="<?= htmlspecialchars($mon['Tenmon']); ?>" required>
        <input type="number" name="Giaban" value="<?= $mon['Giaban']; ?>" required>
        <input type="number" name="Giagoc" value="<?= $mon['Giagoc']; ?>" required>

        <label>Ảnh hiện tại:</label><br>
        <img src="../<?= $mon['Anh']; ?>" height="80" style="margin:8px 0;"><br><br>

        <label>Chọn ảnh mới (tùy chọn):</label>
        <input type="file" name="Anh" accept="image/*">

        <input type="text" name="Anh_text" placeholder="Hoặc nhập link ảnh" value="<?= htmlspecialchars($mon['Anh']); ?>">

        <textarea name="Noidung" placeholder="Mô tả"><?= htmlspecialchars($mon['Noidung']); ?></textarea>

        <select name="Maloai">
            <?php while ($l = $loai->fetch_assoc()): ?>
                <option value="<?= $l['Maloai']; ?>" 
                        <?= $l['Maloai']==$mon['Maloai']?'selected':''; ?>>
                    <?= $l['Tenloai']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit" name="update" class="btn">Cập nhật</button>
    </form>
</main>

</body>
</html>
