<?php
session_start();
require_once '../config/database.php';

// 1. CHECK ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php");
    exit;
}

$message = "";

// Hàm hỗ trợ upload ảnh
function uploadImage($file) {
    // Đường dẫn thư mục lưu ảnh (tính từ file hiện tại)
    $target_dir = "../public/img/";
    
    // Kiểm tra và tạo thư mục nếu chưa có
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Tạo tên file mới để tránh trùng lặp (Time + Tên gốc)
    $filename = time() . "_" . basename($file["name"]);
    $target_file = $target_dir . $filename;
    
    // Chỉ cho phép file ảnh
    $check = getimagesize($file["tmp_name"]);
    if($check === false) { return false; }

    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // Trả về đường dẫn để lưu vào DB (Đường dẫn tuyệt đối tính từ web root)
        return "/public/img/" . $filename;
    }
    return false;
}

// 2. XỬ LÝ FORM
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // A. THÊM SẢN PHẨM
    if (isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $cat_id = $_POST['category_id'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $desc = $_POST['description'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $imagePath = ""; // Mặc định rỗng hoặc ảnh placeholder
        
        // Xử lý upload ảnh
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $uploaded = uploadImage($_FILES['image']);
            if ($uploaded) {
                $imagePath = $uploaded;
            } else {
                $message = "Lỗi upload ảnh!";
            }
        }

        $sql = "INSERT INTO products (category_id, name, price, stock, image, description, is_active) 
                VALUES (:cat, :name, :price, :stock, :img, :desc, :active)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':cat' => $cat_id, ':name' => $name, ':price' => $price, 
            ':stock' => $stock, ':img' => $imagePath, ':desc' => $desc, ':active' => $is_active
        ]);
        $message = "Thêm sản phẩm thành công!";
    }

    // B. CẬP NHẬT SẢN PHẨM
    if (isset($_POST['update_product'])) {
        $id = $_POST['prod_id'];
        $name = $_POST['name'];
        $cat_id = $_POST['category_id'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $desc = $_POST['description'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $old_image = $_POST['old_image']; // Đường dẫn ảnh cũ

        $imagePath = $old_image; // Mặc định giữ ảnh cũ

        // Nếu người dùng chọn ảnh mới
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $uploaded = uploadImage($_FILES['image']);
            if ($uploaded) {
                $imagePath = $uploaded;
            }
        }

        $sql = "UPDATE products SET category_id=:cat, name=:name, price=:price, 
                stock=:stock, image=:img, description=:desc, is_active=:active WHERE id=:id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':cat' => $cat_id, ':name' => $name, ':price' => $price, 
            ':stock' => $stock, ':img' => $imagePath, ':desc' => $desc, ':active' => $is_active, ':id' => $id
        ]);
        $message = "Cập nhật sản phẩm thành công!";
    }
}

// C. XÓA SẢN PHẨM
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Lấy thông tin ảnh để xóa file vật lý
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $prod = $stmt->fetch();

    if ($prod && !empty($prod['image']) && file_exists(".." . $prod['image'])) {
        unlink(".." . $prod['image']);
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header("Location: product.php");
    exit;
}

// 3. LẤY DỮ LIỆU
// Lấy danh sách danh mục (cho Dropdown)
$cats = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách sản phẩm (kèm tên danh mục)
$sql = "SELECT p.*, c.name as cat_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.id DESC";
$products = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sản phẩm - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #343a40; color: white; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; border-bottom: 1px solid #495057; }
        .sidebar a:hover, .sidebar a.active { background-color: #0d6efd; color: white; padding-left: 25px; transition: 0.3s; }
        .sidebar i { width: 20px; text-align: center; margin-right: 10px; }
        .img-thumbnail-custom { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; }
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
                <a href="product.php" class="active"><i class="fas fa-box-open"></i> Quản lý Sản phẩm</a>
                <a href="order.php"><i class="fas fa-shopping-cart"></i> Quản lý Đơn hàng</a>
                <a href="users.php"><i class="fas fa-users-cog"></i> Tài khoản Nhân viên</a>
                <a href="../index.php" target="_blank"><i class="fas fa-home"></i> Xem Trang chủ</a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Quản lý Sản phẩm</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus me-2"></i> Thêm sản phẩm
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
                                    <th class="ps-3">Hình ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Danh mục</th>
                                    <th>Giá (VNĐ)</th>
                                    <th>Kho</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end pe-3">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $p): ?>
                                <tr>
                                    <td class="ps-3">
                                        <?php if (!empty($p['image'])): ?>
                                            <img src="<?php echo '..' . $p['image']; ?>" class="img-thumbnail-custom" alt="img">
                                        <?php else: ?>
                                            <span class="text-muted small">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($p['name']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($p['cat_name'] ?? 'N/A'); ?></span></td>
                                    <td class="text-primary fw-bold"><?php echo number_format($p['price'], 0, ',', '.'); ?></td>
                                    <td><?php echo $p['stock']; ?></td>
                                    <td>
                                        <?php if ($p['is_active']): ?>
                                            <span class="badge bg-success">Hiển thị</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Ẩn</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <button class="btn btn-sm btn-outline-warning me-1 edit-btn" 
                                                data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-id="<?php echo $p['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($p['name']); ?>"
                                                data-cat="<?php echo $p['category_id']; ?>"
                                                data-price="<?php echo $p['price']; ?>"
                                                data-stock="<?php echo $p['stock']; ?>"
                                                data-desc="<?php echo htmlspecialchars($p['description']); ?>"
                                                data-active="<?php echo $p['is_active']; ?>"
                                                data-img="<?php echo $p['image']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="product.php?delete=<?php echo $p['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Xóa sản phẩm này?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
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

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm Sản phẩm mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($cats as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control" required min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số lượng kho</label>
                            <input type="number" name="stock" class="form-control" value="0" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hình ảnh</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả chi tiết</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="activeCheck" checked>
                        <label class="form-check-label" for="activeCheck">Hiển thị sản phẩm ngay</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="add_product" class="btn btn-primary">Lưu sản phẩm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cập nhật Sản phẩm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="prod_id" id="e_id">
                    <input type="hidden" name="old_image" id="e_old_img">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên sản phẩm</label>
                            <input type="text" name="name" id="e_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Danh mục</label>
                            <select name="category_id" id="e_cat" class="form-select" required>
                                <?php foreach ($cats as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giá (VNĐ)</label>
                            <input type="number" name="price" id="e_price" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số lượng kho</label>
                            <input type="number" name="stock" id="e_stock" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Thay đổi hình ảnh (Bỏ trống nếu giữ ảnh cũ)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div class="mt-2" id="e_img_preview_box">
                            <small>Ảnh hiện tại:</small><br>
                            <img src="" id="e_img_preview" style="height: 60px; border-radius: 4px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" id="e_desc" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="e_active">
                        <label class="form-check-label" for="e_active">Hiển thị sản phẩm</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="update_product" class="btn btn-warning">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // JS điền dữ liệu vào Modal Sửa
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('e_id').value = this.dataset.id;
            document.getElementById('e_name').value = this.dataset.name;
            document.getElementById('e_cat').value = this.dataset.cat;
            document.getElementById('e_price').value = this.dataset.price;
            document.getElementById('e_stock').value = this.dataset.stock;
            document.getElementById('e_desc').value = this.dataset.desc;
            document.getElementById('e_active').checked = (this.dataset.active == 1);
            
            // Xử lý ảnh
            const imgPath = this.dataset.img;
            document.getElementById('e_old_img').value = imgPath;
            
            if (imgPath) {
                document.getElementById('e_img_preview').src = '..' + imgPath;
                document.getElementById('e_img_preview_box').style.display = 'block';
            } else {
                document.getElementById('e_img_preview_box').style.display = 'none';
            }
        });
    });
</script>

</body>
</html>