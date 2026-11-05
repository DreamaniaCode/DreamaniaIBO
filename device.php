<?php
/**
 * Dreamania Player - End-User Device & Playlist Portal
 * Inspired by iboplayer.com
 * Allows TV users to upload playlists (M3U & Xtream Codes) and activate subscriptions (PayPal & RIB).
 */

declare(strict_types=1);

session_start();
require_once __DIR__ . '/db.php';

try {
    $pdo = DB::getConnection();
} catch (Exception $e) {
    die("Database Connection Error: " . htmlspecialchars($e->getMessage()));
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

$settings = DB::getAllSettings();
$price1Y = $settings['price_client_1y'] ?? '50';
$priceLife = $settings['price_client_life'] ?? '150';
$ribBank = $settings['rib_bank_name'] ?? 'CIH BANK';
$ribAccount = $settings['rib_account_name'] ?? 'DREAMANIA MEDIA';
$ribNumber = $settings['rib_number'] ?? '230 780 4567890123456789 12';
$ribSwift = $settings['rib_swift'] ?? 'CIHMMAMC';
$paypalEmail = $settings['paypal_email'] ?? 'contact@lumineasy.com';
$paypalMe = $settings['paypal_me_link'] ?? 'https://paypal.me/dreamaniacode';
$whatsapp = $settings['whatsapp_number'] ?? '+212600000000';

$msg = '';
$msgType = 'info';

// Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['client_mac'], $_SESSION['client_device_id']);
    header('Location: device.php');
    exit;
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postCsrf = $_POST['csrf'] ?? '';
    if (!hash_equals($csrf, $postCsrf)) {
        $msg = "Session expirée ou requête invalide (CSRF). Veuillez réessayer.";
        $msgType = "error";
    } else {
        $action = $_POST['action'] ?? '';

        // 1. Device Login
        if ($action === 'login') {
            $rawMac = trim($_POST['mac_address'] ?? '');
            $deviceKey = trim($_POST['device_key'] ?? '');

            if (empty($rawMac) || empty($deviceKey)) {
                $msg = "Veuillez saisir votre Adresse MAC et votre Clé d'appareil (Device Key).";
                $msgType = "error";
            } else {
                $normMac = DB::normalizeMac($rawMac);
                $device = DB::getDeviceByMacAndKey($normMac, $deviceKey, true);

                if ($device) {
                    $_SESSION['client_mac'] = $device['mac_address'];
                    $_SESSION['client_device_id'] = $device['id'];
                    header('Location: device.php');
                    exit;
                } else {
                    $existing = DB::getDeviceByMac($normMac);
                    if ($existing) {
                        $msg = "Clé d'appareil incorrecte pour cette adresse MAC. Vérifiez l'écran de votre téléviseur.";
                    } else {
                        $msg = "Appareil introuvable. Veuillez d'abord ouvrir l'application Dreamania Player sur votre TV.";
                    }
                    $msgType = "error";
                }
            }
        }

        // Must be logged in for subsequent actions
        $currentMac = $_SESSION['client_mac'] ?? '';
        if (!empty($currentMac)) {
            // 2. Add M3U Playlist
            if ($action === 'add_m3u') {
                $pName = trim($_POST['name'] ?? 'Ma Playlist M3U');
                $pUrl = trim($_POST['url'] ?? '');
                $isProtected = !empty($_POST['is_protected']);

                if (empty($pUrl) || !filter_var($pUrl, FILTER_VALIDATE_URL)) {
                    $msg = "Veuillez fournir une URL M3U/M3U8 valide.";
                    $msgType = "error";
                } else {
                    DB::addM3uPlaylist($currentMac, $pName, $pUrl, $isProtected);
                    $msg = "Playlist M3U ajoutée avec succès ! Rafraîchissez l'application sur votre TV.";
                    $msgType = "success";
                }
            }

            // 3. Add Xtream Codes Playlist
            elseif ($action === 'add_xtream') {
                $pName = trim($_POST['name'] ?? 'Serveur Xtream');
                $serverUrl = trim($_POST['server_url'] ?? '');
                $username = trim($_POST['username'] ?? '');
                $password = trim($_POST['password'] ?? '');
                $isProtected = !empty($_POST['is_protected']);

                if (empty($serverUrl) || empty($username) || empty($password)) {
                    $msg = "Veuillez remplir l'URL du serveur, le nom d'utilisateur et le mot de passe.";
                    $msgType = "error";
                } else {
                    DB::addXtreamPlaylist($currentMac, $pName, $serverUrl, $username, $password, $isProtected);
                    $msg = "Connexion Xtream Codes enregistrée avec succès ! Vos chaînes sont disponibles sur votre TV.";
                    $msgType = "success";
                }
            }

            // 4. Delete Playlist
            elseif ($action === 'delete_playlist') {
                $playlistId = (int)($_POST['playlist_id'] ?? 0);
                if ($playlistId > 0) {
                    DB::deletePlaylist($playlistId, $currentMac);
                    $msg = "Playlist supprimée avec succès.";
                    $msgType = "success";
                }
            }

            // 5. Toggle Playlist Protection (PIN lock)
            elseif ($action === 'toggle_protection') {
                $playlistId = (int)($_POST['playlist_id'] ?? 0);
                if ($playlistId > 0) {
                    DB::togglePlaylistProtection($playlistId, $currentMac);
                    $msg = "Verrouillage parental mis à jour pour cette playlist.";
                    $msgType = "success";
                }
            }

            // 6. Order / Renew Subscription (PayPal or RIB)
            elseif ($action === 'order_subscription') {
                $plan = $_POST['plan'] ?? 'sub_1y';
                if (!in_array($plan, ['sub_1y', 'sub_lifetime'])) {
                    $plan = 'sub_1y';
                }

                $amount = ($plan === 'sub_lifetime') ? (float)$priceLife : (float)$price1Y;
                $paymentMethod = $_POST['payment_method'] ?? 'rib';
                if (!in_array($paymentMethod, ['rib', 'paypal'])) {
                    $paymentMethod = 'rib';
                }

                $customerName = trim($_POST['customer_name'] ?? '');
                $customerPhone = trim($_POST['customer_phone'] ?? '');
                $customerEmail = trim($_POST['customer_email'] ?? '');
                $transactionRef = trim($_POST['transaction_ref'] ?? '');

                // Handle file upload
                $proofPath = null;
                if (!empty($_FILES['proof_file']['name']) && $_FILES['proof_file']['error'] === UPLOAD_ERR_OK) {
                    $fileTmp = $_FILES['proof_file']['tmp_name'];
                    $fileName = $_FILES['proof_file']['name'];
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

                    if (in_array($fileExt, $allowedExts) && filesize($fileTmp) < 10 * 1024 * 1024) {
                        $cleanMac = str_replace([':', '-'], '', $currentMac);
                        $newFileName = 'proof_client_' . $cleanMac . '_' . time() . '_' . random_int(1000, 9999) . '.' . $fileExt;
                        $uploadDir = __DIR__ . '/data/proofs/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0775, true);
                        }
                        $destPath = $uploadDir . $newFileName;
                        if (move_uploaded_file($fileTmp, $destPath)) {
                            $proofPath = 'data/proofs/' . $newFileName;
                        }
                    }
                }

                $deviceObj = DB::getDeviceByMac($currentMac);

                $orderId = DB::createOrder([
                    'order_type'      => 'client',
                    'mac_address'     => $currentMac,
                    'device_key'      => $deviceObj['device_key'] ?? '',
                    'plan'            => $plan,
                    'amount_dh'       => $amount,
                    'credits'         => 0,
                    'payment_method'  => $paymentMethod,
                    'customer_name'   => $customerName,
                    'customer_phone'  => $customerPhone,
                    'customer_email'  => $customerEmail,
                    'transaction_ref' => $transactionRef,
                    'proof_file'      => $proofPath,
                    'admin_notes'     => null
                ]);

                $msg = "Demande d'activation N° #{$orderId} transmise avec succès ! Nous validons votre paiement dans les plus brefs délais.";
                $msgType = "success";
            }
        }
    }
}

// Current Client State
$isClientLogged = !empty($_SESSION['client_mac']);
$clientDevice = null;
$clientPlaylists = [];
$clientOrders = [];

if ($isClientLogged) {
    $clientDevice = DB::getDeviceByMac($_SESSION['client_mac']);
    if (!$clientDevice) {
        unset($_SESSION['client_mac']);
        $isClientLogged = false;
    } else {
        $clientPlaylists = DB::getPlaylistsForDevice($_SESSION['client_mac']);
        $clientOrders = DB::getOrdersForDevice($_SESSION['client_mac']);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Client &amp; Playlists | Dreamania Player</title>
    <meta name="description" content="Gérez vos playlists IPTV et activez votre abonnement Dreamania Player pour Smart TV et Android TV.">
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0b0914;
            --surface: #120e22;
            --card: #18132e;
            --card-hover: #201a3d;
            --primary: #8b5cf6;
            --primary-glow: rgba(139, 92, 246, 0.35);
            --secondary: #ec4899;
            --accent-gold: #f59e0b;
            --accent-gold-glow: rgba(245, 158, 11, 0.3);
            --success: #10b981;
            --danger: #ef4444;
            --text: #f8fafc;
            --text-secondary: #94a3b8;
            --border: rgba(255, 255, 255, 0.08);
            --border-highlight: rgba(139, 92, 246, 0.4);
            --radius-sm: 8px;
            --radius: 14px;
            --radius-lg: 20px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: 
                radial-gradient(circle at 15% 15%, rgba(139, 92, 246, 0.12) 0%, transparent 40%),
                radial-gradient(circle at 85% 85%, rgba(236, 72, 153, 0.1) 0%, transparent 40%);
            background-attachment: fixed;
        }

        /* Header */
        .portal-header {
            background: rgba(18, 14, 34, 0.85);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--border);
            padding: 16px 24px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #fff;
        }
        .brand img {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 14px var(--primary-glow);
        }
        .brand-text h1 {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .brand-text span {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: 1px;
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            font-size: 13px;
            font-weight: 700;
            border-radius: var(--radius-sm);
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
            color: #fff;
            box-shadow: 0 4px 16px var(--primary-glow);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
        }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
            border: 1px solid var(--border);
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: var(--primary);
        }
        .btn-gold {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #000;
            box-shadow: 0 4px 16px var(--accent-gold-glow);
        }
        .btn-gold:hover {
            transform: translateY(-2px);
            filter: brightness(1.08);
        }
        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
        }
        .btn-whatsapp {
            background: #25d366;
            color: #fff;
            font-weight: 800;
        }
        .btn-whatsapp:hover {
            filter: brightness(1.08);
            transform: translateY(-2px);
        }
        .btn-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .btn-danger:hover {
            background: #ef4444;
            color: #fff;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        .btn-lg {
            padding: 14px 28px;
            font-size: 15px;
        }

        /* Container */
        .main-wrapper {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
            width: 100%;
            flex: 1;
        }

        /* Alerts */
        .alert {
            padding: 14px 20px;
            border-radius: var(--radius-sm);
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.4);
            color: #34d399;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #f87171;
        }

        /* Cards */
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px;
            margin-bottom: 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            position: relative;
        }
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 12px;
        }
        .card-title {
            font-size: 18px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
        }

        /* Device Bar (Top when logged in) */
        .device-bar {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.15) 0%, rgba(24, 19, 46, 0.9) 100%);
            border: 1px solid var(--border-highlight);
            border-radius: var(--radius);
            padding: 24px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            align-items: center;
            margin-bottom: 30px;
        }
        .device-bar-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .bar-label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--text-secondary);
            letter-spacing: 0.8px;
        }
        .bar-val {
            font-family: 'JetBrains Mono', monospace;
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-active { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); }
        .badge-lifetime { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); }
        .badge-trial { background: rgba(139, 92, 246, 0.2); color: #c084fc; border: 1px solid rgba(139, 92, 246, 0.4); }
        .badge-expired { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); }

        /* Forms */
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
        }
        input[type="text"], input[type="password"], input[type="url"], input[type="tel"], input[type="email"], select, textarea {
            width: 100%;
            padding: 12px 16px;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: #fff;
            font-size: 14px;
            outline: none;
            transition: all 0.2s;
            font-family: inherit;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-size: 13px;
            color: var(--text);
            margin-top: 10px;
        }
        .checkbox-label input {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        /* Tabs */
        .nav-tabs {
            display: flex;
            gap: 10px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 24px;
            padding-bottom: 12px;
        }
        .tab-btn {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-secondary);
            padding: 10px 18px;
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .tab-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }
        .tab-btn.active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
            box-shadow: 0 4px 14px var(--primary-glow);
        }

        /* Pricing Selector Cards */
        .plan-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        .plan-card {
            background: rgba(0, 0, 0, 0.25);
            border: 2px solid var(--border);
            border-radius: var(--radius);
            padding: 22px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        .plan-card:hover {
            border-color: var(--border-highlight);
            transform: translateY(-2px);
        }
        .plan-card.selected {
            border-color: var(--primary);
            background: rgba(139, 92, 246, 0.08);
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.2);
        }
        .plan-card input[type="radio"] {
            display: none;
        }
        .plan-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .plan-title {
            font-size: 16px;
            font-weight: 800;
            color: #fff;
        }
        .plan-price {
            font-size: 26px;
            font-weight: 800;
            color: var(--primary);
        }
        .plan-badge {
            position: absolute;
            top: -12px;
            right: 18px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #000;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 10px;
            border-radius: 12px;
            text-transform: uppercase;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.4);
        }

        /* Bank & Payment Info Box */
        .payment-box {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 20px;
            margin-bottom: 20px;
        }
        .copy-pill {
            background: rgba(255, 255, 255, 0.08);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            cursor: pointer;
            color: #cbd5e1;
            margin-left: 8px;
            border: 1px solid var(--border);
        }
        .copy-pill:hover {
            background: var(--primary);
            color: #fff;
        }

        /* Tables */
        .table-responsive {
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
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-secondary);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            border-bottom: 1px solid var(--border);
        }
        td {
            padding: 14px;
            border-bottom: 1px solid var(--border);
            color: #e2e8f0;
        }
        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        /* Visual Guide */
        .guide-box {
            background: radial-gradient(circle at 10% 50%, rgba(139, 92, 246, 0.15) 0%, rgba(24, 19, 46, 0.7) 100%);
            border: 1px dashed var(--border-highlight);
            border-radius: var(--radius);
            padding: 24px;
            margin-top: 24px;
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .guide-icon {
            font-size: 40px;
        }

        /* Footer */
        .portal-footer {
            border-top: 1px solid var(--border);
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: auto;
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header class="portal-header">
        <div class="header-content">
            <a href="landing.php" class="brand">
                <img src="assets/img/icon.png" alt="Logo">
                <div class="brand-text">
                    <h1>DREAMANIA <span style="color:var(--primary);">PLAYER</span></h1>
                    <span>ESPACE CLIENT &amp; PLAYLISTS</span>
                </div>
            </a>

            <div class="header-nav">
                <a href="landing.php" class="btn btn-secondary btn-sm">🏠 Accueil</a>
                <a href="credits.php" class="btn btn-secondary btn-sm">⚡ Crédits Revendeur</a>
                <?php if ($isClientLogged): ?>
                    <a href="device.php?action=logout" class="btn btn-danger btn-sm">Déconnexion</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="main-wrapper">

        <?php if (!empty($msg)): ?>
            <div class="alert <?= $msgType === 'success' ? 'alert-success' : 'alert-error' ?>">
                <span><?= $msgType === 'success' ? '✅' : '⚠️' ?></span>
                <div><?= htmlspecialchars($msg) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!$isClientLogged): ?>
            <!-- LOGIN VIEW (Inspired by iboplayer.com) -->
            <div style="max-width: 540px; margin: 40px auto 20px;">
                <div class="card">
                    <div style="text-align: center; margin-bottom: 24px;">
                        <span style="font-size: 42px;">📺</span>
                        <h2 style="font-size: 22px; font-weight: 800; margin-top: 8px;">Gérer votre Téléviseur &amp; Playlists</h2>
                        <p style="color: var(--text-secondary); font-size: 13px; margin-top: 6px;">
                            Connectez votre appareil pour téléverser vos listes de chaînes ou activer votre licence.
                        </p>
                    </div>

                    <form method="post" action="device.php">
                        <input type="hidden" name="action" value="login">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                        <div class="form-group">
                            <label for="mac_address">ADRESSE MAC DE L'APPAREIL</label>
                            <input type="text" id="mac_address" name="mac_address" required 
                                   placeholder="00:1A:79:XX:XX:XX" autocomplete="off"
                                   style="font-family: 'JetBrains Mono', monospace; font-size: 16px; letter-spacing: 1px;">
                            <small style="color: var(--text-secondary); font-size: 11px; margin-top: 4px; display: block;">
                                Format: 12 caractères hexadécimaux séparés par des deux-points.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="device_key">CLÉ D'APPAREIL (DEVICE KEY)</label>
                            <input type="text" id="device_key" name="device_key" required maxlength="6"
                                   placeholder="Ex: 584920" autocomplete="off"
                                   style="font-family: 'JetBrains Mono', monospace; font-size: 16px; letter-spacing: 2px;">
                            <small style="color: var(--text-secondary); font-size: 11px; margin-top: 4px; display: block;">
                                Code à 6 chiffres affiché à l'écran de l'application sur votre TV.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 10px;">
                            🔓 Accéder à mon Appareil
                        </button>
                    </form>
                </div>

                <!-- How to Find Credentials Guide -->
                <div class="guide-box">
                    <div class="guide-icon">💡</div>
                    <div style="font-size: 13px; line-height: 1.6;">
                        <strong style="color: #fff; font-size: 14px; display: block; margin-bottom: 4px;">Comment trouver vos identifiants ?</strong>
                        1. Lancez <strong>Dreamania Player</strong> sur votre Smart TV, Box Android ou Fire TV.<br>
                        2. Votre <strong>Adresse MAC</strong> et votre <strong>Clé (Device Key)</strong> sont affichées en grand sur l'écran d'accueil et dans les Paramètres.<br>
                        3. Saisissez-les ci-dessus pour envoyer vos listes ou activer votre licence.
                    </div>
                </div>

                <!-- Quick Pricing Highlights -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 24px;">
                    <div style="background: rgba(139, 92, 246, 0.08); border: 1px solid var(--border-highlight); border-radius: var(--radius); padding: 16px; text-align: center;">
                        <span style="font-size: 12px; font-weight: 700; color: var(--primary); text-transform: uppercase;">Abonnement 1 An</span>
                        <div style="font-size: 24px; font-weight: 800; color: #fff; margin: 4px 0;"><?= htmlspecialchars($price1Y) ?> DH</div>
                        <span style="font-size: 11px; color: var(--text-secondary);">Renouvellement 12 mois</span>
                    </div>
                    <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.4); border-radius: var(--radius); padding: 16px; text-align: center;">
                        <span style="font-size: 12px; font-weight: 700; color: var(--accent-gold); text-transform: uppercase;">Licence À Vie</span>
                        <div style="font-size: 24px; font-weight: 800; color: #fff; margin: 4px 0;"><?= htmlspecialchars($priceLife) ?> DH</div>
                        <span style="font-size: 11px; color: var(--text-secondary);">Paiement unique Lifetime</span>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- AUTHENTICATED DASHBOARD -->

            <!-- Top Device Bar -->
            <?php
                $isPaid = (bool)$clientDevice['is_paid'];
                $isTrial = (bool)$clientDevice['is_trial'];
                $expiry = $clientDevice['expire_date'] ?? '';
                $isLifetime = ($expiry >= '2090-01-01');
                $isExpired = (strtotime($expiry) < time());
            ?>
            <div class="device-bar">
                <div class="device-bar-item">
                    <span class="bar-label">Adresse MAC</span>
                    <span class="bar-val">
                        <?= htmlspecialchars($clientDevice['mac_address']) ?>
                        <span class="copy-pill" onclick="copyText('<?= htmlspecialchars($clientDevice['mac_address']) ?>')">Copier</span>
                    </span>
                </div>
                <div class="device-bar-item">
                    <span class="bar-label">Clé d'Appareil</span>
                    <span class="bar-val"><?= htmlspecialchars($clientDevice['device_key']) ?></span>
                </div>
                <div class="device-bar-item">
                    <span class="bar-label">Statut Licence</span>
                    <div>
                        <?php if ($isLifetime): ?>
                            <span class="status-badge badge-lifetime">⭐ Lifetime VIP (Illimité)</span>
                        <?php elseif ($isExpired): ?>
                            <span class="status-badge badge-expired">❌ Licence Expirée</span>
                        <?php elseif ($isPaid): ?>
                            <span class="status-badge badge-active">✅ Actif (1 An)</span>
                        <?php elseif ($isTrial): ?>
                            <span class="status-badge badge-trial">⏳ Essai Gratuit</span>
                        <?php else: ?>
                            <span class="status-badge badge-trial">En cours</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="device-bar-item">
                    <span class="bar-label">Date d'Expiration</span>
                    <span class="bar-val" style="font-size: 15px;">
                        <?= $isLifetime ? 'Accès Permanent' : htmlspecialchars($expiry) ?>
                    </span>
                </div>
                <div class="device-bar-item" style="text-align: right;">
                    <a href="#activation-section" class="btn btn-gold btn-sm">
                        ⚡ Activer / Prolonger
                    </a>
                </div>
            </div>

            <!-- SECTION 1: Active Playlists -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>📋</span>
                        <span>Mes Playlists Connectées (<?= count($clientPlaylists) ?>)</span>
                    </div>
                    <a href="#add-playlist-box" class="btn btn-primary btn-sm">
                        ➕ Ajouter une Playlist
                    </a>
                </div>

                <?php if (empty($clientPlaylists)): ?>
                    <div style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
                        <span style="font-size: 40px; display: block; margin-bottom: 10px;">📡</span>
                        <h3 style="color: #fff; font-size: 16px; margin-bottom: 6px;">Aucune playlist configurée pour cet appareil</h3>
                        <p style="font-size: 13px; max-width: 480px; margin: 0 auto;">
                            Ajoutez votre lien M3U ou vos identifiants Xtream Codes ci-dessous pour profiter de vos chaînes immédiatement sur votre TV.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nom de la Playlist</th>
                                    <th>Type</th>
                                    <th>Code Parental (PIN)</th>
                                    <th>Date d'Ajout</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clientPlaylists as $pl): ?>
                                    <tr>
                                        <td>
                                            <strong style="color: #fff;"><?= htmlspecialchars($pl['name']) ?></strong>
                                            <?php if ($pl['type'] === 'ibo'): ?>
                                                <div style="font-size: 11px; color: var(--text-secondary); font-family: monospace;">
                                                    User: <?= htmlspecialchars($pl['username'] ?? '') ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span style="padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; background: rgba(255,255,255,0.06);">
                                                <?= $pl['type'] === 'ibo' ? 'Xtream Codes' : 'Lien M3U' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($pl['is_protected']) && $pl['is_protected'] === '1'): ?>
                                                <span style="color: #f59e0b; font-weight: 700; font-size: 12px;">🔒 Protégé (PIN)</span>
                                            <?php else: ?>
                                                <span style="color: #94a3b8; font-size: 12px;">🔓 Public</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="color: var(--text-secondary); font-size: 12px;">
                                            <?= htmlspecialchars(substr($pl['created_at'] ?? '', 0, 10)) ?>
                                        </td>
                                        <td style="text-align: right; white-space: nowrap;">
                                            <!-- Toggle Protection Form -->
                                            <form method="post" action="device.php" style="display: inline-block;">
                                                <input type="hidden" name="action" value="toggle_protection">
                                                <input type="hidden" name="playlist_id" value="<?= (int)$pl['id'] ?>">
                                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                                <button type="submit" class="btn btn-secondary btn-sm" title="Activer / Désactiver le verrouillage parental">
                                                    <?= (!empty($pl['is_protected']) && $pl['is_protected'] === '1') ? '🔓 Déverrouiller' : '🔒 Verrouiller' ?>
                                                </button>
                                            </form>

                                            <!-- Delete Form -->
                                            <form method="post" action="device.php" style="display: inline-block; margin-left: 6px;" 
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette playlist ?');">
                                                <input type="hidden" name="action" value="delete_playlist">
                                                <input type="hidden" name="playlist_id" value="<?= (int)$pl['id'] ?>">
                                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    🗑️ Supprimer
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- SECTION 2: Add Playlist Form (Tabs: M3U vs Xtream Codes) -->
            <div class="card" id="add-playlist-box">
                <div class="card-header">
                    <div class="card-title">
                        <span>➕</span>
                        <span>Ajouter une Nouvelle Playlist sur ma TV</span>
                    </div>
                </div>

                <div class="nav-tabs">
                    <button class="tab-btn active" onclick="switchPlaylistTab('tab-m3u')">
                        🔗 Lien M3U / M3U8
                    </button>
                    <button class="tab-btn" onclick="switchPlaylistTab('tab-xtream')">
                        ⚡ Xtream Codes API
                    </button>
                </div>

                <!-- Tab 1: M3U Playlist Form -->
                <div id="tab-m3u" class="tab-pane">
                    <form method="post" action="device.php">
                        <input type="hidden" name="action" value="add_m3u">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                        <div class="form-group">
                            <label for="m3u_name">NOM DE LA PLAYLIST</label>
                            <input type="text" id="m3u_name" name="name" required placeholder="Ex: Mon Abonnement IPTV" value="Mon IPTV">
                        </div>

                        <div class="form-group">
                            <label for="m3u_url">URL DE LA PLAYLIST (M3U / M3U8)</label>
                            <input type="url" id="m3u_url" name="url" required placeholder="http://serveur.com:8080/get.php?username=...&amp;password=...&amp;type=m3u_plus">
                            <small style="color: var(--text-secondary); font-size: 11px; margin-top: 4px; display: block;">
                                Fourni par votre prestataire IPTV.
                            </small>
                        </div>

                        <label class="checkbox-label">
                            <input type="checkbox" name="is_protected" value="1">
                            <span>Protéger cette playlist avec le code parental (PIN par défaut: 0000)</span>
                        </label>

                        <button type="submit" class="btn btn-primary btn-lg" style="margin-top: 20px;">
                            🚀 Envoyer la Playlist M3U vers ma TV
                        </button>
                    </form>
                </div>

                <!-- Tab 2: Xtream Codes API Form -->
                <div id="tab-xtream" class="tab-pane" style="display: none;">
                    <form method="post" action="device.php">
                        <input type="hidden" name="action" value="add_xtream">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                        <div class="form-group">
                            <label for="xtream_name">NOM DE LA PLAYLIST</label>
                            <input type="text" id="xtream_name" name="name" required placeholder="Ex: Serveur VIP" value="Serveur VIP">
                        </div>

                        <div class="form-group">
                            <label for="xtream_server">URL DU SERVEUR / DNS XTREAM</label>
                            <input type="url" id="xtream_server" name="server_url" required placeholder="http://monserveur.net:8080">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label for="xtream_user">NOM D'UTILISATEUR (USERNAME)</label>
                                <input type="text" id="xtream_user" name="username" required placeholder="Nom d'utilisateur">
                            </div>
                            <div class="form-group">
                                <label for="xtream_pass">MOT DE PASSE (PASSWORD)</label>
                                <input type="password" id="xtream_pass" name="password" required placeholder="Mot de passe">
                            </div>
                        </div>

                        <label class="checkbox-label">
                            <input type="checkbox" name="is_protected" value="1">
                            <span>Protéger cette playlist avec le code parental (PIN par défaut: 0000)</span>
                        </label>

                        <button type="submit" class="btn btn-primary btn-lg" style="margin-top: 20px;">
                            🚀 Connecter Xtream Codes vers ma TV
                        </button>
                    </form>
                </div>
            </div>

            <!-- SECTION 3: Activation & Payment (PayPal & RIB) -->
            <div class="card" id="activation-section">
                <div class="card-header">
                    <div class="card-title">
                        <span>💳</span>
                        <span>Activer ou Prolonger mon Abonnement Dreamania Player</span>
                    </div>
                </div>

                <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 24px;">
                    Activez votre appareil en toute sécurité. Choisissez votre formule et payez simplement par <strong>Virement Bancaire (RIB Maroc)</strong> ou par <strong>PayPal</strong>.
                </p>

                <form method="post" action="device.php" enctype="multipart/form-data" id="order-form">
                    <input type="hidden" name="action" value="order_subscription">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                    <!-- Plan Selection -->
                    <label style="font-weight: 700; margin-bottom: 10px; display: block; color: #fff;">1. CHOISISSEZ VOTRE FORMULE :</label>
                    <div class="plan-cards">
                        <label class="plan-card selected" onclick="selectPlan('sub_1y', this)">
                            <input type="radio" name="plan" value="sub_1y" checked>
                            <div class="plan-header">
                                <div class="plan-title">Abonnement 1 An</div>
                                <div class="plan-price">€5.00 / $5.00 <span style="font-size: 13px; color: var(--text-secondary);">(<?= htmlspecialchars($price1Y) ?> DH)</span></div>
                            </div>
                            <ul style="list-style: none; font-size: 12px; color: var(--text-secondary); line-height: 1.8;">
                                <li>✔️ Accès complet pendant 12 mois</li>
                                <li>✔️ Compatible M3U &amp; Xtream Codes</li>
                                <li>✔️ Mises à jour gratuites incluses</li>
                            </ul>
                        </label>

                        <label class="plan-card" onclick="selectPlan('sub_lifetime', this)">
                            <span class="plan-badge">RECOMMANDÉ</span>
                            <input type="radio" name="plan" value="sub_lifetime">
                            <div class="plan-header">
                                <div class="plan-title">Licence Lifetime (À Vie)</div>
                                <div class="plan-price" style="color: var(--accent-gold);">€15.00 / $15.00 <span style="font-size: 13px; color: var(--text-secondary);">(<?= htmlspecialchars($priceLife) ?> DH)</span></div>
                            </div>
                            <ul style="list-style: none; font-size: 12px; color: var(--text-secondary); line-height: 1.8;">
                                <li>⭐ <strong>Paiement unique, plus jamais de frais</strong></li>
                                <li>✔️ Activation permanente de votre téléviseur</li>
                                <li>✔️ Support prioritaire VIP</li>
                            </ul>
                        </label>
                    </div>

                    <!-- Payment Method Selection -->
                    <label style="font-weight: 700; margin-bottom: 10px; display: block; color: #fff;">2. MODE DE PAIEMENT :</label>
                    <div class="nav-tabs" style="margin-bottom: 18px;">
                        <button type="button" class="tab-btn active" id="btn-pay-rib" onclick="selectPaymentMethod('rib')">
                            🏦 Virement Bancaire (RIB Maroc)
                        </button>
                        <button type="button" class="tab-btn" id="btn-pay-paypal" onclick="selectPaymentMethod('paypal')">
                            🅿️ PayPal / Carte Bancaire
                        </button>
                    </div>
                    <input type="hidden" name="payment_method" id="payment_method_input" value="rib">

                    <!-- RIB Instructions Box -->
                    <div id="box-rib" class="payment-box">
                        <strong style="color: #fff; font-size: 14px; display: block; margin-bottom: 12px;">
                            Coordonnées Bancaires Officielles (Virement instantané ou standard) :
                        </strong>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; font-size: 13px;">
                            <div>
                                <span style="color: var(--text-secondary); display: block; font-size: 11px;">BANQUE</span>
                                <strong style="color: #fff;"><?= htmlspecialchars($ribBank) ?></strong>
                            </div>
                            <div>
                                <span style="color: var(--text-secondary); display: block; font-size: 11px;">TITULAIRE DU COMPTE</span>
                                <strong style="color: #fff;"><?= htmlspecialchars($ribAccount) ?></strong>
                            </div>
                            <div>
                                <span style="color: var(--text-secondary); display: block; font-size: 11px;">CODE SWIFT / BIC</span>
                                <strong style="color: #fff;"><?= htmlspecialchars($ribSwift) ?></strong>
                            </div>
                        </div>

                        <div style="margin-top: 16px; background: rgba(0,0,0,0.4); padding: 12px 16px; border-radius: var(--radius-sm); border: 1px dashed var(--border-highlight);">
                            <span style="color: var(--text-secondary); font-size: 11px; display: block; margin-bottom: 4px;">RIB (24 CHIFFRES)</span>
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                                <strong style="font-family: 'JetBrains Mono', monospace; font-size: 16px; color: #fbbf24; letter-spacing: 1px;">
                                    <?= htmlspecialchars($ribNumber) ?>
                                </strong>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="copyText('<?= htmlspecialchars(str_replace(' ', '', $ribNumber)) ?>')">
                                    📋 Copier le RIB
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- PayPal Instructions Box -->
                    <div id="box-paypal" class="payment-box" style="display: none;">
                        <strong style="color: #fff; font-size: 14px; display: block; margin-bottom: 12px;">
                            Paiement Sécurisé PayPal :
                        </strong>
                        <p style="color: var(--text-secondary); font-size: 13px; line-height: 1.6; margin-bottom: 14px;">
                            Envoyez votre paiement directement via PayPal en sélectionnant <strong>"Envoi entre proches"</strong> pour éviter des frais de commission, ou cliquez sur le lien direct ci-dessous :
                        </p>
                        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                            <a href="<?= htmlspecialchars($paypalMe) ?>" target="_blank" rel="noopener" class="btn btn-primary btn-sm">
                                🔗 Ouvrir mon lien PayPal.me
                            </a>
                            <span style="color: var(--text-secondary); font-size: 12px;">ou envoyer à : <strong style="color: #fff;"><?= htmlspecialchars($paypalEmail) ?></strong></span>
                        </div>
                    </div>

                    <!-- Customer Contact & Proof Fields -->
                    <label style="font-weight: 700; margin: 20px 0 10px; display: block; color: #fff;">3. VOS INFORMATIONS &amp; JUSTIFICATIF :</label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
                        <div class="form-group">
                            <label for="customer_name">VOTRE NOM COMPLET</label>
                            <input type="text" id="customer_name" name="customer_name" required placeholder="Nom &amp; Prénom">
                        </div>
                        <div class="form-group">
                            <label for="customer_phone">NUMÉRO WHATSAPP</label>
                            <input type="tel" id="customer_phone" name="customer_phone" required placeholder="+212 6XX XX XX XX">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
                        <div class="form-group">
                            <label for="transaction_ref">RÉFÉRENCE / TRANSACTION / COMMENTAIRE (OPTIONNEL)</label>
                            <input type="text" id="transaction_ref" name="transaction_ref" placeholder="Ex: N° Opération CIH ou Transaction PayPal">
                        </div>
                        <div class="form-group">
                            <label for="proof_file">REÇU DE VIREMENT / CAPTURE D'ÉCRAN (JPG, PNG, PDF)</label>
                            <input type="file" id="proof_file" name="proof_file" accept="image/*,application/pdf">
                        </div>
                    </div>

                    <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap; margin-top: 10px;">
                        <button type="submit" class="btn btn-gold btn-lg">
                            ✅ Valider ma Demande d'Activation
                        </button>
                        <button type="button" class="btn btn-whatsapp btn-lg" onclick="sendViaWhatsApp()">
                            💬 Confirmer via WhatsApp
                        </button>
                    </div>
                </form>
            </div>

            <!-- SECTION 4: Order History for this Device -->
            <?php if (!empty($clientOrders)): ?>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <span>📜</span>
                            <span>Historique de mes Demandes d'Activation</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>N° Commande</th>
                                    <th>Date</th>
                                    <th>Formule</th>
                                    <th>Montant</th>
                                    <th>Paiement</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clientOrders as $ord): ?>
                                    <tr>
                                        <td><strong>#<?= (int)$ord['id'] ?></strong></td>
                                        <td style="color: var(--text-secondary);"><?= htmlspecialchars(substr($ord['created_at'], 0, 16)) ?></td>
                                        <td>
                                            <strong><?= $ord['plan'] === 'sub_lifetime' ? '⭐ Lifetime (À Vie)' : '1 An' ?></strong>
                                        </td>
                                        <td style="color: #fbbf24; font-weight: 700;"><?= htmlspecialchars((string)$ord['amount_dh']) ?> DH</td>
                                        <td>
                                            <span style="text-transform: uppercase; font-size: 11px; padding: 2px 6px; background: rgba(255,255,255,0.06); border-radius: 4px;">
                                                <?= htmlspecialchars($ord['payment_method']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($ord['status'] === 'approved'): ?>
                                                <span class="status-badge badge-active">✅ Validé &amp; Activé</span>
                                            <?php elseif ($ord['status'] === 'rejected'): ?>
                                                <span class="status-badge badge-expired">❌ Rejeté</span>
                                            <?php else: ?>
                                                <span class="status-badge badge-trial">⏳ En cours de validation</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>

    <!-- Footer -->
    <footer class="portal-footer">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                © <?= date('Y') ?> <strong>Dreamania Player</strong>. Tous droits réservés.
            </div>
            <div>
                Besoin d'assistance ? Contactez-nous sur WhatsApp : 
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $whatsapp) ?>" target="_blank" style="color: #25d366; text-decoration: none; font-weight: 700;">
                    <?= htmlspecialchars($whatsapp) ?>
                </a>
            </div>
        </div>
    </footer>

    <script>
        // Tab Switcher for Playlist Form
        function switchPlaylistTab(tabId) {
            document.querySelectorAll('#add-playlist-box .tab-btn').forEach(btn => btn.classList.remove('active'));
            event.currentTarget.classList.add('active');

            document.getElementById('tab-m3u').style.display = (tabId === 'tab-m3u') ? 'block' : 'none';
            document.getElementById('tab-xtream').style.display = (tabId === 'tab-xtream') ? 'block' : 'none';
        }

        // Plan Selector
        function selectPlan(planValue, element) {
            document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
            element.classList.add('selected');
            element.querySelector('input[type="radio"]').checked = true;
        }

        // Payment Method Selector
        function selectPaymentMethod(method) {
            const btnRib = document.getElementById('btn-pay-rib');
            const btnPaypal = document.getElementById('btn-pay-paypal');
            const boxRib = document.getElementById('box-rib');
            const boxPaypal = document.getElementById('box-paypal');
            const inputMethod = document.getElementById('payment_method_input');

            if (method === 'rib') {
                btnRib.classList.add('active');
                btnPaypal.classList.remove('active');
                boxRib.style.display = 'block';
                boxPaypal.style.display = 'none';
                inputMethod.value = 'rib';
            } else {
                btnPaypal.classList.add('active');
                btnRib.classList.remove('active');
                boxRib.style.display = 'none';
                boxPaypal.style.display = 'block';
                inputMethod.value = 'paypal';
            }
        }

        // Copy Text Helper
        function copyText(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Copié dans le presse-papier : ' + text);
            }).catch(() => {
                prompt('Copiez le texte :', text);
            });
        }

        // WhatsApp Direct Confirmation
        function sendViaWhatsApp() {
            const mac = "<?= htmlspecialchars($clientDevice['mac_address'] ?? '') ?>";
            const planRadio = document.querySelector('input[name="plan"]:checked');
            const plan = planRadio ? planRadio.value : 'sub_1y';
            const planText = (plan === 'sub_lifetime') ? 'Licence Lifetime À Vie (<?= htmlspecialchars($priceLife) ?> DH)' : 'Abonnement 1 An (<?= htmlspecialchars($price1Y) ?> DH)';
            const name = document.getElementById('customer_name').value || 'Client';
            const phone = document.getElementById('customer_phone').value || '';
            const ref = document.getElementById('transaction_ref').value || 'Paiement effectué';

            const message = `Bonjour Dreamania Player, je souhaite confirmer le paiement pour mon appareil :\n` +
                            `📺 MAC : ${mac}\n` +
                            `⚡ Formule : ${planText}\n` +
                            `👤 Nom : ${name}\n` +
                            `📞 Tél : ${phone}\n` +
                            `💳 Réf : ${ref}\n\n` +
                            `Merci d'activer mon appareil !`;

            const cleanPhone = "<?= preg_replace('/[^0-9]/', '', $whatsapp) ?>";
            const waUrl = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(message)}`;
            window.open(waUrl, '_blank');
        }

        // Auto-Format MAC address input
        const macInput = document.getElementById('mac_address');
        if (macInput) {
            macInput.addEventListener('input', function(e) {
                let v = e.target.value.replace(/[^a-fA-F0-9]/g, '').toUpperCase();
                if (v.length > 12) v = v.substring(0, 12);
                const parts = [];
                for (let i = 0; i < v.length; i += 2) {
                    parts.push(v.substring(i, i + 2));
                }
                e.target.value = parts.join(':');
            });
        }
    </script>
</body>
</html>
