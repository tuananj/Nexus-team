<?php
// 1. CẤU HÌNH SESSION ĐỒNG BỘ
ini_set('session.cookie_path', '/');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền truy cập (thẻ MFA)
if (!isset($_SESSION['mfa_verified']) || $_SESSION['mfa_verified'] !== true) {
    header("Location: http://localhost/Demo/Nexus-team/login.php");
    exit();
}

$username = isset($_SESSION['auth_username']) ? $_SESSION['auth_username'] : "PILOT_001";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXUS - COMMANDER_COCKPIT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --mech-cyan: #00ffff;
            --mech-purple: #9d00ff;
            --mech-orange: #ff9d00;
            --bg-dark: #000;
            --panel-bg: rgba(0, 10, 20, 0.4); 
        }

        body {
            background-color: var(--bg-dark);
            margin: 0; padding: 0;
            height: 100vh; overflow: hidden;
            font-family: 'Share Tech Mono', monospace;
            color: var(--mech-cyan);
            display: flex; align-items: center; justify-content: center;
        }

        /* --- VIDEO NỀN (GIỮ NGUYÊN ĐỂ CHẠY ĐƯỢC) --- */
        #mechaVideoBG {
            position: absolute; top: 50%; left: 50%;
            min-width: 100%; min-height: 100%;
            width: auto; height: auto;
            transform: translate(-50%, -50%);
            z-index: -100;
            object-fit: cover;
            filter: brightness(0.8) contrast(1.2);
        }

        /* --- HUD OVERLAY --- */
        .mecha-cockpit-hud {
            position: relative;
            width: 100vw; height: 100vh;
            display: flex; flex-direction: column;
            animation: hudFadeIn 1.5s ease-out forwards;
            pointer-events: none;
            z-index: 10;
        }

        /* Hiệu ứng kính quét radar */
        .mecha-cockpit-hud::before {
            content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 2px;
            background: rgba(0, 255, 255, 0.2);
            box-shadow: 0 0 15px var(--mech-cyan);
            animation: scanLine 4s linear infinite;
            z-index: 50;
        }

        /* PANEL GÓC: Thiết kế hầm hố hơn */
        .hud-corner-panel {
            position: absolute;
            background: var(--panel-bg);
            border: 1px solid var(--mech-cyan);
            padding: 12px;
            backdrop-filter: blur(8px);
            pointer-events: auto;
            box-shadow: inset 0 0 15px rgba(0, 255, 255, 0.1);
        }

        /* Panel phía trên */
        .top-left { top: 20px; left: 20px; border-left: 4px solid var(--mech-cyan); }
.top-right { top: 20px; right: 20px; border-right: 4px solid var(--mech-cyan); text-align: right; }

        /* Panel phía dưới */
        .bot-left { bottom: 20px; left: 20px; border-left: 4px solid var(--mech-orange); width: 200px; }
        .bot-right { bottom: 20px; right: 20px; border-right: 4px solid var(--mech-orange); width: 200px; text-align: right;}

        .stat-label { font-size: 10px; color: rgba(0, 255, 255, 0.6); letter-spacing: 2px; }
        .stat-value { font-size: 16px; color: #fff; text-shadow: 0 0 10px var(--mech-cyan); }

        /* --- WELCOME TEXT: NẰM GỌN GÀNG ĐỂ KHÔNG CHE LOGO --- */
        .welcome-center {
            position: absolute;
            top: 70%; /* Đẩy xuống dưới một chút để logo trung tâm video lộ ra */
            width: 100%;
            text-align: center;
            z-index: 20;
        }

        .welcome-center h1 {
            font-size: 4.5rem;
            font-weight: 900;
            color: #fff;
            letter-spacing: 15px;
            text-shadow: 0 0 20px var(--mech-cyan);
            animation: glitchEffect 3s infinite;
        }

        /* NÚT DISCONNECT */
        .disconnect-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 40px;
            border: 2px solid var(--mech-orange);
            color: var(--mech-orange);
            background: rgba(0, 0, 0, 0.6);
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
            pointer-events: auto;
        }
        .disconnect-btn:hover { background: var(--mech-orange); color: #000; box-shadow: 0 0 20px var(--mech-orange); }

        /* --- ANIMATIONS --- */
        @keyframes scanLine { 0% { top: 0; } 100% { top: 100%; } }
        @keyframes hudFadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes glitchEffect {
            0% { transform: skew(0deg); }
            1% { transform: skew(10deg); opacity: 0.8; }
            2% { transform: skew(-10deg); opacity: 1; }
            3% { transform: skew(0deg); }
            100% { transform: skew(0deg); }
        }

    </style>
</head>
<body>

    <video autoplay loop muted playsinline id="mechaVideoBG">
        <source src="mechabackground.mp4" type="video/mp4">
    </video>

    <div class="mecha-cockpit-hud">
        <!-- TOP HUD -->
        <div class="hud-corner-panel top-left">
            <div class="stat-label">> OPERATOR_ID</div>
            <div class="stat-value"><?php echo strtoupper($username); ?></div>
        </div>

        <div class="hud-corner-panel top-right">
            <div class="stat-label">SYSTEM_STATUS <i class="fas fa-circle text-success ms-2"></i></div>
            <div class="stat-value">PROTOCOL_G10_ACTIVE</div>
        </div>

        <!-- CENTER CONTENT -->
        <div class="welcome-center">
            <h1>WELCOME</h1>
<div style="letter-spacing: 5px; color: var(--mech-cyan);">NEURAL LINK ESTABLISHED // READY FOR COMBAT</div>
            
            <a href="http://localhost/Nexus-team/logout.php" class="disconnect-btn">DISCONNECT SYSTEM</a>
        </div>

        <!-- BOTTOM HUD -->
        <div class="hud-corner-panel bot-left">
            <div class="stat-label">POWER_CORE</div>
            <div class="stat-value">98.4% <small style="font-size: 10px;">SYNC</small></div>
            <div class="progress" style="height: 4px; background: #333; margin-top: 5px;">
                <div class="progress-bar bg-info" style="width: 98%;"></div>
            </div>
        </div>

        <div class="hud-corner-panel bot-right">
            <div class="stat-label">LOCATION</div>
            <div class="stat-value">SECTOR_SEA_05</div>
            <div class="stat-label" style="font-size: 8px;">ENCRYPTION: AES-256</div>
        </div>
    </div>

</body>
</html>
