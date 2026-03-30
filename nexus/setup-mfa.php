<?php
session_start();
// 1. Phải dùng require_once để tránh nạp chồng file
require_once '../config.php'; 
require_once 'GoogleAuthenticator.php';

// 2. Kiểm tra đăng nhập
if (!isset($_SESSION['temp_user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['temp_user']['id']; 
$email = $_SESSION['temp_user']['email']; 

$ga = new GoogleAuthenticator();

// 3. Tạo Secret và lưu vào Database
$secret = $ga->generateSecret(); 
$sql = "UPDATE users SET mfa_secret = '$secret' WHERE id = '$user_id'";
mysqli_query($conn, $sql);

// 4. Tạo URL mã QR bằng QuickChart (API này quét cực nhạy)
$issuer = 'Nexus_System';
$otpauth_url = "otpauth://totp/$issuer:$email?secret=$secret&issuer=$issuer";
$qrCodeUrl = "https://quickchart.io/chart?cht=qr&chs=250x250&chl=" . urlencode($otpauth_url);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thiết lập MFA</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; max-width: 400px; }
        .qr-code { background: #fff; padding: 10px; border: 1px solid #ddd; display: inline-block; margin: 20px 0; min-height: 250px; min-width: 250px; }
        .btn { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; }
        p { color: #606770; font-size: 14px; }
    </style>
</head>
<body>

<div class="card">
    <h2>Thiết lập MFA</h2>
    <p>Dùng ứng dụng <b>Google Authenticator</b> quét mã QR này:</p>
    
    <div class="qr-code">
        <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code" width="250" height="250">
    </div>

    <p>Tài khoản: <b><?php echo htmlspecialchars($email); ?></b></p>
    <br>
    <a href="verify-otp.php" class="btn">Tôi đã quét xong</a>
</div>

</body>
</html>