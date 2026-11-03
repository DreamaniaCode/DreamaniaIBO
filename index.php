<?php
/**
 * Dreamania IBO Pro - Web Administration Portal
 * Sleek, high-performance IPTV control panel with 10 modern themes.
 */

declare(strict_types=1);
session_start();

require_once __DIR__ . '/db.php';

// Available Themes Definition
$themes = [
    'purple'    => [
        'name'    => 'Royal Purple',
        'badge'   => 'Default',
        'icon'    => '🟣',
        'desc'    => 'Dreamania signature violet & electric purple',
        'primary' => '#9333ea',
        'card'    => '#120d24',
        'bg'      => '#090614',
        'accent'  => '#c084fc',
    ],
    'midnight'  => [
        'name'    => 'Obsidian Crimson',
        'badge'   => 'Classic',
        'icon'    => '🔴',
        'desc'    => 'Obsidian black with deep crimson highlights',
        'primary' => '#e11d48',
        'card'    => '#111827',
        'bg'      => '#0b0f19',
        'accent'  => '#fb7185',
    ],
    'emerald'   => [
        'name'    => 'Cyber Matrix',
        'badge'   => 'Tech',
        'icon'    => '🟢',
        'desc'    => 'Cyberpunk matrix emerald & glowing mint',
        'primary' => '#059669',
        'card'    => '#091f16',
        'bg'      => '#04120c',
        'accent'  => '#34d399',
    ],
    'sapphire'  => [
        'name'    => 'Oceanic Sapphire',
        'badge'   => 'Vivid',
        'icon'    => '🔵',
        'desc'    => 'Deep oceanic navy & vivid sapphire blue',
        'primary' => '#2563eb',
        'card'    => '#0b1a30',
        'bg'      => '#050e1c',
        'accent'  => '#60a5fa',
    ],
    'cyberpunk' => [
        'name'    => 'Neo Synthwave',
        'badge'   => 'Vibrant',
        'icon'    => '💖',
        'desc'    => 'Synthwave hot pink & electric cyan glow',
        'primary' => '#ec4899',
        'card'    => '#160c2b',
        'bg'      => '#0c0617',
        'accent'  => '#22d3ee',
    ],
    'amber'     => [
        'name'    => 'Sunset Gold',
        'badge'   => 'Warm',
        'icon'    => '🟡',
        'desc'    => 'Warm amber, golden honey & dusk highlights',
        'primary' => '#d97706',
        'card'    => '#1c1308',
        'bg'      => '#100b05',
        'accent'  => '#fbbf24',
    ],
    'aurora'    => [
        'name'    => 'Glacial Aurora',
        'badge'   => 'Cool',
        'icon'    => '🌌',
        'desc'    => 'Northern lights teal & indigo space glow',
        'primary' => '#0891b2',
        'card'    => '#0c1e2b',
        'bg'      => '#061118',
        'accent'  => '#818cf8',
    ],
    'stealth'   => [
        'name'    => 'Titanium Stealth',
        'badge'   => 'Clean',
        'icon'    => '⚪',
        'desc'    => 'Minimalist monochrome titanium & silver slate',
        'primary' => '#52525b',
        'card'    => '#141418',
        'bg'      => '#0a0a0c',
        'accent'  => '#f4f4f5',
    ],
    'rosegold'  => [
        'name'    => 'Imperial Rose',
        'badge'   => 'Luxe',
        'icon'    => '🌸',
        'desc'    => 'Lustrous rose gold & burgundy copper',
        'primary' => '#e11d48',
        'card'    => '#1f1019',
        'bg'      => '#12090e',
        'accent'  => '#fda4af',
    ],
    'carbon'    => [
        'name'    => 'OLED Ultra Black',
        'badge'   => 'OLED',
        'icon'    => '⚡',
        'desc'    => 'True zero pitch black with ultraviolet beam',
        'primary' => '#7c3aed',
        'card'    => '#08080a',
        'bg'      => '#000000',
        'accent'  => '#d8b4fe',
    ],
];
$allowedThemes = array_keys($themes);

// Resolve Active Theme (Cookie -> DB -> 'purple')
$cookieTheme = $_COOKIE['dreamania_theme'] ?? null;
$dbTheme = DB::getSetting('panel_theme', 'purple');
$currentTheme = ($cookieTheme && in_array($cookieTheme, $allowedThemes, true))
    ? $cookieTheme
    : (in_array($dbTheme, $allowedThemes, true) ? $dbTheme : 'purple');

// Quick theme switcher via GET
if (isset($_GET['set_theme']) && in_array($_GET['set_theme'], $allowedThemes, true)) {
    $selTheme = $_GET['set_theme'];
    setcookie('dreamania_theme', $selTheme, time() + (86400 * 365), '/');
    if (!empty($_SESSION['admin_logged'])) {
        DB::setSetting('panel_theme', $selTheme);
    }
    $targetTab = isset($_GET['tab']) ? '?tab=' . urlencode($_GET['tab']) : '';
    header("Location: index.php" . $targetTab);
    exit;
}

// Handle CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

$msg = '';
$msgType = '';

// Handle Logout
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    $_SESSION['admin_logged'] = false;
    session_destroy();
    header("Location: index.php");
    exit;
}

// Handle Login
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf'] ?? '')) {
        $msg = "Invalid session token. Please try again.";
        $msgType = "error";
    } else {
        $enteredPass = (string)($_POST['access_password'] ?? '');
        $savedPass = DB::getSetting('admin_password', '200600');
        if ($enteredPass === $savedPass) {
            $_SESSION['admin_logged'] = true;
            header("Location: index.php");
            exit;
        } else {
            $msg = "Incorrect password. Please try again.";
            $msgType = "error";
        }
    }
}

$isLogged = !empty($_SESSION['admin_logged']);

// Handle Authenticated Actions
if ($isLogged && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf'] ?? '')) {
        $msg = "Session verification failed.";
        $msgType = "error";
    } else {
        $formType = $_POST['form_type'] ?? '';

        try {
            if ($formType === 'ibo') {
                $dnsId = (int)($_POST['dns_id'] ?? 0);
                $mac = trim($_POST['mac'] ?? '');
                $username = trim($_POST['username'] ?? '');
                $password = trim($_POST['password'] ?? '');

                if ($dnsId <= 0 || empty($mac) || empty($username) || empty($password)) {
                    throw new Exception("All fields are required for IBO user.");
                }

                DB::addIboUser($dnsId, $mac, $username, $password);
                $msg = "IBO user added successfully for MAC: " . htmlspecialchars(DB::normalizeMac($mac));
                $msgType = "success";
            } elseif ($formType === 'm3u') {
                $mac = trim($_POST['mac_address'] ?? '');
                $name = trim($_POST['m3u_name'] ?? '');
                $m3uUrl = trim($_POST['m3u'] ?? '');

                if (empty($mac) || empty($name) || empty($m3uUrl)) {
                    throw new Exception("All fields are required for M3U user.");
                }

                DB::addM3uUser($mac, $name, $m3uUrl);
                $msg = "M3U user added successfully for MAC: " . htmlspecialchars(DB::normalizeMac($mac));
                $msgType = "success";
            } elseif ($formType === 'delete_playlist') {
                $pId = (int)($_POST['playlist_id'] ?? 0);
                if ($pId > 0) {
                    DB::deletePlaylist($pId);
                    $msg = "Playlist deleted successfully.";
                    $msgType = "success";
                }
            } elseif ($formType === 'add_dns') {
                $name = trim($_POST['dns_name'] ?? '');
                $url = trim($_POST['dns_url'] ?? '');
                if (!empty($name) && !empty($url)) {
                    DB::addDns($name, $url);
                    $msg = "DNS Server added successfully.";
                    $msgType = "success";
                }
            } elseif ($formType === 'delete_dns') {
                $dId = (int)($_POST['dns_id'] ?? 0);
                if ($dId > 0) {
                    DB::deleteDns($dId);
                    $msg = "DNS Server removed.";
                    $msgType = "success";
                }
            } elseif ($formType === 'upload_apk_file') {
                if (isset($_FILES['apk_file']) && $_FILES['apk_file']['error'] === UPLOAD_ERR_OK) {
                    $dest = __DIR__ . '/data/app.apk';
                    if (move_uploaded_file($_FILES['apk_file']['tmp_name'], $dest)) {
                        $sizeMb = round(filesize($dest) / (1024 * 1024), 2);
                        $msg = "APK file uploaded successfully! Total size: {$sizeMb} MB";
                        $msgType = "success";
                    } else {
                        throw new Exception("Could not move uploaded APK to data/app.apk. Check directory permissions.");
                    }
                } else {
                    $errCode = $_FILES['apk_file']['error'] ?? 'Unknown';
                    throw new Exception("Upload error: code {$errCode}");
                }
            } elseif ($formType === 'set_active_theme') {
                $themeToSet = trim($_POST['theme_name'] ?? '');
                if (in_array($themeToSet, $allowedThemes, true)) {
                    DB::setSetting('panel_theme', $themeToSet);
                    setcookie('dreamania_theme', $themeToSet, time() + (86400 * 365), '/');
                    $currentTheme = $themeToSet;
                    $msg = "Theme changed to " . htmlspecialchars($themes[$themeToSet]['name']) . " and saved as default!";
                    $msgType = "success";
                }
            } elseif ($formType === 'save_settings') {
                if (!empty($_POST['admin_password'])) {
                    DB::setSetting('admin_password', trim($_POST['admin_password']));
                }
                if (!empty($_POST['panel_theme']) && in_array($_POST['panel_theme'], $allowedThemes, true)) {
                    DB::setSetting('panel_theme', trim($_POST['panel_theme']));
                    setcookie('dreamania_theme', trim($_POST['panel_theme']), time() + (86400 * 365), '/');
                    $currentTheme = trim($_POST['panel_theme']);
                }
                DB::setSetting('note_title', trim($_POST['note_title'] ?? ''));
                DB::setSetting('note_content', trim($_POST['note_content'] ?? ''));
                DB::setSetting('default_expiry', trim($_POST['default_expiry'] ?? '2030-01-01'));
                DB::setSetting('app_version', trim($_POST['app_version'] ?? '1.0.0'));
                DB::setSetting('apk_link', trim($_POST['apk_link'] ?? ''));
                $msg = "Settings updated successfully.";
                $msgType = "success";
            }
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $msgType = "error";
        }
    }
}

// Data for views
$dnsList = $isLogged ? DB::getAllDns() : [];
$playlists = $isLogged ? DB::getAllPlaylists() : [];
$devices = $isLogged ? DB::getAllDevices() : [];
$settings = $isLogged ? DB::getAllSettings() : [];

$activeTab = $_GET['tab'] ?? 'add';

// APK File Status Check
$apkPath = __DIR__ . '/data/app.apk';
if (!file_exists($apkPath) && file_exists(__DIR__ . '/DreamaniaIBOPro.apk')) {
    $apkPath = __DIR__ . '/DreamaniaIBOPro.apk';
}
$apkFileExists = file_exists($apkPath) && filesize($apkPath) > 100000;
$apkFileSize = $apkFileExists ? round(filesize($apkPath) / (1024 * 1024), 2) : 0;
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($currentTheme) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dreamania IBO Pro - Enterprise IPTV Middleware</title>
<link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
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
    transition: background-color 0.3s ease, color 0.3s ease;
}

/* Navbar */
.navbar {
    background: rgba(14, 10, 26, 0.88);
    backdrop-filter: blur(14px);
    border-bottom: 1px solid var(--border);
    padding: 12px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 50;
    transition: border-color 0.3s ease;
}
.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: inherit;
}
.brand-logo-img {
    width: 38px;
    height: 38px;
    filter: drop-shadow(0 4px 10px var(--primary-glow));
    transition: transform 0.25s ease;
}
.brand:hover .brand-logo-img {
    transform: scale(1.08) rotate(-3deg);
}
.brand-name {
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -0.5px;
    color: var(--text);
}
.brand-badge {
    background: var(--badge-bg);
    color: var(--badge-color);
    border: 1px solid var(--badge-border);
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 6px;
    font-weight: 700;
    letter-spacing: 0.5px;
    margin-left: 6px;
}
.nav-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}
.theme-select-wrapper {
    position: relative;
    display: inline-flex;
    align-items: center;
}
.theme-select {
    padding: 7px 12px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--card);
    color: var(--text);
    cursor: pointer;
    outline: none;
    transition: border-color 0.2s;
}
.theme-select:focus {
    border-color: var(--border-focus);
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 9px 18px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.25s ease;
    text-decoration: none;
    color: #fff;
    background: var(--primary-gradient);
    box-shadow: 0 4px 14px var(--primary-glow);
}
.btn:hover {
    background: var(--primary-gradient-hover);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px var(--primary-glow);
}
.btn-secondary {
    background: rgba(255, 255, 255, 0.05);
    box-shadow: none;
    color: var(--muted);
    border: 1px solid var(--border);
}
.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.12);
    color: var(--text);
    transform: none;
    box-shadow: none;
    border-color: var(--accent);
}
.btn-danger {
    background: #e11d48;
    box-shadow: 0 2px 8px rgba(225, 29, 72, 0.3);
}
.btn-danger:hover {
    background: #be123c;
}
.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 6px;
}

/* Layout */
.main-container {
    width: 100%;
    max-width: 1140px;
    margin: 24px auto;
    padding: 0 16px;
    flex: 1;
}

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 18px 20px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    transition: border-color 0.3s;
}
.stat-label {
    font-size: 13px;
    color: var(--muted);
    font-weight: 500;
}
.stat-value {
    font-size: 28px;
    font-weight: 800;
    color: var(--text);
}

/* Tabs */
.tabs-nav {
    display: flex;
    gap: 8px;
    background: var(--card);
    border: 1px solid var(--border);
    padding: 6px;
    border-radius: 10px;
    margin-bottom: 24px;
    overflow-x: auto;
}
.tab-link {
    padding: 9px 18px;
    font-size: 14px;
    font-weight: 600;
    color: var(--muted);
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.2s ease;
    white-space: nowrap;
}
.tab-link:hover {
    color: #fff;
    background: var(--badge-bg);
}
.tab-link.active {
    color: #fff;
    background: var(--primary-gradient);
    box-shadow: 0 3px 12px var(--primary-glow);
}

/* Cards & Grid */
.grid-2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}
@media (max-width: 800px) {
    .grid-2 { grid-template-columns: 1fr; }
}
.card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 24px;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
    transition: border-color 0.3s, background 0.3s;
}
.card h2 {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 8px;
}
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
input[type=text], input[type=password], input[type=url], input[type=file], select, textarea {
    width: 100%;
    padding: 10px 14px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: rgba(0, 0, 0, 0.25);
    color: var(--text);
    font-size: 14px;
    outline: none;
    transition: border 0.2s ease, box-shadow 0.2s ease;
}
input:focus, select:focus, textarea:focus {
    border-color: var(--border-focus);
    box-shadow: 0 0 0 3px var(--primary-glow);
}

/* Alerts */
.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.alert-success { background: #064e3b; color: #a7f3d0; border: 1px solid #059669; }
.alert-error { background: #4c0519; color: #fecdd3; border: 1px solid #e11d48; }

/* Tables */
.table-wrapper {
    overflow-x: auto;
    border: 1px solid var(--border);
    border-radius: 12px;
}
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}
th {
    background: rgba(255, 255, 255, 0.04);
    padding: 12px 14px;
    font-weight: 600;
    color: var(--muted);
    border-bottom: 1px solid var(--border);
}
td {
    padding: 12px 14px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}
tr:hover td {
    background: var(--card-hover);
}

/* Badges */
.badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}
.badge-ibo { background: var(--badge-bg); color: var(--badge-color); border: 1px solid var(--badge-border); }
.badge-m3u { background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.4); }
.badge-active { background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.4); }
.badge-missing { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.4); }
.mac-badge {
    font-family: monospace;
    font-size: 13px;
    color: var(--accent);
    background: var(--badge-bg);
    padding: 2px 7px;
    border-radius: 4px;
    border: 1px solid var(--badge-border);
}

/* Theme Gallery */
.theme-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}
.theme-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}
.theme-card:hover {
    transform: translateY(-2px);
    border-color: var(--accent);
}
.theme-card.active {
    border: 2px solid var(--primary);
    box-shadow: 0 0 20px var(--primary-glow);
}
.theme-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.theme-title {
    font-size: 15px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
}
.swatches {
    display: flex;
    gap: 8px;
    align-items: center;
}
.swatch {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.25);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
}
.theme-desc {
    font-size: 12px;
    color: var(--muted);
    line-height: 1.4;
    min-height: 34px;
}

/* ======================================================== */
/* High-End Redesigned Login Page UI                        */
/* ======================================================== */
.login-wrapper {
    min-height: calc(100vh - 120px);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 16px;
}
.login-container {
    width: 100%;
    max-width: 440px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.login-header {
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}
.login-logo-aura {
    position: relative;
    width: 92px;
    height: 92px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.login-logo-aura::before {
    content: '';
    position: absolute;
    inset: -12px;
    background: var(--primary-glow);
    filter: blur(28px);
    border-radius: 50%;
    opacity: 0.85;
    animation: auraPulse 3.5s ease-in-out infinite alternate;
}
@keyframes auraPulse {
    from { transform: scale(0.9); opacity: 0.55; }
    to { transform: scale(1.18); opacity: 0.95; }
}
.login-logo-img {
    width: 82px;
    height: 82px;
    position: relative;
    z-index: 1;
    filter: drop-shadow(0 8px 24px rgba(0, 0, 0, 0.6));
}
.login-title {
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.5px;
}
.login-title span {
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.login-subtitle {
    font-size: 13px;
    color: var(--muted);
    max-width: 320px;
    line-height: 1.5;
}
.login-glass-card {
    background: var(--card);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 28px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    position: relative;
    overflow: hidden;
}
.login-glass-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--primary-gradient);
}
.input-with-icon {
    position: relative;
    display: flex;
    align-items: center;
}
.input-icon {
    position: absolute;
    left: 14px;
    color: var(--muted);
    display: flex;
    align-items: center;
    pointer-events: none;
    font-size: 16px;
}
.input-with-icon input {
    padding-left: 42px;
    padding-right: 42px;
    font-size: 15px;
    height: 46px;
}
.password-toggle {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    color: var(--muted);
    cursor: pointer;
    font-size: 14px;
    padding: 4px;
}
.password-toggle:hover {
    color: var(--text);
}
.trust-badges {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    margin-top: 18px;
}
.trust-badge-item {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 8px 10px;
    font-size: 11px;
    font-weight: 600;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 6px;
}
.client-download-box {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}
.theme-preview-bar {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 6px;
    padding: 8px 12px;
    background: rgba(0, 0, 0, 0.35);
    border-radius: 30px;
    border: 1px solid var(--border);
}
.theme-pill-btn {
    background: transparent;
    border: 1px solid transparent;
    padding: 4px 10px;
    border-radius: 20px;
    color: var(--muted);
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 5px;
}
.theme-pill-btn:hover, .theme-pill-btn.active {
    background: var(--badge-bg);
    border-color: var(--badge-border);
    color: #fff;
}

/* Footer */
.footer {
    text-align: center;
    padding: 20px;
    color: var(--muted);
    font-size: 13px;
    border-top: 1px solid var(--border);
    margin-top: auto;
}
.footer a { color: var(--accent); text-decoration: none; }
</style>
</head>
<body>

<!-- Navbar -->
<header class="navbar">
    <a href="index.php" class="brand">
        <img src="assets/img/logo.svg" alt="Dreamania Logo" class="brand-logo-img">
        <div>
            <span class="brand-name">Dreamania IBO Pro</span>
            <span class="brand-badge">PRO V10</span>
        </div>
    </a>
    <div class="nav-actions">
        <!-- Theme Switcher Dropdown -->
        <div class="theme-select-wrapper">
            <select class="theme-select" id="themeQuickSelect" onchange="applyTheme(this.value)" title="Choose Theme">
                <?php foreach ($themes as $tKey => $t): ?>
                    <option value="<?= $tKey ?>" <?= $tKey === $currentTheme ? 'selected' : '' ?>>
                        <?= $t['icon'] ?> <?= htmlspecialchars($t['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($apkFileExists): ?>
            <a href="download" class="btn btn-secondary btn-sm" title="Download APK for TV/Mobile">📲 Download APK</a>
        <?php endif; ?>

        <?php if ($isLogged): ?>
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
        <div class="alert alert-<?= $msgType ?>">
            <span><?= htmlspecialchars($msg) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!$isLogged): ?>
        <!-- ======================================================== -->
        <!-- Redesigned High-End Login UI                             -->
        <!-- ======================================================== -->
        <div class="login-wrapper">
            <div class="login-container">
                <div class="login-header">
                    <div class="login-logo-aura">
                        <img src="assets/img/logo.svg" alt="Dreamania Logo" class="login-logo-img">
                    </div>
                    <h1 class="login-title">DREAMANIA <span>IBO PRO</span></h1>
                    <p class="login-subtitle">Enterprise IPTV Middleware & Device Control System</p>
                </div>

                <div class="login-glass-card">
                    <form method="post">
                        <input type="hidden" name="action" value="login">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="access_password">Portal Master Key</label>
                            <div class="input-with-icon">
                                <span class="input-icon">🔒</span>
                                <input type="password" id="access_password" name="access_password" placeholder="Enter password (default: 200600)" required autofocus>
                                <button type="button" class="password-toggle" onclick="togglePasswordVisibility()" title="Show/Hide Password">👁️</button>
                            </div>
                        </div>

                        <button type="submit" class="btn" style="width: 100%; height: 46px; font-size: 15px;">
                            Unlock Management Portal ➔
                        </button>
                    </form>

                    <div class="trust-badges">
                        <div class="trust-badge-item">⚡ Sub-ms JSON API</div>
                        <div class="trust-badge-item">🛡️ AES-256 Encrypted</div>
                        <div class="trust-badge-item">🌐 Smart DNS Cluster</div>
                        <div class="trust-badge-item">📺 Android TV & Mobile</div>
                    </div>
                </div>

                <!-- Client APK Download Box -->
                <div class="client-download-box">
                    <div style="text-align: left;">
                        <div style="font-weight: 700; font-size: 14px; color: var(--text);">Client App Available</div>
                        <div style="font-size: 12px; color: var(--muted);">Dreamania IBO Pro APK (TV & Phone)</div>
                    </div>
                    <a href="download" class="btn btn-secondary btn-sm" style="flex-shrink: 0;">
                        📲 Download APK
                    </a>
                </div>

                <!-- Quick Theme Preview Pill Bar -->
                <div class="theme-preview-bar">
                    <span style="font-size: 11px; color: var(--muted); font-weight: 600; margin-right: 4px;">THEMES:</span>
                    <?php foreach ($themes as $tKey => $t): ?>
                        <button type="button" class="theme-pill-btn <?= $tKey === $currentTheme ? 'active' : '' ?>" onclick="applyTheme('<?= $tKey ?>')">
                            <?= $t['icon'] ?> <?= htmlspecialchars($t['name']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- ======================================================== -->
        <!-- Dashboard & Navigation (Authenticated)                   -->
        <!-- ======================================================== -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Total Connected Devices</span>
                <span class="stat-value"><?= count($devices) ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Active Playlists</span>
                <span class="stat-value"><?= count($playlists) ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Configured DNS Servers</span>
                <span class="stat-value"><?= count($dnsList) ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Hosted APK Status</span>
                <span class="stat-value" style="font-size: 18px; margin-top: 4px;">
                    <?php if ($apkFileExists): ?>
                        <span class="badge badge-active">Available (<?= $apkFileSize ?> MB)</span>
                    <?php else: ?>
                        <span class="badge badge-missing">Not Uploaded</span>
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <nav class="tabs-nav">
            <a href="?tab=add" class="tab-link <?= $activeTab === 'add' ? 'active' : '' ?>">⚡ Add Users</a>
            <a href="?tab=playlists" class="tab-link <?= $activeTab === 'playlists' ? 'active' : '' ?>">📋 Manage Playlists (<?= count($playlists) ?>)</a>
            <a href="?tab=dns" class="tab-link <?= $activeTab === 'dns' ? 'active' : '' ?>">🌐 DNS Servers (<?= count($dnsList) ?>)</a>
            <a href="?tab=themes" class="tab-link <?= $activeTab === 'themes' ? 'active' : '' ?>">🎨 10 Modern Themes</a>
            <a href="?tab=settings" class="tab-link <?= $activeTab === 'settings' ? 'active' : '' ?>">⚙️ Settings & APK</a>
        </nav>

        <?php if ($activeTab === 'add'): ?>
            <!-- Add Users Forms Grid -->
            <div class="grid-2">
                <!-- Add IBO User -->
                <div class="card">
                    <h2>⚡ Add IBO User (Xtream Codes)</h2>
                    <form method="post">
                        <input type="hidden" name="form_type" value="ibo">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                        
                        <div class="form-group">
                            <label for="dns_id">DNS Server</label>
                            <select name="dns_id" id="dns_id" required>
                                <option value="">-- Select DNS Server --</option>
                                <?php foreach ($dnsList as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['url']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="mac">Device MAC Address</label>
                            <input type="text" id="mac" name="mac" placeholder="00:11:22:33:44:55" required>
                        </div>
                        <div class="form-group">
                            <label for="username">Xtream Username</label>
                            <input type="text" id="username" name="username" placeholder="Enter username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Xtream Password</label>
                            <input type="text" id="password" name="password" placeholder="Enter password" required>
                        </div>
                        <button type="submit" class="btn" style="width:100%;">Add IBO User</button>
                    </form>
                </div>

                <!-- Add M3U User -->
                <div class="card">
                    <h2>📄 Add M3U Playlist User</h2>
                    <form method="post">
                        <input type="hidden" name="form_type" value="m3u">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                        
                        <div class="form-group">
                            <label for="mac_address">Device MAC Address</label>
                            <input type="text" id="mac_address" name="mac_address" placeholder="00:11:22:33:44:55" required>
                        </div>
                        <div class="form-group">
                            <label for="m3u_name">Playlist Display Name</label>
                            <input type="text" id="m3u_name" name="m3u_name" placeholder="e.g. Premium VIP Live & VOD" required>
                        </div>
                        <div class="form-group">
                            <label for="m3u">M3U Link (URL)</label>
                            <input type="url" id="m3u" name="m3u" placeholder="https://example.com/playlist.m3u" required>
                        </div>
                        <button type="submit" class="btn" style="width:100%;">Add M3U User</button>
                    </form>
                </div>
            </div>

        <?php elseif ($activeTab === 'playlists'): ?>
            <!-- Manage Playlists Table -->
            <div class="card">
                <h2>📋 Registered Playlists & Devices</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>MAC Address</th>
                                <th>Device Key</th>
                                <th>Playlist Name</th>
                                <th>Type</th>
                                <th>URL / Host</th>
                                <th>Expiry</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($playlists)): ?>
                                <tr>
                                    <td colspan="8" style="text-align:center; padding: 24px; color: var(--muted);">No playlists registered yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($playlists as $p): ?>
                                    <tr>
                                        <td>#<?= $p['id'] ?></td>
                                        <td><span class="mac-badge"><?= htmlspecialchars($p['mac_address']) ?></span></td>
                                        <td><strong><?= htmlspecialchars($p['device_key'] ?? 'N/A') ?></strong></td>
                                        <td><?= htmlspecialchars($p['name']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $p['type'] === 'ibo' ? 'ibo' : 'm3u' ?>">
                                                <?= strtoupper(htmlspecialchars($p['type'])) ?>
                                            </span>
                                        </td>
                                        <td style="max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($p['url']) ?>">
                                            <?= htmlspecialchars($p['url']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($p['expiry'] ?: 'Unlimited') ?></td>
                                        <td>
                                            <form method="post" onsubmit="return confirm('Delete this playlist?');" style="display:inline;">
                                                <input type="hidden" name="form_type" value="delete_playlist">
                                                <input type="hidden" name="playlist_id" value="<?= $p['id'] ?>">
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

        <?php elseif ($activeTab === 'dns'): ?>
            <!-- Manage DNS Servers -->
            <div class="grid-2">
                <div class="card">
                    <h2>➕ Add New DNS Server</h2>
                    <form method="post">
                        <input type="hidden" name="form_type" value="add_dns">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <div class="form-group">
                            <label for="dns_name">Server Name</label>
                            <input type="text" id="dns_name" name="dns_name" placeholder="e.g. Dreamania 4K VIP" required>
                        </div>
                        <div class="form-group">
                            <label for="dns_url">Server Base URL</label>
                            <input type="text" id="dns_url" name="dns_url" placeholder="http://domain.com:8080" required>
                        </div>
                        <button type="submit" class="btn" style="width:100%;">Save DNS</button>
                    </form>
                </div>

                <div class="card">
                    <h2>🌐 Configured DNS Servers</h2>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>URL</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dnsList as $d): ?>
                                    <tr>
                                        <td>#<?= $d['id'] ?></td>
                                        <td><strong><?= htmlspecialchars($d['name']) ?></strong></td>
                                        <td><?= htmlspecialchars($d['url']) ?></td>
                                        <td>
                                            <form method="post" onsubmit="return confirm('Delete this DNS?');" style="display:inline;">
                                                <input type="hidden" name="form_type" value="delete_dns">
                                                <input type="hidden" name="dns_id" value="<?= $d['id'] ?>">
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

        <?php elseif ($activeTab === 'themes'): ?>
            <!-- ======================================================== -->
            <!-- 10 Modern Design Themes Gallery                          -->
            <!-- ======================================================== -->
            <div class="card" style="margin-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
                    <div>
                        <h2>🎨 Visual Theme Studio (10 Modern Themes)</h2>
                        <p style="font-size: 13px; color: var(--muted); margin-top: 4px;">
                            Switch themes instantly with real-time preview, or save any theme as the system default.
                        </p>
                    </div>
                    <div>
                        <span class="badge badge-active" style="padding: 6px 12px; font-size: 12px;">
                            Active Theme: <?= htmlspecialchars($themes[$currentTheme]['name']) ?>
                        </span>
                    </div>
                </div>

                <div class="theme-grid">
                    <?php foreach ($themes as $tKey => $t): ?>
                        <div class="theme-card <?= $tKey === $currentTheme ? 'active' : '' ?>">
                            <div class="theme-header">
                                <span class="theme-title">
                                    <span><?= $t['icon'] ?></span>
                                    <span><?= htmlspecialchars($t['name']) ?></span>
                                </span>
                                <span class="brand-badge"><?= htmlspecialchars($t['badge']) ?></span>
                            </div>

                            <p class="theme-desc"><?= htmlspecialchars($t['desc']) ?></p>

                            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 8px; border-top: 1px solid var(--border);">
                                <span style="font-size: 11px; color: var(--muted); font-weight: 600;">PALETTE:</span>
                                <div class="swatches">
                                    <span class="swatch" style="background: <?= $t['bg'] ?>;" title="Background"></span>
                                    <span class="swatch" style="background: <?= $t['card'] ?>;" title="Card"></span>
                                    <span class="swatch" style="background: <?= $t['primary'] ?>;" title="Primary"></span>
                                    <span class="swatch" style="background: <?= $t['accent'] ?>;" title="Accent"></span>
                                </div>
                            </div>

                            <div style="display: flex; gap: 8px; margin-top: 4px;">
                                <button type="button" class="btn btn-secondary btn-sm" style="flex: 1;" onclick="applyTheme('<?= $tKey ?>')">
                                    Preview
                                </button>
                                
                                <form method="post" style="flex: 1.3;">
                                    <input type="hidden" name="form_type" value="set_active_theme">
                                    <input type="hidden" name="theme_name" value="<?= $tKey ?>">
                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                    <button type="submit" class="btn btn-sm" style="width: 100%;">
                                        <?= $tKey === $currentTheme ? '✓ Default' : 'Set Default' ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php elseif ($activeTab === 'settings'): ?>
            <!-- Panel & App Settings -->
            <div style="max-width: 740px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">
                <!-- APK Hosting Card -->
                <div class="card">
                    <h2>📦 APK Hosting on Coolify</h2>
                    <p style="font-size: 13px; color: var(--muted); margin-bottom: 16px;">
                        Host your <code>DreamaniaIBOPro.apk</code> directly on this domain inside your Coolify persistent volume (no GitHub upload needed).
                    </p>

                    <div style="background: rgba(0, 0, 0, 0.25); padding: 14px; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 18px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 13px; color: var(--muted);">Status:</span>
                            <?php if ($apkFileExists): ?>
                                <span class="badge badge-active">Available (<?= $apkFileSize ?> MB)</span>
                            <?php else: ?>
                                <span class="badge badge-missing">No APK Uploaded</span>
                            <?php endif; ?>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 13px; color: var(--muted);">Direct URL:</span>
                            <a href="download" target="_blank" style="font-family: monospace; font-size: 13px; color: var(--accent);">/download</a>
                        </div>
                    </div>

                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="form_type" value="upload_apk_file">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                        
                        <div class="form-group">
                            <label for="apk_file">Upload / Replace APK File</label>
                            <input type="file" id="apk_file" name="apk_file" accept=".apk" required>
                        </div>
                        <button type="submit" class="btn" style="width: 100%;">Upload APK to Coolify</button>
                    </form>
                </div>

                <!-- Settings Card -->
                <div class="card">
                    <h2>⚙️ Panel & Application Settings</h2>
                    <form method="post">
                        <input type="hidden" name="form_type" value="save_settings">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                        
                        <div class="form-group">
                            <label for="panel_theme">Default Panel Theme</label>
                            <select id="panel_theme" name="panel_theme">
                                <?php foreach ($themes as $tKey => $t): ?>
                                    <option value="<?= $tKey ?>" <?= ($settings['panel_theme'] ?? 'purple') === $tKey ? 'selected' : '' ?>>
                                        <?= $t['icon'] ?> <?= htmlspecialchars($t['name']) ?> (<?= htmlspecialchars($t['badge']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="admin_password">Admin Portal Password</label>
                            <input type="password" id="admin_password" name="admin_password" value="<?= htmlspecialchars($settings['admin_password'] ?? '200600') ?>">
                        </div>
                        <div class="form-group">
                            <label for="note_title">In-App Notification Title</label>
                            <input type="text" id="note_title" name="note_title" value="<?= htmlspecialchars($settings['note_title'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="note_content">In-App Notification Message</label>
                            <textarea id="note_content" name="note_content" rows="3"><?= htmlspecialchars($settings['note_content'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="default_expiry">Default Account Expiry</label>
                            <input type="text" id="default_expiry" name="default_expiry" value="<?= htmlspecialchars($settings['default_expiry'] ?? '2030-01-01') ?>">
                        </div>
                        <div class="form-group">
                            <label for="app_version">App Version</label>
                            <input type="text" id="app_version" name="app_version" value="<?= htmlspecialchars($settings['app_version'] ?? '1.0.0') ?>">
                        </div>
                        <div class="form-group">
                            <label for="apk_link">APK Download Link (auto-filled if empty)</label>
                            <input type="url" id="apk_link" name="apk_link" placeholder="https://dmibo.myskillscloud.com/download" value="<?= htmlspecialchars($settings['apk_link'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn" style="width: 100%;">Save Settings</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<footer class="footer">
    &copy; <?= date('Y') ?> Dreamania IBO Pro &bull; High Performance IPTV Middleware &bull; Powered by Coolify
</footer>

<script>
function applyTheme(themeName) {
    document.documentElement.setAttribute('data-theme', themeName);
    document.cookie = "dreamania_theme=" + encodeURIComponent(themeName) + "; path=/; max-age=31536000; SameSite=Lax";
    const select = document.getElementById('themeQuickSelect');
    if (select) select.value = themeName;

    // Update pill buttons if present
    document.querySelectorAll('.theme-pill-btn').forEach(btn => {
        if (btn.textContent.includes(themeName)) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
}

function togglePasswordVisibility() {
    const input = document.getElementById('access_password');
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>

</body>
</html>