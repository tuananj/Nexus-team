<?php
// 1. CẤU HÌNH VÀ KIỂM TRA QUYỀN
include "config.php"; 
// Huy nhớ kiểm tra đường dẫn file send_mail.php này cho đúng máy mình nhé
include 'C:/xampp/htdocs/Nexus-team/send_mail.php'; 

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['auth_username'])) { header("Location: login.php"); exit(); }

$current_user = $_SESSION['auth_username'];
$error = "";
$success = "";

// 1. Lấy thông tin hiện tại của User
$stmt = $conn->prepare("SELECT * FROM users WHERE name = ?");
$stmt->bind_param("s", $current_user);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// 2. Xử lý khi nhấn nút Cập nhật
if (isset($_POST['btn_update'])) {
    $new_name = mysqli_real_escape_string($conn, $_POST['name']);
    $new_email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_pass = $_POST['password'];

    $is_password_changed = !empty($new_pass);

    if ($is_password_changed) {
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssi", $new_name, $new_email, $hashed_pass, $user_data['id']);
    } else {
        $update_sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $new_name, $new_email, $user_data['id']);
    }

    if ($update_stmt->execute()) {
        // --- PHẦN MỚI: GỬI EMAIL CẢNH BÁO THAY ĐỔI THÔNG TIN ---
        $ip = $_SERVER['REMOTE_ADDR'];
        $time = date('Y-m-d H:i:s');
        $device = "Nexus Core Profile Manager";
        
        // Tạo nội dung thông báo tùy biến
        $status_msg = "Thay đổi thông tin: ";
        if ($is_password_changed) {
            $status_msg .= " Tên hoặc Mật khẩu đã được thay đổi từ thiết bị lạ nếu không phải bạn hãy truy cập Dashboard để kiểm tra!";
        }

        // Gọi hàm gửi mail của Huy (Gửi tới email mới cập nhật)
        // Dùng dấu @ để nếu mail lỗi cũng không làm hỏng giao diện
        @sendLoginMail($new_email, $ip, $device, $time, "CẢNH BÁO: " . $status_msg);

        // Ghi log vào Database cho Dashboard hiển thị
        $log_note = "User đã cập nhật profile. Mail cảnh báo đã gửi tới: " . $new_email;
        $log_stmt = $conn->prepare("INSERT INTO login_logs (username, ip_address, status, note) VALUES (?, ?, 'UPDATE', ?)");
        $log_stmt->bind_param("sss", $new_name, $ip, $log_note);
        $log_stmt->execute();

        // Cập nhật lại session và biến hiển thị
        $_SESSION['auth_username'] = $new_name;
        $success = "HỆ THỐNG ĐÃ CẬP NHẬT & GỬI MAIL XÁC THỰC!";
        $current_user = $new_name;
        
        // Làm mới dữ liệu hiển thị trên Form
        $user_data['name'] = $new_name;
        $user_data['email'] = $new_email;
} else {
        $error = "LỖI TRUY XUẤT DỮ LIỆU!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - Nexus Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --matrix-green: #00ff88; --glass-bg: rgba(0, 10, 20, 0.9); }
        body, html { margin: 0; padding: 0; height: 100%; background: #000; color: #fff; font-family: 'Segoe UI', sans-serif; overflow: hidden; }
        .dashboard-wrapper { display: flex; height: 100vh; width: 100vw; }

        .sidebar { flex: 1; background: rgba(0, 20, 30, 0.8); border-right: 1px solid var(--matrix-green); display: flex; flex-direction: column; padding: 20px 10px; backdrop-filter: blur(10px); }
        .sidebar-brand { color: var(--matrix-green); font-weight: bold; text-align: center; margin-bottom: 40px; letter-spacing: 2px; font-size: 14px; }
        .nav-link { color: #fff; text-decoration: none; display: flex; align-items: center; padding: 12px; border: 1px solid transparent; transition: 0.3s; font-size: 13px; margin-bottom: 10px; }
        .nav-link i { margin-right: 10px; width: 20px; }
        .nav-link:hover, .nav-link.active { border: 1px solid var(--matrix-green); background: rgba(0, 255, 136, 0.1); color: var(--matrix-green); }

        .main-content { flex: 9; padding: 50px; background: radial-gradient(circle at center, #001a1a 0%, #000 100%); display: flex; justify-content: center; }
        .edit-card { width: 500px; background: var(--glass-bg); border: 1px solid var(--matrix-green); padding: 40px; box-shadow: 0 0 20px rgba(0, 255, 136, 0.2); height: fit-content; }
        
        h2 { color: var(--matrix-green); letter-spacing: 3px; border-bottom: 1px solid var(--matrix-green); padding-bottom: 15px; margin-bottom: 30px; }
        label { display: block; margin-bottom: 8px; font-size: 12px; color: var(--matrix-green); opacity: 0.8; text-transform: uppercase; }
        input { display: block; width: 100%; margin-bottom: 25px; padding: 12px; background: rgba(0,0,0,0.5); border: 1px solid var(--matrix-green); color: #fff; box-sizing: border-box; outline: none; font-size: 16px; }
        input:focus { box-shadow: 0 0 10px var(--matrix-green); }

        .btn-update { width: 100%; padding: 15px; background: var(--matrix-green); color: #000; border: none; font-weight: bold; cursor: pointer; font-size: 16px; text-transform: uppercase; transition: 0.3s; }
        .btn-update:hover { background: #fff; box-shadow: 0 0 15px var(--matrix-green); }
        
        .alert { padding: 15px; margin-bottom: 20px; text-align: center; font-weight: bold; font-size: 13px; border: 1px solid; }
        .alert-success { border-color: var(--matrix-green); color: var(--matrix-green); background: rgba(0, 255, 136, 0.1); }
        .alert-danger { border-color: #ff4d4d; color: #ff4d4d; background: rgba(255, 0, 0, 0.1); }
    </style>
</head>
<body>
<div class="dashboard-wrapper">
    <div class="sidebar">
        <div class="sidebar-brand">NEXUS CORE</div>
        <a href="Dashboard.php" class="nav-link"><i class="fas fa-home"></i> TRANG CHỦ</a>
        <a href="edit_profile.php" class="nav-link active"><i class="fas fa-user-edit"></i> CHỈNH SỬA THÔNG TIN</a>
        <a href="logout.php" style="margin-top:auto; border: 1px solid #ff4d4d;" class="nav-link"><i class="fas fa-power-off"></i> DISCONNECT</a>
    </div>

    <div class="main-content">
        <div class="edit-card">
            <h2>USER_PROFILE_EDIT</h2>

            <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
            <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

            <form method="POST">
                <label>Operator Name:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>

                <label>Credential Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>

                <label>New Access Password (Để trống nếu không đổi):</label>
                <input type="password" name="password" placeholder="********">

                <button type="submit" name="btn_update" class="btn-update">UPDATE DATA</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>