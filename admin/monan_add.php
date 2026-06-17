<?php
require_once __DIR__ . '/../database/db.php';

if (isset($_POST['submit'])) {
    $ten = $_POST['ten'];
    $gia = $_POST['gia'];

    $file = $_FILES['anh']['name'];
    move_uploaded_file($_FILES['anh']['tmp_name'], "../assets/img/" . $file);

    $sql = "INSERT INTO monan (Tenmon, Giaban, Anh) VALUES (?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sis", $ten, $gia, $file);
    $stmt->execute();

    header("Location: monan.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Thêm món</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="admin-main">
    <h1>Thêm món ăn</h1>

    <form method="POST" enctype="multipart/form-data">
        <label>Tên món:</label><br>
        <input type="text" name="ten"><br><br>

        <label>Giá bán:</label><br>
        <input type="number" name="gia"><br><br>

        <label>Ảnh:</label><br>
        <input type="file" name="anh"><br><br>

        <button type="submit" name="submit">Thêm</button>
    </form>
</div>

</body>
</html>
