<?php
session_start();
require_once 'GoogleAuthenticator.php';
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ga = new GoogleAuthenticator();
    // Lấy Secret Key của user đã lưu trong Session từ lúc login thành công
    $secret = $_SESSION['temp_user']['mfa_secret'];
    $otp = $_POST['otp'];

    // Kiểm tra mã OTP Huy nhập vào có khớp với app điện thoại không
    $checkResult = $ga->checkCode($secret, $otp);

    if ($checkResult) {
        // ĐÚNG: Chuyển sang trang chủ chính thức
        $_SESSION['user_id'] = $_SESSION['temp_user']['id'];
        echo "<h2>✅ Xác thực thành công! Chào mừng Huy đến với hệ thống.</h2>";
        echo "<a href='dashboard.php'>Đi tới trang quản trị</a>";
    } else {
        // SAI: Báo lỗi và bắt nhập lại
        echo "<h2>❌ Mã OTP sai rồi Huy ơi! Hãy kiểm tra lại App.</h2>";
        echo "<a href='verify-otp.php'>Thử lại lần nữa</a>";
    }
}
?>