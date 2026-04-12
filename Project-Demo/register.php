<?php
// 1. PHẢI mở kết nối Database thì mới dùng được biến $conn
include "config.php"; 

if(isset($_POST['register'])){

    // Lấy dữ liệu từ Form
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    // Dùng PASSWORD_BCRYPT để đồng bộ với hàm password_verify ở trang Login
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // 2. Kiểm tra xem Email đã tồn tại chưa để tránh lỗi trùng lặp
    $check = $conn->query("SELECT id FROM users WHERE email='$email'");
    if($check->num_rows > 0) {
        $error = "Email này đã được sử dụng!";
    } else {
        // 3. Thực hiện chèn dữ liệu
        $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";

        if($conn->query($sql)){
            // Đăng ký xong đẩy về index.php của Web A theo ý bạn
            header("Location: login.php?msg=success");
            exit();
        } else {
            $error = "Lỗi hệ thống: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Web A</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h4 class="mb-0">Đăng ký Web A</h4>
                </div>
                <div class="card-body p-4">
                    <?php if(isset($error)) echo "<div class='alert alert-danger small'>$error</div>"; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Tên người dùng</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button class="btn btn-primary w-100 mb-3" name="register">Đăng ký tài khoản</button>
                    </form>
                    <div class="text-center">
                        <a href="index.php" class="text-decoration-none small">Đã có tài khoản? Đăng nhập</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>