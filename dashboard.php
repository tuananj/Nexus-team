dashboard
<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user']) || !isset($_SESSION['verified']) || $_SESSION['verified'] !== true) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user'];
$mfa = !empty($_SESSION['secret']);
$filter = $_GET['filter'] ?? 'all';

// Tổng số lần login
$stmt = $conn->prepare("
    SELECT COUNT(*) as total
    FROM login_history
    WHERE email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$login_count = $row['total'] ?? 0;

// Tổng số thiết bị
$stmt2 = $conn->prepare("
    SELECT COUNT(DISTINCT device) as total_device
    FROM login_history
    WHERE email = ?
");
$stmt2->bind_param("s", $email);
$stmt2->execute();
$result2 = $stmt2->get_result();
$row2 = $result2->fetch_assoc();
$device_count = $row2['total_device'] ?? 0;

// Lấy trạng thái tài khoản
$stmtUser = $conn->prepare("
    SELECT is_locked, locked_until, secret
    FROM users
    WHERE email = ?
    LIMIT 1
");
$stmtUser->bind_param("s", $email);
$stmtUser->execute();
$userInfo = $stmtUser->get_result()->fetch_assoc();

$isLocked = !empty($userInfo['is_locked']) && $userInfo['is_locked'] == 1;
$lockedUntil = $userInfo['locked_until'] ?? null;

// Đồng bộ lại session secret theo database
if (isset($userInfo['secret'])) {
    $_SESSION['secret'] = $userInfo['secret'];
    $mfa = !empty($userInfo['secret']);
}

// Last login
$stmtLast = $conn->prepare("
    SELECT ip_address, device, login_time, status
    FROM login_history
    WHERE email = ?
    ORDER BY login_time DESC
    LIMIT 1
");
$stmtLast->bind_param("s", $email);
$stmtLast->execute();
$lastLogin = $stmtLast->get_result()->fetch_assoc();

// Query lịch sử đăng nhập có filter
if ($filter === 'Thiết bị lạ' || $filter === 'IP mới' || $filter === 'Đăng nhập đáng ngờ') {
    $stmt3 = $conn->prepare("
        SELECT email, ip_address, device, login_time, status
        FROM login_history
        WHERE email = ? AND status = ?
        ORDER BY login_time DESC
        LIMIT 10
    ");
    $stmt3->bind_param("ss", $email, $filter);
} else {
    $stmt3 = $conn->prepare("
        SELECT email, ip_address, device, login_time, status
        FROM login_history
        WHERE email = ?
        ORDER BY login_time DESC
        LIMIT 10
    ");
    $stmt3->bind_param("s", $email);
}
$stmt3->execute();
$history = $stmt3->get_result();

// Dữ liệu thống kê theo trạng thái
$stmt4 = $conn->prepare("
    SELECT status, COUNT(*) as total
    FROM login_history
    WHERE email = ?
    GROUP BY status
");
$stmt4->bind_param("s", $email);
$stmt4->execute();
$statusResult = $stmt4->get_result();

$statusData = [
    "Thành công" => 0,
    "IP mới" => 0,
    "Thiết bị lạ" => 0,
    "Đăng nhập đáng ngờ" => 0
];

while ($s = $statusResult->fetch_assoc()) {
    if (isset($statusData[$s['status']])) {
        $statusData[$s['status']] = $s['total'];
    }
}

$maxValue = max($statusData);
if ($maxValue == 0) {
    $maxValue = 1;
}

$message = "";
if (isset($_GET['mfa'])) {
    if ($_GET['mfa'] === 'enabled') {
        $message = "Bật MFA thành công.";
    } elseif ($_GET['mfa'] === 'disabled') {
        $message = "Tắt MFA thành công.";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Nexus Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

<style>
* {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    margin: 0;
    font-family: 'Inter', sans-serif;
    background: #090B10;
    color: white;
}

.header {
    display: flex;
    justify-content: space-between;
    padding: 15px 40px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.logo {
    display: flex;
    gap: 10px;
    align-items: center;
}

.logo-circle {
    width: 30px;
    height: 30px;
    background: #3b82f6;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.nav {
    display: flex;
    gap: 30px;
}

.nav a {
    color: #aaa;
    text-decoration: none;
}

.nav a.active {
    color: #3b82f6;
}

.user {
    display: flex;
    gap: 10px;
    align-items: center;
}

.avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #3b82f6;
    display: flex;
    justify-content: center;
    align-items: center;
}

.container {
    padding: 30px 60px;
}

.title-small {
    color: gray;
    font-size: 12px;
}

.title-large {
    font-size: 28px;
    margin-bottom: 20px;
}

.cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.cards-2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.card {
    background: #141A24;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid rgba(255,255,255,0.05);
    transition: 0.3s;
}

.card:hover {
    transform: translateY(-3px);
}

.section {
    margin-top: 40px;
    scroll-margin-top: 80px;
}

.blue { color: #3b82f6; }
.green { color: #22c55e; }
.orange { color: #f59e0b; }
.red { color: #ef4444; }
.yellow { color: #facc15; }

.badge {
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 12px;
    display: inline-block;
}

.badge-green {
    background: rgba(34,197,94,0.2);
    color: #22c55e;
}

.badge-red {
    background: rgba(239,68,68,0.2);
    color: #ef4444;
}

.badge-orange {
    background: rgba(245,158,11,0.2);
    color: #f59e0b;
}

.badge-yellow {
    background: rgba(250,204,21,0.2);
    color: #facc15;
}

.list-item {
    display: flex;
    justify-content: space-between;
    padding: 15px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.list-item:last-child {
    border-bottom: none;
}

.activity {
    display: flex;
    justify-content: space-between;
    padding: 14px 12px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.activity:last-child {
    border-bottom: none;
}

.activity-left {
    display: flex;
    gap: 10px;
    align-items: center;
}

.dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #22c55e;
}

.logout-link {
    display: inline-block;
    margin-top: 25px;
    color: #ef4444;
    text-decoration: none;
}

.chart-row {
    margin-bottom: 15px;
}

.chart-head {
    display: flex;
    justify-content: space-between;
    margin-bottom: 6px;
}

.chart-bg {
    background: #0f172a;
    height: 10px;
    border-radius: 8px;
    overflow: hidden;
}

.chart-bar {
    height: 10px;
    background: #3b82f6;
}

.filter-box {
    display: flex;
    gap: 10px;
    margin: 15px 0 20px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 8px 14px;
    border-radius: 8px;
    background: #0f172a;
    color: #cbd5e1;
    text-decoration: none;
    border: 1px solid rgba(255,255,255,0.05);
    font-size: 14px;
}

.filter-btn.active {
    background: #3b82f6;
    color: white;
}

.small-muted {
    color: #94a3b8;
    font-size: 13px;
    line-height: 1.6;
}

.success-box {
    background: rgba(34,197,94,0.15);
    color: #86efac;
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 18px;
}

.mfa-actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.mfa-btn {
    padding: 8px 14px;
    border-radius: 8px;
    text-decoration: none;
    color: white;
    font-size: 13px;
    font-weight: 600;
}

.mfa-enable {
    background: #16a34a;
}

.mfa-disable {
    background: #dc2626;
}
</style>
</head>
<body>

<div class="header">
    <div class="logo">
        <div class="logo-circle">N</div>
        <b>Nexus</b>
    </div>

    <div class="nav">
        <a href="setup_qr.php">Authenticator</a>
        <a href="login.php">Login</a>
        <a href="#" class="active">Dashboard</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin.php">Admin</a>
        <?php endif; ?>
    </div>

    <div class="user">
        <div class="avatar">U</div>
        <div>
            <div><?php echo htmlspecialchars($email); ?></div>
            <small style="color:#22c55e;">
                <?php echo $mfa ? "Đã xác thực MFA" : "Chưa bật MFA"; ?>
            </small>
        </div>
    </div>
</div>

<div class="container">
    <div class="title-small">QUẢN LÝ TÀI KHOẢN</div>
    <div class="title-large">Dashboard Nexus</div>

    <?php if (!empty($message)): ?>
        <div class="success-box"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="cards">
        <div class="card">
            <div>Lần đăng nhập</div>
            <h1 class="blue"><?php echo $login_count; ?></h1>
        </div>

        <div class="card">
            <div>Thiết bị hoạt động</div>
            <h1 class="green"><?php echo $device_count; ?></h1>
        </div>

        <div class="card">
            <div>MFA</div>
            <h1 class="<?php echo $mfa ? 'green' : 'red'; ?>">
                <?php echo $mfa ? "ON" : "OFF"; ?>
            </h1>
        </div>

        <div class="card">
            <div>Account Locked</div>
            <h1 class="<?php echo $isLocked ? 'red' : 'green'; ?>">
                <?php echo $isLocked ? "YES" : "NO"; ?>
            </h1>
        </div>
    </div>

    <div class="section" id="mfa-section">
        <div class="title-small">BẢO MẬT MFA</div>

        <div class="card">
            <div class="list-item">
                <div>
                    Trạng thái MFA<br>
                    <small class="small-muted">
                        <?php echo $mfa ? "Tài khoản đang bật xác thực hai lớp." : "Tài khoản chưa bật MFA."; ?>
                    </small>
                </div>

                <?php if ($mfa): ?>
                    <span class="badge badge-green">ON</span>
                <?php else: ?>
                    <span class="badge badge-red">OFF</span>
                <?php endif; ?>
            </div>

            <div class="mfa-actions">
                <?php if (!$mfa): ?>
                    <a class="mfa-btn mfa-enable" href="enable_mfa.php">Bật MFA</a>
                <?php else: ?>
                    <a class="mfa-btn mfa-disable" href="disable_mfa.php">Tắt MFA</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="cards-2">
            <div class="card">
                <div class="title-small">LAST LOGIN</div>
                <?php if (!empty($lastLogin)): ?>
                    <div style="margin-top:12px;">
                        <div style="margin-bottom:10px;">
                            <b>Device:</b><br>
                            <span class="small-muted"><?php echo htmlspecialchars($lastLogin['device']); ?></span>
                        </div>

                        <div style="margin-bottom:10px;">
                            <b>IP:</b><br>
                            <span class="small-muted"><?php echo htmlspecialchars($lastLogin['ip_address']); ?></span>
                        </div>

                        <div style="margin-bottom:10px;">
                            <b>Time:</b><br>
                            <span class="small-muted"><?php echo date("H:i:s d/m/Y", strtotime($lastLogin['login_time'])); ?></span>
                        </div>

                        <div>
                            <b>Status:</b><br>
                            <?php
                                $lastBadge = "badge-green";
                                if ($lastLogin['status'] === 'Thiết bị lạ') {
                                    $lastBadge = "badge-red";
                                } elseif ($lastLogin['status'] === 'IP mới') {
                                    $lastBadge = "badge-orange";
                                } elseif ($lastLogin['status'] === 'Đăng nhập đáng ngờ') {
                                    $lastBadge = "badge-yellow";
                                }
                            ?>
                            <span class="badge <?php echo $lastBadge; ?>">
                                <?php echo htmlspecialchars($lastLogin['status']); ?>
                            </span>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="small-muted">Chưa có dữ liệu đăng nhập.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="title-small">TRẠNG THÁI BẢO MẬT</div>
                <div style="margin-top:12px;">
                    <div class="list-item">
                        <div>MFA</div>
                        <span class="badge <?php echo $mfa ? 'badge-green' : 'badge-red'; ?>">
                            <?php echo $mfa ? "ON" : "OFF"; ?>
                        </span>
                    </div>

                    <div class="list-item">
                        <div>Account Locked</div>
                        <span class="badge <?php echo $isLocked ? 'badge-red' : 'badge-green'; ?>">
                            <?php echo $isLocked ? "YES" : "NO"; ?>
                        </span>
                    </div>

                    <div class="list-item">
                        <div>Locked Until</div>
                        <span class="small-muted">
                            <?php
                                if (!empty($lockedUntil) && strtotime($lockedUntil) > time()) {
                                    echo date("H:i:s d/m/Y", strtotime($lockedUntil));
                                } else {
                                    echo "Không có";
                                }
                            ?>
                        </span>
                    </div>

                    <div class="list-item">
                        <div>Email</div>
                        <span class="small-muted"><?php echo htmlspecialchars($email); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="title-small">THỐNG KÊ ĐĂNG NHẬP</div>
        <div class="card">
            <?php foreach ($statusData as $label => $value): ?>
                <?php $width = ($value / $maxValue) * 100; ?>
                <div class="chart-row">
                    <div class="chart-head">
                        <span><?php echo htmlspecialchars($label); ?></span>
                        <span><?php echo $value; ?></span>
                    </div>
                    <div class="chart-bg">
                        <div class="chart-bar" style="width: <?php echo $width; ?>%;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="section" id="history-section">
        <div class="title-small">LỊCH SỬ ĐĂNG NHẬP</div>

        <div class="filter-box">
            <a class="filter-btn <?php echo ($filter === 'all') ? 'active' : ''; ?>" href="dashboard.php?filter=all#history-section">All</a>
            <a class="filter-btn <?php echo ($filter === 'Thiết bị lạ') ? 'active' : ''; ?>" href="dashboard.php?filter=<?php echo urlencode('Thiết bị lạ'); ?>#history-section">Thiết bị lạ</a>
            <a class="filter-btn <?php echo ($filter === 'IP mới') ? 'active' : ''; ?>" href="dashboard.php?filter=<?php echo urlencode('IP mới'); ?>#history-section">IP mới</a>
            <a class="filter-btn <?php echo ($filter === 'Đăng nhập đáng ngờ') ? 'active' : ''; ?>" href="dashboard.php?filter=<?php echo urlencode('Đăng nhập đáng ngờ'); ?>#history-section">Đăng nhập đáng ngờ</a>
        </div>

        <div class="card">
            <?php if ($history->num_rows > 0): ?>
                <?php while ($row = $history->fetch_assoc()): ?>
                    <?php
                        $badgeClass = "badge-green";
                        if ($row['status'] === 'Thiết bị lạ') {
                            $badgeClass = "badge-red";
                        } elseif ($row['status'] === 'IP mới') {
                            $badgeClass = "badge-orange";
                        } elseif ($row['status'] === 'Đăng nhập đáng ngờ') {
                            $badgeClass = "badge-yellow";
                        }
                    ?>

                    <div class="activity">
                        <div class="activity-left">
                            <div class="dot"></div>

                            <div>
                                <b><?php echo htmlspecialchars($row['email']); ?></b><br>
                                <small class="small-muted">
                                    <?php echo htmlspecialchars($row['device']); ?><br>
                                    IP: <?php echo htmlspecialchars($row['ip_address']); ?><br>
                                    Vị trí: Không xác định
                                </small>
                            </div>
                        </div>

                        <div style="text-align:right;">
                            <?php echo date("H:i:s d/m/Y", strtotime($row['login_time'])); ?><br><br>
                            <span class="badge <?php echo $badgeClass; ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="small-muted">Không có dữ liệu phù hợp với bộ lọc hiện tại.</p>
            <?php endif; ?>
        </div>
    </div>

    <a href="logout.php" class="logout-link">Logout</a>
</div>

</body>
</html>