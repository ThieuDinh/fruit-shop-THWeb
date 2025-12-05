<?php
// 1. Khởi động Session & Kết nối Database
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

// 2. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";
$msg_class = "";

// Lấy thông tin user hiện tại để kiểm tra xem đã có pass chưa
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$currentUser = $stmt->fetch();

// Biến kiểm tra xem user có mật khẩu chưa
$has_password = !empty($currentUser['password']);

// 3. XỬ LÝ FORM: CẬP NHẬT TÊN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_name'])) {
    $full_name = trim($_POST['full_name']);
    if (empty($full_name)) {
        $msg = "Họ và tên không được để trống!";
        $msg_class = "danger";
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = :full_name WHERE id = :id");
        if ($stmt->execute([':full_name' => $full_name, ':id' => $user_id])) {
            $msg = "Cập nhật thông tin thành công!";
            $msg_class = "success";
            $_SESSION['user_name'] = $full_name;
        } else {
            $msg = "Có lỗi xảy ra!";
            $msg_class = "danger";
        }
    }
}

// 4. XỬ LÝ FORM: ĐỔI MẬT KHẨU / TẠO MẬT KHẨU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $valid_old_pass = true; // Mặc định là đúng (cho trường hợp không cần pass cũ)

    // A. Nếu user ĐÃ CÓ mật khẩu -> Bắt buộc kiểm tra mật khẩu cũ
    if ($has_password) {
        $old_password = $_POST['old_password'] ?? '';
        if (!password_verify($old_password, $currentUser['password'])) {
            $msg = "Mật khẩu cũ không chính xác!";
            $msg_class = "danger";
            $valid_old_pass = false;
        }
    }

    // B. Nếu bước kiểm tra pass cũ OK (hoặc không cần kiểm tra) -> Xử lý tiếp
    if ($valid_old_pass) {
        if ($new_password !== $confirm_password) {
            $msg = "Mật khẩu xác nhận không khớp!";
            $msg_class = "danger";
        } elseif (strlen($new_password) < 6) {
            $msg = "Mật khẩu mới phải có ít nhất 6 ký tự!";
            $msg_class = "danger";
        } else {
            // Cập nhật mật khẩu mới vào DB
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
            
            if ($stmt->execute([':password' => $hashed_password, ':id' => $user_id])) {
                // Cập nhật lại biến $has_password để giao diện đổi ngay lập tức
                $has_password = true; 
                $currentUser['password'] = $hashed_password; // Cập nhật biến tạm
                
                $msg = "Cập nhật mật khẩu thành công! Bây giờ bạn có thể đăng nhập bằng mật khẩu.";
                $msg_class = "success";
            } else {
                $msg = "Lỗi hệ thống, thử lại sau.";
                $msg_class = "danger";
            }
        }
    }
}

// --- GIAO DIỆN ---
$pageTitle = "Tài khoản của tôi"; 
require_once 'includes/header.php';
?>

<div class="container-fluid page-header py-5" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('img/banner-account.jpg'); background-size: cover;">
    <h1 class="text-center text-white display-6 fw-bold">Thông tin tài khoản</h1>
</div>

<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                
                <?php if ($msg != ""): ?>
                    <div class="alert alert-<?php echo $msg_class; ?> alert-dismissible fade show" role="alert">
                        <?php echo $msg; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow rounded">
                    <div class="card-body p-5">
                        
                        <h4 class="mb-4 text-primary fw-bold">Thông tin chung</h4>
                        <form action="" method="POST" class="mb-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($currentUser['email']); ?>" readonly>
                                
                                <div class="mt-2">
                                    <?php if(!empty($currentUser['google_id'])): ?>
                                        <span class="badge bg-danger"><i class="fab fa-google me-1"></i> Đã liên kết Google</span>
                                    <?php endif; ?>
                                    
                                    <?php if(!empty($currentUser['password'])): ?>
                                        <span class="badge bg-success"><i class="fas fa-key me-1"></i> Đã có mật khẩu</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Họ và tên</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($currentUser['full_name']); ?>" required>
                            </div>

                            <button type="submit" name="update_name" class="btn btn-primary text-white w-100 py-2">Lưu tên</button>
                        </form>

                        <hr class="my-5">

                        <h4 class="mb-4 text-primary fw-bold">
                            <?php echo $has_password ? 'Đổi mật khẩu' : 'Thiết lập mật khẩu'; ?>
                        </h4>
                        
                        <?php if (!$has_password): ?>
                            <div class="alert alert-warning small">
                                <i class="fas fa-info-circle me-1"></i> 
                                Bạn đang đăng nhập bằng Google và chưa có mật khẩu. Hãy tạo mật khẩu để có thể đăng nhập bằng Email/Password.
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            
                            <?php if ($has_password): ?>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mật khẩu hiện tại</label>
                                    <input type="password" name="old_password" class="form-control" required placeholder="********">
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <?php echo $has_password ? 'Mật khẩu mới' : 'Tạo mật khẩu'; ?>
                                </label>
                                <input type="password" name="new_password" class="form-control" required placeholder="Tối thiểu 6 ký tự">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Xác nhận mật khẩu</label>
                                <input type="password" name="confirm_password" class="form-control" required placeholder="Nhập lại mật khẩu trên">
                            </div>

                            <button type="submit" name="update_password" class="btn btn-outline-primary w-100 py-2">
                                <?php echo $has_password ? 'Cập nhật mật khẩu' : 'Tạo mật khẩu mới'; ?>
                            </button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>