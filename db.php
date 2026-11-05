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

        // Orders & Payments table (Client activations & Reseller credits)
        $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
            id {$autoId},
            order_type VARCHAR(50) NOT NULL,
            mac_address VARCHAR(50),
            device_key VARCHAR(50),
            reseller_id INT DEFAULT 0,
            plan VARCHAR(50) NOT NULL,
            amount_dh NUMERIC(10,2) NOT NULL,
            credits INT DEFAULT 0,
            payment_method VARCHAR(50) NOT NULL,
            customer_name VARCHAR(255),
            customer_email VARCHAR(255),
            customer_phone VARCHAR(50),
            transaction_ref VARCHAR(255),
            proof_file VARCHAR(500),
            status VARCHAR(50) DEFAULT 'pending',
            admin_notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Default settings
        $defaults = [
            'admin_password'       => '200600',
            'app_name'             => 'Dreamania Player',
            'note_title'           => 'Dreamania Player',
            'note_content'         => 'Welcome to Dreamania Player! Enjoy your premium experience.',
            'trial_days'           => '7',
            'default_expiry'       => '2030-01-01',
            'price'                => '0',
            'theme'                => 'purple',
            'panel_theme'          => 'purple',
            'qr_url'               => '',
            'subtitle_url'         => '',
            'app_version'          => '1.0.0',
            'apk_link'             => 'https://dm.lumineasy.com/download',
            'paypal_email'         => 'contact@lumineasy.com',
            'paypal_me_link'       => 'https://paypal.me/dreamaniacode',
            'rib_bank_name'        => 'CIH BANK',
            'rib_account_name'     => 'DREAMANIA MEDIA',
            'rib_number'           => '230 780 4567890123456789 12',
            'rib_swift'            => 'CIHMMAMC',
            'whatsapp_number'      => '+212600000000',
            'price_client_1y'      => '50',
            'price_client_life'    => '150',
            'price_reseller_10'    => '150',
            'price_reseller_20'    => '180',
            'price_reseller_30'    => '240',
            'price_reseller_50'    => '350',
            'price_reseller_100'   => '600',
            'downloader_code'      => '7454701'
        ];

        foreach ($defaults as $k => $v) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE key = ?");
            $stmt->execute([$k]);
            if ((int)$stmt->fetchColumn() === 0) {
                $ins = $pdo->prepare("INSERT INTO settings (key, value) VALUES (?, ?)");
                $ins->execute([$k, $v]);
            }
        }

        // Migrate price_reseller_10 from old 100 to 150 if untouched
        $stmt10 = $pdo->prepare("SELECT value FROM settings WHERE key = 'price_reseller_10'");
        $stmt10->execute();
        if ($stmt10->fetchColumn() === '100') {
            $pdo->exec("UPDATE settings SET value = '150' WHERE key = 'price_reseller_10'");
        }

        // Ensure downloader_code exists
        $stmtDc = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE key = 'downloader_code'");
        $stmtDc->execute();
        if ((int)$stmtDc->fetchColumn() === 0) {
            $pdo->prepare("INSERT INTO settings (key, value) VALUES ('downloader_code', '7454701')")->execute();
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
            if (!in_array('is_protected', $pExisting)) {
                $pdo->exec("ALTER TABLE playlists ADD COLUMN is_protected TEXT DEFAULT '0'");
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

    /**
     * Centralized dynamic pricing helper across the entire platform.
     * When any price is changed in settings, all public pages, checkout modals,
     * and reseller portals instantly update automatically.
     */
    public static function getPricing(): array {
        $settings = self::getAllSettings();
        
        $c1y = (float)($settings['price_client_1y'] ?? 50);
        $clife = (float)($settings['price_client_life'] ?? 150);

        $p10 = (float)($settings['price_reseller_10'] ?? 150);
        $p20 = (float)($settings['price_reseller_20'] ?? 180);
        $p30 = (float)($settings['price_reseller_30'] ?? 240);
        $p50 = (float)($settings['price_reseller_50'] ?? 350);
        $p100 = (float)($settings['price_reseller_100'] ?? 600);

        return [
            'client_1y_dh'        => $c1y,
            'client_1y_eur'       => round($c1y / 10, 2),
            'client_1y_usd'       => round($c1y / 10, 2),
            'client_1y_intl'      => '€' . number_format($c1y / 10, 2) . ' / $' . number_format($c1y / 10, 2),
            'client_1y_label'     => '€' . number_format($c1y / 10, 2) . ' / $' . number_format($c1y / 10, 2) . ' (' . (int)$c1y . ' DH)',
            
            'client_life_dh'      => $clife,
            'client_life_eur'     => round($clife / 10, 2),
            'client_life_usd'     => round($clife / 10, 2),
            'client_life_intl'    => '€' . number_format($clife / 10, 2) . ' / $' . number_format($clife / 10, 2),
            'client_life_label'   => '€' . number_format($clife / 10, 2) . ' / $' . number_format($clife / 10, 2) . ' (' . (int)$clife . ' DH)',

            'reseller_packs'      => [
                10  => [
                    'credits'    => 10,
                    'dh'         => $p10,
                    'eur'        => round($p10 / 10, 2),
                    'usd'        => round($p10 / 10, 2),
                    'rate_dh'    => round($p10 / 10, 2),
                    'rate_eur'   => round(($p10 / 10) / 10, 2),
                    'rate_label' => round(($p10 / 10) / 10, 2) . '€ (' . round($p10 / 10, 1) . ' DH) / cr',
                    'badge'      => 'Pack Découverte',
                    'label'      => '€' . round($p10 / 10, 2) . ' / $' . round($p10 / 10, 2) . ' (' . (int)$p10 . ' DH)'
                ],
                20  => [
                    'credits'    => 20,
                    'dh'         => $p20,
                    'eur'        => round($p20 / 10, 2),
                    'usd'        => round($p20 / 10, 2),
                    'rate_dh'    => round($p20 / 20, 2),
                    'rate_eur'   => round(($p20 / 20) / 10, 2),
                    'rate_label' => round(($p20 / 20) / 10, 2) . '€ (' . round($p20 / 20, 1) . ' DH) / cr',
                    'badge'      => 'Pack Standard',
                    'label'      => '€' . round($p20 / 10, 2) . ' / $' . round($p20 / 10, 2) . ' (' . (int)$p20 . ' DH)'
                ],
                30  => [
                    'credits'    => 30,
                    'dh'         => $p30,
                    'eur'        => round($p30 / 10, 2),
                    'usd'        => round($p30 / 10, 2),
                    'rate_dh'    => round($p30 / 30, 2),
                    'rate_eur'   => round(($p30 / 30) / 10, 2),
                    'rate_label' => round(($p30 / 30) / 10, 2) . '€ (' . round($p30 / 30, 1) . ' DH) / cr',
                    'badge'      => 'Pack Pro',
                    'label'      => '€' . round($p30 / 10, 2) . ' / $' . round($p30 / 10, 2) . ' (' . (int)$p30 . ' DH)'
                ],
                50  => [
                    'credits'    => 50,
                    'dh'         => $p50,
                    'eur'        => round($p50 / 10, 2),
                    'usd'        => round($p50 / 10, 2),
                    'rate_dh'    => round($p50 / 50, 2),
                    'rate_eur'   => round(($p50 / 50) / 10, 2),
                    'rate_label' => round(($p50 / 50) / 10, 2) . '€ (' . round($p50 / 50, 1) . ' DH) / cr',
                    'badge'      => '🔥 Top Vente',
                    'label'      => '€' . round($p50 / 10, 2) . ' / $' . round($p50 / 10, 2) . ' (' . (int)$p50 . ' DH)'
                ],
                100 => [
                    'credits'    => 100,
                    'dh'         => $p100,
                    'eur'        => round($p100 / 10, 2),
                    'usd'        => round($p100 / 10, 2),
                    'rate_dh'    => round($p100 / 100, 2),
                    'rate_eur'   => round(($p100 / 100) / 10, 2),
                    'rate_label' => round(($p100 / 100) / 10, 2) . '€ (' . round($p100 / 100, 1) . ' DH) / cr (-40%)',
                    'badge'      => '👑 Grossiste VIP (-40%)',
                    'label'      => '€' . round($p100 / 10, 2) . ' / $' . round($p100 / 10, 2) . ' (' . (int)$p100 . ' DH)'
                ]
            ],

            'downloader_code'     => $settings['downloader_code'] ?? '7454701'
        ];
    }

    public static function getPaymentConfig(): array {
        $s = self::getAllSettings();
        return [
            'paypal_email'     => $s['paypal_email'] ?? 'contact@lumineasy.com',
            'paypal_me_link'   => $s['paypal_me_link'] ?? 'https://paypal.me/dreamaniacode',
            'rib_bank'         => $s['rib_bank_name'] ?? 'CIH BANK',
            'rib_bank_name'    => $s['rib_bank_name'] ?? 'CIH BANK',
            'rib_account_name' => $s['rib_account_name'] ?? 'DREAMANIA MEDIA',
            'rib_number'       => $s['rib_number'] ?? '230 780 4567890123456789 12',
            'rib_swift'        => $s['rib_swift'] ?? 'CIHMMAMC',
            'whatsapp_number'  => $s['whatsapp_number'] ?? '+212600000000',
        ];
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

    public static function getDeviceByMac(string $mac): ?array {
        $mac = self::normalizeMac($mac);
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM devices WHERE mac_address = ?");
        $stmt->execute([$mac]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function getDeviceByMacAndKey(string $mac, string $deviceKey, bool $autoCreateIfMissing = false): ?array {
        $mac = self::normalizeMac($mac);
        $deviceKey = trim($deviceKey);
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM devices WHERE mac_address = ?");
        $stmt->execute([$mac]);
        $device = $stmt->fetch();
        if ($device) {
            if ($device['device_key'] === $deviceKey) {
                return $device;
            }
            return null;
        }
        if ($autoCreateIfMissing && strlen($deviceKey) === 6 && ctype_digit($deviceKey)) {
            $trialDays = (int) self::getSetting('trial_days', '7');
            $expireDate = date('Y-m-d', strtotime("+{$trialDays} days"));
            $ins = $pdo->prepare("INSERT INTO devices (mac_address, device_key, android_id, app_type, app_version, expire_date, is_trial, is_paid, parent_control, status)
                                   VALUES (?, ?, '', 'tv', '1.0.0', ?, 1, 0, '0000', 'active')");
            $ins->execute([$mac, $deviceKey, $expireDate]);
            return self::getDeviceByMac($mac);
        }
        return null;
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

    public static function addXtreamPlaylist(string $mac, string $name, string $serverUrl, string $username, string $password, bool $isProtected = false): bool {
        $mac = self::normalizeMac($mac);
        $device = self::getOrCreateDeviceManual($mac);
        $pdo = self::getConnection();

        $serverUrl = rtrim(trim($serverUrl), '/');
        $stmtDns = $pdo->prepare("SELECT id FROM dns WHERE url LIKE ? LIMIT 1");
        $stmtDns->execute([$serverUrl . '%']);
        $dnsId = $stmtDns->fetchColumn();
        if (!$dnsId) {
            $host = parse_url($serverUrl, PHP_URL_HOST) ?: 'Custom Xtream Server';
            $insDns = $pdo->prepare("INSERT INTO dns (name, url) VALUES (?, ?)");
            $insDns->execute([$host, $serverUrl]);
            $dnsId = (int)$pdo->lastInsertId();
        }

        $playlistUrl = "{$serverUrl}/player_api.php?username=" . urlencode($username) . "&password=" . urlencode($password);
        $playlistName = trim($name) ?: ('Xtream (' . $username . ')');
        $prot = $isProtected ? '1' : '0';

        $stmt = $pdo->prepare("INSERT INTO playlists (device_id, mac_address, name, type, url, dns_id, username, password, is_protected, expiry)
                               VALUES (?, ?, ?, 'ibo', ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $device['id'],
            $mac,
            $playlistName,
            $playlistUrl,
            (int)$dnsId,
            $username,
            $password,
            $prot,
            $device['expire_date']
        ]);
    }

    public static function addM3uPlaylist(string $mac, string $name, string $m3uUrl, bool $isProtected = false): bool {
        $mac = self::normalizeMac($mac);
        $device = self::getOrCreateDeviceManual($mac);
        $pdo = self::getConnection();
        $prot = $isProtected ? '1' : '0';

        $stmt = $pdo->prepare("INSERT INTO playlists (device_id, mac_address, name, type, url, is_protected, expiry)
                               VALUES (?, ?, ?, 'm3u', ?, ?, ?)");
        return $stmt->execute([
            $device['id'],
            $mac,
            trim($name) ?: 'My M3U Playlist',
            trim($m3uUrl),
            $prot,
            $device['expire_date']
        ]);
    }

    public static function togglePlaylistProtection(int $id, string $mac): bool {
        $mac = self::normalizeMac($mac);
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT is_protected FROM playlists WHERE id = ? AND mac_address = ?");
        $stmt->execute([$id, $mac]);
        $curr = $stmt->fetchColumn();
        if ($curr === false) return false;
        $next = ($curr === '1') ? '0' : '1';
        $up = $pdo->prepare("UPDATE playlists SET is_protected = ? WHERE id = ? AND mac_address = ?");
        return $up->execute([$next, $id, $mac]);
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

    // --- Order & Activation Operations ---

    public static function createOrder(array $data): int {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("INSERT INTO orders (
            order_type, mac_address, device_key, reseller_id, plan, amount_dh, credits,
            payment_method, customer_name, customer_email, customer_phone, transaction_ref,
            proof_file, status, admin_notes, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");

        $stmt->execute([
            $data['order_type'] ?? 'client',
            !empty($data['mac_address']) ? self::normalizeMac($data['mac_address']) : null,
            $data['device_key'] ?? null,
            (int)($data['reseller_id'] ?? 0),
            $data['plan'] ?? 'sub_1y',
            (float)($data['amount_dh'] ?? 0.0),
            (int)($data['credits'] ?? 0),
            $data['payment_method'] ?? 'rib',
            $data['customer_name'] ?? null,
            $data['customer_email'] ?? null,
            $data['customer_phone'] ?? null,
            $data['transaction_ref'] ?? null,
            $data['proof_file'] ?? null,
            $data['admin_notes'] ?? null
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function getAllOrders(?string $type = null, ?string $status = null): array {
        $pdo = self::getConnection();
        $sql = "SELECT o.*, r.username as reseller_username, r.name as reseller_name 
                FROM orders o 
                LEFT JOIN resellers r ON o.reseller_id = r.id";
        $where = [];
        $params = [];
        if ($type) {
            $where[] = "o.order_type = ?";
            $params[] = $type;
        }
        if ($status) {
            $where[] = "o.status = ?";
            $params[] = $status;
        }
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY o.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getOrderById(int $id): ?array {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT o.*, r.username as reseller_username, r.name as reseller_name 
                               FROM orders o 
                               LEFT JOIN resellers r ON o.reseller_id = r.id 
                               WHERE o.id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    public static function getOrdersForDevice(string $mac): array {
        $mac = self::normalizeMac($mac);
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE mac_address = ? ORDER BY id DESC");
        $stmt->execute([$mac]);
        return $stmt->fetchAll();
    }

    public static function getOrdersForReseller(int $resellerId): array {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE reseller_id = ? ORDER BY id DESC");
        $stmt->execute([$resellerId]);
        return $stmt->fetchAll();
    }

    public static function approveOrder(int $id, ?string $adminNotes = null): array {
        $order = self::getOrderById($id);
        if (!$order) {
            return ['status' => false, 'message' => 'Order not found'];
        }
        if ($order['status'] === 'approved') {
            return ['status' => false, 'message' => 'Order already approved'];
        }

        $pdo = self::getConnection();

        if ($order['order_type'] === 'client') {
            if (empty($order['mac_address'])) {
                return ['status' => false, 'message' => 'No MAC address associated with this client order'];
            }
            $mac = self::normalizeMac($order['mac_address']);
            $device = self::getOrCreateDeviceManual($mac);
            $plan = $order['plan'] ?: 'sub_1y';
            $actRes = self::activateSubscription($device['id'], $plan);
            if (!$actRes['status']) {
                return ['status' => false, 'message' => 'Failed to activate device: ' . $actRes['message']];
            }
        } elseif ($order['order_type'] === 'reseller') {
            $resellerId = (int)$order['reseller_id'];
            $credits = (int)$order['credits'];
            if ($resellerId <= 0 || $credits <= 0) {
                return ['status' => false, 'message' => 'Invalid reseller order data'];
            }
            $up = $pdo->prepare("UPDATE resellers SET credits = credits + ? WHERE id = ?");
            $up->execute([$credits, $resellerId]);
        }

        $notes = $adminNotes !== null ? $adminNotes : ($order['admin_notes'] ?? '');
        $stmt = $pdo->prepare("UPDATE orders SET status = 'approved', admin_notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$notes, $id]);

        return ['status' => true, 'message' => 'Order approved and activated successfully'];
    }

    public static function rejectOrder(int $id, ?string $adminNotes = null): bool {
        $pdo = self::getConnection();
        $notes = $adminNotes ?: 'Rejected by admin';
        $stmt = $pdo->prepare("UPDATE orders SET status = 'rejected', admin_notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$notes, $id]);
    }

    public static function addResellerCredits(int $resellerId, int $credits): bool {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("UPDATE resellers SET credits = credits + ? WHERE id = ?");
        return $stmt->execute([$credits, $resellerId]);
    }
}
