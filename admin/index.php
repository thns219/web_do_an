<?php
session_start();
require_once __DIR__ . '/../database/db.php';
//không có role k phải tài khoản admin thì out

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    echo"bạn không phải admin";
    header("Refresh: 2; url=../index.php");
    exit();

}


// --- Hàm lấy dữ liệu ---
function countMenuItems($conn){ $result = $conn->query("SELECT COUNT(*) as total FROM Monan"); return $result->fetch_assoc()['total']; }
function countOrders($conn){ $result = $conn->query("SELECT COUNT(*) as total FROM Donhang"); return $result->fetch_assoc()['total']; }
function countUsers($conn){ $result = $conn->query("SELECT COUNT(*) as total FROM Users WHERE Role='khach'"); return $result->fetch_assoc()['total']; }
function countComments($conn){ $result = $conn->query("SELECT COUNT(*) as total FROM Binhluan"); return $result->fetch_assoc()['total']; }

// --- Lấy tổng số ---
$totalItems = countMenuItems($conn);
$totalOrders = countOrders($conn);
$totalUsers = countUsers($conn);
$totalComments = countComments($conn);

// --- Dữ liệu vòng chart đơn hàng theo trạng thái ---
$statusQuery = $conn->query("SELECT TinhtrangDH, COUNT(*) as total FROM Donhang GROUP BY TinhtrangDH");
$statusLabels = []; $statusData = [];
while($row = $statusQuery->fetch_assoc()){
    $statusLabels[] = $row['TinhtrangDH'];
    $statusData[] = (int)$row['total'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Ăn húp hội</title>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="../assets/css/admin.css">
    
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="icon" type="image/jpg" sizes="16x16" href="../assets/img/logo.jpg">
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


<div class="admin-sidebar">
    <ul>
        <li><a href="index.php"><i class='bx bxs-dashboard'></i> Dashboard</a></li>
        <li><a href="monan.php"><i class='bx bxs-food-menu'></i> Quản lý món ăn</a></li>
        <li><a href="donhang.php"><i class='bx bxs-cart'></i> Quản lý đơn hàng</a></li>
        <li><a href="nguoidung.php"><i class='bx bxs-user'></i> Quản lý người dùng</a></li>
        <li><a href="binhluan.php"><i class='bx bxs-comment-detail'></i> Quản lý bình luận</a></li>
    </ul>
</div>

<main class="admin-main">
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Tổng món ăn</h3>
            <p><?= $totalItems; ?></p>
        </div>
        <div class="stat-card">
            <h3>Tổng đơn hàng</h3>
            <p><?= $totalOrders; ?></p>
        </div>
        <div class="stat-card">
            <h3>Tổng khách hàng</h3>
            <p><?= $totalUsers; ?></p>
        </div>
        <div class="stat-card">
            <h3>Tổng bình luận</h3>
            <p><?= $totalComments; ?></p>
        </div>
    </div>

    <div class="chart-container">
        <h2>Đơn hàng theo trạng thái</h2>
        <canvas id="orderChart"></canvas>
    </div>

    <div class="quick-actions">
        <h2>Hành động nhanh</h2>
        <div class="action-buttons">
            <a href="monan.php" class="btn">Thêm / Sửa món ăn</a>
            <a href="donhang.php" class="btn">Xem đơn hàng</a>
            <a href="nguoidung.php" class="btn">Quản lý người dùng</a>
            <a href="binhluan.php" class="btn">Xem bình luận</a>
        </div>
    </div>
</main>

<script src="../assets/js/admin.js"></script>
<script>
const statusLabels = <?= json_encode($statusLabels); ?>;
const statusData = <?= json_encode($statusData); ?>;
initOrderChart(statusLabels, statusData);
</script>
</body>
</html>
