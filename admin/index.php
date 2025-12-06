<?php
session_start();
require_once '../config/database.php';

// 1. KIỂM TRA QUYỀN ADMIN
// Nếu chưa đăng nhập hoặc role không phải là 1 (Admin) -> Đẩy về trang login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php");
    exit;
}

// 2. LẤY SỐ LIỆU THỐNG KÊ (DASHBOARD)
// Lưu ý: Bạn cần tạo các bảng products, orders, categories trong DB thì đoạn này mới chạy được.
// Tôi sẽ dùng try-catch để nếu chưa có bảng thì nó không báo lỗi, chỉ hiện số 0.

$count_products = 0;
$count_orders = 0;
$count_users = 0;
$count_categories = 0;

try {
    // Đếm sản phẩm
    // $stmt = $conn->query("SELECT COUNT(*) FROM products");
    // $count_products = $stmt->fetchColumn(); 

    // Đếm đơn hàng
    // $stmt = $conn->query("SELECT COUNT(*) FROM orders");
    // $count_orders = $stmt->fetchColumn();

    // Đếm nhân viên/user (role = 1 là admin/nhân viên)
    $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 1");
    $count_users = $stmt->fetchColumn();

    // Đếm danh mục
    // $stmt = $conn->query("SELECT COUNT(*) FROM categories");
    // $count_categories = $stmt->fetchColumn();

} catch (Exception $e) {
    // Bỏ qua lỗi nếu chưa tạo bảng
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Farm2Home</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            border-bottom: 1px solid #495057;
            transition: 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #0d6efd; /* Màu xanh primary */
            color: white;
            padding-left: 25px; /* Hiệu ứng đẩy nhẹ sang phải */
        }
        .sidebar i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .stat-card {
            border-radius: 10px;
            border: none;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        }
        .icon-box {
            font-size: 2.5rem;
            opacity: 0.3;
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            
            <div class="col-md-3 col-lg-2 px-0 sidebar d-none d-md-block">
                <div class="py-4 text-center border-bottom border-secondary">
                    <h4 class="fw-bold text-white">Admin Panel</h4>
                    <small>Xin chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?></small>
                </div>
                
                <div class="mt-3">
                    <a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    <a href="category.php"><i class="fas fa-list"></i> Quản lý Danh mục</a>
                    <a href="product.php"><i class="fas fa-box-open"></i> Quản lý Sản phẩm</a>
                    <a href="order.php"><i class="fas fa-shopping-cart"></i> Quản lý Đơn hàng</a>
                    <a href="users.php"><i class="fas fa-users-cog"></i> Tài khoản Nhân viên</a>
                    <a href="../index.php" target="_blank"><i class="fas fa-home"></i> Xem Trang chủ</a>
                    <a href="../logout.php" class="text-danger mt-4 border-top border-secondary"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
                
                <div class="d-flex justify-content-between align-items-center d-md-none mb-4">
                    <h4 class="fw-bold">Dashboard</h4>
                    <button class="btn btn-dark" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <div class="collapse d-md-none mb-3" id="mobileMenu">
                    <div class="card card-body bg-dark">
                        <a href="index.php" class="text-white mb-2">Dashboard</a>
                        <a href="product.php" class="text-white mb-2">Sản phẩm</a>
                        <a href="orders.php" class="text-white mb-2">Đơn hàng</a>
                        <a href="../logout.php" class="text-danger">Đăng xuất</a>
                    </div>
                </div>

                <h2 class="fw-bold mb-4">Tổng quan hệ thống</h2>

                <div class="row g-4 mb-5">
                    
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1">Tổng sản phẩm</h6>
                                    <h2 class="fw-bold mb-0"><?php echo $count_products; ?></h2>
                                </div>
                                <div class="icon-box">
                                    <i class="fas fa-leaf"></i>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0 small">
                                <a href="products.php" class="text-white text-decoration-none">Xem chi tiết <i class="fas fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1">Đơn hàng mới</h6>
                                    <h2 class="fw-bold mb-0"><?php echo $count_orders; ?></h2>
                                </div>
                                <div class="icon-box">
                                    <i class="fas fa-shopping-basket"></i>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0 small">
                                <a href="orders.php" class="text-white text-decoration-none">Xử lý ngay <i class="fas fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-dark h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1">Danh mục</h6>
                                    <h2 class="fw-bold mb-0"><?php echo $count_categories; ?></h2>
                                </div>
                                <div class="icon-box">
                                    <i class="fas fa-tags"></i>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0 small">
                                <a href="categories.php" class="text-dark text-decoration-none">Quản lý <i class="fas fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white h-100">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1">Nhân viên</h6>
                                    <h2 class="fw-bold mb-0"><?php echo $count_users; ?></h2>
                                </div>
                                <div class="icon-box">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0 small">
                                <a href="users.php" class="text-white text-decoration-none">Phân quyền <i class="fas fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-clock me-2"></i>Đơn hàng vừa đặt</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Mã ĐH</th>
                                        <th>Khách hàng</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày đặt</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="ps-4 fw-bold">#ORD-001</td>
                                        <td>Nguyễn Văn A</td>
                                        <td>500.000đ</td>
                                        <td><span class="badge bg-warning text-dark">Chờ duyệt</span></td>
                                        <td>05/12/2025</td>
                                        <td><a href="#" class="btn btn-sm btn-outline-primary">Chi tiết</a></td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4 fw-bold">#ORD-002</td>
                                        <td>Trần Thị B</td>
                                        <td>1.200.000đ</td>
                                        <td><span class="badge bg-success">Đã giao</span></td>
                                        <td>04/12/2025</td>
                                        <td><a href="#" class="btn btn-sm btn-outline-primary">Chi tiết</a></td>
                                    </tr>
                                    </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>