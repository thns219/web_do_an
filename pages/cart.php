<?php
session_start();
include_once '../database/db.php'; // phải tạo $conn (MySQLi)
require_once __DIR__ . '/../functions/functions.php';

// Lấy UID nếu đã đăng nhập
$uid = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// ----- Xử lý thêm sản phẩm vào giỏ từ ?add=ID&qty=?? -----
if (isset($_GET['add'])) {
    $prod_id = intval($_GET['add']);

    // Yêu cầu người dùng phải đăng nhập trước khi thêm vào giỏ
    if ($uid <= 0) {
        // Chuyển hướng tới trang đăng nhập, giữ lại URL hiện tại để trả về sau khi đăng nhập
        $current = $_SERVER['REQUEST_URI'] ?? '/pages/cart.php';
        header("Location: /pages/login.php?return_url=" . urlencode($current));
        exit;
    }

    // Lấy số lượng từ query (nếu có), mặc định = 1
    $qty = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
    if ($qty < 1) $qty = 1;
    if ($qty > 99) $qty = 99;

    // Kiểm tra sản phẩm có tồn tại không
    $sql = "SELECT Mamon AS id, Tenmon AS name, Giaban AS price, Anh AS image 
            FROM Monan 
            WHERE Mamon = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $prod_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            // Sản phẩm tồn tại → thao tác với bảng Giohang
            // Nếu đã có row (UID, Mamon) thì cộng thêm; nếu chưa thì insert
            $checkSql = "SELECT Soluong FROM Giohang WHERE UID = ? AND Mamon = ? LIMIT 1";
            if ($check = $conn->prepare($checkSql)) {
                $check->bind_param("ii", $uid, $prod_id);
                $check->execute();
                $r = $check->get_result();
                if ($r && $r->num_rows > 0) {
                    // Đã có → UPDATE cộng thêm
                    $row = $r->fetch_assoc();
                    $newQty = (int)$row['Soluong'] + $qty;
                    if ($newQty > 99) $newQty = 99;

                    $uSql = "UPDATE Giohang SET Soluong = ? WHERE UID = ? AND Mamon = ?";
                    if ($u = $conn->prepare($uSql)) {
                        $u->bind_param("iii", $newQty, $uid, $prod_id);
                        $u->execute();
                        $u->close();
                    }
                } else {
                    // Chưa có → INSERT
                    $iSql = "INSERT INTO Giohang (UID, Mamon, Soluong) VALUES (?, ?, ?)";
                    if ($i = $conn->prepare($iSql)) {
                        $i->bind_param("iii", $uid, $prod_id, $qty);
                        $i->execute();
                        $i->close();
                    }
                }
                $check->close();
            }
        }
        $stmt->close();
    }

    // Decide where to redirect after adding
    $return_url = $_GET['return_url'] ?? '';
    // Basic safety: allow only local paths (no http scheme)
    if ($return_url && (stripos($return_url, 'http://') === 0 || stripos($return_url, 'https://') === 0)) {
        $return_url = '';
    }
    if (!$return_url) $return_url = 'cart.php';
    header("Location: " . $return_url);
    exit;
}

// ----- Xóa sản phẩm khỏi giỏ hàng -----
if (isset($_GET['remove_id'])) {
    $rid = intval($_GET['remove_id']);
    if ($uid > 0) {
        $sql = "DELETE FROM Giohang WHERE UID = ? AND Mamon = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $uid, $rid);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("Location: cart.php");
    exit;
}

// ----- Tăng số lượng -----
if (isset($_GET['increase'])) {
    $id = intval($_GET['increase']);
    if ($uid > 0) {
        $sql = "UPDATE Giohang
                SET Soluong = CASE WHEN Soluong < 99 THEN Soluong + 1 ELSE 99 END
                WHERE UID = ? AND Mamon = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $uid, $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("Location: cart.php");
    exit;
}

// ----- Giảm số lượng -----
if (isset($_GET['decrease'])) {
    $id = intval($_GET['decrease']);
    if ($uid > 0) {
        $sql = "UPDATE Giohang
                SET Soluong = CASE WHEN Soluong > 1 THEN Soluong - 1 ELSE 1 END
                WHERE UID = ? AND Mamon = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $uid, $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("Location: cart.php");
    exit;
}

// ----- Cập nhật số lượng từ form -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if ($uid > 0 && !empty($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $id => $q) {
            $id = intval($id);   // chính là Mamon
            $q  = intval($q);
            if ($q < 1) $q = 1;
            if ($q > 99) $q = 99;

            $sql = "UPDATE Giohang SET Soluong = ? WHERE UID = ? AND Mamon = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("iii", $q, $uid, $id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    header("Location: cart.php");
    exit;
}

// ----- Lấy giỏ hàng từ DB -----
$cartItems  = [];
$totalPrice = 0;

if ($uid > 0 && isset($conn)) {
    $sql = "SELECT 
                g.Mamon AS id,
                m.Tenmon AS name,
                m.Giaban AS price,
                m.Anh AS image,
                g.Soluong AS qty
            FROM Giohang g
            JOIN Monan m ON g.Mamon = m.Mamon
            WHERE g.UID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $cartItems[$row['id']] = $row;
                $totalPrice += $row['price'] * $row['qty'];
            }
        }
        $stmt->close();
    }
}
?>

<?php include '../includes/header.php'; ?>
<link rel="stylesheet" href="../assets/css/cart.css">

<div class="cart-container">
    <h2>Giỏ hàng của bạn</h2>

    <?php if (empty($cartItems)) : ?>
        <p>Giỏ hàng đang trống!</p>
        <a href="menu.php" class="btn-link">Xem thực đơn</a>
    <?php else : ?>
        <form method="POST" action="cart.php">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Tạm tính</th>
                        <th>Xóa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td class="product-info">
                                <img src="<?php echo htmlspecialchars(resolveImagePath($item['image'] ?? '')); ?>" alt="">
                                <span><?php echo htmlspecialchars($item['name']); ?></span>
                            </td>
                            <td><?php echo number_format($item['price']); ?>₫</td>
                            <td class="qty-control">
                                <a href="cart.php?decrease=<?php echo (int)$item['id']; ?>" class="qty-btn">-</a>
                                <input type="number"
                                       name="qty[<?php echo (int)$item['id']; ?>]"
                                       value="<?php echo (int)$item['qty']; ?>"
                                       min="1"
                                       max="99">
                                <a href="cart.php?increase=<?php echo (int)$item['id']; ?>" class="qty-btn">+</a>
                            </td>
                            <td><?php echo number_format($item['price'] * $item['qty']); ?>₫</td>
                            <td><a href="cart.php?remove_id=<?php echo (int)$item['id']; ?>" class="remove-link">Xóa</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-footer">
                <button type="submit" name="update_cart" class="btn-update">Cập nhật giỏ hàng</button>
                <span class="total-price">Tổng cộng: <?php echo number_format($totalPrice); ?>₫</span>
            </div>
        </form>

        <div class="checkout">
            <a href="checkout.php" class="btn-checkout">Thanh toán</a>
        </div>
    <?php endif; ?>
</div>
