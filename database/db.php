<?php
$host = "localhost";   // hoặc 127.0.0.1
$user = "root";        // user mặc định của XAMPP/MAMP
$pass = "";            // mật khẩu mặc định rỗng (nếu bạn chưa đặt)
$dbname = "webbandoan";   // tên database bạn import

$conn = new mysqli($host, $user, $pass, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập charset để tránh lỗi tiếng Việt
$conn->set_charset("utf8mb4");
?>
