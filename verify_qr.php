<?php
session_start();

require_once "vendor/autoload.php";
require_once "config.php";

use PragmaRX\Google2FA\Google2FA;

// Kiểm tra session login
if (!isset($_SESSION['user']) || !isset($_SESSION['secret'])) {
    header("Location: login.php");
    exit();
}

$google2fa = new Google2FA();
$error = "";

// Đếm số lần nhập sai OTP
if (!isset($_SESSION['otp_fail'])) {
    $_SESSION['otp_fail'] = 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST['code']);
    $secret = $_SESSION['secret'];

    // Xác thực OTP
    $valid = $google2fa->verifyKey($secret, $code);

    if ($valid) {
        // Reset số lần sai
        $_SESSION['otp_fail'] = 0;

        $_SESSION['verified'] = true;

        $email = $_SESSION['user'];

        // Lưu secret vào DB nếu chưa có (setup lần đầu)
        $stmt = $conn->prepare("
            UPDATE users
            SET secret = ?
            WHERE email = ?
        ");
        $stmt->bind_param("ss", $secret, $email);
        $stmt->execute();

        // Vào dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['otp_fail']++;

        if ($_SESSION['otp_fail'] >= 5) {
            // Quá 5 lần → logout
            session_unset();
            session_destroy();

            header("Location: login.php");
            exit();
        }

        $remain = 5 - $_SESSION['otp_fail'];
        $error = "Mã OTP không đúng! Còn $remain lần thử.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Xác thực OTP</title>

<style>
body {
    margin: 0;
    font-family: Arial;
    background: linear-gradient(135deg, #090B10, #141A24);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
}

.box {
    background: #141A24;
    padding: 30px;
    border-radius: 12px;
    width: 340px;
    text-align: center;
    border: 1px solid rgba(255,255,255,0.05);
}

h3 {
    margin-bottom: 10px;
}

.note {
    color: #aaa;
    font-size: 13px;
    margin-bottom: 15px;
}

input {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: none;
    margin-bottom: 12px;
    background: #090B10;
    color: white;
    text-align: center;
    font-size: 18px;
    letter-spacing: 4px;
}

button {
    width: 100%;
    padding: 12px;
    background: #22c55e;
    border: none;
    border-radius: 8px;
    color: white;
    cursor: pointer;
    font-weight: bold;
}

button:hover {
    background: #16a34a;
}

.error {
    background: rgba(239,68,68,0.15);
    color: #fca5a5;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
}
</style>
</head>

<body>

<div class="box">
    <h3>Xác thực Google Authenticator</h3>

    <p class="note">Nhập mã 6 số từ ứng dụng</p>

    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="code" maxlength="6" placeholder="" required>
        <button type="submit">Xác nhận</button>
    </form>
</div>

</body>
</html>