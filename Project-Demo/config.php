<?php
$host = "localhost";
$user = "root";
$pass = "";

$dbname = "mfa_system"; 

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập font chữ tiếng Việt cho Database
$conn->set_charset("utf8");

if (!defined('MAIL_USERNAME')) {
    define('MAIL_USERNAME', 'nexussecurity01@gmail.com');
}


if (!defined('MAIL_PASSWORD')) {
    define('MAIL_PASSWORD', 'rzno bfyi vlsv wqjs');
}
?>