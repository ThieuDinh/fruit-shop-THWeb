<?php
// coming_soon.php
require_once 'includes/header.php';
?>

<style>
    /* CSS riêng cho trang này để tạo hiệu ứng */
    .maintenance-page {
        min-height: 60vh; /* Chiều cao tối thiểu để căn giữa cho đẹp */
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .gear-icon {
        font-size: 80px;
        color: #ffc107; /* Màu vàng cảnh báo */
        animation: spin 4s linear infinite; /* Hiệu ứng xoay nhẹ */
    }
    @keyframes spin {
        100% { -webkit-transform: rotate(360deg); transform:rotate(360deg); }
    }
</style>

<div class="container maintenance-page">
    <div class="row justify-content-center w-100">
        <div class="col-md-8 col-lg-6 text-center">
            
            <div class="mb-4">
                <i class="fas fa-hammer gear-icon me-3"></i>
                <i class="fas fa-cog gear-icon" style="font-size: 50px; color: #6c757d; animation-direction: reverse;"></i>
            </div>

            <h1 class="fw-bold mb-3">Ôi! Chức năng này đang được "trồng" 🌱 ^^</h1>
            
            <p class="lead text-muted mb-4">
                Xin lỗi bạn nha, tui chưa hoàn thiện tính năng này. 
                Cây chưa lớn, bạn vui lòng quay lại sau nhé!
            </p>

            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <a href="index.php" class="btn btn-primary btn-lg px-4 gap-3">
                    <i class="fas fa-home me-2"></i>Về trang chủ
                </a>
                <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg px-4">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại trang trước
                </a>
            </div>

        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>