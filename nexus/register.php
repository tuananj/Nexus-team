<?php
include 'db.php'; // File này phải chứa $dbname = "nexus id core";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // 1. Kiểm tra xem tên đăng nhập đã tồn tại chưa
    $checkUser = mysqli_query($conn, "SELECT * FROM users WHERE username = '$user'");
    if (mysqli_num_rows($checkUser) > 0) {
        die("Huy ơi, tên đăng nhập này có người dùng rồi, chọn tên khác nhé!");
    }

    // 2. Mã hóa mật khẩu bảo mật cao (Băm mật khẩu)
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

    // 3. Chèn dữ liệu vào bảng users
    // Mặc định mfa_enabled là 0 (chưa bật)
    $sql = "INSERT INTO users (username, password, mfa_enabled) VALUES ('$user', '$hashed_password', 0)";
    
    if (mysqli_query($conn, $sql)) {
        echo "<h2>Chúc mừng Huy, tài khoản đã được tạo thành công!</h2>";
        echo "<p>Bây giờ bạn có thể quay lại <a href='index.php'>Đăng nhập</a> để thiết lập MFA.</p>";
    } else {
        echo "Lỗi hệ thống: " . mysqli_error($conn);
    }
}
?>
