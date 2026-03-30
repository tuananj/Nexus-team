<?php
require "vendor/autoload.php";
include "config.php";

// Đảm bảo session được bật
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use PragmaRX\Google2FA\Google2FA;
$google2fa = new Google2FA();

// 1. Kiểm tra xem đã qua bước Login chưa (Dùng auth_username từ login.php)
if (!isset($_SESSION['auth_username'])) {
    header("Location: login.php");
    exit();
}

// Huy lưu ý: Trong Database của bạn tên cột là 'name'
$current_user = $_SESSION['auth_username']; 

// 2. LẤY SECRET TỪ DATABASE (Dùng cột 'name' thay vì 'username')
$stmt = $conn->prepare("SELECT mfa_secret FROM users WHERE name = ?");
$stmt->bind_param("s", $current_user);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// 3. LOGIC: Nếu chưa có Secret thì tạo mới, có rồi thì dùng lại
if (empty($user_data['mfa_secret'])) {
    // Tạo mã bí mật mới
    $secret = $google2fa->generateSecretKey();
    
    // Lưu vào cột mfa_secret dựa trên cột name
    $update = $conn->prepare("UPDATE users SET mfa_secret = ? WHERE name = ?");
    $update->bind_param("ss", $secret, $current_user);
    $update->execute();
} else {
    // Nếu đã quét rồi, lấy lại cái cũ để không bị đổi mã QR
    $secret = $user_data['mfa_secret'];
}

// Tạo URL để hiện mã QR
$QR_URL = $google2fa->getQRCodeUrl(
    "Nexus-MFA",     // Tên ứng dụng của Huy
    $current_user,   // Tên User hiện trên App
    $secret          // Mã bí mật
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Thiết lập mã QR - Huy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5 card shadow p-4 text-center">
                <h4>Quét mã xác nhận 2 lớp</h4>
                <p class="text-muted small">Chào <b><?php echo $current_user; ?></b>, hãy quét mã này vào Google Authenticator hoặc Extension.</p>
                
                <div class="my-4 p-3 bg-white border d-inline-block">
                    <!-- Sử dụng API để vẽ mã QR -->
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($QR_URL); ?>">
                </div>

                <div class="alert alert-warning py-2 small">
                    Đừng chia sẻ mã này cho bất kỳ ai!
                </div>

                <a href="verify_qr.php" class="btn btn-success w-100">Tôi đã quét xong, tiếp tục</a>
            </div>
        </div>
    </div>
</body>
</html>