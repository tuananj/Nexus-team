<?php
include "config.php"; 

// Kiểm tra session để tránh lỗi Notice
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = "";

if (isset($_POST['btn_login'])) {
    // 1. Lấy dữ liệu và chống SQL Injection
    $user_input = mysqli_real_escape_string($conn, $_POST['username']); 
    $pass_input = $_POST['password']; // Không escape mật khẩu vì sẽ làm sai chuỗi mã hóa

    // 2. Truy vấn tìm tài khoản theo name hoặc email
    $sql = "SELECT * FROM users WHERE name = '$user_input' OR email = '$user_input'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // 3. SỬA LỖI CÚ PHÁP TẠI ĐÂY: Sử dụng password_verify chuẩn cho mật khẩu mã hóa
        if (password_verify($pass_input, $row['password'])) {
            
            // Đăng nhập thành công -> Lưu session
            $_SESSION['auth_username'] = $row['name'];
            $_SESSION['user_id'] = $row['id'];
            
            // 4. GHI LOG THÀNH CÔNG: Để Dashboard đếm được số lần vào
            $ip = $_SERVER['REMOTE_ADDR'];
            $conn->query("INSERT INTO login_logs (username, ip_address, status, note) 
                          VALUES ('".$row['name']."', '$ip', 'SUCCESS', 'Đăng nhập Dashboard thành công')");

            header("Location: Dashboard.php");
            exit();
        } else {
            // GHI LOG THẤT BẠI: Nếu nhập sai mật khẩu
            $ip = $_SERVER['REMOTE_ADDR'];
            $conn->query("INSERT INTO login_logs (username, ip_address, status, note) 
                          VALUES ('$user_input', '$ip', 'FAILED', 'Nhập sai mật khẩu')");
            
            $error = "Mật khẩu không chính xác!";
        }
    } else {
        $error = "Tài khoản không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Login - Nexus System</title>
    <style>
        body { background: #000; color: #00ff88; font-family: 'Consolas', monospace; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { border: 2px solid #00ff88; padding: 40px; background: #050505; width: 380px; box-shadow: 0 0 20px rgba(0, 255, 136, 0.3); border-radius: 5px; }
        h2 { text-align: center; border-bottom: 1px solid #00ff88; padding-bottom: 15px; letter-spacing: 3px; }
        label { display: block; margin-bottom: 8px; font-size: 13px; color: #00ff88; opacity: 0.8; }
        input { display: block; width: 100%; margin-bottom: 25px; padding: 12px; background: #000; border: 1px solid #00ff88; color: #fff; box-sizing: border-box; outline: none; font-size: 16px; }
        input:focus { box-shadow: 0 0 10px #00ff88; }
button { width: 100%; padding: 15px; background: #00ff88; color: #000; border: none; font-weight: bold; cursor: pointer; font-size: 16px; text-transform: uppercase; transition: 0.3s; }
        button:hover { background: #fff; box-shadow: 0 0 15px #00ff88; }
        .err { color: #ff4444; text-align: center; margin-bottom: 20px; font-weight: bold; padding: 10px; border: 1px solid #ff4444; background: rgba(255, 0, 0, 0.1); }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>NEXUS LOGIN</h2>
        <?php if($error != "") echo "<div class='err'>$error</div>"; ?>
        <form method="POST">
            <label>IDENTIFIER (NAME/EMAIL):</label>
            <input type="text" name="username" placeholder="Nhập tên hoặc email..." required>
            
            <label>ACCESS PASSWORD:</label>
            <input type="password" name="password" placeholder="********" required>
            
            <button type="submit" name="btn_login">AUTHENTICATE >></button>
        </form>
    </div>
</body>
</html>