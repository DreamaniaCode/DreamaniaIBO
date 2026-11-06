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
                    'trial_days', 'default_expiry', 'price', 'apk_link',
                    'paypal_email', 'paypal_me_link', 'rib_bank_name', 'rib_account_name',
                    'rib_number', 'rib_swift', 'whatsapp_number',
                    'price_client_1y', 'price_client_life',
                    'price_reseller_10', 'price_reseller_20', 'price_reseller_30', 'price_reseller_50', 'price_reseller_100',
                    'downloader_code'
                ];
                foreach ($settingsToUpdate as $sKey) {
                    if (isset($_POST[$sKey])) {
                        DB::setSetting($sKey, trim((string)$_POST[$sKey]));
                    }
                }
                $msg = "System settings & pricing updated successfully!";
                $msgType = "success";
            }

            // 11. Approve Order (Admin Only)
            elseif ($formType === 'approve_order' && $role === 'admin') {
                $orderId = (int)($_POST['order_id'] ?? 0);
                $adminNotes = trim($_POST['admin_notes'] ?? '');
                $res = DB::approveOrder($orderId, $adminNotes ?: null);
                $msg = $res['message'];
                $msgType = $res['status'] ? 'success' : 'error';
            }

            // 12. Reject Order (Admin Only)
            elseif ($formType === 'reject_order' && $role === 'admin') {
                $orderId = (int)($_POST['order_id'] ?? 0);
                $adminNotes = trim($_POST['admin_notes'] ?? '');
                DB::rejectOrder($orderId, $adminNotes ?: 'Rejected by admin');
                $msg = "Order #{$orderId} has been rejected.";
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
$pricing = DB::getPricing();
$ordersList = ($isLogged && $role === 'admin') ? DB::getAllOrders() : [];
$pendingOrdersCount = 0;
if ($role === 'admin') {
    foreach ($ordersList as $o) {
        if ($o['status'] === 'pending') {
            $pendingOrdersCount++;
        }
    }
}

$activeTab = $_GET['tab'] ?? 'playlists';
if ($role === 'reseller' && in_array($activeTab, ['dns', 'settings', 'resellers', 'lines', 'orders'])) {
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
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
<title>Dreamania Player - Middleware &amp; Reseller Portal</title>
<link rel="icon" type="image/png" href="assets/img/icon.png">

<!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/themes.css">

<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

html, body {
    overflow-x: hidden;
    width: 100%;
}

body {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    background-color: var(--bg);
    background-image: var(--bg-mesh);
    background-attachment: fixed;
    background-size: cover;
    color: var(--text);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    position: relative;
}

/* Ambient Living Mesh Orbs */
.ambient-mesh {
    position: fixed;
    top: 0; left: 0;
    width: 100vw; height: 100vh;
    pointer-events: none;
    z-index: 0;
    overflow: hidden;
}
.mesh-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    opacity: 0.38;
    animation: floatMesh 24s infinite ease-in-out alternate;
}
.orb-1 {
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(139, 92, 246, 0.45) 0%, transparent 70%);
    top: -150px; left: -100px;
    animation-duration: 22s;
}
.orb-2 {
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(6, 182, 212, 0.4) 0%, transparent 70%);
    top: 40%; right: -120px;
    animation-duration: 18s;
    animation-delay: -6s;
}
.orb-3 {
    width: 550px; height: 550px;
    background: radial-gradient(circle, rgba(245, 158, 11, 0.3) 0%, transparent 70%);
    bottom: -150px; left: 20%;
    animation-duration: 26s;
    animation-delay: -12s;
}
@keyframes floatMesh {
    0% { transform: translate(0, 0) scale(1) rotate(0deg); }
    50% { transform: translate(60px, 80px) scale(1.08) rotate(180deg); }
    100% { transform: translate(-40px, 40px) scale(0.96) rotate(360deg); }
}

/* Navbar */
.navbar {
    background: rgba(12, 10, 22, 0.75);
    backdrop-filter: blur(24px) saturate(190%);
    -webkit-backdrop-filter: blur(24px) saturate(190%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
    padding: 14px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
}
.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: inherit;
}
.brand-logo-img {
    width: 42px;
    height: 42px;
    filter: drop-shadow(0 4px 12px var(--primary-glow));
    border-radius: 10px;
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
    font-size: 10px;
    padding: 3px 8px;
    border-radius: 6px;
    font-weight: 800;
    letter-spacing: 0.5px;
    margin-left: 6px;
}
.nav-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}
.role-badge {
    background: rgba(139, 92, 246, 0.18);
    border: 1px solid rgba(139, 92, 246, 0.45);
    color: #c4b5fd;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* Mobile Hamburger Toggle */
.menu-toggle {
    display: none;
    flex-direction: column;
    justify-content: center;
    gap: 5px;
    width: 42px;
    height: 42px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    cursor: pointer;
    padding: 8px;
}
.menu-toggle span {
    display: block;
    width: 100%;
    height: 2.5px;
    background: #fff;
    border-radius: 2px;
    transition: all 0.25s ease;
}

/* Mobile Off-Canvas / Drawer Menu */
.mobile-nav-drawer {
    display: none;
    position: fixed;
    top: 71px; left: 0; right: 0;
    background: rgba(12, 10, 22, 0.95);
    backdrop-filter: blur(28px) saturate(200%);
    -webkit-backdrop-filter: blur(28px) saturate(200%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 20px 45px rgba(0, 0, 0, 0.85);
    padding: 20px;
    flex-direction: column;
    gap: 12px;
    z-index: 99;
}
.mobile-nav-drawer.open {
    display: flex;
}
.mobile-nav-drawer .btn {
    width: 100%;
    padding: 12px 16px;
    font-size: 14px;
}

/* Modern 3D & Glass Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 700;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    text-decoration: none;
    color: #fff;
    background: var(--primary-gradient);
    box-shadow: 0 4px 18px var(--primary-glow);
    position: relative;
    overflow: hidden;
}
.btn:hover {
    background: var(--primary-gradient-hover);
    transform: translateY(-2px);
    box-shadow: 0 6px 24px var(--primary-glow);
}
.btn:active {
    transform: translateY(0);
}
.btn-secondary {
    background: rgba(255, 255, 255, 0.07);
    box-shadow: inset 0 1px 1px 0 rgba(255, 255, 255, 0.12);
    color: var(--text);
    border: 1px solid rgba(255, 255, 255, 0.12);
}
.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.14);
    border-color: rgba(255, 255, 255, 0.25);
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
}
.btn-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35); }
.btn-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #000; box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35); }
.btn-danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35); }
.btn-sm { padding: 6px 12px; font-size: 11px; border-radius: 8px; }

/* Layout & Containers */
.main-container {
    width: 100%;
    max-width: 1280px;
    margin: 28px auto;
    padding: 0 20px;
    flex: 1;
    position: relative;
    z-index: 1;
}

/* Glassmorphism Cards & 3D Tilt Elements */
.card, .stat-card {
    background: rgba(18, 14, 34, 0.65);
    backdrop-filter: blur(28px) saturate(190%);
    -webkit-backdrop-filter: blur(28px) saturate(190%);
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: inset 0 1px 1px 0 rgba(255, 255, 255, 0.15), 0 20px 45px -15px rgba(0, 0, 0, 0.75);
    border-radius: 20px;
    padding: 24px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease, border-color 0.25s ease;
    transform-style: preserve-3d;
}
.card h2 {
    font-size: 19px;
    font-weight: 800;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    letter-spacing: -0.3px;
}
.card-3d:hover, .stat-card:hover {
    border-color: rgba(139, 92, 246, 0.45);
    box-shadow: inset 0 1px 1px 0 rgba(255, 255, 255, 0.25), 0 25px 50px -12px rgba(139, 92, 246, 0.25);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 18px;
    margin-bottom: 28px;
}
.stat-card {
    padding: 22px;
    display: flex;
    align-items: center;
    gap: 18px;
    margin-bottom: 0;
}
.stat-icon {
    font-size: 26px;
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.2);
}
.stat-label { font-size: 12px; color: var(--muted); text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; }
.stat-value { font-size: 26px; font-weight: 800; color: var(--text); margin-top: 2px; }

/* Navigation Tabs */
.tabs-nav {
    display: flex;
    gap: 8px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 26px;
    overflow-x: auto;
    padding-bottom: 10px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
}
.tabs-nav::-webkit-scrollbar {
    height: 4px;
}
.tabs-nav::-webkit-scrollbar-thumb {
    background: rgba(139, 92, 246, 0.3);
    border-radius: 4px;
}
.tab-link {
    padding: 10px 18px;
    font-size: 13.5px;
    font-weight: 700;
    color: var(--muted);
    text-decoration: none;
    border-radius: 12px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    white-space: nowrap;
    border: 1px solid transparent;
}
.tab-link:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.08);
}
.tab-link.active {
    background: var(--primary-gradient);
    color: #fff;
    box-shadow: 0 4px 18px var(--primary-glow);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Tables & Wrappers */
.table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 12px;
}
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
th {
    text-align: left;
    padding: 14px 16px;
    color: var(--muted);
    font-weight: 800;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: rgba(0, 0, 0, 0.2);
}
td {
    padding: 15px 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    vertical-align: middle;
}
tr:hover td {
    background: rgba(255, 255, 255, 0.03);
}

/* Badges */
.mac-badge {
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    padding: 5px 10px;
    background: rgba(139, 92, 246, 0.18);
    border: 1px solid rgba(139, 92, 246, 0.45);
    border-radius: 8px;
    color: #c4b5fd;
    font-weight: 700;
}
.key-badge {
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    padding: 5px 10px;
    background: rgba(245, 158, 11, 0.18);
    border: 1px solid rgba(245, 158, 11, 0.45);
    border-radius: 8px;
    color: #fcd34d;
    font-weight: 700;
}
.status-pill {
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-block;
}
.status-active { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); }
.status-trial { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); }
.status-expired { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); }
.status-blocked { background: rgba(100, 116, 139, 0.2); color: #94a3b8; border: 1px solid rgba(100, 116, 139, 0.4); }

/* Form Controls */
.form-group {
    margin-bottom: 18px;
}
label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 700;
    color: var(--muted);
}
input, select, textarea {
    width: 100%;
    min-height: 46px;
    padding: 12px 16px;
    background: rgba(0, 0, 0, 0.35);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    color: var(--text);
    font-size: 14.5px;
    outline: none;
    transition: all 0.2s ease;
}
input:focus, select:focus, textarea:focus {
    border-color: var(--accent);
    background: rgba(0, 0, 0, 0.5);
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
}
.grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 24px;
}

/* Modals */
.modal {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0, 0, 0, 0.82);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    z-index: 200;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.modal.open {
    display: flex;
}
.modal-content {
    background: rgba(18, 14, 34, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.16);
    border-radius: 20px;
    width: 100%;
    max-width: 520px;
    padding: 28px;
    position: relative;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.85);
}
.modal-close {
    position: absolute;
    top: 18px;
    right: 18px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 8px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--muted);
    font-size: 16px;
    cursor: pointer;
    transition: all 0.2s;
}
.modal-close:hover {
    color: #fff;
    background: rgba(239, 68, 68, 0.25);
    border-color: #ef4444;
}

/* Footer */
.footer {
    text-align: center;
    padding: 24px;
    color: var(--muted);
    font-size: 13px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    margin-top: auto;
    position: relative;
    z-index: 2;
}

/* Responsive Rules */
@media (max-width: 900px) {
    .menu-toggle { display: flex; }
    .nav-actions { display: none; }
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }
}

@media (max-width: 640px) {
    .navbar {
        padding: 12px 16px;
    }
    .brand-name {
        font-size: 17px;
    }
    .main-container {
        padding: 0 14px;
        margin: 18px auto;
    }
    .stats-grid {
        grid-template-columns: 1fr;
    }
    .card {
        padding: 18px;
        border-radius: 16px;
    }
    .modal-content {
        position: fixed;
        bottom: 0; left: 0; right: 0;
        max-width: 100%;
        max-height: 85vh;
        border-radius: 22px 22px 0 0;
        margin-top: auto;
    }
}
</style>
</head>
<body>

<!-- Ambient Living Mesh Orbs -->
<div class="ambient-mesh">
    <div class="mesh-orb orb-1"></div>
    <div class="mesh-orb orb-2"></div>
    <div class="mesh-orb orb-3"></div>
</div>

<!-- Navbar -->
<header class="navbar">
    <a href="index.php" class="brand">
        <img src="assets/img/icon.png" alt="Logo" class="brand-logo-img">
        <div>
            <span class="brand-name">Dreamania Player</span>
            <span class="brand-badge">PORTAL</span>
        </div>
    </a>

    <!-- Desktop Navigation Actions -->
    <div class="nav-actions">
        <a href="device.php" class="btn btn-secondary btn-sm" target="_blank" title="End-User Device &amp; Playlists Portal">📺 Device Portal</a>
        <a href="credits.php" class="btn btn-secondary btn-sm" target="_blank" title="Reseller Credits Portal">⚡ Reseller Credits</a>
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

    <!-- Mobile Hamburger Button -->
    <button class="menu-toggle" id="menuToggle" aria-label="Toggle Navigation" onclick="toggleMobileMenu()">
        <span></span>
        <span></span>
        <span></span>
    </button>
</header>

<!-- Mobile Navigation Drawer -->
<div class="mobile-nav-drawer" id="mobileNavDrawer">
    <?php if ($isLogged): ?>
        <div class="role-badge" style="justify-content: center; width: 100%;">
            <?= ($role === 'admin') ? '👑 Admin: Super Administrator' : '👤 Reseller: ' . htmlspecialchars($_SESSION['user_name'] ?? '') . ' (' . (int)($_SESSION['reseller_credits'] ?? 0) . ' Cr)' ?>
        </div>
    <?php endif; ?>
    <a href="device.php" class="btn btn-secondary" target="_blank">📺 End-User Device Portal</a>
    <a href="credits.php" class="btn btn-secondary" target="_blank">⚡ Reseller Credits Portal</a>
    <a href="landing.php" class="btn btn-secondary">🌐 Public Landing Page</a>
    <?php if ($apkFileExists): ?>
        <a href="download" class="btn btn-secondary">📲 Download Android APK</a>
    <?php endif; ?>
    <?php if ($isLogged): ?>
        <form method="post" style="width: 100%;">
            <input type="hidden" name="action" value="logout">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
            <button type="submit" class="btn btn-danger" style="width: 100%;">Logout</button>
        </form>
    <?php endif; ?>
</div>


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
                <div style="display:flex; justify-content:center; gap:16px; flex-wrap:wrap;">
                    <a href="device.php" style="color: var(--accent); font-size: 13px; text-decoration: none; font-weight:700;">📺 Mon Appareil (Playlists)</a>
                    <a href="landing.php" style="color: var(--muted); font-size: 13px; text-decoration: none;">🏠 Accueil</a>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Stats Bar -->
        <div class="stats-grid">
            <div class="stat-card card-3d">
                <div class="stat-icon">📺</div>
                <div>
                    <div class="stat-label">Total Devices</div>
                    <div class="stat-value"><?= count($devices) ?></div>
                </div>
            </div>
            <div class="stat-card card-3d">
                <div class="stat-icon">📑</div>
                <div>
                    <div class="stat-label">Active Playlists</div>
                    <div class="stat-value"><?= count($playlists) ?></div>
                </div>
            </div>
            <?php if ($role === 'admin'): ?>
                <div class="stat-card card-3d">
                    <div class="stat-icon">👥</div>
                    <div>
                        <div class="stat-label">Total Resellers</div>
                        <div class="stat-value"><?= count($resellersList) ?></div>
                    </div>
                </div>
                <div class="stat-card card-3d">
                    <div class="stat-icon">🌐</div>
                    <div>
                        <div class="stat-label">DNS Portals</div>
                        <div class="stat-value"><?= count($dnsList) ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="stat-card card-3d">
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
            <?php if ($role === 'reseller'): ?>
                <a href="credits.php" class="tab-link" style="color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); background: rgba(245, 158, 11, 0.08);">
                    💳 Buy Credits (<?= (int)($_SESSION['reseller_credits'] ?? 0) ?> Cr)
                </a>
            <?php endif; ?>
            <?php if ($role === 'admin'): ?>
                <a href="?tab=orders" class="tab-link <?= $activeTab === 'orders' ? 'active' : '' ?>" style="<?= $pendingOrdersCount > 0 ? 'border-color: #ef4444;' : '' ?>">
                    💰 Orders &amp; Activations
                    <?php if ($pendingOrdersCount > 0): ?>
                        <span style="background: #ef4444; color: #fff; padding: 2px 7px; border-radius: 10px; font-size: 11px; margin-left: 4px; font-weight: 800;">
                            <?= $pendingOrdersCount ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="?tab=resellers" class="tab-link <?= $activeTab === 'resellers' ? 'active' : '' ?>">👥 Resellers</a>
                <a href="?tab=dns" class="tab-link <?= $activeTab === 'dns' ? 'active' : '' ?>">🌐 DNS Portals</a>
                <a href="?tab=lines" class="tab-link <?= $activeTab === 'lines' ? 'active' : '' ?>">📈 Used Lines</a>
                <a href="?tab=settings" class="tab-link <?= $activeTab === 'settings' ? 'active' : '' ?>">⚙️ Settings &amp; Pricing</a>
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
                                            <?php
                                                $devType = strtolower($d['app_type'] ?? 'tv');
                                                $isMobileDev = ($devType === 'mobile');
                                                $devBadgeBg = $isMobileDev ? 'rgba(6,182,212,0.18)' : 'rgba(139,92,246,0.18)';
                                                $devBadgeColor = $isMobileDev ? '#06b6d4' : '#c084fc';
                                                $devBadgeBorder = $isMobileDev ? 'rgba(6,182,212,0.45)' : 'rgba(139,92,246,0.45)';
                                                $devIcon = $isMobileDev ? '📱 Mobile' : '📺 Smart TV';
                                                $devModel = !empty($d['notes']) ? htmlspecialchars($d['notes']) : '';
                                            ?>
                                            <div style="display:flex; align-items:center; gap:6px; margin-top:4px; flex-wrap:wrap;">
                                                <span style="background:<?= $devBadgeBg ?>; color:<?= $devBadgeColor ?>; border:1px solid <?= $devBadgeBorder ?>; padding:2px 8px; border-radius:6px; font-size:10.5px; font-weight:700;">
                                                    <?= $devIcon ?>
                                                </span>
                                                <span style="font-size:10.5px; color:var(--muted);">v<?= htmlspecialchars($d['app_version'] ?? '1.2.4') ?></span>
                                            </div>
                                            <?php if (!empty($devModel)): ?>
                                                <div style="font-size:10.5px; color:#cbd5e1; margin-top:2px;">🔹 <?= $devModel ?></div>
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

        <?php elseif ($activeTab === 'orders' && $role === 'admin'): ?>
            <!-- Orders & Activations Management -->
            <div class="card">
                <h2>
                    <span>💰 Orders &amp; Activations (<?= count($ordersList) ?> total, <?= $pendingOrdersCount ?> pending)</span>
                    <div style="font-size: 13px; font-weight: normal; display: flex; gap: 8px;">
                        <a href="?tab=orders" class="btn btn-sm btn-secondary">All (<?= count($ordersList) ?>)</a>
                        <a href="?tab=orders&status=pending" class="btn btn-sm" style="background:#f59e0b; color:#000;">Pending (<?= $pendingOrdersCount ?>)</a>
                    </div>
                </h2>

                <?php
                    $filterStatus = $_GET['status'] ?? '';
                    $displayOrders = $ordersList;
                    if ($filterStatus === 'pending') {
                        $displayOrders = array_filter($ordersList, fn($o) => $o['status'] === 'pending');
                    }
                ?>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Type</th>
                                <th>Target / Customer</th>
                                <th>Plan / Credits</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Reference / Proof</th>
                                <th>Status</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($displayOrders)): ?>
                                <tr>
                                    <td colspan="9" style="text-align:center; padding:30px; color:var(--muted);">
                                        No orders found matching criteria.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($displayOrders as $ord): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?= (int)$ord['id'] ?></strong>
                                            <div style="font-size:11px; color:var(--muted);"><?= htmlspecialchars(substr($ord['created_at'], 0, 16)) ?></div>
                                        </td>
                                        <td>
                                            <?php if ($ord['order_type'] === 'client'): ?>
                                                <span class="status-pill status-active">📺 End-User TV</span>
                                            <?php else: ?>
                                                <span class="status-pill status-trial">⚡ Reseller Pack</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($ord['order_type'] === 'client'): ?>
                                                <span class="mac-badge"><?= htmlspecialchars($ord['mac_address'] ?? '') ?></span>
                                                <?php if (!empty($ord['customer_name'])): ?>
                                                    <div style="font-size:12px; margin-top:2px;">
                                                        <?= htmlspecialchars($ord['customer_name']) ?>
                                                        <?= !empty($ord['customer_phone']) ? ' (' . htmlspecialchars($ord['customer_phone']) . ')' : '' ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <strong><?= htmlspecialchars($ord['reseller_name'] ?: ($ord['reseller_username'] ?? 'Reseller #' . $ord['reseller_id'])) ?></strong>
                                                <div style="font-size:11px; color:var(--muted);">User: <?= htmlspecialchars($ord['reseller_username'] ?? '') ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($ord['order_type'] === 'client'): ?>
                                                <strong><?= $ord['plan'] === 'sub_lifetime' ? '⭐ Lifetime VIP' : '1 Year Activation' ?></strong>
                                            <?php else: ?>
                                                <strong style="color:#fbbf24;">+<?= (int)$ord['credits'] ?> Credits (Years)</strong>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong style="color:var(--accent);"><?= htmlspecialchars((string)$ord['amount_dh']) ?> DH</strong>
                                        </td>
                                        <td>
                                            <span style="text-transform:uppercase; font-size:11px; font-weight:700; padding:2px 6px; border-radius:4px; background:rgba(255,255,255,0.06);">
                                                <?= htmlspecialchars($ord['payment_method']) ?>
                                            </span>
                                            <?php if (!empty($ord['transaction_ref'])): ?>
                                                <div style="font-size:11px; color:var(--muted); font-family:monospace; margin-top:2px;">
                                                    Ref: <?= htmlspecialchars($ord['transaction_ref']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($ord['proof_file']) && file_exists(__DIR__ . '/' . $ord['proof_file'])): ?>
                                                <a href="<?= htmlspecialchars($ord['proof_file']) ?>" target="_blank" class="btn btn-sm btn-secondary" style="font-size:11px; padding:3px 8px;">
                                                    📎 View Receipt
                                                </a>
                                            <?php else: ?>
                                                <span style="font-size:11px; color:var(--muted);">No file</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($ord['status'] === 'approved'): ?>
                                                <span class="status-pill status-active">✅ Approved</span>
                                            <?php elseif ($ord['status'] === 'rejected'): ?>
                                                <span class="status-pill status-expired">❌ Rejected</span>
                                            <?php else: ?>
                                                <span class="status-pill status-trial">⏳ Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align:right; white-space:nowrap;">
                                            <?php if ($ord['status'] === 'pending'): ?>
                                                <form method="post" style="display:inline;" onsubmit="return confirm('Approve order #<?= (int)$ord['id'] ?> and activate immediately?');">
                                                    <input type="hidden" name="form_type" value="approve_order">
                                                    <input type="hidden" name="order_id" value="<?= (int)$ord['id'] ?>">
                                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                                    <button type="submit" class="btn btn-sm" style="background:#10b981; color:#fff; border:none;" title="Approve &amp; Activate">
                                                        ✅ Approve
                                                    </button>
                                                </form>
                                                <form method="post" style="display:inline; margin-left:4px;" onsubmit="return confirm('Reject order #<?= (int)$ord['id'] ?>?');">
                                                    <input type="hidden" name="form_type" value="reject_order">
                                                    <input type="hidden" name="order_id" value="<?= (int)$ord['id'] ?>">
                                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                                    <button type="submit" class="btn btn-sm btn-secondary" style="background:rgba(239,68,68,0.2); color:#f87171; border-color:#ef4444;" title="Reject">
                                                        ❌
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span style="font-size:11px; color:var(--muted);">Completed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($activeTab === 'settings' && $role === 'admin'): ?>
            <!-- Settings & Configuration (All Tabs) -->
            <form method="post">
                <input type="hidden" name="form_type" value="update_settings">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                <div class="card" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(139, 92, 246, 0.15)); border: 1px solid rgba(16, 185, 129, 0.4); margin-bottom: 24px;">
                    <div style="display:flex; align-items:center; gap:16px;">
                        <span style="font-size:32px;">💳</span>
                        <div>
                            <h3 style="color:#fff; margin:0 0 4px; font-size:18px;">Configuration des Paiements (RIB Bancaire &amp; PayPal)</h3>
                            <p style="color:#cbd5e1; margin:0; font-size:13px;">
                                Renseignez ici votre <strong>RIB bancaire marocain (24 chiffres)</strong> pour les virements au Maroc, et votre <strong>adresse email PayPal</strong> pour les paiements internationaux par carte ou solde PayPal.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid-2">
                    <!-- 1. Global System Settings -->
                    <div class="card">
                        <h2>⚙️ Global System Configuration</h2>
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
                            <input type="url" id="apk_link" name="apk_link" value="<?= htmlspecialchars($settings['apk_link'] ?? 'https://dm.lumineasy.com/download') ?>">
                        </div>
                    </div>

                    <!-- 2. Banking & PayPal Configuration -->
                    <div class="card">
                        <h2>🏦 Bank Details (RIB) &amp; PayPal Gateways</h2>
                        <div class="form-group">
                            <label for="rib_bank_name">Moroccan Bank Name</label>
                            <input type="text" id="rib_bank_name" name="rib_bank_name" value="<?= htmlspecialchars($settings['rib_bank_name'] ?? 'CIH BANK') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="rib_account_name">Bank Account Holder (Titulaire)</label>
                            <input type="text" id="rib_account_name" name="rib_account_name" value="<?= htmlspecialchars($settings['rib_account_name'] ?? 'DREAMANIA MEDIA') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="rib_number">24-Digit RIB Number</label>
                            <input type="text" id="rib_number" name="rib_number" value="<?= htmlspecialchars($settings['rib_number'] ?? '230 780 4567890123456789 12') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="rib_swift">SWIFT / BIC Code</label>
                            <input type="text" id="rib_swift" name="rib_swift" value="<?= htmlspecialchars($settings['rib_swift'] ?? 'CIHMMAMC') ?>">
                        </div>
                        <div class="form-group">
                            <label for="paypal_email">PayPal Email Address</label>
                            <input type="email" id="paypal_email" name="paypal_email" value="<?= htmlspecialchars($settings['paypal_email'] ?? 'contact@lumineasy.com') ?>">
                        </div>
                        <div class="form-group">
                            <label for="paypal_me_link">PayPal.me Direct Link</label>
                            <input type="url" id="paypal_me_link" name="paypal_me_link" value="<?= htmlspecialchars($settings['paypal_me_link'] ?? 'https://paypal.me/dreamaniacode') ?>">
                        </div>
                        <div class="form-group">
                            <label for="whatsapp_number">Support WhatsApp Number (with country code)</label>
                            <input type="text" id="whatsapp_number" name="whatsapp_number" value="<?= htmlspecialchars($settings['whatsapp_number'] ?? '+212600000000') ?>">
                        </div>
                    </div>

                    <!-- 3. Pricing Configuration (Client & Reseller) -->
                    <div class="card">
                        <h2>💵 Pricing Configuration (Moroccan Dirham - DH)</h2>
                        <h4 style="color:var(--accent); margin:12px 0 6px;">End-User (Particulier) Pricing</h4>
                        <div class="form-group">
                            <label for="price_client_1y">1 Year Client Activation (DH)</label>
                            <input type="number" id="price_client_1y" name="price_client_1y" value="<?= htmlspecialchars($settings['price_client_1y'] ?? '50') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="price_client_life">Lifetime VIP Client Activation (DH)</label>
                            <input type="number" id="price_client_life" name="price_client_life" value="<?= htmlspecialchars($settings['price_client_life'] ?? '150') ?>" required>
                        </div>

                        <h4 style="color:#fbbf24; margin:16px 0 6px;">Reseller Wholesale Credit Packs (1 Credit = 1 Year)</h4>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="form-group">
                                <label for="price_reseller_10">10 Credits Pack (DH) <span style="color:#fbbf24; font-size:11px;">(Default: 150 DH / €15)</span></label>
                                <input type="number" id="price_reseller_10" name="price_reseller_10" value="<?= htmlspecialchars($settings['price_reseller_10'] ?? '150') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="price_reseller_20">20 Credits Pack (DH)</label>
                                <input type="number" id="price_reseller_20" name="price_reseller_20" value="<?= htmlspecialchars($settings['price_reseller_20'] ?? '180') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="price_reseller_30">30 Credits Pack (DH)</label>
                                <input type="number" id="price_reseller_30" name="price_reseller_30" value="<?= htmlspecialchars($settings['price_reseller_30'] ?? '240') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="price_reseller_50">50 Credits Pack (DH)</label>
                                <input type="number" id="price_reseller_50" name="price_reseller_50" value="<?= htmlspecialchars($settings['price_reseller_50'] ?? '350') ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="price_reseller_100">100 Credits Pack (DH)</label>
                            <input type="number" id="price_reseller_100" name="price_reseller_100" value="<?= htmlspecialchars($settings['price_reseller_100'] ?? '600') ?>" required>
                        </div>
                    </div>

                    <!-- Live Pricing Engine Preview -->
                    <div class="card" style="margin-top: 18px; background: rgba(18, 14, 34, 0.7); border: 1px solid rgba(139, 92, 246, 0.3);">
                        <h2 style="font-size: 16px; color: var(--accent); margin-bottom: 10px;">
                            🔄 Live Dynamic Pricing Engine Active Everywhere
                        </h2>
                        <p style="color: var(--muted); font-size: 12.5px; margin-bottom: 14px;">
                            Any price modified above updates across the public Landing page, End-user Device portal, and Reseller Credits wholesale portal automatically.
                        </p>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; font-size: 12.5px;">
                            <div style="background: rgba(0,0,0,0.3); padding: 10px 14px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.06);">
                                <span style="color: var(--muted);">1 Year Client:</span><br>
                                <strong style="color: #fff; font-size: 14px;"><?= $pricing['client_1y_label'] ?></strong>
                            </div>
                            <div style="background: rgba(0,0,0,0.3); padding: 10px 14px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.06);">
                                <span style="color: var(--muted);">Lifetime VIP:</span><br>
                                <strong style="color: #fbbf24; font-size: 14px;"><?= $pricing['client_life_label'] ?></strong>
                            </div>
                            <?php foreach ($pricing['reseller_packs'] as $pCredits => $pack): ?>
                                <div style="background: rgba(0,0,0,0.3); padding: 10px 14px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.06);">
                                    <span style="color: var(--muted);"><?= $pCredits ?> Credits Pack:</span><br>
                                    <strong style="color: #fff; font-size: 14px;"><?= $pack['label'] ?></strong><br>
                                    <span style="color: #34d399; font-size: 11px; font-weight: 700;"><?= $pack['rate_label'] ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Downloader Code Configuration -->
                    <div class="card" style="margin-top: 18px; border: 1px solid rgba(245, 158, 11, 0.4);">
                        <h2 style="font-size: 16px; color: #fbbf24;">
                            🔥 AFTVnews Downloader App Integration (Fire TV &amp; Android TV)
                        </h2>
                        <div class="form-group" style="margin-top: 12px;">
                            <label for="downloader_code">Downloader Short Code</label>
                            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                                <input type="text" id="downloader_code" name="downloader_code" value="<?= htmlspecialchars($settings['downloader_code'] ?? '7454701') ?>" style="font-family: 'JetBrains Mono', monospace; font-size: 18px; font-weight: 800; color: #fbbf24; max-width: 200px;" required>
                                <div style="font-size: 13px; color: var(--muted);">
                                    Clients enter this code directly in the Downloader app on their TV.<br>
                                    Short link: <code style="color:#fbbf24;">go.aftvnews.com/<?= htmlspecialchars($settings['downloader_code'] ?? '7454701') ?></code> or <code style="color:#fbbf24;">https://dm.lumineasy.com/<?= htmlspecialchars($settings['downloader_code'] ?? '7454701') ?></code>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Save Button & APK Upload -->
                    <div>
                        <button type="submit" class="btn" style="width:100%; margin-bottom:20px; padding:16px; font-size:16px;">
                            💾 Save All Settings &amp; Pricing Changes
                        </button>
                    </div>
                </div>
            </form>

            <div class="card" style="margin-top:20px;">
                <h2>📲 Application APK File</h2>
                <div style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.4); border-radius: 12px; padding: 14px 18px; margin-bottom: 16px; display: flex; align-items: center; gap: 14px;">
                    <span style="font-size: 26px;">🔥</span>
                    <div>
                        <div style="font-size: 11px; text-transform: uppercase; font-weight: 800; color: #fbbf24;">Downloader App Short Code</div>
                        <div style="font-size: 14px; color: #fff; margin-top: 2px;">
                            Users can install on Fire TV Stick &amp; Android TV by entering code: <strong style="font-family: 'JetBrains Mono', monospace; font-size: 18px; color: #fbbf24; letter-spacing: 1.5px;"><?= htmlspecialchars($settings['downloader_code'] ?? '7454701') ?></strong>
                        </div>
                    </div>
                </div>
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

<script>
function toggleMobileMenu() {
    const drawer = document.getElementById('mobileNavDrawer');
    if (drawer) {
        drawer.classList.toggle('open');
    }
}

// 3D Card Interactive Tilt Micro-Engine
document.addEventListener('DOMContentLoaded', () => {
    const tiltElements = document.querySelectorAll('.card-3d, .stat-card');
    tiltElements.forEach(card => {
        card.addEventListener('mousemove', e => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = ((y - centerY) / centerY) * -5;
            const rotateY = ((x - centerX) / centerX) * 5;
            card.style.transform = `perspective(1000px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) translateY(-4px)`;
            card.style.setProperty('--mouse-x', `${x}px`);
            card.style.setProperty('--mouse-y', `${y}px`);
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0)';
        });
    });
});
</script>
</body>
</html>
