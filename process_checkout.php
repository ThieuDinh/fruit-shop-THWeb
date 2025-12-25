<?php
session_start();
require_once 'config/database.php';
require_once 'includes/function.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_order'])) {
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    $fullname = $_POST['lastname'] . ' ' . $_POST['firstname'];
    $phone = $_POST['phone'];
    $address = $_POST['address_detail'] . ', ' . $_POST['final_address_unit'];
    $note = $_POST['note'];
    $payment_method = $_POST['payment_method'];

    $cart_items = $_SESSION['cart'];
    
    //kiểm tra kho
    foreach ($cart_items as $id => $qty) {
        $stmt = $conn->prepare("SELECT name, stock FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $checkPro = $stmt->fetch();
        
        if ($checkPro['stock'] < $qty) {
            echo "<script>alert('Sản phẩm " . $checkPro['name'] . " chỉ còn " . $checkPro['stock'] . " sản phẩm. Vui lòng cập nhật giỏ hàng!'); window.location.href='cart.php';</script>";
            exit;
        }
    }

    $products = get_Cart_Products($conn, $cart_items);
    $total_money = 0;
    foreach ($products as $p) {
        $total_money += $p['price'] * $cart_items[$p['id']];
    }
    $total_money += 30000;

    try {
    $conn->beginTransaction();
  
        // thêm đơn vào db
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_money, payment_method, shipping_address, phone, status, created_at) VALUES (:uid, :total, :method, :address, :phone, 'pending', NOW())");
        $stmt->execute([':uid' => $user_id, ':total' => $total_money, ':method' => $payment_method, ':address' => $address, ':phone' => $phone]);
        $order_id = $conn->lastInsertId();

        //trừ vào kho
        $sql_detail = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (:oid, :pid, :qty, :price)";
        $stmt_detail = $conn->prepare($sql_detail);
        $sql_update_stock = "UPDATE products SET stock = stock - :qty WHERE id = :pid";
        $stmt_stock = $conn->prepare($sql_update_stock);

        foreach ($products as $p) {
            $buy_qty = $cart_items[$p['id']];

            $stmt_detail->execute([
                ':oid' => $order_id,
                ':pid' => $p['id'],
                ':qty' => $buy_qty,
                ':price' => $p['price']
            ]);

            $stmt_stock->execute([
                ':qty' => $buy_qty,
                ':pid' => $p['id']
            ]);
        }

        $conn->commit();
        unset($_SESSION['cart']);

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