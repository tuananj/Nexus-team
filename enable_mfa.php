<?php
session_start();
require_once "vendor/autoload.php";
require_once "config.php";

use PragmaRX\Google2FA\Google2FA;

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user'];
$google2fa = new Google2FA();
$error = "";

// Nếu đã bật rồi thì không cho bật lại
$stmt = $conn->prepare("SELECT secret FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!empty($data['secret'])) {
    header("Location: dashboard.php");
    exit();
}

// Tạo secret tạm
if (!isset($_SESSION['temp_secret'])) {
    $_SESSION['temp_secret'] = $google2fa->generateSecretKey();
}

$secret = $_SESSION['temp_secret'];

$qr = $google2fa->getQRCodeUrl("Nexus", $email, $secret);
$qrImage = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qr);

// Xác nhận OTP
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = $_POST['code'];

    if ($google2fa->verifyKey($secret, $code)) {
        $update = $conn->prepare("UPDATE users SET secret = ? WHERE email = ?");
        $update->bind_param("ss", $secret, $email);
        $update->execute();

        $_SESSION['secret'] = $secret;
        unset($_SESSION['temp_secret']);

        header("Location: dashboard.php?mfa=on");
        exit();
    } else {
        $error = "OTP không đúng!";
    }
}
?>

<h2>Bật MFA</h2>

<?php if ($error) echo "<p style='color:red'>$error</p>"; ?>

<img src="<?php echo $qrImage; ?>"><br><br>

<form method="POST">
    <input type="text" name="code" placeholder="Nhập OTP" required>
    <button>Xác nhận</button>
</form>