<?php
require_once 'config/database.php'; // Gọi file kết nối

// Thử lấy dữ liệu từ DB ra xem được chưa

function get_All_category($conn) {
      $sql = "SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            GROUP BY c.id";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_All_products($conn) {
    $sql = "SELECT * FROM products";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function get_Products_Dynamic($conn, $category_id = null, $page = 1, $limit = 9) {
    $offset = ($page - 1) * $limit;
    
    $sql = "SELECT * FROM products WHERE is_active = 1";
    
    // Nếu có chọn danh mục thì thêm điều kiện WHERE
    if ($category_id != null) {
        $sql .= " AND category_id = :cat_id";
    }
    
    $sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($sql);
    
    if ($category_id != null) {
        $stmt->bindValue(':cat_id', $category_id, PDO::PARAM_INT);
    }
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 2. HÀM ĐẾM TỔNG SẢN PHẨM (ĐỂ TÍNH SỐ TRANG)
function count_Total_Products($conn, $category_id = null) {
    $sql = "SELECT COUNT(*) FROM products WHERE is_active = 1";
    
    if ($category_id != null) {
        $sql .= " AND category_id = :cat_id";
    }
    
    $stmt = $conn->prepare($sql);
    
    if ($category_id != null) {
        $stmt->bindValue(':cat_id', $category_id, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    return $stmt->fetchColumn(); // Trả về 1 số duy nhất
}

function get_Cart_Products($conn, $cart_ids) {
    if (empty($cart_ids)) return [];

    // Chuyển mảng ID thành chuỗi (ví dụ: "1,3,5")
    $ids = implode(',', array_keys($cart_ids));
    
    $sql = "SELECT * FROM products WHERE id IN ($ids)";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Hàm lấy chi tiết 1 sản phẩm theo ID
function get_Product_By_Id($conn, $id) {
    // Join với bảng categories để lấy luôn tên danh mục nếu cần
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = :id AND p.is_active = 1";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC); // Lấy 1 dòng
}
?>