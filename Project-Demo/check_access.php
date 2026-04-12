<?php
include "config.php";
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Nếu người dùng cố vào trang này mà chưa xác thực MFA
if (!isset($_SESSION['mfa_verified']) || $_SESSION['mfa_verified'] !== true) {
    
    $user = isset($_SESSION['auth_username']) ? $_SESSION['auth_username'] : 'UNKNOWN';
    $ip = $_SERVER['REMOTE_ADDR'];
    $note = "Cố gắng truy cập Welcome Page trái phép (Chưa qua MFA)";

    // GHI LOG THẤT BẠI VÀO DATABASE
    $log = $conn->prepare("INSERT INTO login_logs (username, ip_address, status, note) VALUES (?, ?, 'FAILED', ?)");
    $log->bind_param("sss", $user, $ip, $note);
    $log->execute();

    // Đá về trang login
    header("Location: login.php?error=unauthorized");
    exit();
}
?>