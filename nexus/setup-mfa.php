<?php
session_start();
include 'db.php';
require_once 'GoogleAuthenticator.php';

// Kiểm tra xem Huy đã đăng nhập chưa (lấy từ session của login.php)
if (!isset($_SESSION['temp_user'])) {
    die("Huy ơi, bạn cần đăng nhập trước khi thiết lập MFA nhé!");
}

$user_id = $_SESSION['temp_user']['id'];
$username = $_SESSION['temp_user']['username'];

// 1. Khởi tạo class (Sửa lại tên class cho đúng với file GoogleAuthenticator.php của bạn)
$ga = new GoogleAuthenticator();

// 2. Tạo Secret Key ngẫu nhiên (Chuỗi 16 ký tự bí mật)
$secret = $ga->generateSecret();

// Sửa getQRCodeGoogleUrl thành getUrl cho đúng với thư viện của Huy
$qrCodeUrl = $ga->getUrl($username, 'Project_Piltover', $secret);
// 4. Cập nhật Secret Key vào Database cho user này
// Mình dùng câu lệnh SQL đúng với tên bảng và cột của Huy
$sql = "UPDATE users SET mfa_secret = '$secret', mfa_enabled = 1 WHERE id = '$user_id'";

if (mysqli_query($conn, $sql)) {
    echo "<h2>Thiết lập MFA cho tài khoản: $username</h2>";
    echo "<p>1. Quét mã QR này bằng App Google Authenticator trên điện thoại:</p>";
    echo "<img src='".$qrCodeUrl."' style='border: 10px solid white; box-shadow: 0 0 10px #ccc;' />";
    echo "<p>2. Sau khi quét xong, mỗi khi đăng nhập bạn sẽ cần nhập mã 6 số từ App.</p>";
    echo "<br><a href='index.php'>Quay lại trang Đăng nhập</a>";
} else {
    echo "Lỗi cập nhật Database: " . mysqli_error($conn);
}
?>