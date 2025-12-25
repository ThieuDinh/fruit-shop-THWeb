<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header("Location: ../login.php");
    exit;
}
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status']; 
    //kiểm tra trạng thái
    $stmt_check = $conn->prepare("SELECT status FROM orders WHERE id = :id");
    $stmt_check->execute([':id' => $order_id]);
    $current_status = $stmt_check->fetchColumn();
    
    // hoàn kho
    if ($new_status == 'cancelled' && $current_status != 'cancelled') {
        // Lấy chi tiết đơn hàng
        $stmt_items = $conn->prepare("SELECT product_id, quantity FROM order_details WHERE order_id = :id");
        $stmt_items->execute([':id' => $order_id]);
        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        // Cộng lại kho
        $sql_restock = "UPDATE products SET stock = stock + :qty WHERE id = :pid";
        $stmt_restock = $conn->prepare($sql_restock);

        foreach ($items as $item) {
            $stmt_restock->execute([
                ':qty' => $item['quantity'],
                ':pid' => $item['product_id']
            ]);
        }
    }

    // khôi phục đơn
    if ($current_status == 'cancelled' && $new_status != 'cancelled') {
        $stmt_items = $conn->prepare("SELECT product_id, quantity FROM order_details WHERE order_id = :id");
        $stmt_items->execute([':id' => $order_id]);
        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        // Trừ kho
        $sql_deduct = "UPDATE products SET stock = stock - :qty WHERE id = :pid";
        $stmt_deduct = $conn->prepare($sql_deduct);

        foreach ($items as $item) {
            $stmt_deduct->execute([
                ':qty' => $item['quantity'],
                ':pid' => $item['product_id']
            ]);
        }
    }

    // D. Cuối cùng mới cập nhật trạng thái đơn hàng
    $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $stmt->execute([':status' => $new_status, ':id' => $order_id]);
    $message = "Cập nhật đơn hàng thành công!";

}

// xóa đơn hàng bị cancel
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $conn->prepare("DELETE FROM order_details WHERE order_id = :id")->execute([':id' => $id]);
    // Xóa đơn hàng
    $conn->prepare("DELETE FROM orders WHERE id = :id")->execute([':id' => $id]);
    header("Location: order.php");
    exit;
}

$sql = "SELECT o.*, u.full_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đơn hàng - Admin</title>
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
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #0d6efd;
            color: white;
            padding-left: 25px;
            transition: 0.3s;
        }

        .sidebar i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
        }

        .status-select {
            min-width: 130px;
        }
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
                    <a href="order.php" class="active"><i class="fas fa-shopping-cart"></i> Quản lý Đơn hàng</a>
                    <a href="users.php"><i class="fas fa-users-cog"></i> Tài khoản Nhân viên</a>
                    <a href="../index.php" target="_blank"><i class="fas fa-home"></i> Xem Trang chủ</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
                <h2 class="fw-bold mb-4">Quản lý Đơn hàng</h2>

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
                                        <th class="ps-3">Mã ĐH</th>
                                        <th>Khách hàng</th>
                                        <th>Ngày đặt</th>
                                        <th>Tổng tiền</th>
                                        <th>HT Thanh toán</th>
                                        <th>Trạng thái</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold text-primary">#<?php echo $order['id']; ?></td>
                                            <td>
                                                <span class="fw-bold"><?php echo htmlspecialchars($order['full_name'] ?? 'Khách lẻ'); ?></span><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($order['phone']); ?></small>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                            <td class="fw-bold text-danger"><?php echo number_format($order['total_money'], 0, ',', '.'); ?>đ</td>
                                            <td>
                                                <span class="badge bg-light text-dark border"><?php echo $order['payment_method']; ?></span>
                                            </td>
                                            <td>
                                                <form action="" method="POST" class="d-flex align-items-center">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <select name="status" class="form-select form-select-sm status-select me-2 
                                                <?php
                                                if ($order['status'] == 'pending') echo 'border-warning text-warning';
                                                elseif ($order['status'] == 'shipping') echo 'border-info text-info';
                                                elseif ($order['status'] == 'completed') echo 'border-success text-success';
                                                else echo 'border-danger text-danger';
                                                ?>">
                                                        <option value="pending" <?php if ($order['status'] == 'pending') echo 'selected'; ?>>Chờ xử lý</option>
                                                        <option value="shipping" <?php if ($order['status'] == 'shipping') echo 'selected'; ?>>Đang giao</option>
                                                        <option value="completed" <?php if ($order['status'] == 'completed') echo 'selected'; ?>>Hoàn thành</option>
                                                        <option value="cancelled" <?php if ($order['status'] == 'cancelled') echo 'selected'; ?>>Đã hủy</option>
                                                    </select>
                                                    <button type="submit" name="update_status" class="btn btn-sm btn-primary" title="Lưu"><i class="fas fa-save"></i></button>
                                                </form>
                                            </td>
                                            <td>
                                                <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary me-1" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($order['status'] == 'cancelled'): ?>
                                                    <a href="order.php?delete=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa vĩnh viễn đơn này?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
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