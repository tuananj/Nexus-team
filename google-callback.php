<?php
require_once 'vendor/autoload.php';
include "config.php";

session_set_cookie_params(0, '/');
// Khởi tạo session ngay đầu file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$client = new Google_Client();
$client->setClientId('892618548419-eskp0p76hc1qe7cqadl073ef2h8h7i45.apps.googleusercontent.com');
$client->setClientSecret('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');
// KIỂM TRA LẠI: Link này phải khớp với Google Cloud Console của Huy
$client->setRedirectUri('http://localhost/Nexus-team/google-callback.php');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    // Nếu lấy token thành công
    if (!isset($token['error'])) {
        $client->setAccessToken($token);
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        
        $email = $google_account_info->email;
        $name = $google_account_info->name;

        // Lưu thông tin vào Database (nếu chưa có)
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $insert = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
            $insert->bind_param("ss", $name, $email);
            $insert->execute();
            $_SESSION['auth_username'] = $name;
        } else {
            $user = $result->fetch_assoc();
            $_SESSION['auth_username'] = $user['name'];
        }

        // --- BƯỚC QUAN TRỌNG NHẤT Ở ĐÂY ---
        $_SESSION['mfa_verified'] = true; // Cấp thẻ bài xác thực
        $_SESSION['logged_in'] = true;    // Đánh dấu đã đăng nhập

        // Gửi mail (Huy nhớ điền App Password vào hàm dưới nhé)
        sendAlertEmail($email, $name);

        // Chuyển hướng thẳng sang Web A
        header("Location: http://localhost/PROJECT-DEMO/welcome.php");
        exit();
        
    } else {
        // Nếu lỗi token, nó sẽ quay về login kèm thông báo lỗi
        header("Location: login.php?error=" . $token['error']);
        exit();
    }
} else {
    // Nếu không có code từ Google, quay về login
    header("Location: login.php");
    exit();
}

// Hàm gửi mail giữ nguyên (Nhớ điền mã 16 ký tự vào Password)
function sendAlertEmail($userEmail, $userName) {
    // ... code PHPMailer cũ của Huy ...
}