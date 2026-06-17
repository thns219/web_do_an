<?php
session_start();
include_once "../database/db.php"; // phải tạo $conn (MySQLi)
require_once __DIR__ . '/../functions/functions.php';

// ----- Xử lý thêm sản phẩm vào giỏ từ ?add=ID -----
if (isset($_GET['add'])) {
    $prod_id = intval($_GET['add']);

    $sql = "SELECT Mamon AS id, Tenmon AS name, Giaban AS price, Anh AS image FROM Monan WHERE Mamon = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $prod_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $product = $res->fetch_assoc();

            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

            if (isset($_SESSION['cart'][$prod_id])) {
                $_SESSION['cart'][$prod_id]['qty'] += 1;
            } else {
                $_SESSION['cart'][$prod_id] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'image' => $product['image'],
                    'qty' => 1
                ];
            }
        }
        $stmt->close();
    }

    // Redirect về trang hiện tại (giữ nguyên từ khóa tìm kiếm)
    $return_url = $_GET['return_url'] ?? $_SERVER['HTTP_REFERER'] ?? '../pages/search.php';
    header("Location: " . $return_url);
    exit;
}

// ----- Lấy từ khóa tìm kiếm từ URL ?keyword=
$keyword = "";
if (isset($_GET['keyword'])) {
    $keyword = trim($_GET['keyword']);
}

// ----- Nếu có từ khóa thì tìm kiếm sản phẩm
$products = [];
if (!empty($keyword)) {
    $sql = "SELECT * FROM Monan 
            WHERE Tenmon LIKE ? OR Noidung LIKE ?";
    $stmt = $conn->prepare($sql);
    $like = "%" . $keyword . "%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<?php include "../includes/header.php"; ?>

<h2 style="margin: 20px;">Kết quả tìm kiếm cho: <b><?php echo htmlspecialchars($keyword); ?></b></h2>

<div class="search-results">
<?php if (empty($products)) : ?>
    <div class="no-results">Không tìm thấy sản phẩm nào phù hợp!</div>
<?php else : ?>
    <?php foreach ($products as $p) : ?>
        <div class="product-card">
            <img src="<?php echo htmlspecialchars(resolveImagePath($p['Anh'] ?? '')); ?>" alt="<?php echo htmlspecialchars($p['Tenmon']); ?>">
            <div class="product-info">
                <h3><?php echo htmlspecialchars($p['Tenmon']); ?></h3>
                <p><?php echo htmlspecialchars($p['Noidung']); ?></p>
                <div class="product-price"><?php echo number_format($p['Giaban'],0,',','.'); ?>₫</div>
            </div>
            <!-- Thêm return_url để quay về trang tìm kiếm -->
            <a href="?add=<?php echo $p['Mamon']; ?>&return_url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="add-to-cart">Thêm vào giỏ</a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>

<link rel="stylesheet" href="../assets/css/search.css">
