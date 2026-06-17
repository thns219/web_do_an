<?php
session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../functions/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

$uid = (int)$_SESSION['user_id'];

// Hai message riêng: 1 cho profile, 1 cho đổi mật khẩu
$messageProfile = "";
$messagePass    = "";

// ======= XỬ LÝ POST =======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // -------- ĐỔI MẬT KHẨU --------
    if ($action === 'change_password') {
        $oldPass = $_POST['old_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $newPass2 = $_POST['new_password_confirm'] ?? '';

        if ($oldPass === '' || $newPass === '' || $newPass2 === '') {
            $messagePass = "Vui lòng nhập đầy đủ các trường.";
        } elseif (strlen($newPass) < 6) {
            $messagePass = "Mật khẩu mới phải từ 6 ký tự trở lên.";
        } elseif ($newPass !== $newPass2) {
            $messagePass = "Xác nhận mật khẩu mới không trùng khớp.";
        } else {
            // Lấy mật khẩu hiện tại từ DB
            $sqlGetPass = "SELECT Matkhau FROM Users WHERE UID = ? LIMIT 1";
            if ($stmt = $conn->prepare($sqlGetPass)) {
                $stmt->bind_param("i", $uid);
                $stmt->execute();
                $rs = $stmt->get_result();
                if ($rs && $rs->num_rows > 0) {
                    $row    = $rs->fetch_assoc();
                    $stored = $row['Matkhau'];
                    $ok     = false;

                    // Nếu mật khẩu đã hash (bắt đầu bằng $)
                    if (is_string($stored) && strlen($stored) > 0 && $stored[0] === '$') {
                        if (password_verify($oldPass, $stored)) {
                            $ok = true;
                        }
                    } else {
                        // Mật khẩu cũ vẫn dạng plaintext (do INSERT mẫu)
                        if ($oldPass === $stored) {
                            $ok = true;
                        }
                    }

                    if (!$ok) {
                        $messagePass = "Mật khẩu hiện tại không đúng.";
                    } else {
                        // Mật khẩu cũ đúng → cập nhật mật khẩu mới (hash)
                        $newHash = password_hash($newPass, PASSWORD_DEFAULT);
                        $sqlUpdatePass = "UPDATE Users SET Matkhau = ? WHERE UID = ?";
                        if ($u = $conn->prepare($sqlUpdatePass)) {
                            $u->bind_param("si", $newHash, $uid);
                            if ($u->execute()) {
                                $messagePass = "Đổi mật khẩu thành công.";
                            } else {
                                $messagePass = "Có lỗi khi cập nhật mật khẩu. Vui lòng thử lại.";
                            }
                            $u->close();
                        } else {
                            $messagePass = "Lỗi hệ thống (prepare update password).";
                        }
                    }
                } else {
                    $messagePass = "Không tìm thấy tài khoản.";
                }
                $stmt->close();
            } else {
                $messagePass = "Lỗi hệ thống (prepare select password).";
            }
        }

    // -------- CẬP NHẬT THÔNG TIN HỒ SƠ --------
    } elseif ($action === 'update_profile') {
        $hoten   = trim($_POST['Hoten'] ?? '');
        $email   = trim($_POST['Email'] ?? '');
        $phone   = trim($_POST['DienthoaiKH'] ?? '');
        $address = trim($_POST['DiachiKH'] ?? '');
        $dob     = trim($_POST['Ngaysinh'] ?? '');

        // Có thể validate email ở đây nếu muốn
        $sqlUpdate = "
            UPDATE Users
            SET Hoten = ?, Email = ?, DienthoaiKH = ?, DiachiKH = ?, Ngaysinh = ?
            WHERE UID = ?
        ";
        if ($stmt = $conn->prepare($sqlUpdate)) {
            $stmt->bind_param("sssssi", $hoten, $email, $phone, $address, $dob, $uid);
            if ($stmt->execute()) {
                $messageProfile = "Cập nhật hồ sơ thành công.";
            } else {
                $messageProfile = "Có lỗi khi cập nhật. Vui lòng thử lại.";
            }
            $stmt->close();
        } else {
            $messageProfile = "Lỗi hệ thống (prepare update profile).";
        }
    }
}

// ======= LẤY THÔNG TIN USER SAU KHI XỬ LÝ =======
$sqlUser = "
SELECT UID, Hoten, Taikhoan, Email, DienthoaiKH, DiachiKH, Ngaysinh
FROM Users
WHERE UID = ?
LIMIT 1
";
$stmt2 = $conn->prepare($sqlUser);
$stmt2->bind_param("i", $uid);
$stmt2->execute();
$rsUser = $stmt2->get_result();
$user = $rsUser->fetch_assoc();
$stmt2->close();

if (!$user) {
    // Không thấy user? Logout luôn
    session_destroy();
    header("Location: /login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Hồ sơ cá nhân</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="/assets/css/profile.css">
</head>

<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <div class="container">
    <div class="card">
      <h1>Hồ sơ cá nhân</h1>
      <div class="subtext">
        Xem và cập nhật thông tin tài khoản của bạn.
      </div>
    </div>

    <!-- CARD ĐỔI MẬT KHẨU (TRÊN CÙNG) -->
    <div class="card">
      <h2>Đổi mật khẩu</h2>
      <p class="subtext">Để bảo mật, vui lòng nhập mật khẩu hiện tại trước khi đổi.</p>

      <?php if (!empty($messagePass)): ?>
        <div class="message <?php echo (strpos($messagePass, 'thành công') !== false) ? 'ok' : 'error'; ?>">
          <?php echo htmlspecialchars($messagePass); ?>
        </div>
      <?php endif; ?>

      <form method="post" action="">
        <input type="hidden" name="action" value="change_password">

        <div class="form-group">
          <label>Mật khẩu hiện tại</label>
          <input type="password" name="old_password" required>
        </div>

        <div class="form-group">
          <label>Mật khẩu mới</label>
          <input type="password" name="new_password" required>
        </div>

        <div class="form-group">
          <label>Nhập lại mật khẩu mới</label>
          <input type="password" name="new_password_confirm" required>
        </div>

        <button type="submit" class="btn-submit">Đổi mật khẩu</button>
      </form>
    </div>

    <!-- CARD THÔNG TIN HỒ SƠ -->
    <div class="card">
      <h2>Thông tin cá nhân</h2>

      <?php if (!empty($messageProfile)): ?>
        <div class="message <?php echo (strpos($messageProfile, 'thành công') !== false) ? 'ok' : 'error'; ?>">
          <?php echo htmlspecialchars($messageProfile); ?>
        </div>
      <?php endif; ?>

      <form method="post" action="">
        <input type="hidden" name="action" value="update_profile">

        <div class="form-group">
          <label>Mã khách hàng</label>
          <input type="text" value="<?php echo (int)$user['UID']; ?>" readonly>
        </div>

        <div class="form-group">
          <label>Tên đăng nhập</label>
          <input type="text" value="<?php echo htmlspecialchars($user['Taikhoan']); ?>" readonly>
        </div>

        <div class="form-group">
          <label>Họ tên</label>
          <input type="text" name="Hoten" required
                 value="<?php echo htmlspecialchars($user['Hoten'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label>Email</label>
          <input type="email" name="Email"
                 value="<?php echo htmlspecialchars($user['Email'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label>Số điện thoại</label>
          <input type="text" name="DienthoaiKH"
                 value="<?php echo htmlspecialchars($user['DienthoaiKH'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label>Địa chỉ</label>
          <textarea name="DiachiKH" rows="3"><?php echo htmlspecialchars($user['DiachiKH'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
          <label>Ngày sinh</label>
          <input type="date" name="Ngaysinh"
                 value="<?php echo htmlspecialchars($user['Ngaysinh'] ?? ''); ?>">
        </div>

        <button type="submit" class="btn-submit">Lưu thay đổi</button>
      </form>
    </div>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
