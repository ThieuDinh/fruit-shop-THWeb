<?php
// 1. Nhúng các file cần thiết
require_once 'includes/function.php';
require_once 'includes/header.php'; // Session đã được start trong này

// 2. Lấy dữ liệu giỏ hàng từ Session
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$products_in_cart = [];
$sub_total = 0;
$shipping_fee = 30000; // Phí ship cố định

// Nếu giỏ hàng trống thì đá về trang Shop
if (empty($cart_items)) {
    echo "<script>alert('Giỏ hàng trống!'); window.location.href='shop.php';</script>";
    exit;
} else {
    // Lấy thông tin chi tiết sản phẩm từ DB
    $products_in_cart = get_Cart_Products($conn, $cart_items);
}
?>

<div class="container-fluid page-header py-5">
    <h1 class="text-center text-white display-6">Thanh toán</h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
        <li class="breadcrumb-item active text-white">Thanh toán</li>
    </ol>
</div>
<div class="container-fluid py-5">
    <div class="container py-5">
        <h1 class="mb-4">Chi tiết thanh toán</h1>
        <form action="process_checkout.php" method="POST">
            <div class="row g-5">

                <div class="col-md-12 col-lg-6 col-xl-7">
                    <div class="row">
                        <div class="col-md-12 col-lg-6">
                            <div class="form-item w-100">
                                <label class="form-label my-3">Họ và tên đệm<sup>*</sup></label>
                                <input type="text" name="lastname" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-6">
                            <div class="form-item w-100">
                                <label class="form-label my-3">Tên<sup>*</sup></label>
                                <input type="text" name="firstname" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-item">
                        <label class="form-label my-3">Số điện thoại<sup>*</sup></label>
                        <input type="tel" name="phone" class="form-control" required>
                    </div>

                    <div class="form-item">
                        <label class="form-label my-3">Địa chỉ cụ thể (Số nhà, đường)<sup>*</sup></label>
                        <input type="text" name="address_detail" class="form-control" placeholder="Ví dụ: 180 Cao Lỗ" required>
                    </div>

                    <div class="form-item">
                        <label class="form-label my-3">Thành phố<sup>*</sup></label>
                        <select class="form-control form-select" id="city" name="city" readonly>
                            <option value="Hồ Chí Minh" selected>Hồ Chí Minh</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-item w-100">
                                <label class="form-label my-3">Chọn Phường <sup>*</sup></label>
                                <select class="form-control form-select" id="wardSelect" name="ward">
                                    <option value="" selected>-- Chọn Phường --</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-item w-100">
                                <label class="form-label my-3">Chọn Xã<sup>*</sup></label>
                                <select class="form-control form-select" id="communeSelect" name="commune">
                                    <option value="" selected>-- Chọn Xã--</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="final_address_unit" name="final_address_unit">

                    <div class="form-item mt-3">
                        <label class="form-label my-3">Ghi chú đơn hàng</label>
                        <textarea name="note" class="form-control" spellcheck="false" cols="30" rows="5" placeholder="Ghi chú về đơn hàng (Ví dụ: Giao giờ hành chính)"></textarea>
                    </div>
                </div>

                <div class="col-md-12 col-lg-6 col-xl-5">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Sản phẩm</th>
                                    <th scope="col">Tên</th>
                                    <th scope="col">Giá</th>
                                    <th scope="col">SL</th>
                                    <th scope="col">Tổng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products_in_cart as $pro): ?>
                                    <?php
                                    $qty = $cart_items[$pro['id']];
                                    $line_total = $pro['price'] * $qty;
                                    $sub_total += $line_total;
                                    ?>
                                    <tr>
                                        <th scope="row">
                                            <div class="d-flex align-items-center mt-2">
                                                <img src="<?php echo htmlspecialchars($pro['image']); ?>" class="img-fluid rounded-circle" style="width: 60px; height: 60px; object-fit: cover;" alt="">
                                            </div>
                                        </th>
                                        <td class="py-5"><?php echo htmlspecialchars($pro['name']); ?></td>
                                        <td class="py-5"><?php echo number_format($pro['price'], 0, ',', '.'); ?></td>
                                        <td class="py-5"><?php echo $qty; ?></td>
                                        <td class="py-5"><?php echo number_format($line_total, 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>

                                <tr>
                                    <th scope="row"></th>
                                    <td class="py-5"></td>
                                    <td class="py-5"></td>
                                    <td class="py-5">
                                        <p class="mb-0 text-dark py-3">Tạm tính</p>
                                    </td>
                                    <td class="py-5">
                                        <div class="py-3 border-bottom border-top">
                                            <p class="mb-0 text-dark"><?php echo number_format($sub_total, 0, ',', '.'); ?> đ</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"></th>
                                    <td class="py-5">
                                        <p class="mb-0 text-dark py-3">Phí vận chuyển</p>
                                    </td>
                                    <td class="py-5"></td>
                                    <td class="py-5"></td>
                                    <td class="py-5">
                                        <div class="py-3 border-bottom border-top">
                                            <p class="mb-0 text-dark"><?php echo number_format($shipping_fee, 0, ',', '.'); ?> đ</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"></th>
                                    <td class="py-5">
                                        <p class="mb-0 text-dark text-uppercase py-3 fw-bold">Tổng cộng</p>
                                    </td>
                                    <td class="py-5"></td>
                                    <td class="py-5"></td>
                                    <td class="py-5">
                                        <div class="py-3 border-bottom border-top">
                                            <p class="mb-0 text-primary fw-bold lead"><?php echo number_format($sub_total + $shipping_fee, 0, ',', '.'); ?> đ</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row g-4 text-center align-items-center justify-content-center border-bottom py-3">
                        <div class="col-12">
                            <div class="form-check text-start my-3">
                                <input type="radio" class="form-check-input bg-primary border-0" id="Payments-Transfer" name="payment_method" value="SEPAY">
                                <label class="form-check-label" for="Payments-Transfer">Chuyển khoản ngân hàng (Quét mã QR SePay)</label>
                            </div>
                        </div>
                    </div>
                    <div class="row g-4 text-center align-items-center justify-content-center border-bottom py-3">
                        <div class="col-12">
                            <div class="form-check text-start my-3">
                                <input type="radio" class="form-check-input bg-primary border-0" id="Payments-COD" name="payment_method" value="COD" checked>
                                <label class="form-check-label" for="Payments-COD">Thanh toán khi nhận hàng (COD)</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 text-center align-items-center justify-content-center pt-4">
                        <button type="submit" name="btn_order" class="btn border-secondary py-3 px-4 text-uppercase w-100 text-primary">Đặt hàng</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // 1. DỮ LIỆU PHƯỜNG (Gom theo Quận/TP)
    const wardData = {
        "TP. Thủ Đức": ["Phường Hiệp Bình", "Phường Tam Bình", "Phường Thủ Đức", "Phường Linh Xuân", "Phường Long Bình", "Phường Tăng Nhơn Phú", "Phường Phước Long", "Phường Long Phước", "Phường Long Trường", "Phường An Khánh", "Phường Bình Trưng", "Phường Cát Lái"],
        "Quận 1": ["Phường Tân Định", "Phường Sài Gòn", "Phường Bến Thành", "Phường Cầu Ông Lãnh"],
        "Quận 3": ["Phường Xuân Hòa", "Phường Nhiêu Lộc", "Phường Bàn Cờ"],
        "Quận 4": ["Phường Vĩnh Hội", "Phường Khánh Hội", "Phường Xóm Chiếu"],
        "Quận 5": ["Phường Chợ Quán", "Phường An Đông", "Phường Chợ Lớn"],
        "Quận 6": ["Phường Bình Tiên", "Phường Bình Tây", "Phường Bình Phú", "Phường Phú Lâm"],
        "Quận 7": ["Phường Tân Mỹ", "Phường Phú Thuận", "Phường Tân Hưng", "Phường Tân Thuận"],
        "Quận 8": ["Phường Chánh Hưng", "Phường Bình Đông", "Phường Phú Định"],
        "Quận 10": ["Phường Vườn Lài", "Phường Diên Hồng", "Phường Hòa Hưng"],
        "Quận 11": ["Phường Minh Phụng", "Phường Bình Thới", "Phường Hòa Bình", "Phường Phú Thọ"],
        "Quận 12": ["Phường Đông Hưng Thuận", "Phường Trung Mỹ Tây", "Phường Tân Thới Hiệp", "Phường Thới An", "Phường An Phú Đông"],
        "Quận Bình Thạnh": ["Phường Gia Định", "Phường Bình Thạnh", "Phường Bình Lợi Trung", "Phường Thạnh Mỹ Tây", "Phường Bình Quới"],
        "Quận Bình Tân": ["Phường Bình Tân", "Phường Bình Hưng Hòa", "Phường Bình Trị Đông", "Phường An Lạc", "Phường Tân Tạo"],
        "Quận Gò Vấp": ["Phường Hạnh Thông", "Phường An Nhơn", "Phường Gò Vấp", "Phường Thông Tây Hội", "Phường An Hội Tây", "Phường An Hội Đông"],
        "Quận Phú Nhuận": ["Phường Đức Nhuận", "Phường Cầu Kiệu", "Phường Phú Nhuận"],
        "Quận Tân Phú": ["Phường Tây Thạnh", "Phường Tân Sơn Nhì", "Phường Phú Thọ Hòa", "Phường Phú Thạnh", "Phường Tân Phú"],
        "Quận Tân Bình": ["Phường Tân Sơn Hòa", "Phường Tân Sơn Nhất", "Phường Tân Hòa", "Phường Bảy Hiền", "Phường Tân Bình", "Phường Tân Sơn"]
    };

    // 2. DỮ LIỆU XÃ (Gom theo Huyện)
    const communeData = {
        "Huyện Bình Chánh": ["Xã Vĩnh Lộc", "Xã Tân Vĩnh Lộc", "Xã Bình Lợi", "Xã Tân Nhựt", "Xã Bình Chánh", "Xã Hưng Long", "Xã Bình Hưng"],
        "Huyện Củ Chi": ["Xã An Nhơn Tây", "Xã Thái Mỹ", "Xã Nhuận Đức", "Xã Tân An Hội", "Xã Củ Chi", "Xã Phú Hòa Đông", "Xã Bình Mỹ"],
        "Huyện Cần Giờ": ["Xã Bình Khánh", "Xã An Thới Đông", "Xã Cần Giờ", "Xã Thạnh An"],
        "Huyện Hóc Môn": ["Xã Đông Thạnh", "Xã Hóc Môn", "Xã Xuân Thới Sơn", "Xã Bà Điểm"],
        "Huyện Nhà Bè": ["Xã Nhà Bè", "Xã Hiệp Phước"]
    };

    const wardSelect = document.getElementById('wardSelect');
    const communeSelect = document.getElementById('communeSelect');
    const finalInput = document.getElementById('final_address_unit');

    function populateSelect(selectElement, dataObj) {
        for (const [groupName, items] of Object.entries(dataObj)) {
            const optgroup = document.createElement('optgroup');
            optgroup.label = groupName;
            items.forEach(item => {
                const option = document.createElement('option');
                option.value = item;
                option.text = item;
                option.setAttribute('data-district', groupName);
                optgroup.appendChild(option);
            });
            selectElement.appendChild(optgroup);
        }
    }

    window.onload = function() {
        populateSelect(wardSelect, wardData);
        populateSelect(communeSelect, communeData);
    };

    wardSelect.addEventListener('change', function() {
        if (this.value !== "") {
            communeSelect.selectedIndex = 0;
            const selectedOption = this.options[this.selectedIndex];
            const district = selectedOption.getAttribute('data-district');
            finalInput.value = `${this.value}, ${district}, TP. Hồ Chí Minh`;
        }
    });

    communeSelect.addEventListener('change', function() {
        if (this.value !== "") {
            wardSelect.selectedIndex = 0;
            const selectedOption = this.options[this.selectedIndex];
            const district = selectedOption.getAttribute('data-district');
            finalInput.value = `${this.value}, ${district}, TP. Hồ Chí Minh`;
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>