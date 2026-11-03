<?php
/**
 * Dreamania IBO Pro - Unified API Endpoint
 * Handles /authenticate, /playlists, /delete, /parent-control/update, /download
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/crypto.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Gc-Token, x-hash, x-hash-2, x-token, x-token-2, x-token-3, X-Admin-Password');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 1. Determine incoming action
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$action = $_GET['action'] ?? '';

if (str_ends_with($uri, '/download') || str_ends_with($uri, '/app.apk') || str_ends_with($uri, '/DreamaniaIBOPro.apk') || $action === 'download_apk') {
    handleDownloadApk();
} elseif ($action === 'upload_apk') {
    handleUploadApk();
} elseif (str_ends_with($uri, '/authenticate') || $action === 'authenticate') {
    header('Content-Type: application/json; charset=utf-8');
    handleAuthenticate();
} elseif (str_ends_with($uri, '/delete') || str_ends_with($uri, '/playlists/delete') || $action === 'delete') {
    header('Content-Type: application/json; charset=utf-8');
    handleDeletePlaylist();
} elseif (str_ends_with($uri, '/parent-control/update') || $action === 'parent_control') {
    header('Content-Type: application/json; charset=utf-8');
    handleParentControl();
} elseif (str_ends_with($uri, '/playlists') || $action === 'playlists') {
    header('Content-Type: application/json; charset=utf-8');
    handleGetPlaylists();
} elseif (str_ends_with($uri, '/player_api.php')) {
    header('Content-Type: application/json; charset=utf-8');
    handlePlayerApi();
} else {
    // Default fallback: if POST data is present, treat as authenticate
    header('Content-Type: application/json; charset=utf-8');
    handleAuthenticate();
}

/**
 * Streams the APK file for direct download from Coolify
 */
function handleDownloadApk(): void {
    $candidates = [
        __DIR__ . '/data/app.apk',
        __DIR__ . '/DreamaniaIBOPro.apk',
        __DIR__ . '/data/DreamaniaIBOPro.apk'
    ];

    $apkPath = null;
    foreach ($candidates as $c) {
        if (file_exists($c) && filesize($c) > 100000) {
            $apkPath = $c;
            break;
        }
    }

    if ($apkPath === null) {
        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');
        echo "<!DOCTYPE html><html><body style='background:#0f172a;color:#fff;font-family:sans-serif;padding:40px;text-align:center;'>";
        echo "<h2>APK File Not Found On Server</h2>";
        echo "<p style='color:#94a3b8;'>Please upload the APK file via the Admin Panel Settings.</p>";
        echo "<a href='index.php' style='color:#38bdf8;'>Back to Admin Portal</a>";
        echo "</body></html>";
        exit;
    }

    // Clear output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    $size = filesize($apkPath);
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.android.package-archive');
    header('Content-Disposition: attachment; filename="DreamaniaIBOPro.apk"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $size);

    $fp = fopen($apkPath, 'rb');
    if ($fp) {
        while (!feof($fp)) {
            echo fread($fp, 1024 * 64);
            flush();
        }
        fclose($fp);
    }
    exit;
}

/**
 * Direct API upload handler for APK
 */
function handleUploadApk(): void {
    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $pass = $_POST['access_password'] ?? ($_SERVER['HTTP_X_ADMIN_PASSWORD'] ?? '');
    $savedPass = DB::getSetting('admin_password', '200600');
    $isLogged = !empty($_SESSION['admin_logged']) || ($pass === $savedPass);

    if (!$isLogged) {
        http_response_code(401);
        echo json_encode(['status' => false, 'message' => 'Unauthorized password']);
        exit;
    }

    if (empty($_FILES['apk_file']) || $_FILES['apk_file']['error'] !== UPLOAD_ERR_OK) {
        $err = $_FILES['apk_file']['error'] ?? 'No file provided';
        echo json_encode(['status' => false, 'message' => "Upload error: {$err}"]);
        exit;
    }

    $dest = __DIR__ . '/data/app.apk';
    if (move_uploaded_file($_FILES['apk_file']['tmp_name'], $dest)) {
        echo json_encode([
            'status' => true,
            'message' => 'APK uploaded successfully to server volume',
            'filename' => 'DreamaniaIBOPro.apk',
            'size' => filesize($dest)
        ]);
        exit;
    }

    echo json_encode(['status' => false, 'message' => 'Failed to save APK file to disk']);
    exit;
}

/**
 * Parses request payload from raw body or $_POST, handling obfuscation
 */
function parseIncomingPayload(): array {
    $raw = file_get_contents('php://input');
    $data = [];

    if (!empty($raw)) {
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $data = $json;
        }
    }

    if (empty($data) && !empty($_POST)) {
        $data = $_POST;
    }

    // Check if 'data' field is obfuscated
    if (isset($data['data']) && is_string($data['data'])) {
        $deobf = CryptoHelper::deobfuscate($data['data']);
        $inner = json_decode($deobf, true);
        if (is_array($inner)) {
            $data = array_merge($data, $inner);
        }
    }

    return $data;
}

/**
 * Handle /authenticate request from SanSat / Dreamania IBO Pro client
 */
function handleAuthenticate(): void {
    $payload = parseIncomingPayload();

    $androidId = '';
    if (!empty($payload['app_device_id'])) {
        // May be base64-encoded android_id
        $decoded = base64_decode($payload['app_device_id'], true);
        $androidId = ($decoded !== false && ctype_print($decoded)) ? trim($decoded) : trim($payload['app_device_id']);
    }

    $appType = $payload['app_type'] ?? 'tv';
    $version = $payload['version'] ?? '1.0.0';

    // If direct MAC provided in payload or query
    $mac = $payload['mac_address'] ?? ($_GET['mac'] ?? '');

    $device = null;
    if (!empty($mac)) {
        $device = DB::getOrCreateDeviceManual($mac);
    } else {
        $device = DB::getOrCreateDeviceForClient($androidId, $appType, $version);
    }

    if (!$device) {
        echo json_encode(['status' => false, 'message' => 'Device initialization failed']);
        exit;
    }

    $macAddress = $device['mac_address'];
    $deviceKey  = $device['device_key'];
    $expireDate = $device['expire_date'];
    $isTrial    = (int)$device['is_trial'];
    $isPaid     = (bool)$device['is_paid'];
    $parentCode = $device['parent_control'] ?? '0000';

    // Fetch playlists assigned to this MAC
    $playlistsDb = DB::getPlaylistsForDevice($macAddress);
    $urls = [];

    foreach ($playlistsDb as $p) {
        $urls[] = [
            'id'           => (string)$p['id'],
            'name'         => (string)$p['name'],
            'url'          => (string)$p['url'],
            'type'         => (string)($p['type'] ?? 'm3u'),
            'is_protected' => (string)($p['is_protected'] ?? '0'),
            'expiry'       => (string)($p['expiry'] ?: $expireDate),
        ];
    }

    // Build DNS portals array
    $dnsRows = DB::getAllDns();
    $portals = [];
    foreach ($dnsRows as $d) {
        $portals[] = [
            'id'   => (string)$d['id'],
            'name' => (string)$d['name'],
            'url'  => (string)$d['url'],
        ];
    }

    $settings = DB::getAllSettings();

    // Protocol URL base
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl = "{$scheme}://{$host}";

    $appInfo = [
        'mac_registered' => true,
        'mac_address'    => $macAddress,
        'device_key'     => $deviceKey,
        'expire_date'    => $expireDate,
        'is_trial'       => $isTrial,
        'lock'           => 0,
        'price'          => $settings['price'] ?? '0',
        'note_title'     => $settings['note_title'] ?? 'Dreamania IBO Pro',
        'note_content'   => $settings['note_content'] ?? 'Welcome to Dreamania IBO Pro!',
        'parent_control' => $parentCode,
        'parent_synced'  => 1,
        'theme'          => $settings['theme'] ?? 'default',
        'qr_url'         => $settings['qr_url'] ?: "{$baseUrl}/qr.php?text=",
        'subtitle_url'   => $settings['subtitle_url'] ?: $baseUrl,
        'app_version'    => $settings['app_version'] ?? '1.0.0',
        'apk_link'       => $settings['apk_link'] ?: "{$baseUrl}/download",
        'portals'        => $portals,
        'urls'           => $urls,
    ];

    $jsonPayload = json_encode($appInfo, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $obfuscatedData = CryptoHelper::obfuscate($jsonPayload);

    echo json_encode([
        'status' => true,
        'data'   => $obfuscatedData,
        // Also provide raw app_info for direct API consumers
        'app_info' => $appInfo
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Handle /playlists/delete
 */
function handleDeletePlaylist(): void {
    $payload = parseIncomingPayload();
    $playlistId = (int)($payload['file_id'] ?? ($payload['id'] ?? ($_GET['id'] ?? 0)));
    $mac = $payload['mac_address'] ?? ($_GET['mac'] ?? '');

    if ($playlistId <= 0) {
        echo json_encode(['status' => false, 'message' => 'Invalid playlist ID']);
        exit;
    }

    $deleted = DB::deletePlaylist($playlistId, !empty($mac) ? $mac : null);
    echo json_encode(['status' => $deleted]);
    exit;
}

/**
 * Handle /parent-control/update
 */
function handleParentControl(): void {
    $payload = parseIncomingPayload();
    $mac = DB::normalizeMac($payload['mac_address'] ?? '');
    $pin = trim($payload['parent_control'] ?? '');

    if (!empty($mac) && !empty($pin)) {
        $pdo = DB::getConnection();
        $stmt = $pdo->prepare("UPDATE devices SET parent_control = ? WHERE mac_address = ?");
        $stmt->execute([$pin, $mac]);
        echo json_encode(['status' => true]);
        exit;
    }

    echo json_encode(['status' => false, 'message' => 'Missing MAC or PIN']);
    exit;
}

/**
 * Handle /playlists (returns raw playlist list)
 */
function handleGetPlaylists(): void {
    $mac = DB::normalizeMac($_GET['mac'] ?? '');
    if (empty($mac)) {
        echo json_encode(['status' => false, 'message' => 'Missing MAC address']);
        exit;
    }

    $playlists = DB::getPlaylistsForDevice($mac);
    echo json_encode(['status' => true, 'playlists' => $playlists]);
    exit;
}

/**
 * Player API compatibility (Xtream Codes fallback)
 */
function handlePlayerApi(): void {
    $username = $_GET['username'] ?? '';
    $password = $_GET['password'] ?? '';

    echo json_encode([
        'user_info' => [
            'username'             => $username ?: 'dreamania',
            'password'             => $password ?: 'dreamania',
            'message'              => 'Dreamania IBO Pro Server Active',
            'auth'                 => 1,
            'status'               => 'Active',
            'exp_date'             => '1924991999',
            'is_trial'             => '0',
            'active_cons'          => '1',
            'created_at'           => '1700000000',
            'max_connections'      => '5',
            'allowed_output_formats'=> ['m3u8', 'ts', 'rtmp']
        ],
        'server_info' => [
            'url'                  => $_SERVER['HTTP_HOST'] ?? 'localhost',
            'port'                 => (string)($_SERVER['SERVER_PORT'] ?? 80),
            'https_port'           => '443',
            'server_protocol'      => 'http',
            'rtmp_server'          => '',
            'timezone'             => 'UTC',
            'time_now'             => date('Y-m-d H:i:s'),
            'process'              => true
        ]
    ]);
    exit;
}
