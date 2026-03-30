<?php
session_start();
require_once '../config.php'; 

$error = "";

// 1. XỬ LÝ ĐĂNG NHẬP TRUYỀN THỐNG (EMAIL + PASSWORD)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputEmail = trim($_POST['username']); 
    $inputPass = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE Email = ?");
    $stmt->bind_param("s", $inputEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();

    if ($userData && password_verify($inputPass, $userData['password'])) {
        // Thiết lập session cần thiết cho file verify_qr.php của bạn
        $_SESSION['auth_username'] = $userData['Email'];
        
        // Sau khi nhập OTP đúng, sẽ dẫn vào welcome.php
        $_SESSION['return_url'] = "welcome.php"; 
        
        header("Location: verify_qr.php");
        exit();
    } else {
        $error = "Thông tin đăng nhập không chính xác!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus ID - Gateway</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --nexus-gold: #c5a059;
            --bg-dark: #0f172a;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            color: #fff;
        }

        .login-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 28px;
            border: 1px solid rgba(197, 160, 89, 0.2);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 380px;
            text-align: center;
        }

        .logo-area i { font-size: 50px; color: var(--nexus-gold); margin-bottom: 10px; }
        .logo-area h2 { letter-spacing: 2px; margin-bottom: 5px; }
        .logo-area p { color: #94a3b8; font-size: 13px; margin-bottom: 30px; }

        .error-box { background: rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 10px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; }

        .input-group { text-align: left; margin-bottom: 15px; }
        .input-group label { display: block; font-size: 11px; color: #94a3b8; margin-bottom: 5px; text-transform: uppercase; }
        .input-group input {
            width: 100%; padding: 12px; background: rgba(0,0,0,0.5); border: 1px solid #333;
            border-radius: 10px; color: #fff; box-sizing: border-box;
        }

        .btn-login {
            width: 100%; padding: 14px; background: var(--nexus-gold); border: none;
            border-radius: 10px; color: #0f172a; font-weight: 800; cursor: pointer; transition: 0.3s;
        }

        .divider { margin: 25px 0; display: flex; align-items: center; color: #444; font-size: 10px; font-weight: bold; }
        .divider::before, .divider::after { content: ""; flex: 1; height: 1px; background: #333; margin: 0 15px; }

        /* NÚT LOGIN WITH NEXUS - Cổng vào Mecha */
        .btn-nexus {
            width: 100%; padding: 14px; background: #000; color: var(--nexus-gold);
            border: 1px solid var(--nexus-gold); border-radius: 10px; font-weight: 800;
            text-decoration: none; display: flex; justify-content: center; align-items: center;
            gap: 12px; transition: 0.3s; letter-spacing: 1px;
        }

        .btn-nexus:hover { background: var(--nexus-gold); color: #000; box-shadow: 0 0 20px rgba(197, 160, 89, 0.4); }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo-area">
        <i class="fas fa-bolt"></i>
        <h2>NEXUS ID</h2>
        <p>Hệ thống cung cấp dịch vụ MFA</p>
    </div>

    <?php if ($error): ?>
        <div class="error-box"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group">
            <label>Tài khoản</label>
            <input type="email" name="username" placeholder="manh@gmail.com" required>
        </div>
        <div class="input-group">
            <label>Mật khẩu</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-login">ĐĂNG NHẬP</button>
    </form>

    <div class="divider">XÁC THỰC NHANH</div>

    <a href="nexus-bridge.php" class="btn-nexus">
        <i class="fas fa-qrcode"></i> LOGIN WITH NEXUS
    </a>
</div>

</body>
</html>