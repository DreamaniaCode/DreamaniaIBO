<?php
/**
 * Dreamania IBO Pro - Database & Models Manager
 * SQLite3 Database Engine with auto-initialization and WAL mode.
 */

declare(strict_types=1);

class DB {
    private static ?PDO $instance = null;
    private static string $dbFile = __DIR__ . '/data/database.sqlite';

    public static function getConnection(): PDO {
        if (self::$instance === null) {
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

            self::initTables();
        }
        return self::$instance;
    }

    private static function initTables(): void {
        $pdo = self::$instance;

        // Settings table
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL
        )");

        // DNS servers table
        $pdo->exec("CREATE TABLE IF NOT EXISTS dns (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            url TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Devices table
        $pdo->exec("CREATE TABLE IF NOT EXISTS devices (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            mac_address TEXT UNIQUE NOT NULL COLLATE NOCASE,
            device_key TEXT NOT NULL,
            android_id TEXT,
            app_type TEXT DEFAULT 'tv',
            app_version TEXT DEFAULT '1.0.0',
            expire_date TEXT NOT NULL,
            is_trial INTEGER DEFAULT 0,
            is_paid INTEGER DEFAULT 1,
            parent_control TEXT DEFAULT '0000',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_seen DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Playlists table
        $pdo->exec("CREATE TABLE IF NOT EXISTS playlists (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            device_id INTEGER,
            mac_address TEXT NOT NULL COLLATE NOCASE,
            name TEXT NOT NULL,
            type TEXT NOT NULL DEFAULT 'm3u',
            url TEXT NOT NULL,
            dns_id INTEGER,
            username TEXT,
            password TEXT,
            expiry TEXT,
            is_protected TEXT DEFAULT '0',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (device_id) REFERENCES devices(id) ON DELETE CASCADE
        )");

        // Initialize default settings if not present
        $defaults = [
            'admin_password'   => '200600',
            'app_name'         => 'Dreamania IBO Pro',
            'note_title'       => 'Dreamania IBO Pro',
            'note_content'     => 'Welcome to Dreamania IBO Pro! Enjoy your premium experience.',
            'trial_days'       => '7',
            'default_expiry'   => '2030-01-01',
            'price'            => '0',
            'theme'            => 'default',
            'qr_url'           => '',
            'subtitle_url'     => '',
            'app_version'      => '1.0.0',
            'apk_link'         => ''
        ];

        $stmt = $pdo->prepare("INSERT OR IGNORE INTO settings (key, value) VALUES (?, ?)");
        foreach ($defaults as $k => $v) {
            $stmt->execute([$k, $v]);
        }

        // Initialize default DNS if empty
        $countDns = (int) $pdo->query("SELECT COUNT(*) FROM dns")->fetchColumn();
        if ($countDns === 0) {
            $stmt = $pdo->prepare("INSERT INTO dns (id, name, url) VALUES (?, ?, ?)");
            $stmt->execute([1, 'Dreamania VIP Server', 'http://dreamania.tv:8080']);
            $stmt->execute([3, 'SanSat Server', 'http://sansat.net:8080']);
        }
    }

    public static function getSetting(string $key, string $default = ''): string {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (string)$val : $default;
    }

    public static function setSetting(string $key, string $value): void {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value");
        $stmt->execute([$key, $value]);
    }

    public static function getAllSettings(): array {
        $pdo = self::getConnection();
        $rows = $pdo->query("SELECT key, value FROM settings")->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }
        return $settings;
    }

    // --- Device Management ---

    public static function normalizeMac(string $mac): string {
        $clean = preg_replace('/[^0-9A-Fa-f]/', '', $mac);
        if (strlen($clean) === 12) {
            return strtoupper(implode(':', str_split($clean, 2)));
        }
        return strtoupper(trim($mac));
    }

    public static function getDeviceByMac(string $mac): ?array {
        $mac = self::normalizeMac($mac);
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM devices WHERE mac_address = ?");
        $stmt->execute([$mac]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getDeviceByAndroidId(string $androidId): ?array {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM devices WHERE android_id = ?");
        $stmt->execute([$androidId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function deriveMacFromAndroidId(string $androidId): string {
        $hash = md5($androidId);
        $macParts = [
            '02', // Local MAC prefix
            substr($hash, 0, 2),
            substr($hash, 2, 2),
            substr($hash, 4, 2),
            substr($hash, 6, 2),
            substr($hash, 8, 2),
        ];
        return strtoupper(implode(':', $macParts));
    }

    public static function generateDeviceKey(): string {
        return (string) random_int(100000, 999999);
    }

    public static function getOrCreateDeviceForClient(string $androidId, string $appType = 'tv', string $version = '1.0.0'): array {
        $pdo = self::getConnection();
        $device = null;

        if (!empty($androidId)) {
            $device = self::getDeviceByAndroidId($androidId);
        }

        if (!$device) {
            $mac = self::deriveMacFromAndroidId($androidId ?: uniqid('dev_', true));
            // Check if mac exists
            $existing = self::getDeviceByMac($mac);
            if ($existing) {
                $device = $existing;
            } else {
                $deviceKey = self::generateDeviceKey();
                $expireDate = self::getSetting('default_expiry', '2030-01-01');
                $stmt = $pdo->prepare("INSERT INTO devices (mac_address, device_key, android_id, app_type, app_version, expire_date, is_trial, is_paid)
                                       VALUES (?, ?, ?, ?, ?, ?, 0, 1)");
                $stmt->execute([$mac, $deviceKey, $androidId, $appType, $version, $expireDate]);
                $device = self::getDeviceByMac($mac);
            }
        }

        // Update last seen
        if ($device) {
            $pdo->prepare("UPDATE devices SET last_seen = CURRENT_TIMESTAMP WHERE id = ?")->execute([$device['id']]);
        }

        return $device;
    }

    public static function getOrCreateDeviceManual(string $mac): array {
        $mac = self::normalizeMac($mac);
        $device = self::getDeviceByMac($mac);
        if ($device) {
            return $device;
        }

        $pdo = self::getConnection();
        $deviceKey = self::generateDeviceKey();
        $expireDate = self::getSetting('default_expiry', '2030-01-01');
        $stmt = $pdo->prepare("INSERT INTO devices (mac_address, device_key, expire_date, is_trial, is_paid)
                               VALUES (?, ?, ?, 0, 1)");
        $stmt->execute([$mac, $deviceKey, $expireDate]);
        return self::getDeviceByMac($mac);
    }

    // --- Playlist Management ---

    public static function getPlaylistsForDevice(string $mac): array {
        $mac = self::normalizeMac($mac);
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM playlists WHERE mac_address = ? ORDER BY id ASC");
        $stmt->execute([$mac]);
        return $stmt->fetchAll();
    }

    public static function addIboUser(int $dnsId, string $mac, string $username, string $password): bool {
        $mac = self::normalizeMac($mac);
        $device = self::getOrCreateDeviceManual($mac);

        // Fetch DNS info
        $pdo = self::getConnection();
        $dnsStmt = $pdo->prepare("SELECT * FROM dns WHERE id = ?");
        $dnsStmt->execute([$dnsId]);
        $dns = $dnsStmt->fetch();
        if (!$dns) {
            throw new Exception("Selected DNS not found.");
        }

        $dnsUrl = rtrim($dns['url'], '/');
        // Build standard Xtream M3U URL with credentials
        $playlistUrl = "{$dnsUrl}/get.php?username=" . urlencode($username) . "&password=" . urlencode($password) . "&type=m3u_plus&output=ts";
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

    public static function addM3uUser(string $mac, string $name, string $m3uUrl): bool {
        $mac = self::normalizeMac($mac);
        $device = self::getOrCreateDeviceManual($mac);

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

    public static function getAllPlaylists(): array {
        $pdo = self::getConnection();
        return $pdo->query("SELECT p.*, d.device_key, d.expire_date as dev_expiry FROM playlists p LEFT JOIN devices d ON p.mac_address = d.mac_address ORDER BY p.id DESC")->fetchAll();
    }

    public static function getAllDevices(): array {
        $pdo = self::getConnection();
        return $pdo->query("SELECT d.*, (SELECT COUNT(*) FROM playlists p WHERE p.mac_address = d.mac_address) as playlist_count FROM devices d ORDER BY d.id DESC")->fetchAll();
    }

    public static function deleteDevice(int $id): bool {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("DELETE FROM devices WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- DNS Management ---

    public static function getAllDns(): array {
        $pdo = self::getConnection();
        return $pdo->query("SELECT * FROM dns ORDER BY id ASC")->fetchAll();
    }

    public static function addDns(string $name, string $url): bool {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("INSERT INTO dns (name, url) VALUES (?, ?)");
        return $stmt->execute([trim($name), trim($url)]);
    }

    public static function updateDns(int $id, string $name, string $url): bool {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("UPDATE dns SET name = ?, url = ? WHERE id = ?");
        return $stmt->execute([trim($name), trim($url), $id]);
    }

    public static function deleteDns(int $id): bool {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("DELETE FROM dns WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

