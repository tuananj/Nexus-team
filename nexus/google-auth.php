<?php
session_start();
require_once '../config.php';

// Giả lập: Google xác nhận tài khoản manh@gmail.com
$google_email = "manh@gmail.com"; 

$stmt = $conn->prepare("SELECT * FROM users WHERE Email = ?");
$stmt->bind_param("s", $google_email);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

if ($userData) {
    $_SESSION['user_logged_in'] = true;
    $_SESSION['temp_user'] = $userData;
    
    // Tạo thông báo để Dashboard hiển thị
    $_SESSION['toast_msg'] = "Đã xác thực danh tính qua Google MFA!";
    
    header("Location: dashboard.php"); // Vào thẳng Dashboard, không alert
} else {
    header("Location: login.php?error=access_denied");
}
exit();