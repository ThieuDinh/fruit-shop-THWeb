<?php
session_start();
require_once 'config/database.php';
// Xóa tất cả biến trong session
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("UPDATE users SET remember_token = NULL WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
}
if (isset($_COOKIE['remember_token'])) {
    // setcookie(tên, rỗng, quá khứ, đường dẫn, domain, secure, httponly)
    setcookie('remember_token', '', time() - 3600, "/", "", false, true);
    unset($_COOKIE['remember_token']);
}
// Hủy session trên server
session_unset();
session_destroy();

// Chuyển hướng về trang login
header("Location: login.php");
exit();
?>