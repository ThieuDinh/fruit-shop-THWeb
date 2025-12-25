<?php
// BẮT BUỘC: session_start() phải ở dòng đầu tiên của file header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

// 2. === CODE MỚI: Tự động đăng nhập nếu có Cookie ===
// Chỉ chạy khi chưa đăng nhập (Session rỗng) và có Cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {

    $token = $_COOKIE['remember_token'];

    // Tìm user trong DB có token này
    $stmt = $conn->prepare("SELECT * FROM users WHERE remember_token = :token");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user) {
        // Nếu tìm thấy -> Tự động đăng nhập lại (Tạo lại Session)
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name']; // Hoặc user['email'] nếu dùng logic name tạm
        $_SESSION['user_role'] = $user['role'];
    }
}
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    // Dùng array_sum để tính tổng số lượng (VD: 2 táo + 1 cam = 3)
    $cart_count =  count($_SESSION['cart']);

    // Nếu muốn đếm số loại sản phẩm (VD: 2 táo + 1 cam = 2 loại) thì dùng: count($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <title>Farm2Home</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@600;800&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="/public/lib/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="/public/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">


    <!-- Customized Bootstrap Stylesheet -->
    <link href="/public/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="/public/css/style.css" rel="stylesheet">
</head>

<body>

    <!-- Spinner Start -->
    <div id="spinner" class="show w-100 vh-100 bg-white position-fixed translate-middle top-50 start-50  d-flex align-items-center justify-content-center">
        <div class="spinner-grow text-primary" role="status"></div>
    </div>
    <!-- Spinner End -->


    <!-- Navbar start -->
    <div class="container-fluid fixed-top">
        <div class="container topbar bg-primary d-none d-lg-block">
            <div class="d-flex justify-content-between">
                <div class="top-info ps-2">
                    <small class="me-3"><i class="fas fa-map-marker-alt me-2 text-secondary"></i> <a href="#" class="text-white">180 Cao Lỗ, Phường Chánh Hưng, TP.HCM</a></small>
                    <small class="me-3"><i class="fas fa-envelope me-2 text-secondary"></i><a href="#" class="text-white">spfarm2home@gmail.com</a></small>
                </div>
                <div class="top-link pe-2">
                    <a href="coming_soon.php" class="text-white"><small class="text-white mx-2">Privacy Policy</small>/</a>
                    <a href="coming_soon.php" class="text-white"><small class="text-white mx-2">Terms of Use</small>/</a>
                    <a href="coming_soon.php" class="text-white"><small class="text-white ms-2">Sales and Refunds</small></a>
                </div>
            </div>
        </div>
        <div class="container px-0">
            <nav class="navbar navbar-light bg-white navbar-expand-xl">
                <a href="index.php" class="navbar-brand">
                    <img src="/public/img/logo-bg.png" class="" width="100" alt="">
                </a>
                <button class="navbar-toggler py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                    <span class="fa fa-bars text-primary"></span>
                </button>
                <div class="collapse navbar-collapse bg-white" id="navbarCollapse">
                    <div class="navbar-nav mx-auto">
                        <a href="index.php" class="nav-item nav-link active">Trang chủ</a>
                        <a href="shop.php" class="nav-item nav-link">Cửa hàng</a>
                        <a href="checkout.php" class="nav-item nav-link">Thanh toán</a>
                       
                    </div>
                    <div class="d-flex m-3 me-0">
                        
                        <a href="cart.php" class="position-relative me-4 my-auto">
                            <i class="fa fa-shopping-bag fa-2x"></i>
                            <span class="position-absolute bg-secondary rounded-circle d-flex align-items-center justify-content-center text-dark px-1" style="top: -5px; left: 15px; height: 20px; min-width: 20px;"><?php echo $cart_count; ?></span>
                        </a>
                     
                        <div class="dropdown">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                                    <i class="fa fa-user fa-2x "></i>
                                    <span class="d-none d-sm-inline small fw-bold"><?php echo $_SESSION['user_name']; ?></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <?php if ($_SESSION['user_role'] != 1): ?>
                                        <li><a class="dropdown-item" href="account.php">Thông tin tài khoản</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item" href="#">Đơn hàng của tôi</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item text-danger" href="logout.php">Đăng xuất</a></li>

                                    <?php else: ?>
                                        <li><a class="dropdown-item" href="admin/index.php">Trang quản trị</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item text-danger" href="logout.php">Đăng xuất</a></li>
                                    <?php endif; ?>
                                </ul>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-sm btn-outline-success fw-bold ms-2">Đăng nhập</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </div>