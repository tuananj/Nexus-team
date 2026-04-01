<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require "config.php";
require "device.php";
require "get_ip.php";
require "send_mail.php";

$error = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) {
        die("Lỗi prepare: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // 🔒 Check lock
        if (isset($user['is_locked']) && $user['is_locked'] == 1) {
            $error = "Tài khoản đã bị khóa!";
        } else {

            // 🔑 Verify password
            if (password_verify($password, $user['password'])) {

                // ✅ SESSION
                $_SESSION['user'] = $user['email'];
                $_SESSION['secret'] = $user['secret'];
                $_SESSION['role'] = $user['role'] ?? 'user';

                // 🌍 IP + DEVICE
                $ip = getRealIP();
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? "";
                $device = getDevice($userAgent);

                // 📱 Check device
                $checkDevice = $conn->prepare("
                    SELECT COUNT(*) AS total
                    FROM login_history
                    WHERE email = ? AND device = ?
                ");
                $checkDevice->bind_param("ss", $email, $device);
                $checkDevice->execute();
                $deviceResult = $checkDevice->get_result()->fetch_assoc();
                $deviceExists = $deviceResult['total'] > 0;

                // 🌐 Check IP + device
                $checkPair = $conn->prepare("
                    SELECT COUNT(*) AS total
                    FROM login_history
                    WHERE email = ? AND device = ? AND ip_address = ?
                ");
                $checkPair->bind_param("sss", $email, $device, $ip);
                $checkPair->execute();
                $pairResult = $checkPair->get_result()->fetch_assoc();
                $pairExists = $pairResult['total'] > 0;

                // 📊 Status
                if (!$deviceExists) {
                    $status = "Thiết bị lạ";
                } elseif (!$pairExists) {
                    $status = "IP mới";
                } else {
                    $status = "Thành công";
                }

                // 📝 Save history
                $insert = $conn->prepare("
                    INSERT INTO login_history (email, ip_address, device, status)
                    VALUES (?, ?, ?, ?)
                ");
                $insert->bind_param("ssss", $email, $ip, $device, $status);
                $insert->execute();

                // 📧 Send mail
                date_default_timezone_set("Asia/Ho_Chi_Minh");
                $time = date("H:i:s d-m-Y");
                sendLoginMail($email, $ip, $device, $time, $status);

                // 👉 Trigger loading
                $success = true;

            } else {
                $error = "Sai mật khẩu!";
            }
        }
    } else {
        $error = "Email không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Nexus Login</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

<style>
body {
    margin: 0;
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #090B10, #141A24);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
}

/* LOGIN BOX */
.login-box {
    background: rgba(20, 26, 36, 0.95);
    padding: 35px;
    width: 380px;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.05);
    box-shadow: 0 10px 30px rgba(0,0,0,0.6);
    text-align: center;
}

.login-box h2 {
    margin-bottom: 20px;
    font-weight: 600;
}

input {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border-radius: 10px;
    border: none;
    background: #090B10;
    color: white;
}

button {
    width: 100%;
    padding: 12px;
    background: #3b82f6;
    border: none;
    border-radius: 10px;
    color: white;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    background: #2563eb;
}

.error {
    background: rgba(239, 68, 68, 0.2);
    color: #fca5a5;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
}

.bottom-link {
    margin-top: 15px;
    font-size: 14px;
}

.bottom-link a {
    color: #3b82f6;
}

/* LOADING */
#loadingScreen {
    display: none;
    text-align: center;
}

.spinner {
    width: 60px;
    height: 60px;
    border: 6px solid #1f2937;
    border-top: 6px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
</head>

<body>

<!-- LOGIN -->
<div class="login-box" id="loginBox">
    <h2>Nexus Login</h2>

    <?php if ($error != ""): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <button type="submit">Đăng nhập</button>
    </form>

    <div class="bottom-link">
        Chưa có tài khoản? <a href="register.php">Đăng ký</a>
    </div>
</div>

<!-- LOADING -->
<div id="loadingScreen">
    <div class="spinner"></div>
    <div style="margin-top:10px;color:#aaa;">
        Loading...
    </div>
</div>

<script>
<?php if ($success): ?>
    document.getElementById("loginBox").style.display = "none";
    document.getElementById("loadingScreen").style.display = "block";

    setTimeout(() => {
        window.location.href = "setup_qr.php";
    }, 3000);
<?php endif; ?>
</script>

</body>
</html>