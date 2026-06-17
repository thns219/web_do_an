<?php
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    echo"bạn không phải admin";
    header("Refresh: 2; url=../index.php");
    exit();

}
?>
<div class="admin-sidebar">
    <ul>
        <li><a href="index.php">Dashboard</a></li>
        <li><a href="monan.php">Quản lý món ăn</a></li>
        <li><a href="donhang.php">Quản lý đơn hàng</a></li>
        <li><a href="nguoidung.php">Quản lý người dùng</a></li>
        <li><a href="binhluan.php">Quản lý bình luận</a></li>
    </ul>
</div>
