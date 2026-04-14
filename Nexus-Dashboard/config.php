<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$conn = new mysqli("localhost","root","","mfa_system");

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}
// Cấu hình Mail cho Dashboard
if (!defined('MAIL_USERNAME')) {
    define('MAIL_USERNAME', 'nexussecurity01@gmail.com'); 
}
if (!defined('MAIL_PASSWORD')) {
    define('MAIL_PASSWORD', 'rznobfyivlsvwqjs'); 
}
?>