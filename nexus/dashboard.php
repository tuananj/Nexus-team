<?php
session_start();
require_once "../config.php"; 

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['temp_user']['email'] ?? 'User Nexus';

// --- PHẦN TRUY VẤN DỮ LIỆU (Giữ nguyên như cũ) ---
$login_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM login_history WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res1 = $stmt->get_result();
if ($row1 = $res1->fetch_assoc()) { $login_count = $row1['total']; }

$device_count = 0;
$stmt2 = $conn->prepare("SELECT COUNT(DISTINCT device) as total_device FROM login_history WHERE email = ?");
$stmt2->bind_param("s", $email);
$stmt2->execute();
$res2 = $stmt2->get_result();
if ($row2 = $res2->fetch_assoc()) { $device_count = $row2['total_device']; }

$stmt3 = $conn->prepare("SELECT ip_address, device, login_time FROM login_history WHERE email = ? ORDER BY login_time DESC LIMIT 5");
$stmt3->bind_param("s", $email);
$stmt3->execute();
$history = $stmt3->get_result();

function getBrowser($ua) {
    if (strpos($ua, 'Chrome') !== false) return 'Chrome';
    if (strpos($ua, 'Firefox') !== false) return 'Firefox';
    return 'Browser';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nexus Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --blue: #3b82f6; --green: #22c55e; --dark: #090B10; --card: #141A24; }
        body { margin: 0; font-family: 'Inter', sans-serif; background: var(--dark); color: white; }
        
        /* Giao diện Dashboard */
        .header { display: flex; justify-content: space-between; padding: 20px 40px; border-bottom: 1px solid #111; align-items: center; background: rgba(9,11,16,0.8); backdrop-filter: blur(10px); position: sticky; top: 0; z-index: 100; }
        .container { padding: 40px; max-width: 1100px; margin: auto; }
        .cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 30px; }
        .card { background: var(--card); padding: 30px; border-radius: 20px; border: 1px solid #222; transition: 0.3s; }
        .card:hover { border-color: var(--blue); transform: translateY(-5px); }

        /* 🔥 CSS CHO THÔNG BÁO GÓC TRÊN BÊN PHẢI */
        #nexus-toast {
            position: fixed;
            top: 25px;
            right: 25px;
            background: #1e293b;
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            border-left: 6px solid var(--green);
            box-shadow: 0 15px 40px rgba(0,0,0,0.6);
            display: flex;
            align-items: center;
            gap: 15px;
            z-index: 9999;
            transform: translateX(150%); /* Ẩn đi ban đầu */
            transition: transform 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        #nexus-toast.active { transform: translateX(0); }
        .toast-icon { color: var(--green); font-size: 24px; }
        .toast-content b { display: block; font-size: 14px; letter-spacing: 1px; }
        .toast-content span { font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>

<div id="nexus-toast">
    <i class="fas fa-check-circle toast-icon"></i>
    <div class="toast-content">
        <b>NEXUS SECURITY</b>
        <span><?php echo $_SESSION['toast_msg'] ?? ''; ?></span>
    </div>
</div>

<header class="header">
    <div style="font-weight:800; font-size:22px;"><i class="fas fa-bolt" style="color:#c5a059"></i> NEXUS ID</div>
    <div style="text-align:right; font-size:13px;">
        <b><?php echo $email; ?></b><br>
        <span style="color:var(--green)">● Google MFA Verified</span>
    </div>
</header>

<div class="container">
    <p style="color:gray; font-size:12px; letter-spacing:2px">QUẢN TRỊ BẢO MẬT</p>
    <h1 style="margin-top:5px">Tổng quan hệ thống</h1>

    <div class="cards">
        <div class="card"><small style="color:gray">TRUY CẬP</small><h1 style="color:var(--blue)"><?php echo $login_count; ?></h1></div>
        <div class="card"><small style="color:gray">THIẾT BỊ</small><h1 style="color:var(--green)"><?php echo $device_count; ?></h1></div>
        <div class="card"><small style="color:gray">BẢO MẬT</small><h1 style="color:#f59e0b">ACTIVE</h1></div>
    </div>

    <div style="margin-top:50px; background:var(--card); border-radius:20px; padding:25px; border:1px solid #222;">
        <p style="font-size:12px; color:gray; margin-bottom:20px">NHẬT KÝ ĐĂNG NHẬP GẦN ĐÂY</p>
        <?php while($row = $history->fetch_assoc()): ?>
        <div style="display:flex; justify-content:space-between; padding:15px 0; border-bottom:1px solid #222;">
            <div>
                <b><?php echo $row['ip_address']; ?></b><br>
                <small style="color:gray"><?php echo getBrowser($row['device']); ?></small>
            </div>
            <div style="text-align:right">
                <span style="font-size:12px"><?php echo date("H:i d/m/Y", strtotime($row['login_time'])); ?></span><br>
                <small style="color:var(--green)">Thành công</small>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <center><a href="logout.php" style="display:inline-block; margin-top:40px; color:#ef4444; text-decoration:none; font-weight:bold; border:1px solid #ef4444; padding:10px 30px; border-radius:10px;">ĐĂNG XUẤT</a></center>
</div>

<?php if (isset($_SESSION['toast_msg'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toast = document.getElementById('nexus-toast');
        
        // Trượt vào sau 0.5s
        setTimeout(() => {
            toast.classList.add('active');
        }, 500);

        // Tự động trượt ra sau 4.5s
        setTimeout(() => {
            toast.classList.remove('active');
        }, 5000);
    });
</script>
<?php unset($_SESSION['toast_msg']); // Xóa để không hiện lại khi F5 ?>
<?php endif; ?>

</body>
</html>