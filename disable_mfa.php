<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user']) || !isset($_SESSION['verified']) || $_SESSION['verified'] !== true) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("UPDATE users SET secret = NULL WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $_SESSION['secret'] = null;
    unset($_SESSION['temp_secret']);

    header("Location: dashboard.php?mfa=disabled#mfa-section");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tắt MFA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #090B10, #141A24);
            color: white;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .box {
            width: 420px;
            background: rgba(20, 26, 36, 0.96);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 12px 35px rgba(0,0,0,0.45);
        }

        .icon-wrap {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.15);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 26px;
            margin-bottom: 18px;
        }

        h2 {
            margin: 0 0 10px;
            font-size: 24px;
            font-weight: 700;
        }

        .desc {
            margin: 0;
            color: #cbd5e1;
            line-height: 1.7;
            font-size: 14px;
        }

        .info-box {
            margin-top: 22px;
            background: #0f172a;
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 16px;
        }

        .info-label {
            color: #94a3b8;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .info-value {
            font-size: 15px;
            font-weight: 600;
            word-break: break-word;
        }

        .warning {
            margin-top: 18px;
            background: rgba(245, 158, 11, 0.12);
            color: #fcd34d;
            border: 1px solid rgba(245, 158, 11, 0.2);
            border-radius: 12px;
            padding: 14px;
            font-size: 14px;
            line-height: 1.6;
        }

        .actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .btn {
            flex: 1;
            padding: 13px 14px;
            border-radius: 12px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
            transition: 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-cancel {
            background: #334155;
            color: white;
        }

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .footer-note {
            margin-top: 16px;
            color: #94a3b8;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="box">
    <div class="icon-wrap">!</div>

    <h2>Tắt MFA</h2>
    <p class="desc">
        Bạn có chắc muốn tắt xác thực hai lớp cho tài khoản này không?
        Sau khi tắt, hệ thống sẽ không yêu cầu mã OTP khi đăng nhập.
    </p>

    <div class="info-box">
        <div class="info-label">Tài khoản</div>
        <div class="info-value"><?php echo htmlspecialchars($email); ?></div>
    </div>

    <div class="warning">
        Cảnh báo: Tắt MFA sẽ làm giảm mức độ bảo mật của tài khoản.
    </div>

    <form method="POST">
        <div class="actions">
            <a class="btn btn-cancel" href="dashboard.php#mfa-section">Hủy</a>
            <button class="btn btn-danger" type="submit">Xác nhận tắt MFA</button>
        </div>
    </form>

    <div class="footer-note">
        Nexus Security Center
    </div>
</div>

</body>
</html>