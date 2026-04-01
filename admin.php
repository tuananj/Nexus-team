<?php
session_start();
require_once "config.php";

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Kiểm tra quyền admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Bạn không có quyền truy cập!");
}

$filter = $_GET['filter'] ?? 'all';

/* Xử lý khóa / mở khóa */
if (isset($_GET['action']) && isset($_GET['email'])) {
    $action = $_GET['action'];
    $targetEmail = trim($_GET['email']);

    if ($targetEmail !== $_SESSION['user']) {
        if ($action === 'lock') {
            $stmt = $conn->prepare("UPDATE users SET is_locked = 1 WHERE email = ?");
            $stmt->bind_param("s", $targetEmail);
            $stmt->execute();
        }

        if ($action === 'unlock') {
            $stmt = $conn->prepare("
                UPDATE users
                SET is_locked = 0, failed_attempts = 0, locked_until = NULL
                WHERE email = ?
            ");
            $stmt->bind_param("s", $targetEmail);
            $stmt->execute();
        }
    }

    header("Location: admin.php#users-section");
    exit();
}

/* Thống kê tổng quan */
$totalUsers = $conn->query("SELECT COUNT(*) as t FROM users")->fetch_assoc()['t'] ?? 0;
$totalLogins = $conn->query("SELECT COUNT(*) as t FROM login_history")->fetch_assoc()['t'] ?? 0;
$strange = $conn->query("SELECT COUNT(*) as t FROM login_history WHERE status = 'Thiết bị lạ'")->fetch_assoc()['t'] ?? 0;
$newIP = $conn->query("SELECT COUNT(*) as t FROM login_history WHERE status = 'IP mới'")->fetch_assoc()['t'] ?? 0;
$suspicious = $conn->query("SELECT COUNT(*) as t FROM login_history WHERE status = 'Đăng nhập đáng ngờ'")->fetch_assoc()['t'] ?? 0;

/* Lịch sử đăng nhập có filter */
if ($filter === 'Thiết bị lạ' || $filter === 'IP mới' || $filter === 'Đăng nhập đáng ngờ') {
    $stmt = $conn->prepare("
        SELECT email, ip_address, device, login_time, status
        FROM login_history
        WHERE status = ?
        ORDER BY login_time DESC
        LIMIT 10
    ");
    $stmt->bind_param("s", $filter);
} else {
    $stmt = $conn->prepare("
        SELECT email, ip_address, device, login_time, status
        FROM login_history
        ORDER BY login_time DESC
        LIMIT 10
    ");
}
$stmt->execute();
$history = $stmt->get_result();

/* Danh sách user */
$users = $conn->query("
    SELECT
        u.id,
        u.email,
        u.secret,
        u.role,
        u.is_locked,
        u.failed_attempts,
        u.locked_until,
        COUNT(lh.id) as total_logins
    FROM users u
    LEFT JOIN login_history lh ON u.email = lh.email
    GROUP BY u.id, u.email, u.secret, u.role, u.is_locked, u.failed_attempts, u.locked_until
    ORDER BY u.id DESC
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Admin Nexus</title>

<style>
* {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #090B10;
    color: white;
}

.header {
    position: sticky;
    top: 0;
    z-index: 100;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 40px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    background: rgba(9, 11, 16, 0.96);
    backdrop-filter: blur(8px);
}

.logo {
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo-circle {
    width: 30px;
    height: 30px;
    background: #3b82f6;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
}

.nav {
    display: flex;
    gap: 24px;
}

.nav a {
    color: #aaa;
    text-decoration: none;
}

.nav a.active {
    color: #3b82f6;
    font-weight: bold;
}

.container {
    padding: 30px 50px 50px;
}

.page-title {
    margin: 0 0 24px 0;
    font-size: 28px;
}

.cards {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 18px;
}

.card {
    background: #141A24;
    padding: 20px;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,0.05);
    transition: 0.25s ease;
}

.card:hover {
    transform: translateY(-2px);
}

.card-title {
    color: #94a3b8;
    font-size: 14px;
    margin-bottom: 10px;
}

.card-value {
    font-size: 30px;
    font-weight: bold;
}

.green { color: #22c55e; }
.red { color: #ef4444; }
.orange { color: #f59e0b; }
.yellow { color: #facc15; }
.blue { color: #3b82f6; }

.section {
    margin-top: 42px;
    scroll-margin-top: 90px;
}

.section h3 {
    margin-bottom: 14px;
}

.table-wrapper {
    background: #141A24;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.05);
}

.table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.table th,
.table td {
    padding: 14px 12px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    vertical-align: middle;
    word-wrap: break-word;
}

.table th {
    color: white;
    font-weight: 600;
    background: rgba(255,255,255,0.02);
}

.table td {
    color: #e5e7eb;
}

.table tr:last-child td {
    border-bottom: none;
}

.col-email { width: 38%; }
.col-mfa { width: 8%; }
.col-login { width: 8%; }
.col-status { width: 12%; }
.col-lock { width: 14%; }
.col-action { width: 10%; }

.col-h-email { width: 24%; }
.col-h-ip { width: 16%; }
.col-h-device { width: 28%; }
.col-h-time { width: 18%; }
.col-h-status { width: 14%; }

.center {
    text-align: center;
}

.email-text {
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.badge {
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 12px;
    display: inline-block;
}

.badge-green {
    background: rgba(34,197,94,0.18);
    color: #22c55e;
}

.badge-red {
    background: rgba(239,68,68,0.18);
    color: #ef4444;
}

.badge-orange {
    background: rgba(245,158,11,0.18);
    color: #f59e0b;
}

.badge-yellow {
    background: rgba(250,204,21,0.18);
    color: #facc15;
}

.badge-blue {
    background: rgba(59,130,246,0.18);
    color: #3b82f6;
}

.btn {
    padding: 8px 14px;
    border-radius: 10px;
    text-decoration: none;
    color: white;
    font-size: 13px;
    display: inline-block;
    min-width: 76px;
    text-align: center;
    transition: 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
    opacity: 0.95;
}

.btn-red {
    background: #dc2626;
}

.btn-green {
    background: #16a34a;
}

.filter-box {
    display: flex;
    gap: 10px;
    margin-bottom: 16px;
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
    transition: 0.2s ease;
}

.filter-btn:hover {
    transform: translateY(-1px);
}

.filter-btn.active {
    background: #3b82f6;
    color: white;
}

.muted {
    color: #64748b;
}

.empty-row {
    text-align: center;
    color: #64748b;
    padding: 18px 12px;
}

@media (max-width: 1200px) {
    .cards {
        grid-template-columns: repeat(2, 1fr);
    }

    .container {
        padding: 24px;
    }
}
</style>
</head>

<body>

<div class="header">
    <div class="logo">
        <div class="logo-circle">N</div>
        <b>Nexus Admin</b>
    </div>

    <div class="nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="admin.php" class="active">Admin</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2 class="page-title">Admin Dashboard</h2>

    <div class="cards">
        <div class="card">
            <div class="card-title">Tổng Users</div>
            <div class="card-value blue"><?php echo $totalUsers; ?></div>
        </div>

        <div class="card">
            <div class="card-title">Tổng Logins</div>
            <div class="card-value green"><?php echo $totalLogins; ?></div>
        </div>

        <div class="card">
            <div class="card-title">Thiết bị lạ</div>
            <div class="card-value red"><?php echo $strange; ?></div>
        </div>

        <div class="card">
            <div class="card-title">IP mới</div>
            <div class="card-value orange"><?php echo $newIP; ?></div>
        </div>

        <div class="card">
            <div class="card-title">Đăng nhập đáng ngờ</div>
            <div class="card-value yellow"><?php echo $suspicious; ?></div>
        </div>
    </div>

    <div class="section" id="users-section">
        <h3>Danh sách người dùng</h3>

        <div class="table-wrapper">
            <table class="table">
                <tr>
                    <th class="col-email">Email</th>
                    <th class="col-mfa center">MFA</th>
                    <th class="col-login center">Login</th>
                    <th class="col-status center">Trạng thái</th>
                    <th class="col-lock center">Khóa tạm</th>
                    <th class="col-action center">Action</th>
                </tr>

                <?php if ($users && $users->num_rows > 0): ?>
                    <?php while($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td class="col-email">
                            <span class="email-text" title="<?php echo htmlspecialchars($u['email']); ?>">
                                <?php echo htmlspecialchars($u['email']); ?>
                            </span>
                        </td>

                        <td class="col-mfa center">
                            <?php if (!empty($u['secret'])): ?>
                                <span class="badge badge-green">ON</span>
                            <?php else: ?>
                                <span class="badge badge-red">OFF</span>
                            <?php endif; ?>
                        </td>

                        <td class="col-login center">
                            <?php echo (int)$u['total_logins']; ?>
                        </td>

                        <td class="col-status center">
                            <?php if (!empty($u['is_locked'])): ?>
                                <span class="badge badge-red">Locked</span>
                            <?php else: ?>
                                <span class="badge badge-green">Active</span>
                            <?php endif; ?>
                        </td>

                        <td class="col-lock center">
                            <?php
                            if (!empty($u['locked_until']) && strtotime($u['locked_until']) > time()) {
                                echo date("H:i d/m/Y", strtotime($u['locked_until']));
                            } else {
                                echo "-";
                            }
                            ?>
                        </td>

                        <td class="col-action center">
                            <?php if ($u['email'] !== $_SESSION['user']): ?>
                                <?php if (!empty($u['is_locked'])): ?>
                                    <a class="btn btn-green" href="admin.php?action=unlock&email=<?php echo urlencode($u['email']); ?>#users-section">Unlock</a>
                                <?php else: ?>
                                    <a class="btn btn-red" href="admin.php?action=lock&email=<?php echo urlencode($u['email']); ?>#users-section">Lock</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="muted">You</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty-row">Không có dữ liệu người dùng.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <div class="section" id="history-section">
        <h3>Lịch sử đăng nhập</h3>

        <div class="filter-box">
            <a class="filter-btn <?php echo ($filter === 'all') ? 'active' : ''; ?>" href="admin.php?filter=all#history-section">All</a>
            <a class="filter-btn <?php echo ($filter === 'Thiết bị lạ') ? 'active' : ''; ?>" href="admin.php?filter=<?php echo urlencode('Thiết bị lạ'); ?>#history-section">Thiết bị lạ</a>
            <a class="filter-btn <?php echo ($filter === 'IP mới') ? 'active' : ''; ?>" href="admin.php?filter=<?php echo urlencode('IP mới'); ?>#history-section">IP mới</a>
            <a class="filter-btn <?php echo ($filter === 'Đăng nhập đáng ngờ') ? 'active' : ''; ?>" href="admin.php?filter=<?php echo urlencode('Đăng nhập đáng ngờ'); ?>#history-section">Đăng nhập đáng ngờ</a>
        </div>

        <div class="table-wrapper">
            <table class="table">
                <tr>
                    <th class="col-h-email">Email</th>
                    <th class="col-h-ip">IP</th>
                    <th class="col-h-device">Device</th>
                    <th class="col-h-time">Time</th>
                    <th class="col-h-status center">Status</th>
                </tr>

                <?php if ($history && $history->num_rows > 0): ?>
                    <?php while($row = $history->fetch_assoc()): ?>
                    <tr>
                        <td title="<?php echo htmlspecialchars($row['email']); ?>">
                            <span class="email-text"><?php echo htmlspecialchars($row['email']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($row['ip_address']); ?></td>
                        <td><?php echo htmlspecialchars($row['device']); ?></td>
                        <td><?php echo date("H:i:s d/m/Y", strtotime($row['login_time'])); ?></td>
                        <td class="center">
                            <?php
                            $statusClass = "badge-green";
                            if ($row['status'] === 'Thiết bị lạ') {
                                $statusClass = "badge-red";
                            } elseif ($row['status'] === 'IP mới') {
                                $statusClass = "badge-orange";
                            } elseif ($row['status'] === 'Đăng nhập đáng ngờ') {
                                $statusClass = "badge-yellow";
                            }
                            ?>
                            <span class="badge <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty-row">Không có dữ liệu phù hợp.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>