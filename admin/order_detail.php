<?php
session_start();
require_once '../config/database.php';

// 1. CHECK ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: order.php");
    exit;
}

$order_id = $_GET['id'];

// 2. LẤY THÔNG TIN ĐƠN HÀNG
$stmt = $conn->prepare("SELECT o.*, u.full_name, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = :id");
$stmt->execute([':id' => $order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "Đơn hàng không tồn tại!";
    exit;
}

// 3. LẤY CHI TIẾT SẢN PHẨM TRONG ĐƠN
$stmt_detail = $conn->prepare("
    SELECT od.*, p.name, p.image 
    FROM order_details od 
    JOIN products p ON od.product_id = p.id 
    WHERE od.order_id = :id
");
$stmt_detail->execute([':id' => $order_id]);
$details = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng #<?php echo $order_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { background-color: #f8f9fa; }</style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Chi tiết đơn hàng #<?php echo $order_id; ?></h2>
        <a href="order.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Quay lại danh sách</a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Thông tin người nhận</h5>
                </div>
                <div class="card-body">
                    <p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($order['full_name'] ?? 'Khách lẻ'); ?></p>
                    <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email'] ?? 'Không có'); ?></p>
                    <hr>
                    <p><strong>Địa chỉ giao hàng:</strong><br> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Trạng thái đơn hàng</h5>
                </div>
                <div class="card-body">
                    <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                    <p><strong>Phương thức TT:</strong> <?php echo $order['payment_method']; ?></p>
                    <p><strong>Trạng thái:</strong> 
                        <?php 
                            if($order['status']=='pending') echo '<span class="badge bg-warning text-dark">Chờ xử lý</span>';
                            elseif($order['status']=='shipping') echo '<span class="badge bg-info">Đang giao hàng</span>';
                            elseif($order['status']=='completed') echo '<span class="badge bg-success">Đã hoàn thành</span>';
                            else echo '<span class="badge bg-danger">Đã hủy</span>';
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">Danh sách sản phẩm</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Sản phẩm</th>
                                <th class="text-center">Số lượng</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-end pe-4">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $sub_total = 0;
                            foreach ($details as $item): 
                                $row_total = $item['price'] * $item['quantity'];
                                $sub_total += $row_total;
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo '..' . $item['image']; ?>" style="width: 50px; height: 50px; object-fit: cover;" class="rounded me-3">
                                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                                    </div>
                                </td>
                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                <td class="text-end"><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</td>
                                <td class="text-end pe-4 fw-bold"><?php echo number_format($row_total, 0, ',', '.'); ?>đ</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Tạm tính:</td>
                                <td class="text-end pe-4"><?php echo number_format($sub_total, 0, ',', '.'); ?>đ</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Phí vận chuyển:</td>
                                <td class="text-end pe-4">30.000đ</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-bold text-danger fs-5">TỔNG CỘNG:</td>
                                <td class="text-end pe-4 fw-bold text-danger fs-5"><?php echo number_format($order['total_money'], 0, ',', '.'); ?>đ</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>