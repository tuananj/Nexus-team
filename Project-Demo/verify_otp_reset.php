<?php
session_start();

// Nếu không có email hoặc OTP trong session thì đuổi về trang quên mật khẩu
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_otp'])) {
    header("Location: forgot_password.php");
    exit();
}

$error = "";
if (isset($_POST['btn_verify'])) {
    $user_otp = $_POST['otp'];

    // Kiểm tra mã OTP người dùng nhập với mã trong Session
    if ($user_otp == $_SESSION['reset_otp']) {
        // Đúng mã -> Cho phép sang trang đặt lại mật khẩu
        $_SESSION['otp_verified'] = true; 
        header("Location: reset_password.php");
        exit();
    } else {
        $error = "Mã OTP không chính xác. Vui lòng kiểm tra lại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác thực OTP - Nexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .otp-card { width: 400px; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .btn-verify { background-color: #0d6efd; color: white; font-weight: bold; }
    </style>
</head>
<body>

<div class="otp-card">
    <h4 class="text-center mb-4">Xác thực mã OTP</h4>
    <p class="text-center small text-muted">Mã xác thực đã được gửi đến email của bạn.</p>
    
    <?php if($error): ?>
        <div class="alert alert-danger p-2 small text-center"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Nhập mã OTP (6 số)</label>
            <input type="text" name="otp" class="form-control text-center" placeholder="123456" maxlength="6" required>
        </div>
        <button type="submit" name="btn_verify" class="btn btn-verify w-100 py-2">Xác nhận mã</button>
        <div class="text-center mt-3">
            <a href="forgot_password.php" class="text-decoration-none small text-muted">Gửi lại mã?</a>
        </div>
    </form>
</div>

</body>
</html>