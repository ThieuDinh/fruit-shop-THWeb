<?php
session_start();
require_once 'config/database.php';
require_once 'includes/function.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_order'])) {
    
    // ... (Phần lấy thông tin khách hàng giữ nguyên) ...
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    $fullname = $_POST['lastname'] . ' ' . $_POST['firstname'];
    $phone = $_POST['phone'];
    $address = $_POST['address_detail'] . ', ' . $_POST['final_address_unit'];
    $note = $_POST['note'];
    $payment_method = $_POST['payment_method'];

    $cart_items = $_SESSION['cart'];
    
    // --- KIỂM TRA LẠI KHO MỘT LẦN NỮA (QUAN TRỌNG) ---
    // Vì có thể lúc thêm vào giỏ còn hàng, nhưng lúc bấm thanh toán thì người khác mua mất rồi
    foreach ($cart_items as $id => $qty) {
        $stmt = $conn->prepare("SELECT name, stock FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $checkPro = $stmt->fetch();
        
        if ($checkPro['stock'] < $qty) {
            echo "<script>alert('Sản phẩm " . $checkPro['name'] . " chỉ còn " . $checkPro['stock'] . " sản phẩm. Vui lòng cập nhật giỏ hàng!'); window.location.href='cart.php';</script>";
            exit;
        }
    }

    // --- TÍNH TIỀN ---
    $products = get_Cart_Products($conn, $cart_items);
    $total_money = 0;
    foreach ($products as $p) {
        $total_money += $p['price'] * $cart_items[$p['id']];
    }
    $total_money += 30000;

    try {
    $conn->beginTransaction();

    // 1. Tạo đơn hàng (Xóa dòng $sql_order thừa đi)
  
        // ... (Code insert orders giữ nguyên) ...
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_money, payment_method, shipping_address, phone, status, created_at) VALUES (:uid, :total, :method, :address, :phone, 'pending', NOW())");
        $stmt->execute([':uid' => $user_id, ':total' => $total_money, ':method' => $payment_method, ':address' => $address, ':phone' => $phone]);
        $order_id = $conn->lastInsertId();

        // 2. Tạo chi tiết đơn hàng VÀ TRỪ KHO
        $sql_detail = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (:oid, :pid, :qty, :price)";
        $stmt_detail = $conn->prepare($sql_detail);

        // SQL TRỪ KHO
        $sql_update_stock = "UPDATE products SET stock = stock - :qty WHERE id = :pid";
        $stmt_stock = $conn->prepare($sql_update_stock);

        foreach ($products as $p) {
            $buy_qty = $cart_items[$p['id']];

            // Lưu chi tiết
            $stmt_detail->execute([
                ':oid' => $order_id,
                ':pid' => $p['id'],
                ':qty' => $buy_qty,
                ':price' => $p['price']
            ]);

            // Trừ kho ngay lập tức
            $stmt_stock->execute([
                ':qty' => $buy_qty,
                ':pid' => $p['id']
            ]);
        }

        $conn->commit();
        unset($_SESSION['cart']);

        // ... (Phần điều hướng giữ nguyên) ...
        if ($payment_method == 'SEPAY') {
             header("Location: payment_sepay.php?order_id=" . $order_id);
        } else {
             header("Location: order_success.php?order_id=" . $order_id);
        }
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        echo "Lỗi: " . $e->getMessage();
    }
}
?>