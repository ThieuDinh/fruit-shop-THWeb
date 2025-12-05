<?php
session_start();

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 1. THÊM SẢN PHẨM VÀO GIỎ
if (isset($_GET['add'])) {
    $id = (int)$_GET['add'];
    
    // Nếu sản phẩm đã có trong giỏ -> Tăng số lượng
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]++;
    } else {
        // Nếu chưa có -> Thêm mới với số lượng 1
        $_SESSION['cart'][$id] = 1;
    }
    
    // Quay lại trang trước đó (Shop hoặc Product Detail)
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// 2. XÓA SẢN PHẨM
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    unset($_SESSION['cart'][$id]);
    
    header("Location: cart.php");
    exit;
}

// 3. TĂNG/GIẢM SỐ LƯỢNG (Dùng cho trang cart.php)
if (isset($_GET['update']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['update']; // 'inc' (tăng) hoặc 'dec' (giảm)

    if (isset($_SESSION['cart'][$id])) {
        if ($action == 'inc') {
            $_SESSION['cart'][$id]++;
        } elseif ($action == 'dec') {
            $_SESSION['cart'][$id]--;
            // Nếu giảm về 0 thì xóa luôn
            if ($_SESSION['cart'][$id] <= 0) {
                unset($_SESSION['cart'][$id]);
            }
        }
    }
    header("Location: cart.php");
    exit;
}
?>