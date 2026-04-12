<?php
include "config.php";
session_start();

// 1. Kiểm tra xem đã qua bước xác thực OTP chưa
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['otp_verified'])) {
    header("Location: forgot_password.php");
    exit();
}

$msg = "";
$success = false;

if (isset($_POST['btn_reset'])) {
    $pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($pass !== $confirm_pass) {
        $msg = "Mật khẩu xác nhận không khớp!";
    } else {
        // 2. Mã hóa mật khẩu mới
        $new_password_hashed = password_hash($pass, PASSWORD_DEFAULT);
        $email = $_SESSION['reset_email'];

        // 3. Cập nhật vào DB và MỞ KHÓA tài khoản luôn (set lock_until = NULL)
        $sql = "UPDATE users SET password = ?, lock_until = NULL WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $new_password_hashed, $email);

        if ($stmt->execute()) {
            $success = true;
            // Xóa session reset để bảo mật
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_otp']);
            unset($_SESSION['otp_verified']);
            
            echo "<script>
                alert('Mật khẩu đã được thay đổi và tài khoản đã được mở khóa!');
                window.location.href = 'login.php';
            </script>";
            exit();
        } else {
            $msg = "Lỗi hệ thống: Không thể cập nhật mật khẩu.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt lại mật khẩu - Nexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .reset-card { width: 400px; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="reset-card">
    <h4 class="text-center mb-4">Mật khẩu mới</h4>
    <p class="text-center small text-muted">Vui lòng thiết lập mật khẩu mới cho tài khoản của bạn.</p>

    <?php if($msg) echo "<div class='alert alert-danger p-2 small text-center'>$msg</div>"; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Mật khẩu mới</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required minlength="4">
        </div>
        <div class="mb-3">
            <label class="form-label">Xác nhận mật khẩu</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required minlength="4">
        </div>
        <button type="submit" name="btn_reset" class="btn btn-primary w-100 py-2">Cập nhật mật khẩu</button>
    </form>
</div>

</body>
</html>