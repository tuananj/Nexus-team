<?php
ini_set('session.cookie_path', '/');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

include "config.php"; 
include_once 'C:/xampp/htdocs/Nexus-team/send_mail.php'; 
require_once 'C:/xampp/htdocs/Nexus-team/vendor/autoload.php';

// --- CẤU HÌNH GOOGLE LOGIN ---
$client = new Google_Client(); 
$client->setClientId('892618548419-eskp0p76hc1qe7cqadl073ef2h8h7i45.apps.googleusercontent.com');
$client->setClientSecret('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'); 
$client->setRedirectUri('http://localhost/Nexus-team/google-callback.php');
$client->addScope("email");
$client->addScope("profile");
$google_login_url = $client->createAuthUrl();

$error = "";

if (isset($_POST['btn_login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // 1. KIỂM TRA XEM CÓ ĐANG TRONG THỜI GIAN BỊ KHÓA KHÔNG
        $now = date('Y-m-d H:i:s');
        if ($user['lock_until'] && $user['lock_until'] > $now) {
            $remaining = strtotime($user['lock_until']) - strtotime($now);
            $minutes = floor($remaining / 60);
            $seconds = $remaining % 60;
            $error = "Tài khoản đang bị khóa! Thử lại sau $minutes phút $seconds giây.";
        } else {
            // Nếu đã hết thời gian khóa hoặc không bị khóa, tiến hành kiểm tra pass
            if (password_verify($password, $user['password'])) {
                // --- ĐĂNG NHẬP ĐÚNG ---
                $_SESSION['login_attempts'] = 0; 
                $conn->query("UPDATE users SET lock_until = NULL WHERE id = ".$user['id']); // Reset khóa
                
                $_SESSION['auth_username'] = $user['name']; 
                $_SESSION['user_id'] = $user['id'];
                header("Location: index.php");
                exit();
            } else {
                // --- SAI MẬT KHẨU ---
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                $attempts = $_SESSION['login_attempts'];
                $remain = 5 - $attempts;

                if ($attempts >= 5) {
                    // 2. THIẾT LẬP KHÓA 5 PHÚT TRONG DATABASE
                    $lock_until = date('Y-m-d H:i:s', strtotime('+5 minutes'));
                    $conn->query("UPDATE users SET lock_until = '$lock_until' WHERE email = '$email'");

                    // GỬI TELEGRAM KHI BỊ KHÓA
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $time = date('H:i:s d/m/Y');
                    $msg = "<b>🚨 CẢNH BÁO BẢO MẬT NEXUS</b>\n\n";
                    $msg .= "Tài khoản: <code>" . $email . "</code>\n";
                    $msg .= "Trạng thái: <b>BỊ KHÓA 5 PHÚT</b>\n";
                    $msg .= "Lý do: Nhập sai mật khẩu 5 lần\n";
                    $msg .= "📍 IP: <code>$ip</code>\n";
                    $msg .= "⏰ Lúc: $time\n";

                    @sendTelegramAlert($msg);

                    unset($_SESSION['login_attempts']); 
                    $error = "Bạn đã nhập sai 5 lần. Tài khoản bị khóa 5 phút!";
                } else { 
                    $error = "Mật khẩu không đúng! (Còn $remain lần thử)"; 
                }
            }
        }
    } else { $error = "Email không tồn tại!"; }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Web A</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
        .login-card { width: 450px; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .login-header { background-color: #007bff; color: white; padding: 20px; text-align: center; font-size: 24px; font-weight: bold; }
        .login-body { padding: 30px; }
        .form-label { font-weight: 500; color: #333; margin-bottom: 8px; }
        .form-control { background-color: #eef2f7; border: 1px solid #ced4da; padding: 12px; margin-bottom: 20px; }
        .btn-login { background-color: #0d6efd; border: none; padding: 12px; width: 100%; font-weight: bold; font-size: 16px; color: #fff; }
        .btn-login:hover { background-color: #0b5ed7; }
        .footer-link { text-align: center; font-size: 14px; margin-top: 15px; }
        .footer-link a { text-decoration: none; color: #0d6efd; }

        .btn-google {
            display: flex; align-items: center; justify-content: center;
            background-color: #ffffff; border: 1px solid #dadce0;
            border-radius: 4px; padding: 10px 16px; width: 100%;
            font-weight: 500; color: #3c4043; text-decoration: none;
            transition: background-color 0.2s, box-shadow 0.2s;
            margin-top: 10px; margin-bottom: 10px; box-sizing: border-box;
        }
        .btn-google:hover { background-color: #f8f9fa; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .btn-google img { width: 18px; height: 18px; margin-right: 12px; }
        
        .divider { display: flex; align-items: center; text-align: center; color: #888; margin: 15px 0; }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #ddd; }
        .divider:not(:empty)::before { margin-right: .5em; }
        .divider:not(:empty)::after { margin-left: .5em; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">Đăng nhập Web A</div>
    <div class="login-body">
        <?php if($error): ?>
            <div class="alert alert-danger p-2 small text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Nhập email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Mật khẩu</label>
                <input type="password" name="password" class="form-control" placeholder="••••" required>
            </div>
            <button type="submit" name="btn_login" class="btn btn-primary btn-login">Đăng nhập tài khoản</button>
        </form>

        <div class="divider">Hoặc</div>

        <a href="<?php echo $google_login_url; ?>" class="btn-google">
            <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google Logo">
            <span>Login with Google</span>
        </a>

        <div class="footer-link">
            <a href="forgot_password.php" class="text-danger d-block mb-2">Quên mật khẩu?</a>
            <a href="register.php">Chưa có tài khoản? Đăng ký ngay</a>
        </div>
    </div>
</div>

</body>
</html>