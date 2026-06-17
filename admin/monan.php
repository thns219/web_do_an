<?php
session_start();
require_once __DIR__ . '/../database/db.php';

// Lấy danh sách loại món
$loai = $conn->query("SELECT * FROM Loaimonan");

// ------------------------------
// THÊM MÓN ĂN
// ------------------------------
if (isset($_POST['add'])) {
    $ten = $conn->real_escape_string($_POST['Tenmon']);
    $gia = floatval($_POST['Giaban']);
    $goc = floatval($_POST['Giagoc']);
    $nd  = $conn->real_escape_string($_POST['Noidung']);
    $maloai = intval($_POST['Maloai']);

    // Upload ảnh
    $anh = "";
    if (!empty($_FILES['Anh']['name'])) {
        $targetDir = "../assets/img/";
        $fileName = time() . "_" . basename($_FILES["Anh"]["name"]);
        $targetFile = $targetDir . $fileName;

        $allowed = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            move_uploaded_file($_FILES["Anh"]["tmp_name"], $targetFile);
            $anh = "assets/img/" . $fileName;
        }
    }

    // Nếu không upload ảnh thì lấy link text
    if ($anh == "" && !empty($_POST['Anh_text'])) {
        $anh = $_POST['Anh_text'];
    }

    $sql = "INSERT INTO Monan (Tenmon, Giaban, Giagoc, Noidung, Anh, Maloai)
            VALUES ('$ten','$gia','$goc','$nd','$anh','$maloai')";
    $conn->query($sql);

    header("Location: monan.php");
    exit;
}

// ------------------------------
// Lấy danh sách món ăn
// ------------------------------
$mon = $conn->query("
    SELECT Monan.*, Loaimonan.Tenloai
    FROM Monan
    LEFT JOIN Loaimonan ON Monan.Maloai = Loaimonan.Maloai
    ORDER BY Monan.Mamon DESC
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý món ăn</title>
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
    <h2>Quản lý món ăn</h2>

    <!-- Form thêm món -->
    <form method="POST" class="form-box" enctype="multipart/form-data">
        <h3>Thêm món ăn</h3>

        <input type="text" name="Tenmon" placeholder="Tên món" required>
        <input type="number" name="Giaban" placeholder="Giá bán" required>
        <input type="number" name="Giagoc" placeholder="Giá gốc" required>

        <label>Ảnh món ăn:</label>
        <input type="file" name="Anh" accept="image/*">
        <input type="text" name="Anh_text" placeholder="Hoặc nhập link ảnh (tùy chọn)">

        <textarea name="Noidung" placeholder="Mô tả"></textarea>

        <select name="Maloai" required>
            <?php 
            $loai->data_seek(0); // reset pointer
            while ($l = $loai->fetch_assoc()): ?>
                <option value="<?= $l['Maloai']; ?>"><?= htmlspecialchars($l['Tenloai']); ?></option>
            <?php endwhile; ?>
        </select>

        <button type="submit" name="add" class="btn">Thêm món</button>
    </form>

    <!-- Bảng danh sách món -->
    <table class="table">
        <tr>
            <th>STT</th>
            <th>ID</th>
            <th>Tên</th>
            <th>Giá</th>
            <th>Loại</th>
            <th>Ảnh</th>
            <th>Hành động</th>
        </tr>

        <?php 
        $stt = 1; 
        while ($m = $mon->fetch_assoc()): 
        ?>
        <tr>
            <td><?= $stt++; ?></td>
            <td><?= $m['Mamon']; ?></td>
            <td><?= htmlspecialchars($m['Tenmon']); ?></td>
            <td><?= number_format($m['Giaban']); ?>đ</td>
            <td><?= htmlspecialchars($m['Tenloai']); ?></td>
            <td>
                <?php if(!empty($m['Anh'])): ?>
                    <img src="../<?= $m['Anh']; ?>" height="50" alt="<?= htmlspecialchars($m['Tenmon']); ?>">
                <?php endif; ?>
            </td>
            <td>
                <a href="monan_edit.php?id=<?= $m['Mamon']; ?>" class="btn">Sửa</a>
                <a href="monan_delete.php?id=<?= $m['Mamon']; ?>" class="btn red"
                   onclick="return confirm('Xóa món này?')">Xóa</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</main>

</body>
</html>
