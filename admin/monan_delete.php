<?php
session_start();
require_once __DIR__ . '/../database/db.php';

if (!isset($_GET['id'])) {
    header("Location: monan.php");
    exit;
}

$mamon = intval($_GET['id']);

// 1️⃣ Xóa chi tiết đơn hàng liên quan
$conn->query("DELETE FROM Chitietdonhang WHERE Mamon = $mamon");

// 2️⃣ Xóa bình luận liên quan
$conn->query("DELETE FROM Binhluan WHERE Mamon = $mamon");

// 3️⃣ Lấy thông tin món để xóa ảnh
$res = $conn->query("SELECT Anh FROM Monan WHERE Mamon = $mamon LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    if (!empty($row['Anh'])) {
        $fileAnh = __DIR__ . "/../" . $row['Anh'];
        if (file_exists($fileAnh)) {
            @unlink($fileAnh); // xóa file ảnh vật lý
        }
    }
}

// 4️⃣ Xóa món ăn
$conn->query("DELETE FROM Monan WHERE Mamon = $mamon");

// 5️⃣ Quay lại trang quản lý món ăn
header("Location: monan.php");
exit;
