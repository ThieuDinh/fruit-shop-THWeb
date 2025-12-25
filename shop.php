<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/function.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$category_id = isset($_GET['category_id']) && $_GET['category_id'] != '' ? (int)$_GET['category_id'] : null;

//phân trang
$limit = 9; 

$products = get_Products_Dynamic($conn, $category_id, $page, $limit);
$total_products = count_Total_Products($conn, $category_id);
$total_pages = ceil($total_products / $limit);

$count_categories = get_All_category($conn);
$pageTitle = "Shop - Cửa hàng Hoa quả";

require_once 'includes/header.php';
?>
<!-- Modal Search Start -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content rounded-0">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Tìm kiếm</h5>
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


<!-- Single Page Header start -->
<div class="container-fluid page-header py-5">
    <h1 class="text-center text-white display-6">Cửa hàng</h1>
    <ol class="breadcrumb justify-content-center mb-0">
        <li class="breadcrumb-item"><a href="#">Trang chủ</a></li>

        <li class="breadcrumb-item active text-white">Cửa hàng</li>
    </ol>
</div>
<!-- Single Page Header End -->


<!-- Fruits Shop Start-->
<div class="container-fluid fruite py-5">
    <div class="container py-5">
        <h1 class="mb-4">Farm2Home</h1>
        <div class="row g-4">
            <div class="col-lg-12">

                <div class="row g-4">
                    <div class="col-lg-3">
                        <div class="row g-4">
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <h4>Danh mục</h4>
                                    <ul class="list-unstyled fruite-categorie">
                                        <li>
                                            <div class="d-flex justify-content-between fruite-name">
                                                <a href="shop.php"><i class="fas fa-apple-alt me-2"></i>Tất cả</a>
                                                <span>(<?php echo count_Total_Products($conn, null); ?>)</span>
                                            </div>
                                        </li>

                                        <?php foreach ($count_categories as $cat): ?>
                                            <li>
                                                <div class="d-flex justify-content-between fruite-name">
                                                    <a href="shop.php?category_id=<?php echo $cat['id']; ?>"
                                                        class="<?php echo ($category_id == $cat['id']) ? 'text-primary fw-bold' : ''; ?>">
                                                        <i class="fas fa-apple-alt me-2"></i>
                                                        <?php echo htmlspecialchars($cat['name']); ?>
                                                    </a>
                                                    <span>(<?php echo htmlspecialchars($cat['product_count']); ?>)</span>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9">
                        <div class="row g-4 justify-content-center">

                            <?php if (count($products) > 0): ?>
                                <?php foreach ($products as $pro): ?>
                                    <div class="col-md-6 col-lg-6 col-xl-4">
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

                            <?php endif; ?>

                            <?php if ($total_pages > 1): ?>
                                <div class="col-12">
                                    <div class="pagination d-flex justify-content-center mt-5">
                                        <?php
                                        $url_param = "";
                                        if ($category_id) {
                                            $url_param = "&category_id=" . $category_id;
                                        }
                                        ?>

                                        <?php if ($page > 1): ?>
                                            <a href="shop.php?page=<?php echo ($page - 1) . $url_param; ?>" class="rounded">&laquo;</a>
                                        <?php endif; ?>

                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <a href="shop.php?page=<?php echo $i . $url_param; ?>"
                                                class="rounded <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endfor; ?>

                                        <?php if ($page < $total_pages): ?>
                                            <a href="shop.php?page=<?php echo ($page + 1) . $url_param; ?>" class="rounded">&raquo;</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Fruits Shop End-->
<?php
// 4. Nhúng Footer
require_once 'includes/footer.php';
?>