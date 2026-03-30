<?php
session_start();
require_once '../config.php';

// Nếu đã xác thực Nexus rồi thì cho vào luôn (giả lập SSO)
if (isset($_SESSION['nexus_authenticated'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

// Xử lý khi nhấn nút Xác thực
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nexus_id = strtoupper(trim($_POST['nexus_id']));

    // Kiểm tra mã sinh viên của bạn
    if ($nexus_id === "BH01942") {
        // Tìm tài khoản liên kết với mã này (Giả sử là tài khoản manh@gmail.com)
        $sql = "SELECT * FROM users WHERE Email = 'manh@gmail.com' LIMIT 1";
        $result = mysqli_query($conn, $sql);
        $user = mysqli_fetch_assoc($result);

        if ($user) {
            $_SESSION['temp_user'] = $user;
            $_SESSION['nexus_mode'] = true; // Đánh dấu là đăng nhập qua cổng Nexus
            
            // Chuyển sang bước OTP để đảm bảo bảo mật 2 lớp
            header("Location: verify-otp.php");
            exit();
        }
    } else {
        $error = "Mã định danh Nexus không tồn tại hoặc chưa được liên kết!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus Gateway - Secure Access</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --nexus-gold: #c5a059;
            --nexus-dark: #0f172a;
            --nexus-accent: #1e293b;
        }

        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: var(--nexus-dark);
            font-family: 'Segoe UI', Roboto, sans-serif;
            color: white;
            overflow: hidden;
        }

        /* Hiệu ứng nền mờ ảo */
        .bg-blur {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(197, 160, 89, 0.15) 0%, rgba(15, 23, 42, 0) 70%);
            border-radius: 50%;
            z-index: -1;
            filter: blur(50px);
            animation: float 6s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(20px, -20px); }
        }

        .auth-container {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(20px);
            padding: 50px 40px;
            border-radius: 30px;
            border: 1px solid rgba(197, 160, 89, 0.3);
            text-align: center;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }

        .icon-box {
            font-size: 60px;
            color: var(--nexus-gold);
            margin-bottom: 20px;
            text-shadow: 0 0 20px rgba(197, 160, 89, 0.5);
        }

        h2 {
            letter-spacing: 4px;
            margin-bottom: 10px;
            font-weight: 800;
        }

        p {
            color: #94a3b8;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .input-field {
            width: 100%;
            background: transparent;
            border: none;
            border-bottom: 2px solid rgba(197, 160, 89, 0.3);
            color: white;
            padding: 12px;
            font-size: 20px;
            text-align: center;
            outline: none;
            transition: 0.3s;
            margin-bottom: 25px;
            box-sizing: border-box;
            text-transform: uppercase;
        }

        .input-field:focus {
            border-bottom-color: var(--nexus-gold);
            letter-spacing: 3px;
        }

        .btn-auth {
            background: var(--nexus-gold);
            color: var(--nexus-dark);
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-auth:hover {
            box-shadow: 0 0 25px rgba(197, 160, 89, 0.5);
            transform: translateY(-2px);
        }

        .error-msg {
            color: #f87171;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .back-link {
            display: block;
            margin-top: 25px;
            color: #64748b;
            text-decoration: none;
            font-size: 13px;
            transition: 0.3s;
        }

        .back-link:hover { color: var(--nexus-gold); }
    </style>
</head>
<body>

    <div class="bg-blur"></div>

    <div class="auth-container">
        <div class="icon-box">
            <i class="fas fa-bolt"></i>
        </div>
        <h2>GATEWAY</h2>
        <p>Hệ thống xác thực nội bộ Nexus</p>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="nexus_id" class="input-field" placeholder="MÃ SINH VIÊN" required autocomplete="off">
            <button type="submit" class="btn-auth">Xác thực danh tính</button>
        </form>

        <a href="login.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Quay lại cổng công cộng
        </a>
    </div>

</body>
</html>