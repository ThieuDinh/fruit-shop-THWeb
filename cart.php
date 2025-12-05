<?php
// ... code cũ của bạn ở dưới ...
require_once 'includes/function.php';
require_once 'includes/header.php';
// 1. Lấy dữ liệu giỏ hàng
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$products_in_cart = [];
$total_price = 0;

if (!empty($cart_items)) {
    // Gọi hàm vừa viết ở Bước 2
    $products_in_cart = get_Cart_Products($conn, $cart_items);
}


?>

<!-- Single Page Header start -->
<div class="container-fluid page-header py-5">
    <h1 class="text-center text-white display-6">Giỏ hàng</h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="#">Trang chủ</a></li>

        <li class="breadcrumb-item active text-white">Giỏ hàng</li>
    </ol>
</div>
<!-- Single Page Header End -->


<!-- Cart Page Start -->
<div class="container-fluid py-5">
    <div class="container py-5">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Sản phẩm</th>
                        <th scope="col">Tên</th>
                        <th scope="col">Giá</th>
                        <th scope="col">Số lượng</th>
                        <th scope="col">Tổng</th>
                        <th scope="col">Xử lý</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products_in_cart)): ?>
                        <?php foreach ($products_in_cart as $pro): ?>
                            <?php
                            $qty = $cart_items[$pro['id']]; // Lấy số lượng từ session
                            $line_total = $pro['price'] * $qty; // Tính tổng tiền dòng này
                            $total_price += $line_total; // Cộng vào tổng tiền giỏ hàng
                            ?>
                            <tr>
                                <th scope="row">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo htmlspecialchars($pro['image']); ?>" class="img-fluid me-5 rounded-circle" style="width: 80px; height: 80px; object-fit: cover;" alt="">
                                    </div>
                                </th>
                                <td>
                                    <p class="mb-0 mt-4"><?php echo htmlspecialchars($pro['name']); ?></p>
                                </td>
                                <td>
                                    <p class="mb-0 mt-4"><?php echo number_format($pro['price']); ?> đ</p>
                                </td>
                                <td>
                                    <div class="input-group quantity mt-4" style="width: 100px;">
                                        <div class="input-group-btn">
                                            <a href="cart_action.php?update=dec&id=<?php echo $pro['id']; ?>" class="btn btn-sm btn-minus rounded-circle bg-light border">
                                                <i class="fa fa-minus"></i>
                                            </a>
                                        </div>
                                        <input type="text" class="form-control form-control-sm text-center border-0" value="<?php echo $qty . " kg"; ?>" readonly>
                                        <div class="input-group-btn">
                                            <a href="cart_action.php?update=inc&id=<?php echo $pro['id']; ?>" class="btn btn-sm btn-plus rounded-circle bg-light border">
                                                <i class="fa fa-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="mb-0 mt-4"><?php echo number_format($line_total); ?> đ</p>
                                </td>
                                <td>
                                    <a href="cart_action.php?delete=<?php echo $pro['id']; ?>" class="btn btn-md rounded-circle bg-light border mt-4" onclick="return confirm('Bạn muốn xóa sản phẩm này?');">
                                        <i class="fa fa-times text-danger"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">Giỏ hàng trống. <a href="shop.php">Tiếp tục mua sắm</a></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($products_in_cart)): ?>
            <div class="row g-4 justify-content-end">
                <div class="col-8"></div>
                <div class="col-sm-8 col-md-7 col-lg-6 col-xl-4">
                    <div class="bg-light rounded">
                        <div class="p-4">
                            <h1 class="display-6 mb-4">Giỏ hàng <span class="fw-normal">Tổng</span></h1>
                            <div class="d-flex justify-content-between mb-4">
                                <h5 class="mb-0 me-4">Tạm tính:</h5>
                                <p class="mb-0"><?php echo number_format($total_price, 0, ',', '.'); ?> đ</p>
                            </div>
                            <div class="d-flex justify-content-between">
                                <h5 class="mb-0 me-4">Vận chuyển</h5>
                                <div class="">
                                    <p class="mb-0">Phí cố định: <?php echo number_format(30000); ?> đ</p>
                                </div>
                            </div>

                        </div>
                        <div class="py-4 mb-4 border-top border-bottom d-flex justify-content-between">
                            <h5 class="mb-0 ps-4 me-4">Tổng</h5>
                            <p class="mb-0 pe-4"><?php echo number_format($total_price + 30000, 0, ',', '.'); ?> đ</p>
                        </div>
                        <button class="btn border-secondary rounded-pill px-4 py-3 text-primary text-uppercase mb-4 ms-4" type="button">Thanh toán</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


<?php
// 4. Nhúng Footer
require_once 'includes/footer.php';
?>