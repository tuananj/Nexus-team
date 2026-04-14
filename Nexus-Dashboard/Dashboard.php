<?php
include "config.php"; 
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['auth_username'])) { header("Location: login.php"); exit(); }

$current_user = $_SESSION['auth_username'];

// Lấy danh sách nhật ký
$query = "SELECT login_time, ip_address, status, note FROM login_logs WHERE username = ? ORDER BY login_time DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $current_user);
$stmt->execute();
$logs = $stmt->get_result();

// Thống kê
$success_count = $conn->query("SELECT COUNT(*) as total FROM login_logs WHERE username = '$current_user' AND status = 'SUCCESS'")->fetch_assoc()['total'];
$failed_count = $conn->query("SELECT COUNT(*) as total FROM login_logs WHERE username = '$current_user' AND status = 'FAILED'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nexus Dashboard - Piltover OS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --matrix-green: #00ff88; --glass-bg: rgba(0, 10, 20, 0.9); }
        
        body, html { 
            margin: 0; padding: 0; height: 100%; 
            background: #000; color: #fff; font-family: 'Segoe UI', sans-serif;
            overflow: hidden; /* Không cho cuộn cả trang, chỉ cuộn nội dung */
        }

        .dashboard-wrapper {
            display: flex; /* Chia màn hình làm 2 phần */
            height: 100vh;
            width: 100vw;
        }

        /* --- PHẦN 1: SIDEBAR (TỶ LỆ 1) --- */
        .sidebar {
            flex: 1; /* Chiếm 1 phần */
            background: rgba(0, 20, 30, 0.8);
            border-right: 1px solid var(--matrix-green);
            display: flex;
            flex-direction: column;
            padding: 20px 10px;
            backdrop-filter: blur(10px);
        }

        .sidebar-brand {
            color: var(--matrix-green);
            font-weight: bold;
            text-align: center;
            margin-bottom: 40px;
            letter-spacing: 2px;
            font-size: 14px;
        }

        .nav-menu { list-style: none; padding: 0; margin: 0; }
        .nav-item { margin-bottom: 15px; }
        .nav-link {
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px;
            border: 1px solid transparent;
            transition: 0.3s;
            font-size: 13px;
        }
        .nav-link i { margin-right: 10px; width: 20px; text-align: center; }
        .nav-link:hover, .nav-link.active {
            border: 1px solid var(--matrix-green);
            background: rgba(0, 255, 136, 0.1);
            color: var(--matrix-green);
            box-shadow: 0 0 10px rgba(0, 255, 136, 0.2);
        }

        /* --- PHẦN 2: MAIN CONTENT (TỶ LỆ 9) --- */
        .main-content {
            flex: 9; /* Chiếm 9 phần */
padding: 30px;
            overflow-y: auto; /* Cuộn nội dung bên trong */
            background: radial-gradient(circle at center, #001a1a 0%, #000 100%);
        }

        .header-title {
            border-bottom: 2px solid var(--matrix-green);
            padding-bottom: 15px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* CARD THỐNG KÊ */
        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .stat-card { 
            border: 1px solid var(--matrix-green); 
            padding: 20px; text-align: center; 
            background: rgba(0, 255, 136, 0.05);
            backdrop-filter: blur(5px);
        }
        .stat-card h2 { font-size: 50px; margin: 10px 0; color: var(--matrix-green); text-shadow: 0 0 15px var(--matrix-green); }
        .stat-card.fail { border-color: #ff4d4d; color: #ff4d4d; }
        .stat-card.fail h2 { color: #ff4d4d; text-shadow: 0 0 15px #ff4d4d; }

        /* BẢNG DỮ LIỆU */
        table { width: 100%; border-collapse: collapse; background: var(--glass-bg); }
        th { color: var(--matrix-green); border-bottom: 2px solid var(--matrix-green); padding: 15px; text-align: left; text-transform: uppercase; font-size: 12px; }
        td { padding: 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); font-size: 14px; }
        tr:hover { background: rgba(255, 255, 255, 0.02); }
        .status-success { color: var(--matrix-green); font-weight: bold; }
        .status-failed { color: #ff4d4d; font-weight: bold; }

        .btn-logout {
            margin-top: auto;
            text-align: center;
            color: #ff4d4d;
            text-decoration: none;
            font-size: 12px;
            padding: 10px;
            border: 1px solid #ff4d4d;
        }
        .btn-logout:hover { background: #ff4d4d; color: #fff; }
    </style>
</head>
<body>

<div class="dashboard-wrapper">
    <div class="sidebar">
        <div class="sidebar-brand">NEXUS CORE</div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="Dashboard.php" class="nav-link active">
                    <i class="fas fa-home"></i> TRANG CHỦ
                </a>
            </li>
            <li class="nav-item">
                <a href="edit_profile.php" class="nav-link">
                    <i class="fas fa-user-edit"></i> CHỈNH SỬA THÔNG TIN
                </a>
            </li>
        </ul>

        <a href="login.php" class="btn-logout">
            <i class="fas fa-power-off"></i> DISCONNECT
        </a>
    </div>

    <div class="main-content">
        <div class="header-title">
            <h2 style="margin:0; letter-spacing: 2px;">MONITORING DASHBOARD</h2>
            <span style="color: var(--matrix-green);">OPERATOR: <?php echo strtoupper($current_user); ?></span>
        </div>
<div class="stats-grid">
            <div class="stat-card">
                <small>ĐĂNG NHẬP THÀNH CÔNG</small>
                <h2><?php echo $success_count; ?></h2>
            </div>
            <div class="stat-card fail">
                <small>TRUY CẬP THẤT BẠI / CẢNH BÁO</small>
                <h2><?php echo $failed_count; ?></h2>
            </div>
        </div>

        <h4 style="color: var(--matrix-green); margin-bottom: 20px;">CHI TIẾT NHẬT KÝ HÀNG TRÌNH</h4>
        <table>
            <thead>
                <tr>
                    <th>THỜI GIAN</th>
                    <th>ĐỊA CHỈ IP</th>
                    <th>TRẠNG THÁI</th>
                    <th>GHI CHÚ HỆ THỐNG</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $logs->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['login_time']; ?></td>
                    <td><?php echo $row['ip_address']; ?></td>
                    <td>
                        <span class="<?php echo ($row['status'] == 'SUCCESS') ? 'status-success' : 'status-failed'; ?>">
                            <?php echo $row['status']; ?>
                        </span>
                    </td>
                    <td style="opacity: 0.6; font-style: italic; font-size: 12px;">
                        <?php echo htmlspecialchars($row['note']); ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>