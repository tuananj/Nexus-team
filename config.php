<?php
<<<<<<< HEAD

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$conn = new mysqli("localhost","root","","mfa_system");

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

=======
/**
 *  HIỂN THỊ LỖI (CHỈ DÙNG KHI DEV)
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * 🕒 TIMEZONE (GIỜ VIỆT NAM)
 */
date_default_timezone_set("Asia/Ho_Chi_Minh");

/**
 * 🗄 THÔNG TIN DATABASE
 */
$host = "localhost";
$user = "root";
$pass = "";
$db   = "mfa_system";

/**
 * 🔌 KẾT NỐI DATABASE
 */
$conn = new mysqli($host, $user, $pass, $db);

/**
 *  LỖI KẾT NỐI
 */
if ($conn->connect_error) {
    die("Kết nối Database thất bại: " . $conn->connect_error);
}

/**
 * 🔤 CHARSET (HỖ TRỢ TIẾNG VIỆT)
 */
$conn->set_charset("utf8mb4");

/**
 *  BÁO LỖI MYSQLI RÕ RÀNG (DEBUG)
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 *  KHÔNG ĐỂ session_start() Ở ĐÂY
 * (để trong login.php, dashboard.php, ...)
 */
>>>>>>> 0bb000fa (Update code moi nhat Nexus-team)
?>