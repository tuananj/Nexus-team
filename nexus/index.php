<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nexus ID - Đăng ký</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; padding-top: 50px; }
        .form-container { border: 1px solid #ccc; padding: 20px; border-radius: 8px; width: 300px; }
        input { width: 100%; margin-bottom: 10px; padding: 8px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="form-container">
    <h3>Đăng nhập hệ thống</h3> <form action="login.php" method="POST">
        <input type="text" name="username" placeholder="Tên đăng nhập" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <button type="submit">Đăng nhập</button> </form>
    <p>Chưa có tài khoản? <a href="register.php">Đăng ký tại đây</a></p>
</div>
</body>
</html>