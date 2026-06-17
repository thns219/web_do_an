<?php
session_start();
header('Content-Type: application/json');

include_once __DIR__ . '/../database/db.php';

// Kiểm tra request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Lấy UID nếu đã đăng nhập
$uid = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// Yêu cầu đăng nhập
if ($uid <= 0) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thêm vào giỏ hàng', 'needLogin' => true]);
    exit;
}

// Lấy dữ liệu từ POST
$mamon = isset($_POST['mamon']) ? intval($_POST['mamon']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if ($mamon <= 0) {
    echo json_encode(['success' => false, 'message' => 'Món ăn không hợp lệ']);
    exit;
}

if ($quantity < 1) $quantity = 1;
if ($quantity > 99) $quantity = 99;

// Kiểm tra món ăn có tồn tại không
$sql = "SELECT Mamon, Tenmon, Giaban FROM monan WHERE Mamon = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $mamon);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Món ăn không tồn tại']);
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

// Kiểm tra xem đã có trong giỏ hàng chưa
$checkSql = "SELECT Soluong FROM giohang WHERE UID = ? AND Mamon = ? LIMIT 1";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("ii", $uid, $mamon);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    // Đã có → UPDATE cộng thêm
    $row = $checkResult->fetch_assoc();
    $newQty = (int)$row['Soluong'] + $quantity;
    if ($newQty > 99) $newQty = 99;

    $updateSql = "UPDATE giohang SET Soluong = ? WHERE UID = ? AND Mamon = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("iii", $newQty, $uid, $mamon);
    $updateStmt->execute();
    $updateStmt->close();
} else {
    // Chưa có → INSERT
    $insertSql = "INSERT INTO giohang (UID, Mamon, Soluong) VALUES (?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("iii", $uid, $mamon, $quantity);
    $insertStmt->execute();
    $insertStmt->close();
}

$checkStmt->close();

// Đếm tổng số món trong giỏ hàng
$countSql = "SELECT SUM(Soluong) as total FROM giohang WHERE UID = ?";
$countStmt = $conn->prepare($countSql);
$countStmt->bind_param("i", $uid);
$countStmt->execute();
$countResult = $countStmt->get_result();
$cartCount = 0;
if ($countResult->num_rows > 0) {
    $countRow = $countResult->fetch_assoc();
    $cartCount = (int)$countRow['total'];
}
$countStmt->close();

// Trả về kết quả
echo json_encode([
    'success' => true,
    'message' => 'Đã thêm ' . $product['Tenmon'] . ' vào giỏ hàng',
    'cartCount' => $cartCount
]);