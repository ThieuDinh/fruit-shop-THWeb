<?php
require_once 'config/database.php';
require_once 'includes/header.php';

if (!isset($_GET['order_id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$order_id = $_GET['order_id'];

// Lấy thông tin đơn hàng
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = :id");
$stmt->execute([':id' => $order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "Đơn hàng không tồn tại.";
    exit;
}

//CẤU HÌNH NGÂN HÀNG
$my_bank = "MBBank"; 
$my_account = "26020456789"; 
$my_name = "VO DINH THIEU"; 
$amount = $order['total_money'];
$content = "DH" . $order_id; 

// api Sepay
$qr_url = "https://qr.sepay.vn/img?bank={$my_bank}&acc={$my_account}&template=compact&amount={$amount}&des={$content}";
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow rounded">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0 text-white">Thanh toán đơn hàng #<?php echo $order_id; ?></h4>
                </div>
                <div class="card-body text-center p-4">
                    <p class="text-muted">Vui lòng quét mã QR bên dưới để thanh toán</p>
                    
                    <img src="<?php echo $qr_url; ?>" class="img-fluid mb-3 border" style="max-width: 300px;" alt="QR Code">
                    
                    <h3 class="text-primary fw-bold mb-3"><?php echo number_format($amount, 0, ',', '.'); ?> đ</h3>
                    
                    <div class="alert alert-warning text-start" role="alert">
                        <small>
                            <strong>Lưu ý:</strong><br>
                            - Nội dung chuyển khoản: <strong class="text-danger"><?php echo $content; ?></strong><br>
                            - Hệ thống sẽ tự động cập nhật trạng thái sau vài phút.<br>
                            - Nếu quá 15 phút chưa cập nhật, vui lòng liên hệ hotline.
                        </small>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-success" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt me-2"></i> Tôi đã chuyển khoản
                        </button>
                        <a href="index.php" class="btn btn-light">Quay về trang chủ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>