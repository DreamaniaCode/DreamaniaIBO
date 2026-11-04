<?php
/**
 * Dreamania Player - Web Administration & Reseller Portal
 * Complete IPTV Middleware with Multi-Engine DB, Reseller System, and Subscription Controls.
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';

// Initialize DB connection & migrations
try {
    $pdo = DB::getConnection();
} catch (Exception $e) {
    die("Database Connection Error: " . htmlspecialchars($e->getMessage()));
}

// Available Themes Definition
$themes = [
    'purple' => [
        'name'    => 'Dreamania Purple',
        'icon'    => '💜',
        'desc'    => 'Signature violet & electric purple'
    ],
    'dark' => [
        'name'    => 'Onyx Dark',
        'icon'    => '🖤',
        'desc'    => 'Deep dark neutral slate'
    ],
    'cyberpunk' => [
        'name'    => 'Cyber Neon',
        'icon'    => '⚡',
        'desc'    => 'High-contrast cyan & magenta neon'
    ],
    'gold' => [
        'name'    => 'Royal Gold',
        'icon'    => '👑',
        'desc'    => 'Luxurious amber & rich black'
    ]
];

$allowedThemes = array_keys($themes);
$defaultTheme = DB::getSetting('panel_theme', 'purple');
if (!in_array($defaultTheme, $allowedThemes, true)) {
    $defaultTheme = 'purple';
}

$cookieTheme = $_COOKIE['dreamania_theme'] ?? null;
$currentTheme = ($cookieTheme && in_array($cookieTheme, $allowedThemes, true)) ? $cookieTheme : $defaultTheme;

if (isset($_GET['set_theme'])) {
    $selTheme = trim((string)$_GET['set_theme']);
    if (in_array($selTheme, $allowedThemes, true)) {
        setcookie('dreamania_theme', $selTheme, time() + (86400 * 365), '/');
        $redirectUrl = strtok($_SERVER['REQUEST_URI'] ?? 'index.php', '?') ?: 'index.php';
        header("Location: {$redirectUrl}");
        exit;
    }
}

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

$msg = '';
$msgType = '';

// Handle Logout
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    $_SESSION = [];
    session_destroy();
    header("Location: index.php?panel=1");
    exit;
}

// Handle Login (Admin or Reseller)
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf'] ?? '')) {
        $msg = "Invalid session token. Please try again.";
        $msgType = "error";
    } else {
        $loginUser = trim((string)($_POST['login_username'] ?? ''));
        $loginPass = (string)($_POST['access_password'] ?? '');
        $savedMasterPass = DB::getSetting('admin_password', '200600');

        // Check Admin Master Login
        if (empty($loginUser) && $loginPass === $savedMasterPass) {
            $_SESSION['logged_in'] = true;
            $_SESSION['role'] = 'admin';
            $_SESSION['user_name'] = 'Super Administrator';
            header("Location: index.php");
            exit;
        } elseif (!empty($loginUser)) {
            // Check Reseller Login
            $reseller = DB::getResellerByUsername($loginUser);
            if ($reseller && $reseller['status'] === 'active' && password_verify($loginPass, $reseller['password'])) {
                $_SESSION['logged_in'] = true;
                $_SESSION['role'] = 'reseller';
                $_SESSION['reseller_id'] = (int)$reseller['id'];
                $_SESSION['user_name'] = $reseller['name'];
                $_SESSION['reseller_credits'] = (int)$reseller['credits'];
                header("Location: index.php");
                exit;
            } elseif ($loginUser === 'admin' && $loginPass === $savedMasterPass) {
                $_SESSION['logged_in'] = true;
                $_SESSION['role'] = 'admin';
                $_SESSION['user_name'] = 'Super Administrator';
                header("Location: index.php");
                exit;
            } else {
                $msg = "Invalid username or password.";
                $msgType = "error";
            }
        } else {
            $msg = "Incorrect portal password.";
            $msgType = "error";
        }
    }
}

$isLogged = !empty($_SESSION['logged_in']);
$role = $_SESSION['role'] ?? 'guest';
$resellerId = ($role === 'reseller') ? (int)($_SESSION['reseller_id'] ?? 0) : null;

// Public Landing Page vs Management Portal
$wantsPanel = isset($_GET['panel']) || isset($_GET['admin']) || (isset($_POST['action']) && in_array($_POST['action'], ['login', 'logout'], true));
if (!$isLogged && !$wantsPanel) {
    require __DIR__ . '/landing.php';
    exit;
}

// Handle Authenticated Actions
if ($isLogged && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf'] ?? '')) {
        $msg = "Session verification failed.";
        $msgType = "error";
    } else {
        $formType = $_POST['form_type'] ?? '';

        try {
            // 1. Subscription Quick Activations (Trial 7D, 1 Year, Lifetime, Toggle Block)
            if ($formType === 'activate_sub') {
                $deviceId = (int)($_POST['device_id'] ?? 0);
                $subType = trim((string)($_POST['sub_type'] ?? ''));

                if ($deviceId <= 0 || empty($subType)) {
                    throw new Exception("Invalid device activation parameters.");
                }

                // If reseller, verify ownership
                if ($role === 'reseller') {
                    $dev = DB::getDeviceById($deviceId);
                    if (!$dev || (int)$dev['reseller_id'] !== $resellerId) {
                        throw new Exception("You are not authorized to manage this device.");
                    }
                }

                $res = DB::activateSubscription($deviceId, $subType, $resellerId);
                $msg = $res['message'];
                $msgType = $res['status'] ? 'success' : 'error';
            }

            // 2. Add IBO / Xtream User
            elseif ($formType === 'ibo') {
                $dnsId = (int)($_POST['dns_id'] ?? 0);
                $mac = trim($_POST['mac'] ?? '');
                $username = trim($_POST['username'] ?? '');
                $password = trim($_POST['password'] ?? '');

                if ($dnsId <= 0 || empty($mac) || empty($username) || empty($password)) {
                    throw new Exception("All fields are required for Xtream Codes user.");
                }

                DB::addIboUser($dnsId, $mac, $username, $password, $resellerId);
                $msg = "Xtream Codes user registered successfully for MAC: " . htmlspecialchars(DB::normalizeMac($mac));
                $msgType = "success";
            }

            // 3. Add M3U User
            elseif ($formType === 'm3u') {
                $mac = trim($_POST['mac_address'] ?? '');
                $name = trim($_POST['m3u_name'] ?? '');
                $m3uUrl = trim($_POST['m3u'] ?? '');

                if (empty($mac) || empty($name) || empty($m3uUrl)) {
                    throw new Exception("All fields are required for M3U playlist user.");
                }

                DB::addM3uUser($mac, $name, $m3uUrl, $resellerId);
                $msg = "M3U playlist added successfully for MAC: " . htmlspecialchars(DB::normalizeMac($mac));
                $msgType = "success";
            }

            // 4. Edit Device
            elseif ($formType === 'edit_device') {
                $dId = (int)($_POST['device_id'] ?? 0);
                $deviceKey = trim($_POST['device_key'] ?? '');
                $expireDate = trim($_POST['expire_date'] ?? '');
                $status = trim($_POST['status'] ?? 'active');
                $parentCode = trim($_POST['parent_control'] ?? '0000');
                $notes = trim($_POST['notes'] ?? '');

                if ($dId <= 0 || empty($deviceKey) || empty($expireDate)) {
                    throw new Exception("Required fields missing for device update.");
                }

                if ($role === 'reseller') {
                    $dev = DB::getDeviceById($dId);
                    if (!$dev || (int)$dev['reseller_id'] !== $resellerId) {
                        throw new Exception("Unauthorized device modification.");
                    }
                }

                DB::updateDevice($dId, [
                    'device_key'     => $deviceKey,
                    'expire_date'    => $expireDate,
                    'status'         => $status,
                    'parent_control' => $parentCode,
                    'notes'          => $notes
                ]);

                $msg = "Device updated successfully!";
                $msgType = "success";
            }

            // 5. Delete Device
            elseif ($formType === 'delete_device') {
                $dId = (int)($_POST['device_id'] ?? 0);
                if ($dId > 0) {
                    if ($role === 'reseller') {
                        $dev = DB::getDeviceById($dId);
                        if (!$dev || (int)$dev['reseller_id'] !== $resellerId) {
                            throw new Exception("Unauthorized device deletion.");
                        }
                    }
                    DB::deleteDevice($dId);
                    $msg = "Device and associated playlists deleted.";
                    $msgType = "success";
                }
            }

            // 6. Delete Playlist
            elseif ($formType === 'delete_playlist') {
                $pId = (int)($_POST['playlist_id'] ?? 0);
                if ($pId > 0) {
                    DB::deletePlaylist($pId);
                    $msg = "Playlist removed successfully.";
                    $msgType = "success";
                }
            }

            // 7. Reseller Management (Admin Only)
            elseif ($formType === 'add_reseller' && $role === 'admin') {
                $rUser = trim($_POST['reseller_username'] ?? '');
                $rPass = (string)($_POST['reseller_password'] ?? '');
                $rName = trim($_POST['reseller_name'] ?? '');
                $rCredits = (int)($_POST['reseller_credits'] ?? 0);

                if (empty($rUser) || empty($rPass) || empty($rName)) {
                    throw new Exception("Username, password, and name are required.");
                }

                DB::addReseller($rUser, $rPass, $rName, $rCredits);
                $msg = "Reseller account '{$rUser}' created successfully!";
                $msgType = "success";
            } elseif ($formType === 'edit_reseller' && $role === 'admin') {
                $rId = (int)($_POST['reseller_id'] ?? 0);
                $rName = trim($_POST['reseller_name'] ?? '');
                $rCredits = (int)($_POST['reseller_credits'] ?? 0);
                $rStatus = trim($_POST['reseller_status'] ?? 'active');
                $rPass = !empty($_POST['reseller_password']) ? (string)$_POST['reseller_password'] : null;

                if ($rId > 0) {
                    DB::updateReseller($rId, $rName, $rCredits, $rStatus, $rPass);
                    $msg = "Reseller account updated successfully!";
                    $msgType = "success";
                }
            } elseif ($formType === 'delete_reseller' && $role === 'admin') {
                $rId = (int)($_POST['reseller_id'] ?? 0);
                if ($rId > 0) {
                    DB::deleteReseller($rId);
                    $msg = "Reseller account removed.";
                    $msgType = "success";
                }
            }

            // 8. DNS Management (Admin Only)
            elseif ($formType === 'add_dns' && $role === 'admin') {
                $name = trim($_POST['dns_name'] ?? '');
                $url = trim($_POST['dns_url'] ?? '');
                if (!empty($name) && !empty($url)) {
                    DB::addDns($name, $url);
                    $msg = "DNS Portal added successfully.";
                    $msgType = "success";
                }
            } elseif ($formType === 'delete_dns' && $role === 'admin') {
                $dId = (int)($_POST['dns_id'] ?? 0);
                if ($dId > 0) {
                    DB::deleteDns($dId);
                    $msg = "DNS Portal removed.";
                    $msgType = "success";
                }
            }

            // 9. APK Upload (Admin Only)
            elseif ($formType === 'upload_apk_file' && $role === 'admin') {
                if (isset($_FILES['apk_file']) && $_FILES['apk_file']['error'] === UPLOAD_ERR_OK) {
                    $dest = __DIR__ . '/data/app.apk';
                    if (move_uploaded_file($_FILES['apk_file']['tmp_name'], $dest)) {
                        @copy($dest, __DIR__ . '/DreamaniaPlayer.apk');
                        $sizeMb = round(filesize($dest) / (1024 * 1024), 2);
                        $msg = "APK file updated successfully! ({$sizeMb} MB)";
                        $msgType = "success";
                    } else {
                        throw new Exception("Could not move uploaded APK to data/app.apk.");
                    }
                }
            }

            // 10. Settings Update (Admin Only)
            elseif ($formType === 'update_settings' && $role === 'admin') {
                $settingsToUpdate = [
                    'admin_password', 'app_name', 'note_title', 'note_content',
                    'trial_days', 'default_expiry', 'price', 'apk_link'
                ];
                foreach ($settingsToUpdate as $sKey) {
                    if (isset($_POST[$sKey])) {
                        DB::setSetting($sKey, trim((string)$_POST[$sKey]));
                    }
                }
                $msg = "System settings updated successfully!";
                $msgType = "success";
            }

        } catch (Exception $e) {
            $msg = $e->getMessage();
            $msgType = "error";
        }
    }
}

// Fetch Data for Display
$devices = $isLogged ? DB::getAllDevices($resellerId) : [];
$playlists = $isLogged ? DB::getAllPlaylists($resellerId) : [];
$dnsList = ($isLogged && $role === 'admin') ? DB::getAllDns() : [];
$resellersList = ($isLogged && $role === 'admin') ? DB::getAllResellers() : [];
$recentLines = ($isLogged && $role === 'admin') ? DB::getRecentLineActivity(30) : [];
$settings = $isLogged ? DB::getAllSettings() : [];

$activeTab = $_GET['tab'] ?? 'playlists';
if ($role === 'reseller' && in_array($activeTab, ['dns', 'settings', 'resellers', 'lines'])) {
    $activeTab = 'playlists';
}

$apkPath = __DIR__ . '/data/app.apk';
if (!file_exists($apkPath) && file_exists(__DIR__ . '/DreamaniaPlayer.apk')) {
    $apkPath = __DIR__ . '/DreamaniaPlayer.apk';
}
$apkFileExists = file_exists($apkPath) && filesize($apkPath) > 100000;
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($currentTheme) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dreamania Player - Middleware &amp; Reseller Portal</title>
<link rel="icon" type="image/png" href="assets/img/icon.png">
<link rel="stylesheet" href="assets/css/themes.css">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    background-color: var(--bg);
    background-image: var(--bg-mesh);
    background-attachment: fixed;
    background-size: cover;
    color: var(--text);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Navbar */
.navbar {
    background: rgba(12, 10, 22, 0.92);
    backdrop-filter: blur(16px);
    border-bottom: 1px solid var(--border);
    padding: 12px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 50;
}
.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: inherit;
}
.brand-logo-img {
    width: 40px;
    height: 40px;
    filter: drop-shadow(0 4px 10px var(--primary-glow));
}
.brand-name {
    font-size: 19px;
    font-weight: 800;
    letter-spacing: -0.5px;
}
.brand-badge {
    background: var(--badge-bg);
    color: var(--badge-color);
    border: 1px solid var(--badge-border);
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 6px;
    font-weight: 700;
    margin-left: 6px;
}
.nav-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}
.role-badge {
    background: rgba(139, 92, 246, 0.15);
    border: 1px solid #8b5cf6;
    color: #c4b5fd;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: bold;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    color: #fff;
    background: var(--primary-gradient);
    box-shadow: 0 4px 14px var(--primary-glow);
}
.btn:hover {
    background: var(--primary-gradient-hover);
    transform: translateY(-1px);
}
.btn-secondary {
    background: rgba(255, 255, 255, 0.06);
    box-shadow: none;
    color: var(--text);
    border: 1px solid var(--border);
}
.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.12);
    border-color: var(--accent);
}
.btn-success { background: #10b981; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3); }
.btn-warning { background: #f59e0b; color: #000; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3); }
.btn-danger { background: #ef4444; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3); }
.btn-sm { padding: 5px 10px; font-size: 11px; border-radius: 6px; }

/* Layout */
.main-container {
    width: 100%;
    max-width: 1240px;
    margin: 24px auto;
    padding: 0 16px;
    flex: 1;
}

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.stat-icon {
    font-size: 28px;
    width: 52px;
    height: 52px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.05);
    display: flex;
    align-items: center;
    justify-content: center;
}
.stat-label { font-size: 12px; color: var(--muted); text-transform: uppercase; font-weight: 700; }
.stat-value { font-size: 22px; font-weight: 800; color: var(--text); }

/* Navigation Tabs */
.tabs-nav {
    display: flex;
    gap: 8px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 24px;
    overflow-x: auto;
    padding-bottom: 8px;
}
.tab-link {
    padding: 10px 18px;
    font-size: 14px;
    font-weight: 600;
    color: var(--muted);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.2s;
    white-space: nowrap;
}
.tab-link:hover, .tab-link.active {
    color: #fff;
    background: rgba(255, 255, 255, 0.08);
}
.tab-link.active {
    background: var(--primary-gradient);
    color: #fff;
}

/* Card & Table */
.card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 24px;
}
.card h2 {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.table-wrapper {
    overflow-x: auto;
}
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
th {
    text-align: left;
    padding: 12px 14px;
    color: var(--muted);
    font-weight: 700;
    border-bottom: 1px solid var(--border);
    font-size: 11px;
    text-transform: uppercase;
}
td {
    padding: 14px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    vertical-align: middle;
}
tr:hover td {
    background: rgba(255, 255, 255, 0.02);
}

/* Badges */
.mac-badge {
    font-family: monospace;
    font-size: 12px;
    padding: 4px 8px;
    background: rgba(139, 92, 246, 0.15);
    border: 1px solid rgba(139, 92, 246, 0.4);
    border-radius: 6px;
    color: #c4b5fd;
    font-weight: bold;
}
.key-badge {
    font-family: monospace;
    font-size: 12px;
    padding: 4px 8px;
    background: rgba(245, 158, 11, 0.15);
    border: 1px solid rgba(245, 158, 11, 0.4);
    border-radius: 6px;
    color: #fcd34d;
    font-weight: bold;
}
.status-pill {
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 12px;
    font-weight: 700;
    text-transform: uppercase;
}
.status-active { background: rgba(16, 185, 129, 0.2); color: #34d399; }
.status-trial { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
.status-expired { background: rgba(239, 68, 68, 0.2); color: #f87171; }
.status-blocked { background: rgba(100, 116, 139, 0.2); color: #94a3b8; }

/* Forms */
.form-group {
    margin-bottom: 16px;
}
label {
    display: block;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: 600;
    color: var(--muted);
}
input, select, textarea {
    width: 100%;
    padding: 10px 14px;
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
}
input:focus, select:focus, textarea:focus {
    border-color: var(--accent);
}
.grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
}

/* Modals */
.modal {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(8px);
    z-index: 100;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.modal.open {
    display: flex;
}
.modal-content {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    width: 100%;
    max-width: 520px;
    padding: 24px;
    position: relative;
    max-height: 90vh;
    overflow-y: auto;
}
.modal-close {
    position: absolute;
    top: 16px;
    right: 16px;
    background: none;
    border: none;
    color: var(--muted);
    font-size: 20px;
    cursor: pointer;
}

/* Footer */
.footer {
    text-align: center;
    padding: 24px;
    color: var(--muted);
    font-size: 13px;
    border-top: 1px solid var(--border);
    margin-top: auto;
}
</style>
</head>
<body>

<!-- Navbar -->
<header class="navbar">
    <a href="index.php" class="brand">
        <img src="assets/img/icon.png" alt="Logo" class="brand-logo-img">
        <div>
            <span class="brand-name">Dreamania Player</span>
            <span class="brand-badge">PORTAL</span>
        </div>
    </a>

    <div class="nav-actions">
        <a href="landing.php" class="btn btn-secondary btn-sm" title="View Public Landing Page">🌐 Landing Page</a>
        <?php if ($apkFileExists): ?>
            <a href="download" class="btn btn-secondary btn-sm">📲 Download APK</a>
        <?php endif; ?>

        <?php if ($isLogged): ?>
            <span class="role-badge">
                <?= ($role === 'admin') ? '👑 Admin' : '👤 Reseller: ' . htmlspecialchars($_SESSION['user_name'] ?? '') . ' (' . (int)($_SESSION['reseller_credits'] ?? 0) . ' Cr)' ?>
            </span>
            <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="logout">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                <button type="submit" class="btn btn-secondary btn-sm">Logout</button>
            </form>
        <?php endif; ?>
    </div>
</header>

<main class="main-container">
    <?php if (!empty($msg)): ?>
        <div style="padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; font-weight: 600; <?= $msgType === 'success' ? 'background: rgba(16,185,129,0.15); border: 1px solid #10b981; color: #34d399;' : 'background: rgba(239,68,68,0.15); border: 1px solid #ef4444; color: #f87171;' ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <?php if (!$isLogged): ?>
        <!-- Login Card -->
        <div style="max-width: 440px; margin: 40px auto;" class="card">
            <div style="text-align:center; margin-bottom: 24px;">
                <img src="assets/img/icon.png" style="width: 64px; height: 64px; margin-bottom: 12px;">
                <h1 style="font-size: 24px; font-weight: 800;">DREAMANIA <span style="color:var(--accent);">PLAYER</span></h1>
                <p style="color: var(--muted); font-size: 13px; margin-top: 4px;">Middleware &amp; Device Control Portal</p>
            </div>

            <form method="post">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                <div class="form-group">
                    <label for="login_username">Username (Leave empty for Admin Master Key)</label>
                    <input type="text" id="login_username" name="login_username" placeholder="admin or reseller_name">
                </div>

                <div class="form-group">
                    <label for="access_password">Password / Master Key</label>
                    <input type="password" id="access_password" name="access_password" required placeholder="••••••••">
                </div>

                <button type="submit" class="btn" style="width:100%; padding: 12px; margin-top: 8px;">Access Portal</button>
            </form>
            <div style="text-align: center; margin-top: 16px;">
                <a href="landing.php" style="color: var(--muted); font-size: 13px; text-decoration: none;">◀ Back to Main Landing Page</a>
            </div>
        </div>

    <?php else: ?>
        <!-- Stats Bar -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📺</div>
                <div>
                    <div class="stat-label">Total Devices</div>
                    <div class="stat-value"><?= count($devices) ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📑</div>
                <div>
                    <div class="stat-label">Active Playlists</div>
                    <div class="stat-value"><?= count($playlists) ?></div>
                </div>
            </div>
            <?php if ($role === 'admin'): ?>
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div>
                        <div class="stat-label">Total Resellers</div>
                        <div class="stat-value"><?= count($resellersList) ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🌐</div>
                    <div>
                        <div class="stat-label">DNS Portals</div>
                        <div class="stat-value"><?= count($dnsList) ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="stat-card">
                    <div class="stat-icon">💎</div>
                    <div>
                        <div class="stat-label">My Credits</div>
                        <div class="stat-value"><?= (int)($_SESSION['reseller_credits'] ?? 0) ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Navigation Tabs -->
        <nav class="tabs-nav">
            <a href="?tab=playlists" class="tab-link <?= $activeTab === 'playlists' ? 'active' : '' ?>">📋 Registered Devices &amp; Playlists</a>
            <a href="?tab=add_ibo" class="tab-link <?= $activeTab === 'add_ibo' ? 'active' : '' ?>">⚡ Add Xtream User</a>
            <a href="?tab=add_m3u" class="tab-link <?= $activeTab === 'add_m3u' ? 'active' : '' ?>">📄 Add M3U Playlist</a>
            <?php if ($role === 'admin'): ?>
                <a href="?tab=resellers" class="tab-link <?= $activeTab === 'resellers' ? 'active' : '' ?>">👥 Resellers</a>
                <a href="?tab=dns" class="tab-link <?= $activeTab === 'dns' ? 'active' : '' ?>">🌐 DNS Portals</a>
                <a href="?tab=lines" class="tab-link <?= $activeTab === 'lines' ? 'active' : '' ?>">📈 Used Lines</a>
                <a href="?tab=settings" class="tab-link <?= $activeTab === 'settings' ? 'active' : '' ?>">⚙️ Settings &amp; APK</a>
            <?php endif; ?>
        </nav>

        <?php if ($activeTab === 'playlists'): ?>
            <!-- Registered Playlists & Devices with Full CRUD & Activation -->
            <div class="card">
                <h2>
                    <span>📋 Registered Playlists &amp; Devices</span>
                    <a href="?tab=add_ibo" class="btn btn-sm">➕ Add New User</a>
                </h2>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>MAC Address</th>
                                <th>Device Key</th>
                                <th>Status / Expiry</th>
                                <th>Active Playlists</th>
                                <th>Quick Activations</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($devices)): ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding: 28px; color: var(--muted);">
                                        No registered devices found. Click "Add Xtream User" to register a device.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($devices as $d): ?>
                                    <?php
                                        $dId = (int)$d['id'];
                                        $mac = $d['mac_address'];
                                        $devPlaylists = array_filter($playlists, fn($p) => strtoupper($p['mac_address']) === strtoupper($mac));
                                        $isExpired = strtotime($d['expire_date']) < time();
                                        $statusClass = match(true) {
                                            $d['status'] === 'blocked' => 'status-blocked',
                                            $isExpired => 'status-expired',
                                            (bool)$d['is_trial'] => 'status-trial',
                                            default => 'status-active'
                                        };
                                        $statusText = match(true) {
                                            $d['status'] === 'blocked' => 'BLOCKED',
                                            $isExpired => 'EXPIRED',
                                            (bool)$d['is_trial'] => '7D TRIAL',
                                            default => 'ACTIVE'
                                        };
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="mac-badge"><?= htmlspecialchars($mac) ?></span>
                                            <?php if (!empty($d['app_type'])): ?>
                                                <div style="font-size:11px; color:var(--muted); margin-top:2px;"><?= htmlspecialchars($d['app_type']) ?> v<?= htmlspecialchars($d['app_version'] ?? '1.0') ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="key-badge"><?= htmlspecialchars($d['device_key']) ?></span>
                                            <div style="font-size:11px; color:var(--muted); margin-top:2px;">Pin: <?= htmlspecialchars($d['parent_control'] ?? '0000') ?></div>
                                        </td>
                                        <td>
                                            <span class="status-pill <?= $statusClass ?>"><?= $statusText ?></span>
                                            <div style="font-size:12px; margin-top:4px; font-weight:600;">
                                                Expires: <?= htmlspecialchars($d['expire_date']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (empty($devPlaylists)): ?>
                                                <span style="color:var(--muted); font-size:12px;">No Playlist Linked</span>
                                            <?php else: ?>
                                                <?php foreach ($devPlaylists as $pl): ?>
                                                    <div style="margin-bottom: 4px;">
                                                        <strong><?= htmlspecialchars($pl['name']) ?></strong>
                                                        <span style="font-size:10px; padding:2px 6px; border-radius:4px; background:rgba(255,255,255,0.08);">
                                                            <?= strtoupper(htmlspecialchars($pl['type'])) ?>
                                                        </span>
                                                        <form method="post" onsubmit="return confirm('Remove playlist <?= htmlspecialchars($pl['name']) ?>?');" style="display:inline;">
                                                            <input type="hidden" name="form_type" value="delete_playlist">
                                                            <input type="hidden" name="playlist_id" value="<?= $pl['id'] ?>">
                                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                                            <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:11px; margin-left:4px;">✕</button>
                                                        </form>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <!-- Quick Activation Buttons -->
                                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                                <!-- Trial 7 Days -->
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="form_type" value="activate_sub">
                                                    <input type="hidden" name="device_id" value="<?= $dId ?>">
                                                    <input type="hidden" name="sub_type" value="trial_7d">
                                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                                    <button type="submit" class="btn btn-warning btn-sm" title="Activate 7 Days Trial">⚡ 7D Trial</button>
                                                </form>

                                                <!-- 1 Year -->
                                                <form method="post" onsubmit="return confirm('Activate 1 Year subscription?<?= ($role === 'reseller') ? ' (Deducts 1 Credit)' : '' ?>');" style="display:inline;">
                                                    <input type="hidden" name="form_type" value="activate_sub">
                                                    <input type="hidden" name="device_id" value="<?= $dId ?>">
                                                    <input type="hidden" name="sub_type" value="sub_1y">
                                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                                    <button type="submit" class="btn btn-success btn-sm" title="Activate 1 Year Subscription">💎 1 Year</button>
                                                </form>

                                                <!-- Lifetime -->
                                                <form method="post" onsubmit="return confirm('Activate Lifetime subscription?<?= ($role === 'reseller') ? ' (Deducts 2 Credits)' : '' ?>');" style="display:inline;">
                                                    <input type="hidden" name="form_type" value="activate_sub">
                                                    <input type="hidden" name="device_id" value="<?= $dId ?>">
                                                    <input type="hidden" name="sub_type" value="sub_lifetime">
                                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                                    <button type="submit" class="btn btn-sm" title="Activate Lifetime">♾️ Lifetime</button>
                                                </form>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="display:flex; gap:6px;">
                                                <!-- Edit Device Modal Button -->
                                                <button type="button" class="btn btn-secondary btn-sm" onclick="openEditModal(<?= htmlspecialchars(json_encode($d)) ?>)">✏️ Edit</button>

                                                <!-- Toggle Block -->
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="form_type" value="activate_sub">
                                                    <input type="hidden" name="device_id" value="<?= $dId ?>">
                                                    <input type="hidden" name="sub_type" value="toggle_status">
                                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                                    <button type="submit" class="btn btn-secondary btn-sm" title="Block/Unblock"><?= $d['status'] === 'blocked' ? '🔓' : '🔒' ?></button>
                                                </form>

                                                <!-- Delete Device -->
                                                <form method="post" onsubmit="return confirm('Delete device <?= htmlspecialchars($mac) ?> and all its playlists?');" style="display:inline;">
                                                    <input type="hidden" name="form_type" value="delete_device">
                                                    <input type="hidden" name="device_id" value="<?= $dId ?>">
                                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">🗑️</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Edit Device Modal -->
            <div id="editModal" class="modal">
                <div class="modal-content">
                    <button type="button" class="modal-close" onclick="closeEditModal()">&times;</button>
                    <h2 style="margin-bottom: 18px;">✏️ Edit Device Settings</h2>
                    <form method="post">
                        <input type="hidden" name="form_type" value="edit_device">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" id="edit_device_id" name="device_id" value="">

                        <div class="form-group">
                            <label>MAC Address (Hardware Locked)</label>
                            <input type="text" id="edit_mac_address" disabled style="opacity: 0.7;">
                        </div>

                        <div class="form-group">
                            <label for="edit_device_key">Device Key (6-Digit Code)</label>
                            <input type="text" id="edit_device_key" name="device_key" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_expire_date">Expiration Date (YYYY-MM-DD)</label>
                            <input type="date" id="edit_expire_date" name="expire_date" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_status">Account Status</label>
                            <select id="edit_status" name="status">
                                <option value="active">Active</option>
                                <option value="blocked">Blocked / Suspended</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="edit_parent_control">Parental Control PIN</label>
                            <input type="text" id="edit_parent_control" name="parent_control" maxlength="4" value="0000">
                        </div>

                        <div class="form-group">
                            <label for="edit_notes">Notes / Customer Info</label>
                            <textarea id="edit_notes" name="notes" rows="2" placeholder="Customer name, phone number, notes..."></textarea>
                        </div>

                        <button type="submit" class="btn" style="width:100%; margin-top:8px;">Save Device Changes</button>
                    </form>
                </div>
            </div>

        <?php elseif ($activeTab === 'add_ibo'): ?>
            <!-- Add Xtream Codes User Form -->
            <div class="card" style="max-width: 680px; margin: 0 auto;">
                <h2>⚡ Register Xtream Codes User (Player API)</h2>
                <p style="color:var(--muted); font-size:13px; margin-bottom: 20px;">
                    Integrate high-speed Xtream Codes credentials directly. The application uses official <code style="color:var(--accent);">player_api.php</code> for fast on-demand streaming.
                </p>

                <form method="post">
                    <input type="hidden" name="form_type" value="ibo">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                    <div class="form-group">
                        <label for="mac">Device MAC Address</label>
                        <input type="text" id="mac" name="mac" placeholder="00:1A:79:XX:XX:XX" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="dns_id">Target Server DNS Portal</label>
                        <select id="dns_id" name="dns_id" required>
                            <?php foreach (DB::getAllDns() as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['url']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label for="username">Xtream Username</label>
                            <input type="text" id="username" name="username" placeholder="Username" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Xtream Password</label>
                            <input type="text" id="password" name="password" placeholder="Password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn" style="width:100%; padding:12px; margin-top:8px;">Register &amp; Link to Device</button>
                </form>
            </div>

        <?php elseif ($activeTab === 'add_m3u'): ?>
            <!-- Add M3U Playlist User -->
            <div class="card" style="max-width: 680px; margin: 0 auto;">
                <h2>📄 Register Standard M3U Playlist</h2>
                <p style="color:var(--muted); font-size:13px; margin-bottom: 20px;">
                    Link a raw M3U / M3U8 playlist URL to an existing or new device.
                </p>

                <form method="post">
                    <input type="hidden" name="form_type" value="m3u">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                    <div class="form-group">
                        <label for="mac_address">Device MAC Address</label>
                        <input type="text" id="mac_address" name="mac_address" placeholder="00:1A:79:XX:XX:XX" required>
                    </div>

                    <div class="form-group">
                        <label for="m3u_name">Playlist Name</label>
                        <input type="text" id="m3u_name" name="m3u_name" placeholder="e.g. VIP Sports &amp; Cinema" required>
                    </div>

                    <div class="form-group">
                        <label for="m3u">Playlist URL (M3U or M3U Plus)</label>
                        <input type="url" id="m3u" name="m3u" placeholder="https://provider.com/get.php?..." required>
                    </div>

                    <button type="submit" class="btn" style="width:100%; padding:12px; margin-top:8px;">Save M3U Playlist</button>
                </form>
            </div>

        <?php elseif ($activeTab === 'resellers' && $role === 'admin'): ?>
            <!-- Reseller Management (Admin Only) -->
            <div class="grid-2">
                <!-- Add Reseller Form -->
                <div class="card">
                    <h2>➕ Create New Reseller</h2>
                    <form method="post">
                        <input type="hidden" name="form_type" value="add_reseller">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                        <div class="form-group">
                            <label for="reseller_username">Reseller Login Username</label>
                            <input type="text" id="reseller_username" name="reseller_username" required placeholder="e.g. iptv_reseller1">
                        </div>

                        <div class="form-group">
                            <label for="reseller_name">Display / Business Name</label>
                            <input type="text" id="reseller_name" name="reseller_name" required placeholder="e.g. Star IPTV Reseller">
                        </div>

                        <div class="form-group">
                            <label for="reseller_password">Reseller Password</label>
                            <input type="password" id="reseller_password" name="reseller_password" required placeholder="••••••••">
                        </div>

                        <div class="form-group">
                            <label for="reseller_credits">Initial Activation Credits</label>
                            <input type="number" id="reseller_credits" name="reseller_credits" value="20" min="0">
                        </div>

                        <button type="submit" class="btn" style="width:100%;">Create Reseller</button>
                    </form>
                </div>

                <!-- Resellers List -->
                <div class="card">
                    <h2>👥 Active Resellers (<?= count($resellersList) ?>)</h2>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Credits</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($resellersList)): ?>
                                    <tr>
                                        <td colspan="4" style="text-align:center; padding:20px; color:var(--muted);">No resellers created yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($resellersList as $r): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($r['username']) ?></strong>
                                                <div style="font-size:11px; color:var(--muted);"><?= htmlspecialchars($r['name']) ?></div>
                                            </td>
                                            <td><span class="key-badge"><?= (int)$r['credits'] ?> Cr</span></td>
                                            <td>
                                                <span class="status-pill <?= $r['status'] === 'active' ? 'status-active' : 'status-blocked' ?>">
                                                    <?= htmlspecialchars($r['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form method="post" onsubmit="return confirm('Delete reseller <?= htmlspecialchars($r['username']) ?>?');" style="display:inline;">
                                                    <input type="hidden" name="form_type" value="delete_reseller">
                                                    <input type="hidden" name="reseller_id" value="<?= $r['id'] ?>">
                                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($activeTab === 'dns' && $role === 'admin'): ?>
            <!-- DNS Portals (Admin Only) -->
            <div class="grid-2">
                <div class="card">
                    <h2>➕ Add DNS Portal</h2>
                    <form method="post">
                        <input type="hidden" name="form_type" value="add_dns">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                        <div class="form-group">
                            <label for="dns_name">Portal Display Name</label>
                            <input type="text" id="dns_name" name="dns_name" placeholder="e.g. Dreamania 4K VIP" required>
                        </div>

                        <div class="form-group">
                            <label for="dns_url">Server Base URL</label>
                            <input type="text" id="dns_url" name="dns_url" placeholder="http://domain.com:8080" required>
                        </div>

                        <button type="submit" class="btn" style="width:100%;">Save DNS Portal</button>
                    </form>
                </div>

                <div class="card">
                    <h2>🌐 Registered DNS Portals</h2>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>URL</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dnsList as $dns): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($dns['name']) ?></strong></td>
                                        <td><code><?= htmlspecialchars($dns['url']) ?></code></td>
                                        <td>
                                            <form method="post" onsubmit="return confirm('Remove DNS?');">
                                                <input type="hidden" name="form_type" value="delete_dns">
                                                <input type="hidden" name="dns_id" value="<?= $dns['id'] ?>">
                                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($activeTab === 'lines' && $role === 'admin'): ?>
            <!-- Line Activity Tracker -->
            <div class="card">
                <h2>📈 Real-Time Line &amp; Device Activity</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>MAC Address</th>
                                <th>Action / Stream</th>
                                <th>Client IP</th>
                                <th>User Agent</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentLines)): ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:24px; color:var(--muted);">No recent line activity tracked yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentLines as $line): ?>
                                    <tr>
                                        <td><span class="mac-badge"><?= htmlspecialchars($line['mac_address']) ?></span></td>
                                        <td><span class="status-pill status-active"><?= htmlspecialchars($line['stream_type'] ?: 'Handshake') ?></span></td>
                                        <td><code><?= htmlspecialchars($line['ip_address'] ?? 'N/A') ?></code></td>
                                        <td style="font-size:11px; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= htmlspecialchars($line['user_agent'] ?? '') ?></td>
                                        <td style="font-size:11px; color:var(--muted);"><?= htmlspecialchars($line['last_active']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($activeTab === 'settings' && $role === 'admin'): ?>
            <!-- Settings & APK Upload -->
            <div class="grid-2">
                <div class="card">
                    <h2>⚙️ Global System Configuration</h2>
                    <form method="post">
                        <input type="hidden" name="form_type" value="update_settings">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                        <div class="form-group">
                            <label for="admin_password">Admin Master Password</label>
                            <input type="text" id="admin_password" name="admin_password" value="<?= htmlspecialchars($settings['admin_password'] ?? '200600') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="app_name">Application Brand Name</label>
                            <input type="text" id="app_name" name="app_name" value="<?= htmlspecialchars($settings['app_name'] ?? 'Dreamania Player') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="trial_days">Default Trial Duration (Days)</label>
                            <input type="number" id="trial_days" name="trial_days" value="<?= htmlspecialchars($settings['trial_days'] ?? '7') ?>" min="1">
                        </div>

                        <div class="form-group">
                            <label for="apk_link">APK Download URL</label>
                            <input type="url" id="apk_link" name="apk_link" value="<?= htmlspecialchars($settings['apk_link'] ?? 'https://dmibo.myskillscloud.com/download') ?>">
                        </div>

                        <button type="submit" class="btn" style="width:100%;">Save Global Settings</button>
                    </form>
                </div>

                <div class="card">
                    <h2>📲 Application APK File</h2>
                    <p style="color:var(--muted); font-size:13px; margin-bottom:16px;">
                        Upload or replace the client APK served at <code>/download</code>.
                    </p>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="form_type" value="upload_apk_file">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                        <div class="form-group">
                            <label for="apk_file">Select APK File</label>
                            <input type="file" id="apk_file" name="apk_file" accept=".apk" required>
                        </div>

                        <button type="submit" class="btn btn-secondary" style="width:100%;">Upload &amp; Replace APK</button>
                    </form>
                </div>
            </div>

        <?php endif; ?>

    <?php endif; ?>
</main>

<footer class="footer">
    &copy; <?= date('Y') ?> Dreamania Player &bull; Powered by <a href="https://www.facebook.com/dreamaniacode" target="_blank" rel="noopener" style="color:var(--accent);font-weight:bold;">DreamaniaCode</a>
</footer>

<script>
function openEditModal(device) {
    document.getElementById('edit_device_id').value = device.id;
    document.getElementById('edit_mac_address').value = device.mac_address;
    document.getElementById('edit_device_key').value = device.device_key;
    document.getElementById('edit_expire_date').value = device.expire_date;
    document.getElementById('edit_status').value = device.status || 'active';
    document.getElementById('edit_parent_control').value = device.parent_control || '0000';
    document.getElementById('edit_notes').value = device.notes || '';
    document.getElementById('editModal').classList.add('open');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
}
</script>

</body>
</html>
