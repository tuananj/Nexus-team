<?php
date_default_timezone_set("Asia/Ho_Chi_Minh");

$conn = new mysqli("localhost", "root", "", "mfa_system");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!defined('MAIL_USERNAME')) {
    define('MAIL_USERNAME', 'nguyenanhtuan261204@gmail.com');
}

if (!defined('MAIL_PASSWORD')) {
    define('MAIL_PASSWORD', 'runh lqqy oyto nwpx');
}
?>