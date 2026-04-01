<?php
session_start();
require "config.php";

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $passwordRaw = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($passwordRaw) || empty($confirmPassword)) {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    } elseif ($passwordRaw !== $confirmPassword) {
        $error = "Mật khẩu xác nhận không khớp!";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "Email đã tồn tại!";
        } else {
            $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users(name, email, password, secret) VALUES (?, ?, ?, NULL)");
            $stmt->bind_param("sss", $name, $email, $password);

            if ($stmt->execute()) {
                $success = "Đăng ký thành công! Chuyển sang đăng nhập...";
                header("refresh:2;url=login.php");
            } else {
                $error = "Đăng ký thất bại!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nexus Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #090B10, #141A24);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
        }

        .register-box {
            background: rgba(20, 26, 36, 0.95);
            padding: 35px;
            width: 380px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.05);
            box-shadow: 0 10px 30px rgba(0,0,0,0.6);
            text-align: center;
        }

        .register-box h2 {
            margin-bottom: 20px;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 10px;
            border: none;
            background: #090B10;
            color: white;
            box-sizing: border-box;
            outline: none;
        }

        input::placeholder {
            color: #888;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #3b82f6;
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            margin-top: 8px;
        }

        button:hover {
            background: #2563eb;
        }

        .error {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .success {
            background: rgba(34, 197, 94, 0.2);
            color: #86efac;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .bottom-link {
            margin-top: 16px;
            font-size: 14px;
            color: #aaa;
        }

        .bottom-link a {
            color: #3b82f6;
            text-decoration: none;
        }

        .bottom-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="register-box">
    <h2>Nexus Register</h2>

    <?php if ($error != ""): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success != ""): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="name" placeholder="Họ và tên" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>
        <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>

        <button type="submit" name="register">Đăng ký</button>
    </form>

    <div class="bottom-link">
        Đã có tài khoản? <a href="login.php">Đăng nhập</a>
    </div>
</div>

</body>
</html>