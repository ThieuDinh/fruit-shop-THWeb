<?php
session_start();
require_once '../config/database.php';

// 1. CHECK ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php");
    exit;
}

$message = "";

// 2. XỬ LÝ CẬP NHẬT QUYỀN (ROLE)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];
    
    // Không cho phép tự hạ quyền chính mình (để tránh mất admin)
    if ($user_id == $_SESSION['user_id']) {
        $message = "Bạn không thể tự thay đổi quyền của chính mình!";
    } else {
        $stmt = $conn->prepare("UPDATE users SET role = :role WHERE id = :id");
        $stmt->execute([':role' => $new_role, ':id' => $user_id]);
        $message = "Cập nhật quyền thành công!";
    }
}

// 3. XỬ LÝ XÓA TÀI KHOẢN
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    if ($id == $_SESSION['user_id']) {
        echo "<script>alert('Không thể tự xóa tài khoản đang đăng nhập!'); window.location.href='users.php';</script>";
        exit;
    }

    // Xóa đơn hàng liên quan trước (Nếu có ràng buộc khóa ngoại CASCADE thì ko cần dòng này, nhưng an toàn thì cứ để)
    // $conn->prepare("DELETE FROM orders WHERE user_id = :id")->execute([':id' => $id]);
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    if ($stmt->execute([':id' => $id])) {
        header("Location: users.php");
        exit;
    } else {
        $message = "Lỗi khi xóa người dùng!";
    }
}

// 4. LẤY DANH SÁCH USER (Có tìm kiếm)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM users";

if ($search) {
    $sql .= " WHERE full_name LIKE :search OR email LIKE :search";
}
$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if ($search) {
    $stmt->bindValue(':search', "%$search%");
}

$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Tài khoản - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #343a40; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; border-bottom: 1px solid #495057; }
        .sidebar a:hover, .sidebar a.active { background-color: #0d6efd; color: white; padding-left: 25px; transition: 0.3s; }
        .sidebar i { width: 20px; text-align: center; margin-right: 10px; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 px-0 sidebar d-none d-md-block">
            <div class="py-4 text-center border-bottom border-secondary">
                <h4 class="fw-bold text-white">Admin Panel</h4>
                <small>Xin chào, <?php echo $_SESSION['user_name']; ?></small>
            </div>
            <div class="mt-3">
                <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="category.php"><i class="fas fa-list"></i> Quản lý Danh mục</a>
                <a href="product.php"><i class="fas fa-box-open"></i> Quản lý Sản phẩm</a>
                <a href="order.php"><i class="fas fa-shopping-cart"></i> Quản lý Đơn hàng</a>
                <a href="users.php" class="active"><i class="fas fa-users-cog"></i> Tài khoản Nhân viên</a>
                <a href="../index.php" target="_blank"><i class="fas fa-home"></i> Xem Trang chủ</a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Quản lý Tài khoản</h2>
                <form class="d-flex" method="GET">
                    <input class="form-control me-2" type="search" name="search" placeholder="Tìm tên hoặc email..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-outline-primary" type="submit">Tìm</button>
                </form>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">ID</th>
                                    <th>Họ và Tên</th>
                                    <th>Email</th>
                                    <th>Loại tài khoản</th> <th>Vai trò</th>
                                    <th>Ngày tạo</th>
                                    <th class="text-end pe-3">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-muted">#<?php echo $u['id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                                <i class="fas fa-user text-secondary"></i>
                                            </div>
                                            <span class="fw-bold"><?php echo htmlspecialchars($u['full_name']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <?php if ($u['google_id']): ?>
                                            <span class="badge bg-danger"><i class="fab fa-google me-1"></i>Google</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Thường</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form action="" method="POST" class="d-flex align-items-center">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <select name="role" class="form-select form-select-sm border-0 bg-transparent fw-bold 
                                                <?php echo ($u['role'] == 1) ? 'text-primary' : 'text-dark'; ?>" 
                                                onchange="this.form.submit()">
                                                <option value="0" <?php if($u['role']==0) echo 'selected'; ?>>Khách hàng</option>
                                                <option value="1" <?php if($u['role']==1) echo 'selected'; ?>>Quản trị viên</option>
                                                <input type="hidden" name="update_role" value="1">
                                            </select>
                                        </form>
                                    </td>
                                    <td class="text-muted small"><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></td>
                                    <td class="text-end pe-3">
                                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                            <a href="users.php?delete=<?php echo $u['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này? Hành động này không thể hoàn tác!');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">Bạn</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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