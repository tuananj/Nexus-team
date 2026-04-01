<?php
session_start();

require_once "vendor/autoload.php";
require_once "config.php";

use PragmaRX\Google2FA\Google2FA;

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$google2fa = new Google2FA();
$email = $_SESSION['user'];

// Nếu session chưa có secret thì lấy từ DB hoặc tạo mới
if (empty($_SESSION['secret'])) {
    $stmt = $conn->prepare("SELECT secret FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!empty($user['secret'])) {
        $_SESSION['secret'] = $user['secret'];
    } else {
        $_SESSION['secret'] = $google2fa->generateSecretKey();
    }
}

$secret = $_SESSION['secret'];
$companyName = "Nexus";
$qrText = $google2fa->getQRCodeUrl($companyName, $email, $secret);
$qrImage = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" . urlencode($qrText);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quét mã QR</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #090B10, #141A24);
            color: white;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .box {
            background: #141A24;
            padding: 30px;
            border-radius: 14px;
            text-align: center;
            width: 380px;
            border: 1px solid rgba(255,255,255,0.06);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        h2 {
            margin-bottom: 10px;
        }

        p {
            color: #cbd5e1;
            font-size: 14px;
            line-height: 1.6;
        }

        img {
            margin: 18px 0;
            background: white;
            padding: 10px;
            border-radius: 12px;
        }

        .countdown {
            margin-top: 10px;
            font-size: 15px;
            color: #60a5fa;
            font-weight: bold;
        }

        .manual-link {
            display: inline-block;
            margin-top: 14px;
            color: #3b82f6;
            text-decoration: none;
        }

        .manual-link:hover {
            text-decoration: underline;
        }
    </style>

    <script>
        let seconds = 10;

        function updateCountdown() {
            const el = document.getElementById("countdown");
            if (el) {
                el.innerText = seconds;
            }

            if (seconds <= 0) {
                window.location.href = "verify_qr.php";
            } else {
                seconds--;
                setTimeout(updateCountdown, 1000);
            }
        }

        window.onload = updateCountdown;
    </script>
</head>
<body>

<div class="box">
    <h2>Quét mã QR</h2>
    <p>Dùng Google Authenticator để quét mã bên dưới.</p>
    <img src="<?php echo $qrImage; ?>" alt="QR Code">
    <p class="countdown">
        Tự động chuyển sang trang nhập mã sau <span id="countdown">10</span> giây...
    </p>
    <a class="manual-link" href="verify_qr.php">Chuyển ngay</a>
</div>

</body>
</html>