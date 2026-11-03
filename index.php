<?php
/**
 * Dreamania IBO Pro - Web Administration Portal
 * Sleek, high-performance, dark-themed control panel.
 */

declare(strict_types=1);
session_start();

require_once __DIR__ . '/db.php';

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
            } elseif ($formType === 'save_settings') {
                if (!empty($_POST['admin_password'])) {
                    DB::setSetting('admin_password', trim($_POST['admin_password']));
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

// APK File Status
$apkPath = __DIR__ . '/data/app.apk';
if (!file_exists($apkPath) && file_exists(__DIR__ . '/DreamaniaIBOPro.apk')) {
    $apkPath = __DIR__ . '/DreamaniaIBOPro.apk';
}
$apkFileExists = file_exists($apkPath) && filesize($apkPath) > 100000;
$apkFileSize = $apkFileExists ? round(filesize($apkPath) / (1024 * 1024), 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dreamania IBO Pro - Management Panel</title>
<style>
:root {
    --bg: #0b1120;
    --card: #111827;
    --card-hover: #172033;
    --text: #f9fafb;
    --muted: #94a3b8;
    --border: #1e293b;
    --primary: #bf1e2e;
    --primary-dark: #380308;
    --accent: #e11d48;
    --success: #059669;
    --error: #e11d48;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}
.navbar {
    background: rgba(17, 24, 39, 0.95);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--border);
    padding: 14px 24px;
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
.brand-logo {
    width: 38px;
    height: 38px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 18px;
    box-shadow: 0 4px 14px rgba(191, 30, 46, 0.4);
}
.brand-name {
    font-size: 19px;
    font-weight: 700;
    letter-spacing: -0.5px;
}
.brand-badge {
    background: rgba(191, 30, 46, 0.18);
    color: #fb7185;
    font-size: 11px;
    padding: 2px 7px;
    border-radius: 6px;
    font-weight: 600;
    border: 1px solid rgba(191, 30, 46, 0.3);
}
.nav-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 9px 18px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    color: #fff;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    box-shadow: 0 4px 12px rgba(191, 30, 46, 0.25);
}
.btn:hover {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(191, 30, 46, 0.4);
}
.btn-secondary {
    background: #1e293b;
    box-shadow: none;
    color: var(--muted);
}
.btn-secondary:hover {
    background: #334155;
    color: #fff;
    transform: none;
    box-shadow: none;
}
.btn-success {
    background: #059669;
    box-shadow: 0 2px 8px rgba(5, 150, 105, 0.3);
}
.btn-success:hover {
    background: #047857;
}
.btn-danger {
    background: #e11d48;
    box-shadow: 0 2px 8px rgba(225, 29, 72, 0.3);
}
.btn-danger:hover {
    background: #be123c;
}
.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
    border-radius: 6px;
}
.main-container {
    width: 100%;
    max-width: 1140px;
    margin: 24px auto;
    padding: 0 16px;
    flex: 1;
}
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
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 600;
    color: var(--muted);
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.2s ease;
    white-space: nowrap;
}
.tab-link.active, .tab-link:hover {
    color: #fff;
    background: rgba(191, 30, 46, 0.2);
}
.tab-link.active {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
}
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
    background: #0f172a;
    color: var(--text);
    font-size: 14px;
    outline: none;
    transition: border 0.2s ease;
}
input:focus, select:focus, textarea:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(191, 30, 46, 0.2);
}
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
    background: #1e293b;
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
.badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}
.badge-ibo { background: rgba(191, 30, 46, 0.2); color: #fb7185; border: 1px solid rgba(191, 30, 46, 0.4); }
.badge-m3u { background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.4); }
.badge-active { background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.4); }
.badge-missing { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.4); }
.mac-badge {
    font-family: monospace;
    font-size: 13px;
    color: #38bdf8;
    background: rgba(56, 189, 248, 0.1);
    padding: 2px 6px;
    border-radius: 4px;
}
.login-box {
    max-width: 420px;
    margin: 40px auto;
}
.footer {
    text-align: center;
    padding: 20px;
    color: var(--muted);
    font-size: 13px;
    border-top: 1px solid var(--border);
    margin-top: auto;
}
.footer a { color: #38bdf8; text-decoration: none; }
</style>
</head>
<body>

<!-- Navbar -->
<header class="navbar">
    <a href="index.php" class="brand">
        <div class="brand-logo">D</div>
        <div>
            <span class="brand-name">Dreamania IBO Pro</span>
            <span class="brand-badge">PRO V10</span>
        </div>
    </a>
    <div class="nav-actions">
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
        <!-- Login Form -->
        <div class="card login-box">
            <h2 style="justify-content:center; margin-bottom: 24px;">Admin Portal Login</h2>
            <form method="post">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                <div class="form-group">
                    <label for="access_password">Access Password</label>
                    <input type="password" id="access_password" name="access_password" placeholder="Enter password (default: 200600)" required autofocus>
                </div>
                <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">Login</button>
            </form>

            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border); text-align: center;">
                <p style="font-size: 13px; color: var(--muted); margin-bottom: 12px;">Looking for the client app for TV or Android?</p>
                <a href="download" class="btn btn-secondary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    📲 Download Dreamania IBO Pro APK
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Dashboard & Navigation -->
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

        <?php elseif ($activeTab === 'settings'): ?>
            <!-- Panel & App Settings -->
            <div style="max-width: 720px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">
                <!-- APK Hosting Card -->
                <div class="card">
                    <h2>📦 APK Hosting on Coolify</h2>
                    <p style="font-size: 13px; color: var(--muted); margin-bottom: 16px;">
                        Host your <code>DreamaniaIBOPro.apk</code> directly on this domain inside your Coolify persistent volume (no GitHub required).
                    </p>

                    <div style="background: #0f172a; padding: 14px; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 18px;">
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
                            <a href="download" target="_blank" style="font-family: monospace; font-size: 13px; color: #38bdf8;">/download</a>
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
                            <label for="apk_link">APK Download Link (auto-filled if left empty)</label>
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
    &copy; <?= date('Y') ?> Dreamania IBO Pro &bull; High Performance IPTV Management Panel
</footer>

</body>
</html>
