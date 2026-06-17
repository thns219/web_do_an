<?php
// Script migrate_hash_passwords.php
// Chạy 1 lần: tìm các bản ghi Khachhang có Matkhau không phải hash (không bắt đầu bằng '$')
// và cập nhật bằng password_hash.
require_once __DIR__ . '/db.php';

if (!isset($conn)) {
    echo "Không thể kết nối DB.\n";
    exit(1);
}

echo "Bắt đầu di cư mật khẩu...\n";

$sql = "SELECT MaKH, Matkhau FROM Khachhang";
$res = $conn->query($sql);
if (!$res) {
    echo "Lỗi khi đọc Khachhang: " . $conn->error . "\n";
    exit(1);
}

$count = 0;
while ($row = $res->fetch_assoc()) {
    $id = intval($row['MaKH']);
    $pw = $row['Matkhau'];
    if (!is_string($pw) || $pw === '') continue;
    // Nếu không phải hash (ví dụ plaintext), Matkhau thường không bắt đầu bằng '$'
    if ($pw[0] !== '$') {
        $new = password_hash($pw, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE Khachhang SET Matkhau = ? WHERE MaKH = ?");
        if ($upd) {
            $upd->bind_param('si', $new, $id);
            if ($upd->execute()) {
                $count++;
            } else {
                echo "Không thể cập nhật MaKH={$id}: " . $upd->error . "\n";
            }
            $upd->close();
        } else {
            echo "Lỗi prepare update: " . $conn->error . "\n";
        }
    }
}

echo "Hoàn thành. Đã cập nhật $count mật khẩu.\n";

$conn->close();
