<?php
session_start();
require_once '../config.php';

// 1. Thiết lập tài khoản mặc định để demo xác thực nhanh (Dùng cho file verify_qr.php truy vấn DB)
$default_user = "manh@gmail.com"; 

$_SESSION['auth_username'] = $default_user;

// 🔴 SỬA TẠI ĐÂY: Đổi từ commander_cockpit.php thành welcome.php
// Đây là nơi trang verify_qr.php sẽ dẫn bạn đến sau khi nhập mã 6 số đúng
$_SESSION['return_url'] = "welcome.php"; 

// 2. Chuyển hướng sang trang nhập mã OTP
header("Location: verify_qr.php");
exit();
?>