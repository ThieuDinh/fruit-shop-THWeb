<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php");
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // A. XỬ LÝ THÊM MỚI
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['name']);

        if (!empty($name)) {
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (:name)");
            $stmt->execute([':name' => $name]);
            $message = "Thêm danh mục thành công!";
        } else {
            $message = "Tên danh mục không được để trống!";
        }
    }

    // XỬ LÝ CẬP NHẬT
    if (isset($_POST['update_category'])) {
        $id = $_POST['cat_id'];
        $name = trim($_POST['name']);

        if (!empty($name)) {
            $stmt = $conn->prepare("UPDATE categories SET name = :name WHERE id = :id");
            $stmt->execute([':name' => $name, ':id' => $id]);
            $message = "Cập nhật thành công!";
        }
    }
}

// C. XỬ LÝ XÓA 
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header("Location: category.php"); 
    exit;
}

$stmt = $conn->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Danh mục - Admin</title>
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
                <a href="category.php" class="active"><i class="fas fa-list"></i> Quản lý Danh mục</a>
                <a href="product.php"><i class="fas fa-box-open"></i> Quản lý Sản phẩm</a>
                <a href="order.php"><i class="fas fa-shopping-cart"></i> Quản lý Đơn hàng</a>
                <a href="users.php"><i class="fas fa-users-cog"></i> Tài khoản Nhân viên</a>
                <a href="../index.php" target="_blank"><i class="fas fa-home"></i> Xem Trang chủ</a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Quản lý Danh mục</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-2"></i> Thêm mới
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
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
                                    <th class="ps-4">ID</th>
                                    <th>Tên danh mục</th>
                                    <th class="text-end pe-4">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td class="ps-4 fw-bold">#<?php echo $cat['id']; ?></td>
                                    <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($cat['name']); ?></span></td>
                                   
                                   
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-warning me-2 edit-btn" 
                                                data-id="<?php echo $cat['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($cat['name']); ?>"
                                                data-bs-toggle="modal" data-bs-target="#editModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="category.php?delete=<?php echo $cat['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($categories)): ?>
                                    <tr><td colspan="5" class="text-center py-4">Chưa có danh mục nào.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm Danh mục mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Ví dụ: Rau củ">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="add_category" class="btn btn-primary">Lưu lại</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cập nhật Danh mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="cat_id" id="edit_cat_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_cat_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="update_category" class="btn btn-warning">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // JS xử lý đưa dữ liệu vào Modal Sửa
    const editBtns = document.querySelectorAll('.edit-btn');
    const editId = document.getElementById('edit_cat_id');
    const editName = document.getElementById('edit_cat_name');
  

    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Lấy data từ nút bấm
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            

            // Điền vào form modal
            editId.value = id;
            editName.value = name;
        });
    });
</script>

</body>
</html>