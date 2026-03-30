<?php
require_once 'vendor/autoload.php';

ini_set('session.cookie_path', '/');
ini_set('display_errors', 1);
error_reporting(E_ALL);

include "config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['callback'])) {
    $_SESSION['return_url'] = $_GET['callback'];
}

$error = "";

if (isset($_POST['btn_login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['auth_username'] = $user['name']; 
            $_SESSION['user_id'] = $user['id'];

            if (empty($user['mfa_secret'])) {
                header("Location: setup_qr.php");
            } else {
                header("Location: verify_qr.php");
            }
            exit();
        } else {
            $error = "Mật khẩu không đúng!";
        }
    } else {
        $error = "Không tìm thấy tài khoản với Email này!";
    }
}

$client = new Google_Client(); 
$client->setClientId('892618548419-eskp0p76hc1qe7cqadl073ef2h8h7i45.apps.googleusercontent.com');
$client->setClientSecret('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'); 
$client->setRedirectUri('http://localhost/Nexus-team/google-callback.php');
$client->addScope("email");
$client->addScope("profile");

$google_login_url = $client->createAuthUrl();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Login - Nexus MFA Gateway</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        :root {
            --matrix-color: #00ff22; /* Màu hồng Matrix */
            --cyan-glow: #00ffff;
        }

        body, html {
            margin: 0; padding: 0; height: 100%; 
            background: #000; overflow: hidden;
            font-family: 'Share Tech Mono', monospace;
        }

        /* Canvas nền Matrix */
        #matrixCanvas {
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 1;
        }

        /* Container đăng nhập */
        .login-wrapper {
            position: relative; z-index: 10;
            height: 100vh; display: flex;
            align-items: center; justify-content: center;
        }

        .login-card {
            width: 400px;
            background: rgba(0, 10, 20, 0.7);
            border: 2px solid var(--matrix-color);
            border-radius: 0px; /* Vuông vức kiểu cơ khí */
            padding: 30px;
            backdrop-filter: blur(10px);
            box-shadow: 0 0 20px rgba(255, 105, 180, 0.3);
            clip-path: polygon(10% 0, 100% 0, 100% 90%, 90% 100%, 0 100%, 0 10%);
        }

        h3 { color: var(--matrix-color); text-transform: uppercase; letter-spacing: 5px; text-align: center; }
        .form-label { color: var(--matrix-color); font-size: 0.8rem; }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 105, 180, 0.5);
            color: #fff; border-radius: 0;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--matrix-color);
            box-shadow: 0 0 10px var(--matrix-color);
            color: #fff;
        }

        .btn-primary {
            background: transparent; border: 1px solid var(--matrix-color);
            color: var(--matrix-color); border-radius: 0;
            transition: 0.3s; text-transform: uppercase; font-weight: bold;
        }
        .btn-primary:hover {
            background: var(--matrix-color); color: #000;
            box-shadow: 0 0 15px var(--matrix-color);
        }

        .btn-outline-danger {
            border: 1px solid #fff; color: #fff; border-radius: 0; margin-top: 15px;
        }
        .btn-outline-danger:hover { background: #fff; color: #000; }

        .footer-links a { color: var(--matrix-color); text-decoration: none; font-size: 0.8rem; }
        
        /* Hiệu ứng nhấp nháy cho khung hình */
        @keyframes scanline {
            0% { border-color: var(--matrix-color); }
            50% { border-color: #ffffff; }
            100% { border-color: var(--matrix-color); }
        }
        .login-card { animation: scanline 4s infinite; }
    </style>
</head>
<body>

    <canvas id="matrixCanvas"></canvas>

    <div class="login-wrapper">
        <div class="login-card shadow-lg">
            <h3>Nexus Gateway</h3>
            <p class="text-center small mb-4" style="color: var(--cyan-glow);">SYSTEM_LOGIN_REQUIRED</p>
            
            <?php if($error != ""): ?>
                <div class="alert alert-danger py-1 small text-center bg-dark text-danger border-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label text-uppercase">Credential_Email</label>
                    <input type="email" name="email" class="form-control" placeholder="admin@nexus.os" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-uppercase">Secure_Password</label>
                    <input type="password" name="password" class="form-control" placeholder="********" required>
                </div>
                <button type="submit" name="btn_login" class="btn btn-primary w-100 py-2">Initialize Login</button>
            </form>
            
            <div class="text-center mt-3 footer-links">
                <a href="register.php">>> New Operator? Create Account</a>
            </div>

            <hr style="border-color: var(--matrix-color); opacity: 0.3;">

            <a href="<?php echo $google_login_url; ?>" class="btn btn-outline-danger w-100">
                <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" width="18" class="me-2"> 
                Access with Google
            </a>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('matrixCanvas');
        const ctx = canvas.getContext('2d');

        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const katakana = 'アァカサタナハマヤャラワガザダバパイィキシチニヒミミリヰギジヂビピウゥクスツヌフムユュルグズブヅプエェケセテネヘメレヱゲゼデベペオォコソトノホモヨョロヲゴゾドボポヴッン';
        const latin = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const nums = '0123456789';
        const alphabet = katakana + latin + nums;

        const fontSize = 16;
        const columns = canvas.width / fontSize;
        const rainDrops = [];

        for (let x = 0; x < columns; x++) {
            rainDrops[x] = 1;
        }

        const draw = () => {
            ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.fillStyle = '#00fc15'; // Màu hồng Matrix
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