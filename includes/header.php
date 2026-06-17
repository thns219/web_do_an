<?php
// header.php - include this at top of pages where header needed
if (session_status() === PHP_SESSION_NONE) session_start();
//include_once "webbandoan.php"; // phải trả về $conn (MySQLi)

if( isset($_SESSION['role'])){
    $role = $_SESSION['role'];
}
else{
    $role = null;
}
 



// ----- Lấy thông tin user -----
$userInfo = null;
if (!empty($_SESSION['user_id']) && isset($conn)) {
  $uid = intval($_SESSION['user_id']);
  // Lấy thông tin từ Users (sử dụng alias để tương thích với code hiện có)
  $sql = "SELECT UID AS id, Hoten AS username, Email AS email, '' AS avatar FROM Users WHERE UID = ?";
  if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
      $userInfo = $res->fetch_assoc();
    }
    $stmt->close();
  }
}


// ----- Đếm giỏ hàng (session) -----
$cartCount = 0;
if (!empty($_SESSION['user_id']) && isset($conn)) {
  $uid = intval($_SESSION['user_id']);

  // Lấy thông tin từ số lượng từ giỏ hàng
    $sql = "SELECT SUM(Soluong) AS total FROM Giohang WHERE UID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            $cartCount = $row['total'];
        }
        $stmt->close();
    }

}
// (Notifications removed) — no notification queries or UI in header

// đường dẫn resources
$cssPath = "/assets/css/header.css";
$jsPath  = "/assets/js/header.js";
$logo    = '/assets/img/logo.jpg';
$logo_check = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/logo.jpg';
$avatarDefault = "/assets/img/default-avatar.jpg";
?>


<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Ăn Húp Hội</title>
  <link rel="stylesheet" href="<?php echo htmlspecialchars($cssPath); ?>">
</head>
<body>

<header class="site-header" role="banner">
  <div class="header-inner">

    <!-- LEFT: logo -->
    <div class="header-left">
      <a href="/index.php" class="brand-link">
        <?php if (file_exists($logo_check)): ?>
          <img src="<?php echo $logo; ?>" alt="Logo" class="brand-logo">
        <?php else: ?>
          <div class="brand-icon">🍜</div>
        <?php endif; ?>
        <div class="brand-text">
          <span class="brand-name">Ăn Húp Hội</span>
          <small class="brand-slogan">Deals &amp; Món ngon</small>
        </div>
      </a>
    </div>

    <!-- CENTER: nav + search -->
    <div class="header-center">
      <nav class="main-nav" aria-label="Primary navigation">
        <ul class="nav-list">
          <li><a href="/index.php">Trang chủ</a></li>
          <li><a href="/pages/menu.php">Thực đơn</a></li>
          <li><a href="/pages/deals.php">Khuyến mãi</a></li>
        </ul>
      </nav>

      <div class="search-wrap">
          
  
               
        <input id="header-search" class="search-input" type="search" placeholder="Tìm món,..." aria-label="Tìm kiếm">
        <button id="search-btn" class="search-btn" aria-label="Tìm">🔍</button>
          
        <ul id="search-suggestions" class="search-suggestions" role="listbox"></ul>
      </div>
        
    </div>

    <!-- RIGHT: actions -->
    <div class="header-right">

      <!-- notifications removed -->

      <!-- cart -->
      <div class="action-item">
        <a href="/pages/cart.php" class="btn-ghost cart-btn" aria-label="Giỏ hàng">
          🛒
          <?php if ( $cartCount > 0): ?>
            <span class="badge" id="cartBadge"><?php echo $cartCount; ?></span>
          <?php endif; ?>
        </a>
      </div>

      <!-- account -->
      <div class="action-item dropdown" id="accountWrap">
        <?php if ($userInfo): ?>
          	<button id="accountBtn" class="account-btn" aria-haspopup="true" aria-expanded="false">
            <img class="avatar" src="<?php echo (!empty($userInfo['avatar']) && file_exists($userInfo['avatar'])) ? htmlspecialchars($userInfo['avatar']) : $avatarDefault; ?>" alt="avatar">
            
            <div>
              <span class="username"><?php echo htmlspecialchars($userInfo['username']); ?></span>
             	<?php 
                    if($role == "admin"){
                        echo ">" . $role . "<"; 
                    }                 
               ?>               
            </div>
              
          </button>

          <div class="dropdown-panel" id="accountPanel" role="menu" aria-hidden="true">
            <?php if( isset($role) and $role == "admin"): ?>
              <a class="dropdown-item" href="/admin/index.php">Admin</a>
    		<?php endif; ?>
            <a class="dropdown-item" href="/pages/profile.php">Hồ sơ</a>
            <a class="dropdown-item" href="/pages/orders.php">Đơn hàng</a>
            <a class="dropdown-item" href="/pages/auth/logout.php">Đăng xuất</a>
          </div>
          
        <?php else: ?>
          <a href="/pages/login.php" class="btn btn-primary">Đăng nhập</a>
        <?php endif; ?>
      </div>

      <!-- language 
      <div class="action-item">
        <button id="langToggle" class="btn-ghost" aria-label="Đổi ngôn ngữ">🇻🇳</button>
      </div> -->

      <!-- mobile menu toggle -->
      <div class="action-item mobile-only">
        <button id="mobileToggle" class="btn-ghost" aria-label="Mở menu">☰</button>
      </div>
    </div>
  </div>

  <!-- mobile slide menu -->
  <div id="mobileMenu" class="mobile-menu" aria-hidden="true">
    <div class="mobile-inner">
      <button id="mobileClose" class="mobile-close" aria-label="Đóng">✕</button>
      <nav class="mobile-nav">
        <a href="/index.php">Trang chủ</a>
        <a href="/pages/menu.php">Thực đơn</a>
        <a href="/pages/deals.php">Khuyến mãi</a>
        <a href="/pages/news.php">Tin tức</a>
        <?php if ($userInfo): ?>
          <a href="/pages/profile.php">Hồ sơ</a>
          <a href="/pages/orders.php">Đơn hàng</a>
          <a href="/pages/auth/logout.php">Đăng xuất</a>
        <?php else: ?>
          <a href="/pages/login.php">Đăng nhập</a>
          <a href="/pages/login.php?action=register">Đăng ký</a>
        <?php endif; ?>
      </nav>
    </div>
  </div>
</header>

<script src="<?php echo htmlspecialchars($jsPath); ?>"></script>
</body>
</html>
