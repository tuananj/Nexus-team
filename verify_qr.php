<?php
require "vendor/autoload.php";
include "config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use PragmaRX\Google2FA\Google2FA;
$google2fa = new Google2FA();

// 1. Kiểm tra xem đã đăng nhập bước 1 chưa
if (!isset($_SESSION['auth_username'])) {
    header("Location: login.php");
    exit();
}

$current_user = $_SESSION['auth_username'];
$error = "";

// 2. LẤY SECRET TỪ DATABASE
$stmt = $conn->prepare("SELECT mfa_secret FROM users WHERE name = ?");
$stmt->bind_param("s", $current_user);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

if (!$user_data || empty($user_data['mfa_secret'])) {
    header("Location: setup_qr.php");
    exit();
}

$secret_key = $user_data['mfa_secret'];

// 3. XỬ LÝ KHI NHẤP NÚT XÁC NHẬN
if (isset($_POST['btn_verify'])) {
    $otp_code = $_POST['otp_code'];
    $is_valid = $google2fa->verifyKey($secret_key, $otp_code, 2);

    if ($is_valid) {
        $_SESSION['mfa_verified'] = true;
        if (isset($_SESSION['return_url'])) {
            $target = $_SESSION['return_url'];
            unset($_SESSION['return_url']); 
            header("Location: " . $target . "?status=success&user=" . urlencode($current_user));
        } else {
            header("Location: http://localhost/PROJECT-DEMO/welcome.php");
        }
        exit();
    } else {
        $error = "Mã xác nhận không đúng hoặc đã hết hạn!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác minh 2 lớp - Nexus Mecha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        :root {
            --matrix-color: #15ff00; /* Màu hồng Matrix */
            --cyan-glow: #00ffff;
            --bg-dark: #000;
        }

        body, html {
            margin: 0; padding: 0; height: 100%; 
            background: #000; overflow: hidden;
            font-family: 'Share Tech Mono', monospace;
        }

        /* Canvas nền Matrix Hồng */
        #matrixCanvas {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 1;
        }

        .verify-wrapper {
            position: relative; z-index: 10;
            height: 100vh; display: flex;
            align-items: center; justify-content: center;
        }

        /* Khung Verify kiểu Mecha */
        .verify-card {
            width: 420px;
            background: rgba(0, 10, 25, 0.8);
            border: 2px solid var(--matrix-color);
            padding: 35px;
            backdrop-filter: blur(12px);
            box-shadow: 0 0 30px rgba(255, 105, 180, 0.2);
            clip-path: polygon(0 15%, 15% 0, 100% 0, 100% 85%, 85% 100%, 0 100%);
            animation: cardDeploy 0.8s ease-out;
        }

        h4 { 
            color: var(--matrix-color); 
            text-transform: uppercase; 
            letter-spacing: 3px; 
            margin-bottom: 20px;
        }

        .otp-input {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 2px solid rgba(255, 105, 180, 0.4) !important;
            color: #fff !important;
            letter-spacing: 15px;
            font-size: 2rem !important;
            border-radius: 0 !important;
            transition: 0.3s;
        }

        .otp-input:focus {
            background: rgba(255, 255, 255, 0.1) !important;
            border-color: var(--matrix-color) !important;
            box-shadow: 0 0 20px var(--matrix-color) !important;
            outline: none;
        }

        .btn-verify {
            background: transparent;
            border: 2px solid var(--matrix-color);
            color: var(--matrix-color);
            font-weight: bold;
            letter-spacing: 2px;
            padding: 12px;
            border-radius: 0;
            transition: 0.4s;
            text-transform: uppercase;
        }

        .btn-verify:hover {
            background: var(--matrix-color);
            color: #000;
            box-shadow: 0 0 25px var(--matrix-color);
        }

        .user-badge {
            background: rgba(0, 255, 255, 0.1);
            border: 1px solid var(--cyan-glow);
            color: var(--cyan-glow);
            padding: 5px 15px;
            font-size: 0.9rem;
            display: inline-block;
            margin-bottom: 20px;
        }

        @keyframes cardDeploy {
            0% { transform: scale(0.8) opacity: 0; }
            100% { transform: scale(1) opacity: 1; }
        }

        .alert-custom {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid #ff0000;
            color: #ff0000;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>

    <canvas id="matrixCanvas"></canvas>

    <div class="verify-wrapper">
        <div class="verify-card text-center">
            <div class="mb-3">
                <i class="fas fa-shield-alt fa-3x" style="color: var(--matrix-color); filter: drop-shadow(0 0 10px var(--matrix-color));"></i>
            </div>
            
            <h4>Nexus MFA</h4>
            
            <div class="user-badge">
                <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($current_user); ?>
            </div>
            
            <p class="text-white-50 small mb-4">Mã xác thực 6 số (TOTP) được yêu cầu để hoàn tất phiên làm việc.</p>
            
            <?php if ($error): ?>
                <div class="alert alert-custom p-2 mb-3"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-4">
                    <input type="text" name="otp_code" class="form-control otp-input text-center fw-bold" 
                           placeholder="000000" maxlength="6" pattern="\d{6}" required autofocus>
                </div>
                <button type="submit" name="btn_verify" class="btn btn-verify w-100">
                    Kích hoạt truy cập >>
                </button>
            </form>
            
            <div class="mt-4">
                <a href="login.php" style="color: var(--matrix-color); text-decoration: none; font-size: 0.8rem;">
                    <i class="fas fa-arrow-left me-2"></i>Sử dụng tài khoản khác
                </a>
            </div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('matrixCanvas');
        const ctx = canvas.getContext('2d');

        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZアァカサタナハマヤャ';
        const fontSize = 16;
        const columns = canvas.width / fontSize;
        const rainDrops = Array.from({ length: columns }).fill(1);

        const draw = () => {
            ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.fillStyle = '#00ff1a'; // Màu hồng Matrix
            ctx.font = fontSize + 'px monospace';

            for (let i = 0; i < rainDrops.length; i++) {
                const text = alphabet.charAt(Math.floor(Math.random() * alphabet.length));
                ctx.fillText(text, i * fontSize, rainDrops[i] * fontSize);

                if (rainDrops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                    rainDrops[i] = 0;
                }
                rainDrops[i]++;
            }
        };

        setInterval(draw, 30);
        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });
    </script>
</body>
</html>