<?php
// 1. Khởi tạo hệ thống
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../config.php"; // Thoát khỏi nexus/ để tìm config.php
require_once "GoogleAuthenticator.php"; // Dùng file có sẵn trong thư mục nexus

$ga = new GoogleAuthenticator();

// 2. Kiểm tra Session
if (!isset($_SESSION['auth_username'])) {
    header("Location: login.php");
    exit();
}

$current_user = $_SESSION['auth_username'];
$error = "";

// 3. Lấy Secret Key từ Database
// Kiểm tra tên cột trong DB của bạn là 'Email' hay 'name' (Ở đây mình để là Email)
$stmt = $conn->prepare("SELECT mfa_secret FROM users WHERE Email = ?"); 
$stmt->bind_param("s", $current_user);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

if (!$user_data || empty($user_data['mfa_secret'])) {
    header("Location: setup-mfa.php");
    exit();
}

$secret_key = $user_data['mfa_secret'];

// 4. Xử lý xác thực khi bấm nút
if (isset($_POST['btn_verify'])) {
    $otp_code = trim($_POST['otp_code']);
    
    // Kiểm tra mã 6 số bằng file GoogleAuthenticator.php
    // Hàm checkCode: $secret, $code, $discrepancy (độ lệch thời gian)
    $checkResult = $ga->checkCode($secret_key, $otp_code, 2); 

    if ($checkResult) {
        // ✅ THÀNH CÔNG
        $_SESSION['mfa_verified'] = true;
        $_SESSION['user_logged_in'] = true;

        $target = isset($_SESSION['return_url']) ? $_SESSION['return_url'] : "welcome.php";
        unset($_SESSION['return_url']); 
        
        header("Location: " . $target);
        exit();
    } else {
        $error = "Mã OTP không chính xác hoặc đã hết hạn!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nexus ID - Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #0f172a; color: white; font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .verify-card { background: rgba(30, 41, 59, 0.7); border: 1px solid #c5a059; border-radius: 25px; padding: 40px; width: 400px; backdrop-filter: blur(10px); text-align: center; }
        .otp-input { background: #000 !important; color: #c5a059 !important; border: 1px solid #333 !important; font-size: 2rem !important; letter-spacing: 12px; text-align: center; border-radius: 12px; font-weight: bold; }
        .btn-verify { background: #c5a059; color: #0f172a; border: none; padding: 14px; border-radius: 12px; font-weight: 800; width: 100%; transition: 0.3s; margin-top: 20px; }
        .btn-verify:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(197, 160, 89, 0.4); }
    </style>
</head>
<body>
    <div class="verify-card">
        <i class="fas fa-user-shield fa-3x mb-3" style="color:#c5a059;"></i>
        <h4 class="mb-3">XÁC MINH NEXUS ID</h4>
        <p class="text-muted small">Nhập mã 6 số cho tài khoản: <br><strong><?php echo htmlspecialchars($current_user); ?></strong></p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger p-2 small" style="background:rgba(239,68,68,0.1); border:none; color:#fca5a5;"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="otp_code" class="form-control otp-input mb-3" placeholder="······" maxlength="6" required autofocus autocomplete="off">
            <button type="submit" name="btn_verify" class="btn-verify">XÁC NHẬN & ĐĂNG NHẬP</button>
        </form>
    </div>
</body>
</html>