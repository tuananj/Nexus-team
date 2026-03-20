<?php
$host = "localhost";
$user = "root";
$pass = ""; // Mặc định của XAMPP mật khẩu MySQL để trống

// LƯU Ý: Tên database của Huy có dấu cách nên phải viết chính xác như dưới đây
$dbname = "nexus id core";

// Thực hiện kết nối
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Kiểm tra kết nối
if (!$conn) {
    die("Kết nối không thành công: " . mysqli_connect_error());
}

// Thiết lập font chữ tiếng Việt để không bị lỗi hiển thị
mysqli_set_charset($conn, "utf8");

// Nếu Huy muốn kiểm tra thử thì bỏ dấu // ở dòng dưới ra
// echo "✅ Kết nối thành công tới database 'nexus id core'!"; 
?>