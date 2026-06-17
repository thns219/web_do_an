<?php
session_start();
include_once '../database/db.php'; // tạo $conn (MySQLi)
require_once __DIR__ . '/../functions/functions.php';

// Bắt buộc đăng nhập
if (empty($_SESSION['user_id'])) {
    $current = $_SERVER['REQUEST_URI'] ?? '/pages/checkout.php';
    header("Location: /pages/login.php?return_url=" . urlencode($current));
    exit;
}

$uid = (int)($_SESSION['user_id'] ?? 0);

if (!$uid) {
    header("Location: /pages/login.php");
    exit;
}

// ----- Cập nhật số điện thoại / địa chỉ (form Lưu thông tin) -----
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && !isset($_POST['place_order'])
    && (isset($_POST['DienthoaiKH']) || isset($_POST['DiachiKH']))) {

    $dienthoai = trim($_POST['DienthoaiKH'] ?? '');
    $diachi    = trim($_POST['DiachiKH'] ?? '');

    if (isset($conn)) {
        $sql = "UPDATE Users SET DienthoaiKH = ?, DiachiKH = ? WHERE UID = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $dienthoai, $diachi, $uid);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: checkout.php");
    exit;
}

// ----- Lấy giỏ hàng từ bảng Giohang -----
$cartItems  = [];
$totalPrice = 0;

if (isset($conn)) {
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
                $cartItems[] = $row;
                $totalPrice += $row['price'] * $row['qty'];
            }
        }
        $stmt->close();
    }
}

// Nếu giỏ hàng trống → quay lại giỏ
if (empty($cartItems)) {
    header("Location: cart.php");
    exit;
}

// ----- Lấy thông tin user (khách hàng) -----
$customer = null;
if (isset($conn)) {
    $sql = "SELECT UID AS UID, Hoten, Email, DienthoaiKH, DiachiKH 
            FROM Users 
            WHERE UID = ? 
            LIMIT 1";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $customer = $res->fetch_assoc();
        }
        $stmt->close();
    }
}

if (!$customer) {
    echo "Không tìm thấy user, yêu cầu đăng nhập lại!";
    exit;
}

$errorMsg = "";
$success = false;

// ----- Xử lý đặt hàng (Thanh toán khi nhận hàng) -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Double-check giỏ hàng còn tồn tại
    if (empty($cartItems)) {
        header("Location: cart.php");
        exit;
    }

    // Bắt đầu transaction để tránh đơn bị lệch dữ liệu
    $conn->begin_transaction();

    try {
        // 1) Tạo đơn hàng mới trong Donhang
        $sqlInsertOrder = "INSERT INTO Donhang (UID) VALUES (?)";
        $stmtOrder = $conn->prepare($sqlInsertOrder);
        if (!$stmtOrder) {
            throw new Exception("Lỗi chuẩn bị câu lệnh đơn hàng.");
        }
        $stmtOrder->bind_param("i", $uid);
        if (!$stmtOrder->execute()) {
            throw new Exception("Không thể tạo đơn hàng.");
        }
        $orderId = $stmtOrder->insert_id;
        $stmtOrder->close();

        // 2) Thêm từng món trong giỏ vào Chitietdonhang
        $sqlInsertItem = "INSERT INTO Chitietdonhang (MaDH, Mamon, Soluong, Dongia) 
                          VALUES (?, ?, ?, ?)";
        $stmtItem = $conn->prepare($sqlInsertItem);
        if (!$stmtItem) {
            throw new Exception("Lỗi chuẩn bị câu lệnh chi tiết đơn hàng.");
        }

        foreach ($cartItems as $item) {
            $mamon  = (int)$item['id'];      // id món ăn
            $qty    = (int)$item['qty'];     // số lượng
            $price  = (float)$item['price']; // đơn giá (Giaban hiện tại)

            if ($qty < 1) $qty = 1;

            $stmtItem->bind_param("iiid", $orderId, $mamon, $qty, $price);
            if (!$stmtItem->execute()) {
                throw new Exception("Không thể thêm chi tiết đơn hàng cho món ID $mamon.");
            }
        }

        $stmtItem->close();

        // 3) Xóa giỏ hàng trong DB của user này
        $sqlDelCart = "DELETE FROM Giohang WHERE UID = ?";
        if ($stmtDel = $conn->prepare($sqlDelCart)) {
            $stmtDel->bind_param("i", $uid);
            $stmtDel->execute();
            $stmtDel->close();
        }

        // 4) Commit transaction
        $conn->commit();

        // 5) Chuyển sang trang lịch sử đơn (orders.php)
        header("Location: orders.php?placed=1");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $errorMsg = "Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại.";
        // Debug (nếu cần):
        // $errorMsg .= " Chi tiết: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán - Thanh toán khi nhận hàng</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="/assets/css/checkout.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="checkout-container">
        <a href="cart.php" class="back-link">← Quay lại giỏ hàng</a>

        <h1 class="page-title">
            <span class="icon">🧾</span>
            Thanh toán
        </h1>

        <?php if (!empty($errorMsg)): ?>
            <div class="error-msg">
                <?php echo htmlspecialchars($errorMsg); ?>
            </div>
        <?php endif; ?>


        <div class="grid">
            <!-- Cột trái: Thông tin nhận hàng -->
            <div class="card">
                <h3>Thông tin nhận hàng</h3>
                <div class="muted">Dùng thông tin từ hồ sơ tài khoản của bạn.</div>

                <div class="info-row">
                    <div><strong>Họ tên:</strong> <?php echo htmlspecialchars($customer['Hoten']); ?></div>
                    <div><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($customer['DienthoaiKH'] ?? ''); ?></div>
                    <div><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($customer['DiachiKH'] ?? ''); ?></div>
                </div>

                <?php if (empty($customer['DienthoaiKH']) || empty($customer['DiachiKH'])): ?>
                    <form id="formuser" action="" method="post">
                        <div class="form-group">
                            <label>Số điện thoại</label>
                            <input type="text" name="DienthoaiKH"
                                   value="<?php echo htmlspecialchars($customer['DienthoaiKH'] ?? ''); ?>">
                        </div>

                        <div class="form-group align-center">
                            <label>Địa chỉ</label>
                            <textarea name="DiachiKH" rows="3"><?php echo htmlspecialchars($customer['DiachiKH'] ?? ''); ?></textarea>
                        </div>
                        <button id="btnSave" class="btn-save" type="submit">Lưu</button>
                    </form>
                <?php else: ?>
                    <button id="updateUser" class="btn-save" type="button">Cập nhật</button>
                    <form id="formuserr" style="display: none;" action="" method="post">
                        <div class="form-group">
                            <label>Số điện thoại</label>
                            <input type="text" name="DienthoaiKH"
                                   value="<?php echo htmlspecialchars($customer['DienthoaiKH'] ?? ''); ?>">
                        </div>

                        <div class="form-group align-center">
                            <label>Địa chỉ</label>
                            <textarea name="DiachiKH" rows="3"><?php echo htmlspecialchars($customer['DiachiKH'] ?? ''); ?></textarea>
                        </div>
                        <button id="btnSave2" class="btn-save" type="submit">Lưu</button>
                    </form>
                <?php endif; ?>

                <div class="cod-box">
                    <span class="icon">💰</span>
                    <div>
                        <strong>Phương thức thanh toán:</strong> Thanh toán khi nhận hàng (COD)<br>
                        <span class="muted">
                            Bạn sẽ thanh toán trực tiếp cho shipper khi nhận được món ăn.  
                            Hiện tại hệ thống chưa hỗ trợ thanh toán online.
                        </span>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Đơn hàng -->
            <div class="card">
                <h3>Đơn hàng của bạn</h3>

                <div class="items-list">
                    <?php foreach ($cartItems as $item): ?>
                        <?php
                            $lineTotal = $item['price'] * $item['qty'];
                        ?>
                        <div class="item-row">
                            <img src="<?php echo htmlspecialchars(resolveImagePath($item['image'] ?? '')); ?>" 
                                 alt=""
                                 class="item-img">
                            <div class="item-main">
                                <div class="item-name">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </div>
                                <div class="item-meta">
                                    Giá: <?php echo number_format($item['price'], 0, ',', '.'); ?>₫ 
                                    · Số lượng: x<?php echo (int)$item['qty']; ?>
                                </div>
                            </div>
                            <div class="item-total">
                                <?php echo number_format($lineTotal, 0, ',', '.'); ?>₫
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-row">
                    <span>Tạm tính</span>
                    <span class="value">
                        <?php echo number_format($totalPrice, 0, ',', '.'); ?>₫
                    </span>
                </div>

                <div class="summary-row">
                    <span>Phí vận chuyển</span>
                    <span class="value">Miễn phí</span>
                </div>

                <div class="summary-row total">
                    <span>Tổng thanh toán</span>
                    <span class="value">
                        <?php echo number_format($totalPrice, 0, ',', '.'); ?>₫
                    </span>
                </div>

                <form method="POST" action="checkout.php" style="margin-top: 8px;">
                    <input type="hidden" name="place_order" value="1">
                    <button type="submit" class="btn-place-order">
                        Đặt hàng (Thanh toán khi nhận hàng)
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    // JS nhỏ để show form cập nhật
    const btnUpdate = document.getElementById('updateUser');
    const formEdit  = document.getElementById('formuserr');
    if (btnUpdate && formEdit) {
        btnUpdate.addEventListener('click', () => {
            if (formEdit.style.display === 'none' || formEdit.style.display === '') {
                formEdit.style.display = 'block';
            } else {
                formEdit.style.display = 'none';
            }
        });
    }
    </script>

    <script src="../../assets/js/checkout.js"></script>  

    <?php include '../includes/footer.php'; ?>
</body>
</html>
