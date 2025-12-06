<?php
require_once 'config/database.php';
require_once 'includes/header.php';

// Kiểm tra nếu không có ID đơn hàng thì đẩy về trang chủ
if (!isset($_GET['order_id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

$order_id = $_GET['order_id'];

// Lấy thông tin đơn hàng để hiển thị xác nhận
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = :id");
$stmt->execute([':id' => $order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<div class='container py-5 text-center'><h3>Đơn hàng không tồn tại!</h3><a href='index.php'>Về trang chủ</a></div>";
    require_once 'includes/footer.php';
    exit;
}
?>

<div class="container-fluid page-header py-5">
    <h1 class="text-center text-white display-6">Đặt hàng thành công</h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
        <li class="breadcrumb-item active text-white">Hoàn tất</li>
    </ol>
</div>
<div class="container-fluid py-5">
    <div class="container py-5 text-center">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <i class="fa fa-check-circle text-success display-1 mb-4"></i>
                <h1 class="mb-4">Cảm ơn bạn đã đặt hàng!</h1>
                <p class="mb-4 text-dark">Đơn hàng của bạn đã được tiếp nhận và đang trong quá trình xử lý. Chúng tôi sẽ liên hệ với bạn sớm nhất để xác nhận đơn hàng.</p>
                
                <div class="card border-secondary mb-5">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="text-white mb-0">Thông tin đơn hàng</h5>
                    </div>
                    <div class="card-body text-start">
                        <div class="row mb-2">
                            <div class="col-6 fw-bold">Mã đơn hàng:</div>
                            <div class="col-6 text-end">#<?php echo $order['id']; ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 fw-bold">Ngày đặt:</div>
                            <div class="col-6 text-end"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 fw-bold">Phương thức thanh toán:</div>
                            <div class="col-6 text-end">
                                <?php echo ($order['payment_method'] == 'COD') ? 'Thanh toán khi nhận hàng (COD)' : 'Chuyển khoản'; ?>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 fw-bold">Tổng tiền:</div>
                            <div class="col-6 text-end text-primary fw-bold fs-5"><?php echo number_format($order['total_money'], 0, ',', '.'); ?> đ</div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <strong>Địa chỉ giao hàng:</strong><br>
                                <?php echo htmlspecialchars($order['shipping_address']); ?><br>
                                <small class="text-muted">SĐT: <?php echo htmlspecialchars($order['phone']); ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="shop.php" class="btn btn-primary border-secondary rounded-pill py-3 px-5 text-white mb-4">Tiếp tục mua sắm</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>