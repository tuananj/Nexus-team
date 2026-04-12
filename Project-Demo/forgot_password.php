<?php
include "config.php";
include_once 'C:/xampp/htdocs/Nexus-team/send_mail.php'; // Dùng hàm gửi mail có sẵn của Huy

$msg = "";
if (isset($_POST['btn_forgot'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $sql = "SELECT id FROM users WHERE email = '$email'";
    $res = $conn->query($sql);

    if ($res->num_rows > 0) {
        $otp = rand(100000, 999999);
        session_start();
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_otp'] = $otp;

        // Gửi Mail (Huy dùng hàm sendAlertEmail hoặc tương tự đã viết nhé)
        $subject = "Mã xác nhận khôi phục mật khẩu Nexus";
        $content = "Mã OTP của bạn là: <b>$otp</b>. Vui lòng không chia sẻ cho ai.";
        
        // Giả sử Huy có hàm sendCustomMail trong send_mail.php
        if (sendCustomMail($email, $subject, $content)) {
            header("Location: verify_otp_reset.php");
            exit();
        }
    } else {
        $msg = "Email không tồn tại trên hệ thống!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quên mật khẩu - Nexus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="card p-4 shadow" style="width: 400px;">
        <h4 class="text-center mb-4">Khôi phục mật khẩu</h4>
        <?php if($msg) echo "<div class='alert alert-danger'>$msg</div>"; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Nhập Email tài khoản</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" name="btn_forgot" class="btn btn-primary w-100">Gửi mã xác nhận</button>
            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none">Quay lại đăng nhập</a>
            </div>
        </form>
    </div>
</body>
</html>