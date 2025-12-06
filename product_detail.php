<?php
require_once 'includes/function.php';

// 1. Lấy ID từ URL (VD: product_detail.php?id=5)
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. Gọi hàm lấy dữ liệu
$product = get_Product_By_Id($conn, $id);

// Nếu không tìm thấy sản phẩm thì đẩy về trang Shop
if (!$product) {
    header("Location: shop.php");
    exit;
}

$pageTitle = $product['name']; // Đặt tiêu đề tab trình duyệt
require_once 'includes/header.php';
?>

<div class="container-fluid page-header py-5">
    <h1 class="text-center text-white display-6">Chi tiết sản phẩm</h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
        <li class="breadcrumb-item"><a href="shop.php">Cửa hàng</a></li>
        <li class="breadcrumb-item active text-white">Chi tiết</li>
    </ol>
</div>

<div class="container-fluid py-5 mt-5">
    <div class="container py-5">
        <div class="row g-4 mb-5">
            <div class="col-lg-8 col-xl-9">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="border rounded">
                            <a href="#">
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" class="img-fluid rounded" alt="Image">
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <h4 class="fw-bold mb-3"><?php echo htmlspecialchars($product['name']); ?></h4>
                        <p class="mb-3">Danh mục: <?php echo htmlspecialchars($product['category_name']); ?></p>
                        <h5 class="fw-bold mb-3"><?php echo number_format($product['price'], 0, ',', '.'); ?> đ</h5>
                        
                        <p class="mb-4"> <?php echo nl2br(htmlspecialchars($product['description'])); ?>
</p>
                        
                        <div class="input-group quantity mb-5" style="width: 100px;">
                            <div class="input-group-btn">
                                <button class="btn btn-sm btn-minus rounded-circle bg-light border" onclick="var res = document.getElementById('qty'); var qty = parseInt(res.value); if(qty > 1) res.value = qty - 1; return false;">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                            <input type="text" class="form-control form-control-sm text-center border-0" value="1" id="qty" readonly>
                            <div class="input-group-btn">
                                <button class="btn btn-sm btn-plus rounded-circle bg-light border" onclick="var res = document.getElementById('qty'); var qty = parseInt(res.value); res.value = qty + 1; return false;">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        
                        <a href="#" onclick="this.href='cart_action.php?add=<?php echo $product['id']; ?>&qty=' + document.getElementById('qty').value" 
                           class="btn border border-secondary rounded-pill px-4 py-2 mb-4 text-primary">
                            <i class="fa fa-shopping-bag me-2 text-primary"></i> Thêm vào giỏ
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-xl-3">
                </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>