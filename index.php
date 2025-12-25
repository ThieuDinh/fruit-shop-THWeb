<?php
// ... code cũ của bạn ở dưới ...
include 'includes/function.php';
$categories = get_All_category($conn);
$products = get_All_products($conn);
?>
<?php
// 1. Cấu hình tiêu đề trang
$pageTitle = "Trang chủ - Cửa hàng Hoa quả";

// 2. Nhúng Header
require_once 'includes/header.php';
?>
<!-- Modal Search Start -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content rounded-0">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Search by keyword</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex align-items-center">
                <div class="input-group w-75 mx-auto d-flex">
                    <input type="search" class="form-control p-3" placeholder="keywords" aria-describedby="search-icon-1">
                    <span id="search-icon-1" class="input-group-text p-3"><i class="fa fa-search"></i></span>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal Search End -->


<!-- Hero Start -->
<div class="container-fluid py-5 mb-5 hero-header">
    <div class="container py-5">
        <div class="row g-5 align-items-center">
            <div class="col-md-12 col-lg-7">
                <h4 class="mb-3 text-secondary">100% Tự Nhiên</h4>
                <h1 class="mb-5 display-3 text-primary">Thực phẩm rau củ quả hữu cơ</h1>
                
            </div>
            <div class="col-md-12 col-lg-5">
                <div id="carouselId" class="carousel slide position-relative" data-bs-ride="carousel">
                    <div class="carousel-inner" role="listbox">
                        <div class="carousel-item active rounded">
                            <img src="/public/img/hero-img-1.png" class="img-fluid w-100 h-100 bg-secondary rounded" alt="First slide">
                            <a href="#" class="btn px-4 py-2 text-white rounded">Hoa quả</a>
                        </div>
                        <div class="carousel-item rounded">
                            <img src="/public/img/hero-img-2.jpg" class="img-fluid w-100 h-100 rounded" alt="Second slide">
                            <a href="#" class="btn px-4 py-2 text-white rounded">Rau củ</a>
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselId" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselId" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Hero End -->

<!-- Fruits Shop Start-->
<div class="container-fluid fruite py-5">
    <div class="container py-5">
        <div class="text-center">
            <div class="row g-4">
                <div class="col-lg-4 text-start">
                    <h1>Sản phẩm nổi bật</h1>
                </div>
            </div>

            <?php foreach ($categories as $cat): ?>
                <?php
                // Gọi hàm lấy sản phẩm: Truyền ID danh mục, Trang 1, Giới hạn 4
                $cat_products = get_Products_Dynamic($conn, $cat['id'], 1, 4);
                ?>

                <?php if (!empty($cat_products)): ?>

                    <div class="row g-4 mt-3">
                        <div class="col-lg-12 d-flex justify-content-between align-items-center">
                            <h3 class="text-secondary text-start mb-0"><?php echo htmlspecialchars($cat['name']); ?></h3>
                            <a href="shop.php?category_id=<?php echo $cat['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill">Xem tất cả</a>
                        </div>
                    </div>

                    <div class="row g-4 mt-2 mb-5">
                        <?php foreach ($cat_products as $pro): ?>
                            <div class="col-md-6 col-lg-6 col-xl-3">
                                <div class="rounded position-relative fruite-item h-100 border border-secondary">
                                    <div class="fruite-img">
                                        <a href="product_detail.php?id=<?php echo $pro['id']; ?>">
                                        <img src="<?php echo htmlspecialchars($pro['image']); ?>" class="img-fluid w-100 rounded-top" alt="">
                                        </a>
                                    </div>

                                    <div class="p-4 border-top-0 rounded-bottom d-flex flex-column">
                                        <a href="product_detail.php?id=<?php echo $pro['id']; ?>" class="text-dark">
                                        <h4><?php echo htmlspecialchars($pro['name']); ?></h4>
                                        </a>
                                        <p><?php echo htmlspecialchars(mb_substr($pro['description'], 0, 50, 'UTF-8')) . '...'; ?></p>
                                        
                                        <div class="d-flex justify-content-between flex-lg-wrap mt-auto">
                                            <p class="text-dark fs-5 fw-bold mb-0"><?php echo number_format($pro['price']); ?> đ</p>
                                            <a href="cart_action.php?add=<?php echo $pro['id']; ?>" class="btn border border-secondary rounded-pill px-3 text-primary">
                                                <i class="fa fa-shopping-bag me-2 text-primary"></i> Thêm
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>


<?php
// 4. Nhúng Footer
require_once 'includes/footer.php';
?>