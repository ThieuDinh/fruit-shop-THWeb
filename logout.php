<?php
session_start();

// Xóa tất cả biến trong session
$_SESSION = [];

// Hủy session trên server
session_destroy();

// Chuyển hướng về trang login
header("Location: login.php");
exit();
?>