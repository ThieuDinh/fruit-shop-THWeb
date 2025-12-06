<?php
session_start();
require_once 'config/database.php';

// CẤU HÌNH GIỚI HẠN
$MAX_QTY_PER_PRODUCT = 5; // Số lượng tối đa cho phép mua

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 1. THÊM SẢN PHẨM (Nút "Thêm" ở Shop hoặc Chi tiết)
if (isset($_GET['add'])) {
    $id = (int)$_GET['add'];
    
    // Lấy thông tin tồn kho
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch();

    if ($product) {
        $current_cart_qty = isset($_SESSION['cart'][$id]) ? $_SESSION['cart'][$id] : 0;
        $new_qty = $current_cart_qty + 1;

        // KIỂM TRA 1: Giới hạn số lượng mua (Business Rule)
        if ($new_qty > $MAX_QTY_PER_PRODUCT) {
            echo "<script>alert('Mỗi sản phẩm chỉ được mua tối đa " . $MAX_QTY_PER_PRODUCT . " cái!'); window.history.back();</script>";
            exit;
        }

        // KIỂM TRA 2: Giới hạn tồn kho (Physical Rule)
        if ($new_qty <= $product['stock']) {
            $_SESSION['cart'][$id] = $new_qty;
        } else {
            echo "<script>alert('Kho chỉ còn " . $product['stock'] . " sản phẩm, không đủ hàng!'); window.history.back();</script>";
            exit;
        }
    }
    
    // Quay lại trang cũ
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

// 3. TĂNG/GIẢM SỐ LƯỢNG (Trong trang Cart)
if (isset($_GET['update']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['update'];

    if (isset($_SESSION['cart'][$id])) {
        if ($action == 'inc') {
            // Lấy tồn kho hiện tại
            $stmt = $conn->prepare("SELECT stock FROM products WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $product = $stmt->fetch();
            
            $new_qty = $_SESSION['cart'][$id] + 1;

            // CHECK 1: Giới hạn 5
            if ($new_qty > $MAX_QTY_PER_PRODUCT) {
                echo "<script>alert('Bạn đã đạt giới hạn mua " . $MAX_QTY_PER_PRODUCT . " sản phẩm này!'); window.location.href='cart.php';</script>";
                exit;
            }

            // CHECK 2: Tồn kho
            if ($product && $new_qty <= $product['stock']) {
                $_SESSION['cart'][$id]++;
            } else {
                echo "<script>alert('Không đủ hàng trong kho!'); window.location.href='cart.php';</script>";
                exit;
            }

        } elseif ($action == 'dec') {
            $_SESSION['cart'][$id]--;
            if ($_SESSION['cart'][$id] <= 0) {
                unset($_SESSION['cart'][$id]);
            }
        }
    }
    header("Location: cart.php");
    exit;
}
?>