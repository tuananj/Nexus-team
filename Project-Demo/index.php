<?php
// 1. Dùng chung session với Web Login
ini_set('session.cookie_path', '/');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

include "config.php";

// 2. Nếu chưa đăng nhập ở login.php thì đá về trang login
if (!isset($_SESSION['auth_username'])) {
    header("Location: login.php");
    exit();
}

// 3. Kiểm tra xem tài khoản này đã có Secret Key chưa
$username = $_SESSION['auth_username'];
$stmt = $conn->prepare("SELECT mfa_secret FROM users WHERE name = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// 4. Quyết định cái đích đến cho nút bấm
if (empty($user_data['mfa_secret'])) {
    // Tài khoản mới tinh
    $target_link = "http://localhost/Nexus-team/setup_qr.php";
} else {
    // Tài khoản đã từng quét mã
    $target_link = "http://localhost/Nexus-team/verify_qr.php";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Khách hàng Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light text-center p-5">
    <h1>Chào mừng đến với Web Demo</h1>
    <p>Chào <b><?php echo htmlspecialchars($username); ?></b>! Để tiếp tục, bạn cần xác thực qua hệ thống bảo mật của chúng tôi.</p>
    
    <a href="<?php echo $target_link; ?>" class="btn btn-primary btn-lg">
        Login with Nexus (Tiến tới MFA)
    </a>
</body>
</html>