<?php
session_start();
include 'db.php'; // Đảm bảo db.php đã sửa tên db thành "nexus id core"

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // 1. Truy vấn thông tin người dùng từ bảng users
    $sql = "SELECT * FROM users WHERE username = '$user'";
    $result = mysqli_query($conn, $sql);
    $userData = mysqli_fetch_assoc($result);

    // 2. Kiểm tra tài khoản và mật khẩu
    // Dùng password_verify vì register.php đã mã hóa mật khẩu
    if ($userData && password_verify($pass, $userData['password'])) {
        
        // Lưu thông tin người dùng vào session tạm thời
        $_SESSION['temp_user'] = $userData;

        // 3. Kiểm tra xem người dùng đã bật MFA (xác thực 2 lớp) chưa
        if ($userData['mfa_enabled'] == 1) {
            // Nếu đã bật, chuyển sang trang nhập mã 6 số
            header("Location: verify-otp.php");
            exit();
        } else {
            // Nếu chưa bật, thông báo và cho phép vào trang thiết lập MFA
            echo "<h2>✅ Đăng nhập thành công!</h2>";
            echo "<p>Chào $user, bạn chưa bật bảo mật 2 lớp.</p>";
            echo "<a href='setup-mfa.php'>Nhấn vào đây để thiết lập mã QR ngay!</a>";
        }
    } else {
        // Nếu sai mật khẩu hoặc tên đăng nhập
        echo "<h2> Sai tài khoản hoặc mật khẩu!</h2>";
        echo "<a href='index.php'>Quay lại thử lại nhé</a>";
    }
}
?>