<?php
/**
 * Dreamania Player - Database & Models Manager
 * Multi-Engine (PostgreSQL & SQLite) with auto-migration and line tracking.
 */

declare(strict_types=1);

class DB {
    private static ?PDO $instance = null;
    private static string $dbFile = __DIR__ . '/data/database.sqlite';
    private static string $driver = 'sqlite';

    public static function getDriverName(): string {
        return self::$driver;
    }

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            // Load .env if present
            self::loadEnv();

            $dbUrl = getenv('DATABASE_URL') ?: ($_ENV['DATABASE_URL'] ?? ($_SERVER['DATABASE_URL'] ?? ''));

            if (!empty($dbUrl) && (str_starts_with($dbUrl, 'postgres://') || str_starts_with($dbUrl, 'postgresql://'))) {
                try {
                    self::$driver = 'pgsql';
                    $parsed = parse_url($dbUrl);
                    $host = $parsed['host'] ?? 'localhost';
                    $port = $parsed['port'] ?? 5432;
                    $user = isset($parsed['user']) ? urldecode($parsed['user']) : 'postgres';
                    $pass = isset($parsed['pass']) ? urldecode($parsed['pass']) : '';
                    $cleanPath = explode('?', $parsed['path'] ?? '')[0];
                    $dbName = ltrim($cleanPath, '/') ?: 'dreamania';

                    parse_str($parsed['query'] ?? '', $queryParams);
                    $sslMode = $queryParams['sslmode'] ?? '';

                    $dsn = "pgsql:host={$host};port={$port};dbname={$dbName}";
                    if (!empty($sslMode)) {
                        $dsn .= ";sslmode={$sslMode}";
                    }

                    self::$instance = new PDO($dsn, $user, $pass, [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_TIMEOUT            => 5,
                    ]);
                } catch (Throwable $e) {
                    error_log("PostgreSQL connection error: " . $e->getMessage() . " - Falling back to SQLite");
                    self::$instance = null;
                    self::$driver = 'sqlite';
                }
            }

            if (self::$instance === null) {
                // Default SQLite
                self::$driver = 'sqlite';
                $dataDir = dirname(self::$dbFile);
                if (!is_dir($dataDir)) {
                    mkdir($dataDir, 0775, true);
                }

                self::$instance = new PDO('sqlite:' . self::$dbFile, null, null, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT            => 5,
                ]);

                self::$instance->exec("PRAGMA journal_mode = WAL;");
                self::$instance->exec("PRAGMA synchronous = NORMAL;");
                self::$instance->exec("PRAGMA foreign_keys = ON;");
            }

            self::initTables();
            self::runMigrations();
        }
        return self::$instance;
    }

    private static function loadEnv(): void {
        $envPaths = [__DIR__ . '/.env', __DIR__ . '/data/.env', dirname(__DIR__) . '/.env'];
        foreach ($envPaths as $envPath) {
            if (file_exists($envPath)) {
                $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (str_starts_with($line, '#') || !str_contains($line, '=')) continue;
                    [$k, $v] = explode('=', $line, 2);
                    $k = trim($k);
                    $v = trim($v, " \t\n\r\0\x0B\"'");
                    putenv("{$k}={$v}");
                    $_ENV[$k] = $v;
                    $_SERVER[$k] = $v;
                }
            }
        }
    }

    private static function initTables(): void {
        $pdo = self::$instance;
        $isPg = (self::$driver === 'pgsql');

        $autoId = $isPg ? "SERIAL PRIMARY KEY" : "INTEGER PRIMARY KEY AUTOINCREMENT";

        // Settings table
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            key VARCHAR(100) PRIMARY KEY,
            value TEXT NOT NULL
        )");

        // DNS servers table
        $pdo->exec("CREATE TABLE IF NOT EXISTS dns (
            id {$autoId},
            name VARCHAR(255) NOT NULL,
            url VARCHAR(500) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Resellers table
        $pdo->exec("CREATE TABLE IF NOT EXISTS resellers (
            id {$autoId},
            username VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            credits INT DEFAULT 0,
            status VARCHAR(50) DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Devices table
        $pdo->exec("CREATE TABLE IF NOT EXISTS devices (
            id {$autoId},
            mac_address VARCHAR(50) UNIQUE NOT NULL,
            device_key VARCHAR(50) NOT NULL,
            android_id VARCHAR(100),
            app_type VARCHAR(50) DEFAULT 'tv',
            app_version VARCHAR(50) DEFAULT '1.0.0',
            expire_date VARCHAR(50) NOT NULL,
            is_trial INT DEFAULT 0,
            is_paid INT DEFAULT 1,
            parent_control VARCHAR(50) DEFAULT '0000',
            reseller_id INT DEFAULT 0,
            status VARCHAR(50) DEFAULT 'active',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Playlists table
        $pdo->exec("CREATE TABLE IF NOT EXISTS playlists (
            id {$autoId},
            device_id INT,
            mac_address VARCHAR(50) NOT NULL,
            name VARCHAR(255) NOT NULL,
            type VARCHAR(50) NOT NULL DEFAULT 'm3u',
            url TEXT NOT NULL,
            dns_id INT,
            username VARCHAR(100),
            password VARCHAR(100),
            expiry VARCHAR(50),
            is_protected VARCHAR(10) DEFAULT '0',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Used Lines / Activity Tracking table
        $pdo->exec("CREATE TABLE IF NOT EXISTS used_lines (
            id {$autoId},
            mac_address VARCHAR(50) NOT NULL,
            playlist_id INT,
            stream_id VARCHAR(100),
            stream_type VARCHAR(50),
            ip_address VARCHAR(100),
            user_agent VARCHAR(255),
            last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Default settings
        $defaults = [
            'admin_password'   => '200600',
            'app_name'         => 'Dreamania Player',
            'note_title'       => 'Dreamania Player',
            'note_content'     => 'Welcome to Dreamania Player! Enjoy your premium experience.',
            'trial_days'       => '7',
            'default_expiry'   => '2030-01-01',
            'price'            => '0',
            'theme'            => 'purple',
            'panel_theme'      => 'purple',
            'qr_url'           => '',
            'subtitle_url'     => '',
            'app_version'      => '1.0.0',
            'apk_link'         => 'https://dm.lumineasy.com/download'
        ];

        foreach ($defaults as $k => $v) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE key = ?");
            $stmt->execute([$k]);
            if ((int)$stmt->fetchColumn() === 0) {
                $ins = $pdo->prepare("INSERT INTO settings (key, value) VALUES (?, ?)");
                $ins->execute([$k, $v]);
            }
        }

        // Default DNS
        $countDns = (int) $pdo->query("SELECT COUNT(*) FROM dns")->fetchColumn();
        if ($countDns === 0) {
            $stmt = $pdo->prepare("INSERT INTO dns (name, url) VALUES (?, ?)");
            $stmt->execute(['Dreamania VIP Server', 'http://dreamania.tv:8080']);
            $stmt->execute(['SanSat Server', 'http://sansat.net:8080']);
        }
    }

    private static function runMigrations(): void {
        $pdo = self::$instance;
        if (self::$driver === 'sqlite') {
            // Check devices columns
            $cols = $pdo->query("PRAGMA table_info(devices)")->fetchAll();
            $existing = array_column($cols, 'name');

            if (!in_array('reseller_id', $existing)) {
                $pdo->exec("ALTER TABLE devices ADD COLUMN reseller_id INTEGER DEFAULT 0");
            }
            if (!in_array('status', $existing)) {
                $pdo->exec("ALTER TABLE devices ADD COLUMN status TEXT DEFAULT 'active'");
            }
            if (!in_array('notes', $existing)) {
                $pdo->exec("ALTER TABLE devices ADD COLUMN notes TEXT");
            }

            // Check playlists columns
            $pCols = $pdo->query("PRAGMA table_info(playlists)")->fetchAll();
            $pExisting = array_column($pCols, 'name');
            if (!in_array('username', $pExisting)) {
                $pdo->exec("ALTER TABLE playlists ADD COLUMN username TEXT");
            }
            if (!in_array('password', $pExisting)) {
                $pdo->exec("ALTER TABLE playlists ADD COLUMN password TEXT");
            }
        }
    }

    public static function getSetting(string $key, string $default = ''): string {
        if ($key === 'admin_password') {
            $envPass = getenv('ADMIN_PASSWORD') ?: ($_ENV['ADMIN_PASSWORD'] ?? ($_SERVER['ADMIN_PASSWORD'] ?? ''));
            if (!empty($envPass)) {
                return (string)$envPass;
            }
        }
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (string)$val : $default;
    }

    public static function setSetting(string $key, string $value): void {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE key = ?");
        $stmt->execute([$key]);
        if ((int)$stmt->fetchColumn() > 0) {
            $u = $pdo->prepare("UPDATE settings SET value = ? WHERE key = ?");
            $u->execute([$value, $key]);
        } else {
            $i = $pdo->prepare("INSERT INTO settings (key, value) VALUES (?, ?)");
            $i->execute([$key, $value]);
        }
    }

    public static function getAllSettings(): array {
        $pdo = self::getConnection();
        $rows = $pdo->query("SELECT key, value FROM settings")->fetchAll();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['key']] = $r['value'];
        }
        return $settings;
    }

    public static function normalizeMac(string $mac): string {
        $clean = preg_replace('/[^a-fA-F0-9]/', '', $mac);
        if (strlen($clean) !== 12) {
            return strtoupper(trim($mac));
        }
        return strtoupper(implode(':', str_split($clean, 2)));
    }

    // --- Device Operations ---

    public static function getOrCreateDeviceForClient(string $androidId, string $appType, string $appVersion): array {
        $pdo = self::getConnection();
        if (!empty($androidId)) {
            $stmt = $pdo->prepare("SELECT * FROM devices WHERE android_id = ? LIMIT 1");
            $stmt->execute([$androidId]);
            $device = $stmt->fetch();
            if ($device) {
                $up = $pdo->prepare("UPDATE devices SET last_seen = CURRENT_TIMESTAMP, app_version = ? WHERE id = ?");
                $up->execute([$appVersion, $device['id']]);
                return $device;
            }
        }

        $hash = md5($androidId ?: uniqid('dev_', true));
        $mac = strtoupper(sprintf("00:1A:79:%02X:%02X:%02X", hexdec(substr($hash, 0, 2)), hexdec(substr($hash, 2, 2)), hexdec(substr($hash, 4, 2))));
        return self::getOrCreateDeviceManual($mac, $androidId, $appType, $appVersion);
    }

    public static function getOrCreateDeviceManual(string $mac, string $androidId = '', string $appType = 'tv', string $appVersion = '1.0.0'): array {
        $mac = self::normalizeMac($mac);
        $pdo = self::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM devices WHERE mac_address = ?");
        $stmt->execute([$mac]);
        $device = $stmt->fetch();

        if ($device) {
            $up = $pdo->prepare("UPDATE devices SET last_seen = CURRENT_TIMESTAMP WHERE id = ?");
            $up->execute([$device['id']]);
            return $device;
        }

        $deviceKey = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $trialDays = (int) self::getSetting('trial_days', '7');
        $expireDate = date('Y-m-d', strtotime("+{$trialDays} days"));

        $ins = $pdo->prepare("INSERT INTO devices (mac_address, device_key, android_id, app_type, app_version, expire_date, is_trial, is_paid, parent_control, status)
                               VALUES (?, ?, ?, ?, ?, ?, 1, 0, '0000', 'active')");
        $ins->execute([$mac, $deviceKey, $androidId, $appType, $appVersion, $expireDate]);

        $stmt = $pdo->prepare("SELECT * FROM devices WHERE mac_address = ?");
        $stmt->execute([$mac]);
        return $stmt->fetch();
    }

    public static function getAllDevices(?int $resellerId = null): array {
        $pdo = self::getConnection();
        if ($resellerId !== null && $resellerId > 0) {
            $stmt = $pdo->prepare("SELECT * FROM devices WHERE reseller_id = ? ORDER BY id DESC");
            $stmt->execute([$resellerId]);
            return $stmt->fetchAll();
        }
        return $pdo->query("SELECT * FROM devices ORDER BY id DESC")->fetchAll();
    }

    public static function getDeviceById(int $id): ?array {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM devices WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function updateDevice(int $id, array $data): bool {
        $pdo = self::getConnection();
        $fields = [];
        $params = [];
        foreach ($data as $k => $v) {
            $fields[] = "{$k} = ?";
            $params[] = $v;
        }
        $params[] = $id;
        $sql = "UPDATE devices SET " . implode(', ', $fields) . " WHERE id = ?";
        return $pdo->prepare($sql)->execute($params);
    }

    public static function deleteDevice(int $id): bool {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT mac_address FROM devices WHERE id = ?");
        $stmt->execute([$id]);
        $mac = $stmt->fetchColumn();

        if ($mac) {
            $delP = $pdo->prepare("DELETE FROM playlists WHERE mac_address = ?");
            $delP->execute([$mac]);
        }

        $stmt = $pdo->prepare("DELETE FROM devices WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function activateSubscription(int $deviceId, string $type, ?int $resellerId = null): array {
        $device = self::getDeviceById($deviceId);
        if (!$device) {
            return ['status' => false, 'message' => 'Device not found'];
        }

        $pdo = self::getConnection();

        // Check reseller credit requirements
        if ($resellerId !== null && $resellerId > 0) {
            $reseller = self::getReseller($resellerId);
            if (!$reseller) {
                return ['status' => false, 'message' => 'Unauthorized reseller'];
            }

            $creditCost = match($type) {
                'trial_7d' => 0,
                'sub_1y' => 1,
                'sub_lifetime' => 2,
                default => 0
            };

            if ($creditCost > 0 && ($reseller['credits'] < $creditCost)) {
                return ['status' => false, 'message' => "Insufficient credits. Required: {$creditCost}, Available: {$reseller['credits']}"];
            }

            if ($creditCost > 0) {
                $deduct = $pdo->prepare("UPDATE resellers SET credits = credits - ? WHERE id = ?");
                $deduct->execute([$creditCost, $resellerId]);
            }
        }

        $now = time();
        $currentExpiry = strtotime($device['expire_date']);
        $baseTime = ($currentExpiry > $now) ? $currentExpiry : $now;

        switch ($type) {
            case 'trial_7d':
                $newExpiry = date('Y-m-d', strtotime('+7 days', $now));
                self::updateDevice($deviceId, [
                    'expire_date' => $newExpiry,
                    'is_trial' => 1,
                    'is_paid' => 0,
                    'status' => 'active'
                ]);
                return ['status' => true, 'message' => "7-Day Trial activated successfully until {$newExpiry}"];

            case 'sub_1y':
                $newExpiry = date('Y-m-d', strtotime('+1 year', $baseTime));
                self::updateDevice($deviceId, [
                    'expire_date' => $newExpiry,
                    'is_trial' => 0,
                    'is_paid' => 1,
                    'status' => 'active'
                ]);
                return ['status' => true, 'message' => "1-Year subscription activated until {$newExpiry}"];

            case 'sub_lifetime':
                $newExpiry = '2099-12-31';
                self::updateDevice($deviceId, [
                    'expire_date' => $newExpiry,
                    'is_trial' => 0,
                    'is_paid' => 1,
                    'status' => 'active'
                ]);
                return ['status' => true, 'message' => "Lifetime subscription activated!"];

            case 'toggle_status':
                $newStatus = ($device['status'] === 'blocked') ? 'active' : 'blocked';
                self::updateDevice($deviceId, ['status' => $newStatus]);
                return ['status' => true, 'message' => "Device status changed to {$newStatus}"];

            default:
                return ['status' => false, 'message' => 'Unknown activation type'];
        }
    }

    // --- Reseller Operations ---

    public static function getAllResellers(): array {
        $pdo = self::getConnection();
        return $pdo->query("SELECT * FROM resellers ORDER BY id DESC")->fetchAll();
    }

    public static function getReseller(int $id): ?array {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM resellers WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function getResellerByUsername(string $username): ?array {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM resellers WHERE username = ?");
        $stmt->execute([trim($username)]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function addReseller(string $username, string $password, string $name, int $credits): bool {
        $pdo = self::getConnection();
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO resellers (username, password, name, credits, status) VALUES (?, ?, ?, ?, 'active')");
        return $stmt->execute([trim($username), $hash, trim($name), $credits]);
    }

    public static function updateReseller(int $id, string $name, int $credits, string $status, ?string $newPassword = null): bool {
        $pdo = self::getConnection();
        if (!empty($newPassword)) {
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE resellers SET name = ?, credits = ?, status = ?, password = ? WHERE id = ?");
            return $stmt->execute([$name, $credits, $status, $hash, $id]);
        }
        $stmt = $pdo->prepare("UPDATE resellers SET name = ?, credits = ?, status = ? WHERE id = ?");
        return $stmt->execute([$name, $credits, $status, $id]);
    }

    public static function deleteReseller(int $id): bool {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("DELETE FROM resellers WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- Playlist Operations ---

    public static function getPlaylistsForDevice(string $mac): array {
        $mac = self::normalizeMac($mac);
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM playlists WHERE mac_address = ? ORDER BY id DESC");
        $stmt->execute([$mac]);
        return $stmt->fetchAll();
    }

    public static function getAllPlaylists(?int $resellerId = null): array {
        $pdo = self::getConnection();
        if ($resellerId !== null && $resellerId > 0) {
            $stmt = $pdo->prepare("SELECT p.*, d.expire_date as device_expiry, d.is_paid, d.status as device_status 
                                   FROM playlists p 
                                   INNER JOIN devices d ON p.mac_address = d.mac_address 
                                   WHERE d.reseller_id = ? 
                                   ORDER BY p.id DESC");
            $stmt->execute([$resellerId]);
            return $stmt->fetchAll();
        }

        return $pdo->query("SELECT p.*, d.expire_date as device_expiry, d.is_paid, d.status as device_status 
                            FROM playlists p 
                            LEFT JOIN devices d ON p.mac_address = d.mac_address 
                            ORDER BY p.id DESC")->fetchAll();
    }

    public static function getPlaylistById(int $id): ?array {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM playlists WHERE id = ?");
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        return $p ?: null;
    }

    public static function addIboUser(int $dnsId, string $mac, string $username, string $password, ?int $resellerId = null): bool {
        $mac = self::normalizeMac($mac);
        $device = self::getOrCreateDeviceManual($mac);

        if ($resellerId !== null && $resellerId > 0) {
            self::updateDevice($device['id'], ['reseller_id' => $resellerId]);
        }

        $pdo = self::getConnection();
        $dnsStmt = $pdo->prepare("SELECT * FROM dns WHERE id = ?");
        $dnsStmt->execute([$dnsId]);
        $dns = $dnsStmt->fetch();
        if (!$dns) {
            throw new Exception("Selected DNS not found.");
        }

        $dnsUrl = rtrim($dns['url'], '/');
        $playlistUrl = "{$dnsUrl}/player_api.php?username=" . urlencode($username) . "&password=" . urlencode($password);
        $name = $dns['name'] . " (" . $username . ")";

        $stmt = $pdo->prepare("INSERT INTO playlists (device_id, mac_address, name, type, url, dns_id, username, password, expiry)
                               VALUES (?, ?, ?, 'ibo', ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $device['id'],
            $mac,
            $name,
            $playlistUrl,
            $dnsId,
            $username,
            $password,
            $device['expire_date']
        ]);
    }

    public static function addM3uUser(string $mac, string $name, string $m3uUrl, ?int $resellerId = null): bool {
        $mac = self::normalizeMac($mac);
        $device = self::getOrCreateDeviceManual($mac);

        if ($resellerId !== null && $resellerId > 0) {
            self::updateDevice($device['id'], ['reseller_id' => $resellerId]);
        }

        $pdo = self::getConnection();
        $stmt = $pdo->prepare("INSERT INTO playlists (device_id, mac_address, name, type, url, expiry)
                               VALUES (?, ?, ?, 'm3u', ?, ?)");
        return $stmt->execute([
            $device['id'],
            $mac,
            $name,
            $m3uUrl,
            $device['expire_date']
        ]);
    }

    public static function updatePlaylist(int $id, array $data): bool {
        $pdo = self::getConnection();
        $fields = [];
        $params = [];
        foreach ($data as $k => $v) {
            $fields[] = "{$k} = ?";
            $params[] = $v;
        }
        $params[] = $id;
        $sql = "UPDATE playlists SET " . implode(', ', $fields) . " WHERE id = ?";
        return $pdo->prepare($sql)->execute($params);
    }

    public static function deletePlaylist(int $id, ?string $mac = null): bool {
        $pdo = self::getConnection();
        if ($mac !== null) {
            $mac = self::normalizeMac($mac);
            $stmt = $pdo->prepare("DELETE FROM playlists WHERE id = ? AND mac_address = ?");
            return $stmt->execute([$id, $mac]);
        }
        $stmt = $pdo->prepare("DELETE FROM playlists WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- DNS Operations ---

    public static function getAllDns(): array {
        $pdo = self::getConnection();
        return $pdo->query("SELECT * FROM dns ORDER BY id ASC")->fetchAll();
    }

    public static function addDns(string $name, string $url): bool {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("INSERT INTO dns (name, url) VALUES (?, ?)");
        return $stmt->execute([$name, $url]);
    }

    public static function deleteDns(int $id): bool {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("DELETE FROM dns WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- Used Lines Activity Tracking ---

    public static function recordLineActivity(string $mac, ?int $playlistId = null, ?string $streamId = null, ?string $type = null, ?string $ip = null, ?string $ua = null): void {
        try {
            $pdo = self::getConnection();
            $mac = self::normalizeMac($mac);
            $ip = $ip ?: ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
            $ua = $ua ?: ($_SERVER['HTTP_USER_AGENT'] ?? 'Dreamania Player');

            $stmt = $pdo->prepare("INSERT INTO used_lines (mac_address, playlist_id, stream_id, stream_type, ip_address, user_agent, last_active)
                                   VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
            $stmt->execute([$mac, $playlistId, $streamId, $type, $ip, $ua]);
        } catch (Throwable $e) {}
    }

    public static function getRecentLineActivity(int $limit = 50): array {
        $pdo = self::getConnection();
        $limit = max(1, min(500, $limit));
        $stmt = $pdo->query("SELECT * FROM used_lines ORDER BY id DESC LIMIT {$limit}");
        return $stmt->fetchAll();
    }
}
