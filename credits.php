<?php
/**
 * Dreamania Player - Reseller Credits Portal
 * 1 Credit = 1 Year Activation
 * Packs:
 * 10 Credits  = 100 DH (10 DH/cr)
 * 20 Credits  = 180 DH (9 DH/cr)
 * 30 Credits  = 240 DH (8 DH/cr)
 * 50 Credits  = 350 DH (7 DH/cr)
 * 100 Credits = 600 DH (6 DH/cr)
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
$pricePacks = [
    10  => (float)($settings['price_reseller_10'] ?? 100),
    20  => (float)($settings['price_reseller_20'] ?? 180),
    30  => (float)($settings['price_reseller_30'] ?? 240),
    50  => (float)($settings['price_reseller_50'] ?? 350),
    100 => (float)($settings['price_reseller_100'] ?? 600)
];

$ribBank = $settings['rib_bank_name'] ?? 'CIH BANK';
$ribAccount = $settings['rib_account_name'] ?? 'DREAMANIA MEDIA';
$ribNumber = $settings['rib_number'] ?? '230 780 4567890123456789 12';
$ribSwift = $settings['rib_swift'] ?? 'CIHMMAMC';
$paypalEmail = $settings['paypal_email'] ?? 'contact@lumineasy.com';
$paypalMe = $settings['paypal_me_link'] ?? 'https://paypal.me/dreamaniacode';
$whatsapp = $settings['whatsapp_number'] ?? '+212600000000';

$msg = '';
$msgType = 'info';

// Check Reseller Auth State
$isResellerLogged = !empty($_SESSION['user_role']) && ($_SESSION['user_role'] === 'reseller' || $_SESSION['user_role'] === 'admin');
$resellerId = (int)($_SESSION['user_id'] ?? 0);
$resellerData = $isResellerLogged ? DB::getReseller($resellerId) : null;

// Handle Login from this page if not logged in
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postCsrf = $_POST['csrf'] ?? '';
    if (!hash_equals($csrf, $postCsrf)) {
        $msg = "Session expirée ou requête invalide (CSRF).";
        $msgType = "error";
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'reseller_login') {
            $user = trim($_POST['username'] ?? '');
            $pass = trim($_POST['password'] ?? '');

            if ($user === 'admin') {
                $admPass = DB::getSetting('admin_password', '200600');
                if ($pass === $admPass) {
                    $_SESSION['user_role'] = 'admin';
                    $_SESSION['user_id'] = 0;
                    $_SESSION['user_name'] = 'Admin';
                    header('Location: credits.php');
                    exit;
                }
            }

            $reseller = DB::getResellerByUsername($user);
            if ($reseller && password_verify($pass, $reseller['password'])) {
                if ($reseller['status'] === 'blocked') {
                    $msg = "Ce compte revendeur est temporairement suspendu.";
                    $msgType = "error";
                } else {
                    $_SESSION['user_role'] = 'reseller';
                    $_SESSION['user_id'] = $reseller['id'];
                    $_SESSION['user_name'] = $reseller['name'];
                    $_SESSION['reseller_credits'] = $reseller['credits'];
                    header('Location: credits.php');
                    exit;
                }
            } else {
                $msg = "Nom d'utilisateur ou mot de passe incorrect.";
                $msgType = "error";
            }
        }

        // Handle Credit Order Submission
        elseif ($action === 'order_credits' && $isResellerLogged) {
            $credits = (int)($_POST['pack_credits'] ?? 10);
            if (!isset($pricePacks[$credits])) {
                $credits = 10;
            }
            $amount = $pricePacks[$credits];
            $paymentMethod = $_POST['payment_method'] ?? 'rib';
            if (!in_array($paymentMethod, ['rib', 'paypal'])) {
                $paymentMethod = 'rib';
            }

            $customerName = trim($_POST['customer_name'] ?? ($_SESSION['user_name'] ?? ''));
            $customerPhone = trim($_POST['customer_phone'] ?? '');
            $transactionRef = trim($_POST['transaction_ref'] ?? '');

            // Handle Proof Upload
            $proofPath = null;
            if (!empty($_FILES['proof_file']['name']) && $_FILES['proof_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['proof_file']['tmp_name'];
                $fileName = $_FILES['proof_file']['name'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

                if (in_array($fileExt, $allowedExts) && filesize($fileTmp) < 10 * 1024 * 1024) {
                    $newFileName = 'proof_reseller_' . $resellerId . '_' . time() . '_' . random_int(1000, 9999) . '.' . $fileExt;
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

            $orderId = DB::createOrder([
                'order_type'      => 'reseller',
                'mac_address'     => null,
                'device_key'      => null,
                'reseller_id'     => $resellerId,
                'plan'            => "credits_{$credits}",
                'amount_dh'       => $amount,
                'credits'         => $credits,
                'payment_method'  => $paymentMethod,
                'customer_name'   => $customerName,
                'customer_phone'  => $customerPhone,
                'customer_email'  => null,
                'transaction_ref' => $transactionRef,
                'proof_file'      => $proofPath,
                'admin_notes'     => null
            ]);

            $msg = "Commande de {$credits} Crédits N° #{$orderId} transmise avec succès ! Vos crédits seront ajoutés dès confirmation du paiement.";
            $msgType = "success";
        }
    }
}

// Refresh reseller data
if ($isResellerLogged && $resellerId > 0) {
    $resellerData = DB::getReseller($resellerId);
    $_SESSION['reseller_credits'] = $resellerData['credits'] ?? 0;
}

$resellerOrders = ($isResellerLogged && $resellerId > 0) ? DB::getOrdersForReseller($resellerId) : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Revendeurs &amp; Recharges Crédits | Dreamania Player</title>
    <meta name="description" content="Achetez vos packs de crédits revendeur Dreamania Player au meilleur prix grossiste. 1 Crédit = 1 An d'activation.">
    <link rel="icon" type="image/png" href="assets/img/icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0b0914;
            --surface: #120e22;
            --card: #18132e;
            --primary: #8b5cf6;
            --primary-glow: rgba(139, 92, 246, 0.35);
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
                radial-gradient(circle at 10% 20%, rgba(139, 92, 246, 0.12) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(245, 158, 11, 0.1) 0%, transparent 40%);
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
        }
        .brand-text h1 { font-size: 18px; font-weight: 800; letter-spacing: -0.5px; }
        .brand-text span { font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--accent-gold); letter-spacing: 1px; }

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
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.1); }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
            border: 1px solid var(--border);
        }
        .btn-secondary:hover { background: rgba(255, 255, 255, 0.12); border-color: var(--primary); }
        .btn-gold {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #000;
            box-shadow: 0 4px 16px var(--accent-gold-glow);
        }
        .btn-gold:hover { transform: translateY(-2px); filter: brightness(1.08); }
        .btn-whatsapp { background: #25d366; color: #fff; }
        .btn-whatsapp:hover { filter: brightness(1.08); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-lg { padding: 14px 28px; font-size: 15px; }

        .main-wrapper {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
            width: 100%;
            flex: 1;
        }

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
        .alert-success { background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; }
        .alert-error { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px;
            margin-bottom: 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
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
        .card-title { font-size: 18px; font-weight: 800; display: flex; align-items: center; gap: 10px; color: #fff; }

        /* Reseller Balance Banner */
        .balance-banner {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(24, 19, 46, 0.95) 100%);
            border: 1px solid rgba(245, 158, 11, 0.4);
            border-radius: var(--radius);
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .balance-item span { font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-secondary); letter-spacing: 1px; display: block; margin-bottom: 4px; }
        .balance-item strong { font-size: 26px; font-weight: 800; color: #fbbf24; font-family: 'JetBrains Mono', monospace; }

        /* Wholesale Grid */
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }
        .pack-card {
            background: rgba(0, 0, 0, 0.25);
            border: 2px solid var(--border);
            border-radius: var(--radius);
            padding: 22px 18px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease;
            position: relative;
        }
        .pack-card:hover {
            border-color: var(--accent-gold);
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(245, 158, 11, 0.15);
        }
        .pack-card.selected {
            border-color: var(--accent-gold);
            background: rgba(245, 158, 11, 0.08);
            box-shadow: 0 0 24px rgba(245, 158, 11, 0.25);
        }
        .pack-card input[type="radio"] { display: none; }
        .pack-badge {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #000;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 12px;
            border-radius: 12px;
            white-space: nowrap;
        }
        .pack-credits { font-size: 28px; font-weight: 800; color: #fff; margin-top: 6px; }
        .pack-sub { font-size: 12px; color: var(--text-secondary); margin-bottom: 12px; }
        .pack-price { font-size: 24px; font-weight: 800; color: var(--accent-gold); }
        .pack-rate { font-size: 11px; color: #34d399; font-weight: 700; margin-top: 4px; }

        /* Form elements */
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: var(--text-secondary); }
        input[type="text"], input[type="password"], input[type="tel"], select {
            width: 100%;
            padding: 12px 16px;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: #fff;
            font-size: 14px;
            outline: none;
        }
        input:focus { border-color: var(--accent-gold); }

        .payment-box {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 20px;
            margin-bottom: 20px;
        }

        .nav-tabs {
            display: flex;
            gap: 10px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
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
        .tab-btn:hover { color: #fff; }
        .tab-btn.active {
            background: var(--accent-gold);
            color: #000;
            border-color: var(--accent-gold);
            box-shadow: 0 4px 14px var(--accent-gold-glow);
        }

        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { text-align: left; padding: 12px 14px; background: rgba(255, 255, 255, 0.03); color: var(--text-secondary); font-weight: 700; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid var(--border); }
        td { padding: 14px; border-bottom: 1px solid var(--border); color: #e2e8f0; }

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

    <header class="portal-header">
        <div class="header-content">
            <a href="landing.php" class="brand">
                <img src="assets/img/icon.png" alt="Logo">
                <div class="brand-text">
                    <h1>DREAMANIA <span style="color:var(--accent-gold);">PLAYER</span></h1>
                    <span>ESPACE REVENDEURS &amp; CRÉDITS</span>
                </div>
            </a>

            <div style="display: flex; gap: 12px; align-items: center;">
                <a href="landing.php" class="btn btn-secondary btn-sm">🏠 Accueil</a>
                <a href="device.php" class="btn btn-secondary btn-sm">📺 Espace Particulier</a>
                <a href="index.php" class="btn btn-primary btn-sm">🔑 Panel Gestion</a>
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

        <?php if ($isResellerLogged): ?>
            <!-- RESELLER LOGGED IN BANNER -->
            <div class="balance-banner">
                <div class="balance-item">
                    <span>Compte Revendeur Connecté</span>
                    <strong style="color: #fff; font-size: 20px;">
                        <?= htmlspecialchars($_SESSION['user_name'] ?? 'Revendeur') ?>
                    </strong>
                </div>
                <div class="balance-item">
                    <span>Solde de Crédits Actuel</span>
                    <strong><?= (int)($_SESSION['reseller_credits'] ?? 0) ?> CRÉDITS</strong>
                </div>
                <div class="balance-item" style="text-align: right;">
                    <span style="color: #10b981;">1 Crédit = 1 An d'Activation</span>
                    <a href="index.php?tab=playlists" class="btn btn-secondary btn-sm" style="margin-top: 6px;">
                        📺 Ouvrir Panel d'Activation
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- INTRO & RULE -->
        <div class="card" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(24, 19, 46, 0.9) 100%);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h2 style="font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 6px;">
                        💎 Packs de Crédits Revendeur Grossiste
                    </h2>
                    <p style="color: var(--text-secondary); font-size: 14px; max-width: 750px;">
                        Activez les téléviseurs de vos clients finaux en 1 clic grâce à votre solde de crédits.
                        Règle officielle : <strong>1 Crédit = 1 An d'activation complète pour n'importe quel téléviseur</strong>.
                    </p>
                </div>
                <div style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4); padding: 12px 18px; border-radius: 12px; text-align: center;">
                    <span style="font-size: 11px; text-transform: uppercase; font-weight: 800; color: var(--accent-gold);">RÈGLE OFFICIELLE</span>
                    <div style="font-size: 18px; font-weight: 800; color: #fff; margin-top: 2px;">1 CRÉDIT = 1 AN</div>
                </div>
            </div>
        </div>

        <!-- PRICING TIERS -->
        <div class="pricing-grid">
            <!-- 10 Credits -->
            <div class="pack-card selected" onclick="selectCreditPack(10, this, <?= (float)$pricePacks[10] ?>)">
                <input type="radio" name="credit_radio" value="10" checked>
                <div class="pack-credits">10 Crédits</div>
                <div class="pack-sub">Pack Découverte</div>
                <div class="pack-price"><?= (int)$pricePacks[10] ?> <span style="font-size: 13px;">DH</span></div>
                <div class="pack-rate">10 DH / crédit</div>
            </div>

            <!-- 20 Credits -->
            <div class="pack-card" onclick="selectCreditPack(20, this, <?= (float)$pricePacks[20] ?>)">
                <input type="radio" name="credit_radio" value="20">
                <div class="pack-credits">20 Crédits</div>
                <div class="pack-sub">Pack Standard</div>
                <div class="pack-price"><?= (int)$pricePacks[20] ?> <span style="font-size: 13px;">DH</span></div>
                <div class="pack-rate">9 DH / crédit (-10%)</div>
            </div>

            <!-- 30 Credits -->
            <div class="pack-card" onclick="selectCreditPack(30, this, <?= (float)$pricePacks[30] ?>)">
                <input type="radio" name="credit_radio" value="30">
                <div class="pack-credits">30 Crédits</div>
                <div class="pack-sub">Pack Pro</div>
                <div class="pack-price"><?= (int)$pricePacks[30] ?> <span style="font-size: 13px;">DH</span></div>
                <div class="pack-rate">8 DH / crédit (-20%)</div>
            </div>

            <!-- 50 Credits -->
            <div class="pack-card" onclick="selectCreditPack(50, this, <?= (float)$pricePacks[50] ?>)">
                <span class="pack-badge">TOP VENTE</span>
                <input type="radio" name="credit_radio" value="50">
                <div class="pack-credits">50 Crédits</div>
                <div class="pack-sub">Pack Super Pro</div>
                <div class="pack-price"><?= (int)$pricePacks[50] ?> <span style="font-size: 13px;">DH</span></div>
                <div class="pack-rate">7 DH / crédit (-30%)</div>
            </div>

            <!-- 100 Credits -->
            <div class="pack-card" onclick="selectCreditPack(100, this, <?= (float)$pricePacks[100] ?>)">
                <span class="pack-badge" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff;">MAX ÉCONOMIE</span>
                <input type="radio" name="credit_radio" value="100">
                <div class="pack-credits">100 Crédits</div>
                <div class="pack-sub">Pack Grossiste VIP</div>
                <div class="pack-price"><?= (int)$pricePacks[100] ?> <span style="font-size: 13px;">DH</span></div>
                <div class="pack-rate">6 DH / crédit (-40%)</div>
            </div>
        </div>

        <?php if ($isResellerLogged): ?>
            <!-- ORDER FORM FOR LOGGED IN RESELLER -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <span>💳</span>
                        <span>Passer une Commande de Crédits</span>
                    </div>
                    <div style="font-size: 14px; font-weight: 700; color: var(--accent-gold);">
                        Total à régler : <span id="summary-total"><?= (int)$pricePacks[10] ?></span> DH (<span id="summary-credits">10</span> Crédits)
                    </div>
                </div>

                <form method="post" action="credits.php" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="order_credits">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="pack_credits" id="pack_credits_input" value="10">
                    <input type="hidden" name="payment_method" id="payment_method_input" value="rib">

                    <!-- Payment Method Tabs -->
                    <label style="font-weight: 700; margin-bottom: 10px; display: block; color: #fff;">MODE DE PAIEMENT :</label>
                    <div class="nav-tabs">
                        <button type="button" class="tab-btn active" id="btn-pay-rib" onclick="selectResellerPay('rib')">
                            🏦 Virement Bancaire (RIB Maroc)
                        </button>
                        <button type="button" class="tab-btn" id="btn-pay-paypal" onclick="selectResellerPay('paypal')">
                            🅿️ PayPal
                        </button>
                    </div>

                    <!-- RIB Box -->
                    <div id="box-rib" class="payment-box">
                        <strong style="color: #fff; font-size: 14px; display: block; margin-bottom: 12px;">
                            Coordonnées Bancaires Officielles :
                        </strong>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; font-size: 13px;">
                            <div>
                                <span style="color: var(--text-secondary); display: block; font-size: 11px;">BANQUE</span>
                                <strong style="color: #fff;"><?= htmlspecialchars($ribBank) ?></strong>
                            </div>
                            <div>
                                <span style="color: var(--text-secondary); display: block; font-size: 11px;">TITULAIRE</span>
                                <strong style="color: #fff;"><?= htmlspecialchars($ribAccount) ?></strong>
                            </div>
                            <div>
                                <span style="color: var(--text-secondary); display: block; font-size: 11px;">SWIFT / BIC</span>
                                <strong style="color: #fff;"><?= htmlspecialchars($ribSwift) ?></strong>
                            </div>
                        </div>

                        <div style="margin-top: 14px; background: rgba(0,0,0,0.4); padding: 12px 16px; border-radius: var(--radius-sm); border: 1px dashed rgba(245, 158, 11, 0.4); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                            <div>
                                <span style="color: var(--text-secondary); font-size: 11px; display: block;">RIB DU COMPTE (24 CHIFFRES)</span>
                                <strong style="font-family: 'JetBrains Mono', monospace; font-size: 16px; color: #fbbf24; letter-spacing: 1px;">
                                    <?= htmlspecialchars($ribNumber) ?>
                                </strong>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="copyRib()">
                                📋 Copier le RIB
                            </button>
                        </div>
                    </div>

                    <!-- PayPal Box -->
                    <div id="box-paypal" class="payment-box" style="display: none;">
                        <strong style="color: #fff; font-size: 14px; display: block; margin-bottom: 12px;">
                            Paiement PayPal :
                        </strong>
                        <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 12px;">
                            Envoyez le paiement par PayPal entre proches :
                        </p>
                        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                            <a href="<?= htmlspecialchars($paypalMe) ?>" target="_blank" rel="noopener" class="btn btn-primary btn-sm">
                                🔗 Ouvrir PayPal.me
                            </a>
                            <span style="color: #fff; font-size: 13px;">Email : <strong><?= htmlspecialchars($paypalEmail) ?></strong></span>
                        </div>
                    </div>

                    <!-- Contact & Proof Fields -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
                        <div class="form-group">
                            <label for="customer_name">NOM OU PSEUDO REVENDEUR</label>
                            <input type="text" id="customer_name" name="customer_name" required value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="customer_phone">VOTRE TÉLÉPHONE / WHATSAPP</label>
                            <input type="tel" id="customer_phone" name="customer_phone" required placeholder="+212 6XX XX XX XX">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
                        <div class="form-group">
                            <label for="transaction_ref">RÉFÉRENCE / TRANSACTION DU VIREMENT</label>
                            <input type="text" id="transaction_ref" name="transaction_ref" placeholder="Ex: N° Virement bancaire ou PayPal Ref">
                        </div>
                        <div class="form-group">
                            <label for="proof_file">JUSTIFICATIF DE VIREMENT (IMAGE OU PDF)</label>
                            <input type="file" id="proof_file" name="proof_file" accept="image/*,application/pdf">
                        </div>
                    </div>

                    <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap; margin-top: 10px;">
                        <button type="submit" class="btn btn-gold btn-lg">
                            ⚡ Valider ma Commande de Crédits
                        </button>
                        <button type="button" class="btn btn-whatsapp btn-lg" onclick="resellerWhatsApp()">
                            💬 Confirmer via WhatsApp
                        </button>
                    </div>
                </form>
            </div>

            <!-- Reseller Orders History -->
            <?php if (!empty($resellerOrders)): ?>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <span>📜</span>
                            <span>Mes Commandes de Crédits</span>
                        </div>
                    </div>

                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>N° Commande</th>
                                    <th>Date</th>
                                    <th>Crédits</th>
                                    <th>Montant</th>
                                    <th>Paiement</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resellerOrders as $ord): ?>
                                    <tr>
                                        <td><strong>#<?= (int)$ord['id'] ?></strong></td>
                                        <td style="color: var(--text-secondary);"><?= htmlspecialchars(substr($ord['created_at'], 0, 16)) ?></td>
                                        <td><strong style="color: #fbbf24;">+<?= (int)$ord['credits'] ?> Crédits</strong></td>
                                        <td><?= htmlspecialchars((string)$ord['amount_dh']) ?> DH</td>
                                        <td style="text-transform: uppercase;"><?= htmlspecialchars($ord['payment_method']) ?></td>
                                        <td>
                                            <?php if ($ord['status'] === 'approved'): ?>
                                                <span style="color: #34d399; font-weight: 700;">✅ Crédités sur votre solde</span>
                                            <?php elseif ($ord['status'] === 'rejected'): ?>
                                                <span style="color: #f87171; font-weight: 700;">❌ Rejeté</span>
                                            <?php else: ?>
                                                <span style="color: #fbbf24; font-weight: 700;">⏳ En attente de validation</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- NOT LOGGED IN VIEW: LOGIN FORM & REGISTER CTA -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-top: 10px;">
                <!-- Login to existing reseller account -->
                <div class="card">
                    <h3 style="font-size: 18px; font-weight: 800; color: #fff; margin-bottom: 16px;">
                        🔐 Se Connecter à votre Compte Revendeur
                    </h3>
                    <form method="post" action="credits.php">
                        <input type="hidden" name="action" value="reseller_login">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

                        <div class="form-group">
                            <label for="username">NOM D'UTILISATEUR</label>
                            <input type="text" id="username" name="username" required placeholder="Votre pseudo revendeur">
                        </div>

                        <div class="form-group">
                            <label for="password">MOT DE PASSE</label>
                            <input type="password" id="password" name="password" required placeholder="••••••••">
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                            Connexion Revendeur
                        </button>
                    </form>
                </div>

                <!-- Become a reseller CTA -->
                <div class="card" style="border-color: rgba(245, 158, 11, 0.4); background: rgba(245, 158, 11, 0.05);">
                    <h3 style="font-size: 18px; font-weight: 800; color: var(--accent-gold); margin-bottom: 12px;">
                        🚀 Vous souhaitez devenir Revendeur Officiel ?
                    </h3>
                    <p style="color: var(--text-secondary); font-size: 13px; line-height: 1.6; margin-bottom: 20px;">
                        Rejoignez notre réseau de distributeurs Dreamania Player au Maroc et à l'international.
                        Profitez de marges attractives (jusqu'à 70% de marge), d'un panneau de gestion dédié, et d'une activation instantanée pour tous vos clients.
                    </p>

                    <ul style="list-style: none; font-size: 13px; color: #fff; line-height: 2; margin-bottom: 24px;">
                        <li>✔️ 1 Crédit = 1 An d'activation</li>
                        <li>✔️ Tarifs dégressifs dès 10 crédits (6 DH à 10 DH)</li>
                        <li>✔️ Panneau web simple &amp; moderne</li>
                        <li>✔️ Support technique dédié 7j/7</li>
                    </ul>

                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $whatsapp) ?>?text=<?= urlencode("Bonjour Dreamania Player, je souhaite ouvrir un compte revendeur officiel et commander des crédits.") ?>" 
                       target="_blank" class="btn btn-whatsapp btn-lg" style="width: 100%;">
                        💬 Demander un Compte Revendeur sur WhatsApp
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <footer class="portal-footer">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>© <?= date('Y') ?> <strong>Dreamania Player</strong>. Espace Revendeurs &amp; Grossistes.</div>
            <div>WhatsApp Support : <strong style="color: #25d366;"><?= htmlspecialchars($whatsapp) ?></strong></div>
        </div>
    </footer>

    <script>
        let currentCredits = 10;
        let currentAmount = <?= (float)$pricePacks[10] ?>;

        function selectCreditPack(credits, element, price) {
            document.querySelectorAll('.pack-card').forEach(c => c.classList.remove('selected'));
            element.classList.add('selected');
            element.querySelector('input[type="radio"]').checked = true;

            currentCredits = credits;
            currentAmount = price;

            const inputCredits = document.getElementById('pack_credits_input');
            if (inputCredits) inputCredits.value = credits;

            const sumTotal = document.getElementById('summary-total');
            if (sumTotal) sumTotal.innerText = price;

            const sumCredits = document.getElementById('summary-credits');
            if (sumCredits) sumCredits.innerText = credits;
        }

        function selectResellerPay(method) {
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

        function copyRib() {
            const rib = "<?= htmlspecialchars(str_replace(' ', '', $ribNumber)) ?>";
            navigator.clipboard.writeText(rib).then(() => {
                alert('RIB copié : ' + rib);
            }).catch(() => {
                prompt('Copiez le RIB :', rib);
            });
        }

        function resellerWhatsApp() {
            const username = "<?= htmlspecialchars($_SESSION['user_name'] ?? 'Revendeur') ?>";
            const phone = document.getElementById('customer_phone') ? document.getElementById('customer_phone').value : '';
            const ref = document.getElementById('transaction_ref') ? document.getElementById('transaction_ref').value : 'Paiement effectué';

            const message = `Bonjour Dreamania Player, je suis revendeur et souhaite confirmer ma commande de crédits :\n` +
                            `👤 Compte : ${username}\n` +
                            `⚡ Pack : ${currentCredits} Crédits (${currentAmount} DH)\n` +
                            `📞 Téléphone : ${phone}\n` +
                            `💳 Réf Virement : ${ref}\n\n` +
                            `Merci d'ajouter mes crédits !`;

            const cleanPhone = "<?= preg_replace('/[^0-9]/', '', $whatsapp) ?>";
            const waUrl = `https://wa.me/${cleanPhone}?text=${encodeURIComponent(message)}`;
            window.open(waUrl, '_blank');
        }
    </script>
</body>
</html>
