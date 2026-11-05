<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

$pricing = DB::getPricing();
$paymentConfig = method_exists('DB', 'getPaymentConfig') ? DB::getPaymentConfig() : [
    'paypal_email'     => DB::getSetting('paypal_email', 'contact@lumineasy.com'),
    'paypal_me_link'   => DB::getSetting('paypal_me_link', 'https://paypal.me/dreamaniacode'),
    'rib_bank'         => DB::getSetting('rib_bank_name', 'CIH BANK'),
    'rib_bank_name'    => DB::getSetting('rib_bank_name', 'CIH BANK'),
    'rib_account_name' => DB::getSetting('rib_account_name', 'DREAMANIA MEDIA'),
    'rib_number'       => DB::getSetting('rib_number', '230 780 4567890123456789 12'),
    'rib_swift'        => DB::getSetting('rib_swift', 'CIHMMAMC'),
    'whatsapp_number'  => DB::getSetting('whatsapp_number', '+212600000000'),
];
$downloaderCode = $pricing['downloader_code'] ?? '7454701';
$appVersion = 'v1.1.0 PRO';
$appReleaseDate = 'Novembre 2026';
$officialDomain = 'https://dm.lumineasy.com';
$downloadUrl = 'download';
$portalUrl = 'device.php';

$c1y_dh = (int)($pricing['client_1y_dh'] ?? 50);
$c1y_eur = (int)($pricing['client_1y_eur'] ?? 5);
$clife_dh = (int)($pricing['client_life_dh'] ?? 150);
$clife_eur = (int)($pricing['client_life_eur'] ?? 15);
$resellerPacks = $pricing['reseller_packs'] ?? [];
?>
<!DOCTYPE html>
<html class="dark" lang="fr">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>Dreamania Player Pro — Lecteur IPTV &amp; Cinéma 4K Officiel</title>
  <meta name="description" content="Dreamania Player Pro : Le lecteur IPTV et VOD Cinéma nouvelle génération taillé pour Smart TV, Amazon Fire TV Stick, Google TV et NVIDIA Shield."/>
  
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "surface-obsidian": "#0A0B14",
            "surface-container": "#131422",
            "surface-container-high": "#1C1D2E",
            "surface-container-highest": "#26283D",
            "surface-glass-stroke": "rgba(255, 255, 255, 0.08)",
            "neon-cyan": "#00E5FF",
            "electric-violet": "#8B5CF6",
            "remote-amber": "#F59E0B",
            "status-green": "#10B981",
            "live-red": "#EF4444"
          },
          fontFamily: {
            headline: ["Outfit", "sans-serif"],
            body: ["Plus Jakarta Sans", "sans-serif"],
            label: ["Inter", "sans-serif"]
          }
        }
      }
    };
  </script>

  <style>
    @layer base {
      html, body {
        margin: 0;
        padding: 0;
        scroll-behavior: smooth;
        background-color: #0A0B14;
        color: #E2E8F0;
      }
    }
    ::-webkit-scrollbar {
      width: 8px;
    }
    ::-webkit-scrollbar-track {
      background: #0A0B14;
    }
    ::-webkit-scrollbar-thumb {
      background: #26283D;
      border-radius: 4px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: #38BDF8;
    }
    .glass-card {
      background: rgba(19, 20, 34, 0.75);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .glass-card:hover {
      border-color: rgba(0, 229, 255, 0.3);
      box-shadow: 0 10px 30px -10px rgba(0, 229, 255, 0.2);
    }
    .neon-glow-cyan {
      box-shadow: 0 0 25px rgba(0, 229, 255, 0.35);
    }
    .neon-glow-violet {
      box-shadow: 0 0 25px rgba(139, 92, 246, 0.35);
    }
    .neon-glow-amber {
      box-shadow: 0 0 25px rgba(245, 158, 11, 0.35);
    }
  </style>
</head>
<body class="font-body text-slate-200 antialiased selection:bg-neon-cyan/20 selection:text-neon-cyan min-h-screen pb-24 lg:pb-10">

  <!-- ======================================================== -->
  <!-- FLOATING CYBER DOCK NAVIGATION                            -->
  <!-- ======================================================== -->
  <header class="fixed top-3 left-0 right-0 z-50 px-3 sm:px-6 pointer-events-none">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-3 p-2 sm:p-2.5 rounded-2xl bg-[#0D0E1A]/90 backdrop-blur-2xl border border-surface-glass-stroke shadow-[0_12px_40px_rgba(0,0,0,0.7)] pointer-events-auto">
      
      <!-- Brand Logo & Live Badge -->
      <div class="flex items-center gap-3 pl-1 sm:pl-2">
        <a class="flex items-center gap-2.5 group" href="device.php">
          <div class="relative w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-surface-container-highest/70 flex items-center justify-center p-1.5 border border-neon-cyan/40 shadow-[0_0_20px_rgba(0,229,255,0.35)] group-hover:shadow-[0_0_28px_rgba(0,229,255,0.7)] transition-all">
            <img alt="Dreamania Player Official Logo" class="w-full h-full object-contain filter drop-shadow-[0_0_8px_rgba(0,229,255,0.8)]" src="assets/img/icon.png"/>
          </div>
          <div class="flex flex-col">
            <span class="font-headline text-base sm:text-lg font-extrabold text-white tracking-wider uppercase drop-shadow-[0_0_12px_rgba(0,229,255,0.4)]">DREAMANIA</span>
            <span class="text-[10px] tracking-widest text-neon-cyan font-bold font-label uppercase -mt-1">BROADCAST PRO</span>
          </div>
        </a>

        <!-- Animated Version Badge -->
        <div class="hidden md:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container border border-neon-cyan/20">
          <span class="w-2 h-2 rounded-full bg-status-green animate-pulse"></span>
          <span class="font-label text-[11px] uppercase tracking-wider text-neon-cyan font-semibold"><?php echo htmlspecialchars($appVersion); ?></span>
        </div>
      </div>

      <!-- Centered Navigation Links -->
      <nav class="hidden xl:flex items-center gap-1 p-1 rounded-xl bg-surface-container/90 border border-surface-glass-stroke">
        <a class="px-3.5 py-1.5 rounded-lg text-slate-300 font-label text-xs hover:text-white hover:bg-white/10 transition-all" href="#features" data-i18n="nav_features">Fonctionnalités</a>
        <a class="px-3.5 py-1.5 rounded-lg text-slate-300 font-label text-xs hover:text-white hover:bg-white/10 transition-all" href="#screenshots" data-i18n="nav_app_tour">Visite Guidée</a>
        <a class="px-3.5 py-1.5 rounded-lg text-slate-300 font-label text-xs hover:text-white hover:bg-white/10 transition-all" href="#broadcast-hub" data-i18n="nav_engine">Moteur Media3</a>
        <a class="px-3.5 py-1.5 rounded-lg text-slate-300 font-label text-xs hover:text-white hover:bg-white/10 transition-all" href="#setup" data-i18n="nav_setup">Installation</a>
        <a class="px-3.5 py-1.5 rounded-lg text-slate-300 font-label text-xs hover:text-white hover:bg-white/10 transition-all" href="#pricing" data-i18n="nav_plans">Tarifs</a>
        <a class="px-3.5 py-1.5 rounded-lg text-slate-300 font-label text-xs hover:text-white hover:bg-white/10 transition-all" href="#faq" data-i18n="nav_faq">FAQ</a>
      </nav>

      <!-- Action Buttons & Language Switcher -->
      <div class="flex items-center gap-2 pr-1">
        
        <!-- Language Switcher Pill -->
        <div class="flex items-center p-0.5 rounded-xl bg-surface-container-highest/80 border border-surface-glass-stroke">
          <button type="button" onclick="setLanguage('fr')" id="lang-btn-fr" class="px-2 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 bg-neon-cyan/20 text-neon-cyan border border-neon-cyan/40 shadow-sm">
            <span>🇫🇷</span> <span>FR</span>
          </button>
          <button type="button" onclick="setLanguage('en')" id="lang-btn-en" class="px-2 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 text-slate-400 hover:text-white">
            <span>🇬🇧</span> <span>EN</span>
          </button>
        </div>

        <!-- Mon Appareil -->
        <a class="flex items-center gap-1.5 px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-xl bg-gradient-to-r from-remote-amber to-amber-500 text-slate-950 font-label text-xs sm:text-sm font-bold shadow-[0_0_15px_rgba(245,158,11,0.4)] hover:shadow-[0_0_24px_rgba(245,158,11,0.7)] hover:brightness-110 active:scale-95 transition-all" href="device.php">
          <span class="material-symbols-outlined text-[18px]">tv</span>
          <span class="hidden sm:inline" data-i18n="nav_device">Mon Appareil</span>
          <span class="sm:hidden" data-i18n="nav_device_short">Appareil</span>
        </a>

        <!-- Download APK Button -->
        <a class="flex items-center gap-1.5 px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-xl bg-gradient-to-r from-electric-violet to-neon-cyan text-slate-950 font-label text-xs sm:text-sm font-bold shadow-[0_0_18px_rgba(139,92,246,0.5)] hover:shadow-[0_0_26px_rgba(0,229,255,0.7)] hover:scale-105 active:scale-95 transition-all" href="<?php echo htmlspecialchars($downloadUrl); ?>">
          <span class="material-symbols-outlined text-[18px]">download</span>
          <span class="hidden md:inline" data-i18n="nav_download">Télécharger APK</span>
          <span class="md:hidden">APK</span>
        </a>
      </div>

    </div>
  </header>

  <!-- ======================================================== -->
  <!-- HERO SECTION                                             -->
  <!-- ======================================================== -->
  <section class="relative pt-28 sm:pt-36 pb-16 sm:pb-24 px-4 sm:px-6 overflow-hidden">
    <!-- Ambient Cyber Glow Gradients -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[350px] bg-gradient-to-r from-electric-violet/20 via-neon-cyan/15 to-transparent blur-[120px] pointer-events-none rounded-full"></div>
    <div class="absolute top-20 right-10 w-96 h-96 bg-neon-cyan/10 blur-[130px] pointer-events-none rounded-full"></div>

    <div class="max-w-6xl mx-auto text-center relative z-10">
      
      <!-- Official Badge -->
      <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-surface-container border border-neon-cyan/30 text-neon-cyan mb-6 shadow-[0_0_20px_rgba(0,229,255,0.15)]">
        <span class="w-2 h-2 rounded-full bg-neon-cyan animate-ping"></span>
        <span class="font-label text-xs uppercase tracking-widest font-bold" data-i18n="hero_badge">Moteur Broadcast Officiel • Certifié Android TV &amp; Fire TV</span>
      </div>

      <!-- Main Title -->
      <h1 class="font-headline text-3xl sm:text-5xl md:text-6xl font-black text-white tracking-tight leading-tight max-w-5xl mx-auto mb-6">
        <span data-i18n="hero_title_1">Le Lecteur IPTV &amp; Cinéma</span>
        <span class="bg-gradient-to-r from-neon-cyan via-sky-300 to-electric-violet bg-clip-text text-transparent" data-i18n="hero_title_hl">Nouvelle Génération</span>
        <span data-i18n="hero_title_2">Taillé pour Votre Smart TV</span>
      </h1>

      <!-- Subtitle Description -->
      <p class="font-body text-base sm:text-lg md:text-xl text-slate-300 max-w-3xl mx-auto mb-8 sm:mb-10 leading-relaxed" data-i18n="hero_desc">
        Conçu pour un zapping instantané, le streaming sportif 4K 60FPS et la VOD cinéma enrichie TMDb. Propulsé par Google Media3 ExoPlayer avec zéro superflu et une navigation fluide dédiée à la télécommande TV.
      </p>

      <!-- High-Conversion Primary CTA Row -->
      <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 mb-10">
        <!-- Direct Download APK -->
        <a class="flex items-center gap-2 px-6 sm:px-8 py-3.5 rounded-xl bg-gradient-to-r from-electric-violet via-indigo-600 to-neon-cyan text-slate-950 font-label font-bold text-sm sm:text-base shadow-[0_0_25px_rgba(139,92,246,0.6)] hover:shadow-[0_0_35px_rgba(0,229,255,0.8)] hover:scale-105 active:scale-95 transition-all" href="<?php echo htmlspecialchars($downloadUrl); ?>">
          <span class="material-symbols-outlined text-[20px]">download</span>
          <span data-i18n="btn_download_apk">Télécharger APK (<?php echo htmlspecialchars($appVersion); ?>)</span>
        </a>

        <!-- Device Portal -->
        <a class="flex items-center gap-2 px-5 sm:px-7 py-3.5 rounded-xl bg-gradient-to-r from-remote-amber to-amber-500 text-slate-950 font-label font-bold text-sm sm:text-base shadow-[0_0_20px_rgba(245,158,11,0.4)] hover:shadow-[0_0_30px_rgba(245,158,11,0.6)] hover:brightness-110 active:scale-95 transition-all" href="device.php">
          <span class="material-symbols-outlined text-[20px]">tv</span>
          <span data-i18n="btn_device_portal">Gérer TV / Téléverser Playlist</span>
        </a>

        <!-- Reseller Portal -->
        <a class="flex items-center gap-2 px-5 sm:px-6 py-3.5 rounded-xl bg-surface-container-high/90 hover:bg-surface-container-highest text-white border border-surface-glass-stroke font-label font-bold text-sm sm:text-base hover:border-purple-400/50 hover:shadow-[0_0_20px_rgba(139,92,246,0.3)] transition-all" href="index.php?panel=1">
          <span class="material-symbols-outlined text-purple-400 text-[20px]">storefront</span>
          <span data-i18n="btn_reseller_panel">Espace Revendeur</span>
        </a>

        <!-- Pricing Link -->
        <a class="flex items-center gap-2 px-4 sm:px-5 py-3.5 rounded-xl bg-surface-container/80 hover:bg-surface-container-high text-slate-300 hover:text-white border border-surface-glass-stroke font-label text-sm sm:text-base transition-all" href="#pricing">
          <span class="material-symbols-outlined text-neon-cyan text-[20px]">credit_card</span>
          <span data-i18n="btn_pricing_link">Tarifs (<?= $c1y_dh ?> DH / <?= $c1y_eur ?>€ – <?= $clife_dh ?> DH / <?= $clife_eur ?>€)</span>
        </a>
      </div>

      <!-- Version & Release Notes Showcase Card -->
      <div class="max-w-3xl mx-auto p-4 sm:p-5 rounded-2xl glass-card text-left mb-10 border border-neon-cyan/20">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 mb-3 border-b border-white/5">
          <div class="flex items-center gap-2.5">
            <span class="w-3 h-3 rounded-full bg-status-green animate-pulse"></span>
            <span class="font-headline font-bold text-white text-sm sm:text-base" data-i18n="release_latest_title">Dernière Version Déployée :</span>
            <span class="px-2.5 py-0.5 rounded-full bg-neon-cyan/15 text-neon-cyan font-mono font-bold text-xs"><?php echo htmlspecialchars($appVersion); ?></span>
            <span class="text-xs text-slate-400 font-label">(<?php echo htmlspecialchars($appReleaseDate); ?>)</span>
          </div>
          <!-- Clickable Downloader Code Badge -->
          <button type="button" onclick="copyDownloaderCode()" class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-surface-container-highest border border-remote-amber/40 hover:border-remote-amber text-remote-amber hover:text-amber-300 transition-all font-mono text-xs sm:text-sm font-bold shadow-sm group">
            <span class="text-slate-400 font-sans text-xs" data-i18n="code_label">Code Downloader :</span>
            <span class="text-white text-base tracking-wider"><?php echo htmlspecialchars($downloaderCode); ?></span>
            <span class="material-symbols-outlined text-[16px] group-hover:scale-110 transition-transform">content_copy</span>
          </button>
        </div>
        <!-- What's New Bullet Points -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs sm:text-sm text-slate-300">
          <div class="flex items-center gap-2">
            <span class="text-neon-cyan font-bold">✓</span>
            <span data-i18n="release_feat_1">Sélecteur audio &amp; sous-titres en direct lors de la lecture</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-neon-cyan font-bold">✓</span>
            <span data-i18n="release_feat_2">Formatage propre des dates des séries TV (fini les timestamps)</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-neon-cyan font-bold">✓</span>
            <span data-i18n="release_feat_3">Navigation télécommande D-Pad ultra-réactive avec focus 3D</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="text-neon-cyan font-bold">✓</span>
            <span data-i18n="release_feat_4">Moteur Media3 ExoPlayer zéro mise en mémoire tampon</span>
          </div>
        </div>
      </div>

      <!-- Real Hardware Certified Brand Badges -->
      <div class="max-w-4xl mx-auto">
        <p class="font-label text-xs uppercase tracking-widest text-slate-400 font-semibold mb-4" data-i18n="hardware_title">Compatibilité Matérielle 100% Certifiée</p>
        <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4">
          
          <!-- Amazon Fire TV -->
          <div class="flex items-center gap-2.5 px-4 py-2 rounded-xl bg-surface-container/90 border border-amber-500/30 hover:border-amber-500/70 shadow-[0_0_15px_rgba(245,158,11,0.15)] hover:shadow-[0_0_25px_rgba(245,158,11,0.3)] transition-all">
            <svg class="w-6 h-6 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 2C9.5 4.5 8 7 8 9.5C8 12 10 14 12 14C14 14 16 12 16 9.5C16 7 14.5 4.5 12 2Z" fill="#FF9900"/>
              <path d="M3.5 17.5C7.5 20.5 14.5 20.5 19 16.5M19 16.5L16.5 15M19 16.5L20 13.5" stroke="#FF9900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <div class="flex flex-col text-left">
              <span class="font-headline text-xs font-bold text-white tracking-wide">Amazon Fire TV</span>
              <span class="text-[10px] text-amber-400 font-label">Stick 4K / Max / Cube</span>
            </div>
          </div>

          <!-- NVIDIA Shield -->
          <div class="flex items-center gap-2.5 px-4 py-2 rounded-xl bg-surface-container/90 border border-[#76B900]/30 hover:border-[#76B900]/70 shadow-[0_0_15px_rgba(118,185,0,0.15)] hover:shadow-[0_0_25px_rgba(118,185,0,0.3)] transition-all">
            <svg class="w-6 h-6 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M2.5 12C2.5 6.75 6.75 2.5 12 2.5C17.25 2.5 21.5 6.75 21.5 12C21.5 17.25 17.25 21.5 12 21.5C6.75 21.5 2.5 17.25 2.5 12Z" fill="#131422" stroke="#76B900" stroke-width="1.5"/>
              <path d="M7 12C7 9.24 9.24 7 12 7C14.76 7 17 9.24 17 12C17 14.76 14.76 17 12 17" stroke="#76B900" stroke-width="2" stroke-linecap="round"/>
              <path d="M9.5 12C9.5 10.62 10.62 9.5 12 9.5C13.38 9.5 14.5 10.62 14.5 12" stroke="#76B900" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <div class="flex flex-col text-left">
              <span class="font-headline text-xs font-bold text-white tracking-wide">NVIDIA Shield</span>
              <span class="text-[10px] text-[#76B900] font-label">TV 4K HDR &amp; Pro</span>
            </div>
          </div>

          <!-- Google TV -->
          <div class="flex items-center gap-2.5 px-4 py-2 rounded-xl bg-surface-container/90 border border-sky-400/30 hover:border-sky-400/70 shadow-[0_0_15px_rgba(56,189,248,0.15)] hover:shadow-[0_0_25px_rgba(56,189,248,0.3)] transition-all">
            <svg class="w-6 h-6 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
              <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
              <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
              <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
            </svg>
            <div class="flex flex-col text-left">
              <span class="font-headline text-xs font-bold text-white tracking-wide">Google TV</span>
              <span class="text-[10px] text-sky-400 font-label">Chromecast 4K HD</span>
            </div>
          </div>

          <!-- Android TV -->
          <div class="flex items-center gap-2.5 px-4 py-2 rounded-xl bg-surface-container/90 border border-[#3DDC84]/30 hover:border-[#3DDC84]/70 shadow-[0_0_15px_rgba(61,220,132,0.15)] hover:shadow-[0_0_25px_rgba(61,220,132,0.3)] transition-all">
            <svg class="w-6 h-6 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M6 18c0 .55.45 1 1 1h1v3.5c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5V19h2v3.5c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5V19h1c.55 0 1-.45 1-1V8H6v10zM3.5 8C2.67 8 2 8.67 2 9.5v6c0 .83.67 1.5 1.5 1.5S5 16.33 5 15.5v-6C5 8.67 4.33 8 3.5 8zm17 0c-.83 0-1.5.67-1.5 1.5v6c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5v-6c0-.83-.67-1.5-1.5-1.5zM15.53 2.16l1.3-1.3c.2-.2.2-.51 0-.71-.2-.2-.51-.2-.71 0l-1.48 1.48C13.68 1.24 12.87 1 12 1s-1.68.24-2.64.63L7.88.15c-.2-.2-.51-.2-.71 0-.2.2-.2.51 0 .71l1.3 1.3C6.73 3.32 5.5 5.5 5.5 8h13c0-2.5-1.23-4.68-2.97-5.84zM9 5c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm6 0c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z" fill="#3DDC84"/>
            </svg>
            <div class="flex flex-col text-left">
              <span class="font-headline text-xs font-bold text-white tracking-wide">Android TV Boxes</span>
              <span class="text-[10px] text-[#3DDC84] font-label">Xiaomi / Mecool / Formuler</span>
            </div>
          </div>

          <!-- Apple TV & Mobiles -->
          <div class="flex items-center gap-2.5 px-4 py-2 rounded-xl bg-surface-container/90 border border-slate-600/50 hover:border-slate-400 shadow-[0_0_15px_rgba(255,255,255,0.08)] transition-all">
            <svg class="w-6 h-6 shrink-0 text-white fill-current" viewBox="0 0 24 24">
              <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.85c.67-.82 1.13-1.96 1-3.1-.98.04-2.16.65-2.85 1.47-.61.71-1.14 1.87-1 2.99 1.09.08 2.2-.55 2.85-1.36z"/>
            </svg>
            <div class="flex flex-col text-left">
              <span class="font-headline text-xs font-bold text-white tracking-wide">Apple TV &amp; iOS</span>
              <span class="text-[10px] text-slate-400 font-label">AirPlay &amp; iPhone / iPad</span>
            </div>
          </div>

        </div>
      </div>

    </div>
  </section>

  <!-- ======================================================== -->
  <!-- INTERACTIVE SCREENSHOT GALLERY (6 REAL TABS)             -->
  <!-- ======================================================== -->
  <section class="py-16 sm:py-24 px-4 sm:px-6 relative bg-surface-obsidian" id="screenshots">
    <div class="max-w-6xl mx-auto">
      
      <!-- Section Header -->
      <div class="text-center mb-10 sm:mb-12">
        <span class="font-label text-xs uppercase tracking-widest text-neon-cyan font-bold" data-i18n="gallery_badge">Expérience Télécommande 10-Foot UI</span>
        <h2 class="font-headline text-2xl sm:text-4xl md:text-5xl font-black text-white mt-2 mb-4" data-i18n="gallery_title">Visite Guidée de l'Application</h2>
        <p class="font-body text-slate-300 text-sm sm:text-base max-w-2xl mx-auto" data-i18n="gallery_subtitle">Découvrez l'interface moderne de Dreamania Player conçue exclusivement pour l'ergonomie des téléviseurs et télécommandes.</p>
      </div>

      <!-- Tab Switcher Chips -->
      <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-2.5 mb-8">
        <button type="button" onclick="switchScreenshotTab('launcher')" id="tab-btn-launcher" class="screenshot-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-bold font-label transition-all bg-neon-cyan/20 text-neon-cyan border border-neon-cyan/50 shadow-[0_0_15px_rgba(0,229,255,0.3)]">
          <span class="material-symbols-outlined text-[16px] align-middle mr-1">dashboard</span>
          <span data-i18n="tab_launcher">Launcher TV</span>
        </button>
        <button type="button" onclick="switchScreenshotTab('epg')" id="tab-btn-epg" class="screenshot-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-bold font-label transition-all bg-surface-container text-slate-300 hover:text-white border border-surface-glass-stroke">
          <span class="material-symbols-outlined text-[16px] align-middle mr-1">live_tv</span>
          <span data-i18n="tab_epg">Guide TV &amp; EPG</span>
        </button>
        <button type="button" onclick="switchScreenshotTab('player')" id="tab-btn-player" class="screenshot-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-bold font-label transition-all bg-surface-container text-slate-300 hover:text-white border border-surface-glass-stroke">
          <span class="material-symbols-outlined text-[16px] align-middle mr-1">play_circle</span>
          <span data-i18n="tab_player">Lecteur &amp; OSD</span>
        </button>
        <button type="button" onclick="switchScreenshotTab('vod')" id="tab-btn-vod" class="screenshot-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-bold font-label transition-all bg-surface-container text-slate-300 hover:text-white border border-surface-glass-stroke">
          <span class="material-symbols-outlined text-[16px] align-middle mr-1">movie</span>
          <span data-i18n="tab_vod">Cinéma VOD 4K</span>
        </button>
        <button type="button" onclick="switchScreenshotTab('series')" id="tab-btn-series" class="screenshot-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-bold font-label transition-all bg-surface-container text-slate-300 hover:text-white border border-surface-glass-stroke">
          <span class="material-symbols-outlined text-[16px] align-middle mr-1">video_library</span>
          <span data-i18n="tab_series">Séries &amp; Saisons</span>
        </button>
        <button type="button" onclick="switchScreenshotTab('themes')" id="tab-btn-themes" class="screenshot-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-bold font-label transition-all bg-surface-container text-slate-300 hover:text-white border border-surface-glass-stroke">
          <span class="material-symbols-outlined text-[16px] align-middle mr-1">palette</span>
          <span data-i18n="tab_themes">4 Thèmes Cyber</span>
        </button>
      </div>

      <!-- TV Screen Bezel Frame -->
      <div class="relative max-w-5xl mx-auto rounded-3xl p-3 sm:p-4 bg-gradient-to-b from-[#1C1D2E] via-[#10111D] to-[#0A0B14] border-2 border-white/10 shadow-[0_20px_60px_rgba(0,0,0,0.85)]">
        
        <!-- Screen Container with Aspect Ratio -->
        <div class="relative w-full aspect-video rounded-2xl overflow-hidden bg-black border border-white/5 shadow-inner">
          
          <!-- Tab 1: Launcher -->
          <div id="screen-launcher" class="screen-panel w-full h-full relative transition-opacity duration-300">
            <img src="assets/img/screenshots/screen_hero_dashboard.png" alt="Dreamania Launcher TV Dashboard" class="w-full h-full object-cover"/>
            <div class="absolute bottom-0 inset-x-0 p-4 sm:p-6 bg-gradient-to-t from-black/90 via-black/50 to-transparent flex items-center justify-between">
              <div>
                <h3 class="font-headline font-bold text-white text-base sm:text-lg" data-i18n="desc_launcher_t">Tableau de Bord TV Haute Précision</h3>
                <p class="text-xs sm:text-sm text-slate-300" data-i18n="desc_launcher_p">Accès direct Télévision en Direct, Films VOD, Séries TV, Favoris ❤️ et statut d'abonnement en temps réel.</p>
              </div>
              <span class="hidden sm:inline-block px-3 py-1 rounded-full bg-neon-cyan/20 text-neon-cyan font-mono text-xs font-bold">1080p / 4K UI</span>
            </div>
          </div>

          <!-- Tab 2: EPG Guide -->
          <div id="screen-epg" class="screen-panel w-full h-full relative hidden transition-opacity duration-300">
            <img src="assets/img/screenshots/screen_livetv_guide.png" alt="Guide TV et EPG en Direct" class="w-full h-full object-cover"/>
            <div class="absolute bottom-0 inset-x-0 p-4 sm:p-6 bg-gradient-to-t from-black/90 via-black/50 to-transparent flex items-center justify-between">
              <div>
                <h3 class="font-headline font-bold text-white text-base sm:text-lg" data-i18n="desc_epg_t">Guide TV &amp; Grille EPG Dynamique</h3>
                <p class="text-xs sm:text-sm text-slate-300" data-i18n="desc_epg_p">Aperçu direct du programme en cours, barre de progression en temps réel et titre de l'émission suivante.</p>
              </div>
              <span class="hidden sm:inline-block px-3 py-1 rounded-full bg-status-green/20 text-status-green font-mono text-xs font-bold">Zapping 0.38s</span>
            </div>
          </div>

          <!-- Tab 3: Player OSD -->
          <div id="screen-player" class="screen-panel w-full h-full relative hidden transition-opacity duration-300">
            <img src="assets/img/screenshots/screen_player_osd.png" alt="Lecteur Vidéo & OSD Media3" class="w-full h-full object-cover"/>
            <div class="absolute bottom-0 inset-x-0 p-4 sm:p-6 bg-gradient-to-t from-black/90 via-black/50 to-transparent flex items-center justify-between">
              <div>
                <h3 class="font-headline font-bold text-white text-base sm:text-lg" data-i18n="desc_player_t">Lecteur Vidéo Avancé &amp; Contrôle D-Pad</h3>
                <p class="text-xs sm:text-sm text-slate-300" data-i18n="desc_player_p">Changement de piste audio multilingue, sous-titres, ratio d'écran 16:9 / Zoom, et tiroir de chaînes sans couper la vidéo.</p>
              </div>
              <span class="hidden sm:inline-block px-3 py-1 rounded-full bg-electric-violet/20 text-electric-violet font-mono text-xs font-bold">Google Media3</span>
            </div>
          </div>

          <!-- Tab 4: VOD Movies -->
          <div id="screen-vod" class="screen-panel w-full h-full relative hidden transition-opacity duration-300">
            <img src="assets/img/screenshots/screen_vod_movies.png" alt="Catalogue Cinéma VOD" class="w-full h-full object-cover"/>
            <div class="absolute bottom-0 inset-x-0 p-4 sm:p-6 bg-gradient-to-t from-black/90 via-black/50 to-transparent flex items-center justify-between">
              <div>
                <h3 class="font-headline font-bold text-white text-base sm:text-lg" data-i18n="desc_vod_t">Bibliothèque Cinéma 4K Enrichie TMDb</h3>
                <p class="text-xs sm:text-sm text-slate-300" data-i18n="desc_vod_p">Affiches haute résolution, notes IMDb, synopsis complet, casting et reprise automatique de lecture.</p>
              </div>
              <span class="hidden sm:inline-block px-3 py-1 rounded-full bg-remote-amber/20 text-remote-amber font-mono text-xs font-bold">4K HDR10+</span>
            </div>
          </div>

          <!-- Tab 5: Series -->
          <div id="screen-series" class="screen-panel w-full h-full relative hidden transition-opacity duration-300">
            <img src="assets/img/screenshots/screen_series_detail.png" alt="Détail Séries TV et Épisodes" class="w-full h-full object-cover"/>
            <div class="absolute bottom-0 inset-x-0 p-4 sm:p-6 bg-gradient-to-t from-black/90 via-black/50 to-transparent flex items-center justify-between">
              <div>
                <h3 class="font-headline font-bold text-white text-base sm:text-lg" data-i18n="desc_series_t">Organisation Intelligente par Saisons &amp; Épisodes</h3>
                <p class="text-xs sm:text-sm text-slate-300" data-i18n="desc_series_p">Navigation D-Pad fluide de saison en saison, durées exactes et dates de diffusion formatées avec soin.</p>
              </div>
              <span class="hidden sm:inline-block px-3 py-1 rounded-full bg-sky-400/20 text-sky-400 font-mono text-xs font-bold">Multi-Saisons</span>
            </div>
          </div>

          <!-- Tab 6: Themes -->
          <div id="screen-themes" class="screen-panel w-full h-full relative hidden transition-opacity duration-300">
            <img src="assets/img/screenshots/screen_themes_showcase.png" alt="4 Thèmes d'ambiance Cyber" class="w-full h-full object-cover"/>
            <div class="absolute bottom-0 inset-x-0 p-4 sm:p-6 bg-gradient-to-t from-black/90 via-black/50 to-transparent flex items-center justify-between">
              <div>
                <h3 class="font-headline font-bold text-white text-base sm:text-lg" data-i18n="desc_themes_t">4 Thèmes d'Ambiance Personnalisables</h3>
                <p class="text-xs sm:text-sm text-slate-300" data-i18n="desc_themes_p">Basculez instantanément entre Cyber Neon, Obsidian Cinema, Sports Stadium et Dark Obsidian.</p>
              </div>
              <span class="hidden sm:inline-block px-3 py-1 rounded-full bg-pink-500/20 text-pink-400 font-mono text-xs font-bold">Thèmes Pro</span>
            </div>
          </div>

        </div>
      </div>

    </div>
  </section>

  <!-- ======================================================== -->
  <!-- PERFORMANCE & MEDIA3 BROADCAST ENGINE                    -->
  <!-- ======================================================== -->
  <section class="py-16 sm:py-24 px-4 sm:px-6 relative" id="broadcast-hub">
    <div class="max-w-6xl mx-auto">
      
      <div class="text-center mb-12 sm:mb-16">
        <span class="font-label text-xs uppercase tracking-widest text-status-green font-bold" data-i18n="engine_badge">Google Media3 ExoPlayer Architecture</span>
        <h2 class="font-headline text-2xl sm:text-4xl md:text-5xl font-black text-white mt-2 mb-4" data-i18n="engine_title">Performance &amp; Fluidité Sans Équivalent</h2>
        <p class="font-body text-slate-300 text-sm sm:text-base max-w-2xl mx-auto" data-i18n="engine_subtitle">Développé spécifiquement pour éliminer les ralentissements des applications IPTV classiques sur les Fire TV Sticks et box TV abordables.</p>
      </div>

      <!-- 3 Metrics Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
        
        <div class="p-6 sm:p-8 rounded-2xl glass-card text-center border-t-2 border-t-neon-cyan">
          <div class="font-headline text-4xl sm:text-5xl font-black text-neon-cyan mb-2">0.38s</div>
          <h3 class="font-headline font-bold text-white text-lg mb-2" data-i18n="metric_1_t">Zapping Instantané</h3>
          <p class="text-xs sm:text-sm text-slate-300" data-i18n="metric_1_p">Changement de flux en une fraction de seconde grâce au pré-chargement HTTP 2.0 et au pipeline Media3 optimisé.</p>
        </div>

        <div class="p-6 sm:p-8 rounded-2xl glass-card text-center border-t-2 border-t-electric-violet">
          <div class="font-headline text-4xl sm:text-5xl font-black text-electric-violet mb-2">4K 60FPS</div>
          <h3 class="font-headline font-bold text-white text-lg mb-2" data-i18n="metric_2_t">Décodage Matériel Pur</h3>
          <p class="text-xs sm:text-sm text-slate-300" data-i18n="metric_2_p">Pass-through direct vers le GPU de votre Smart TV ou NVIDIA Shield. Zéro perte de trame lors des grands matchs sportifs.</p>
        </div>

        <div class="p-6 sm:p-8 rounded-2xl glass-card text-center border-t-2 border-t-remote-amber">
          <div class="font-headline text-4xl sm:text-5xl font-black text-remote-amber mb-2">13.7 Mo</div>
          <h3 class="font-headline font-bold text-white text-lg mb-2" data-i18n="metric_3_t">Poids Plume &amp; Zéro Lag</h3>
          <p class="text-xs sm:text-sm text-slate-300" data-i18n="metric_3_p">Application ultra-légère sans aucun traceur ni publicité encombrante, parfaitement adaptée aux 8 Go de stockage des FireSticks.</p>
        </div>

      </div>

    </div>
  </section>

  <!-- ======================================================== -->
  <!-- 6 KEY PRO FEATURES                                       -->
  <!-- ======================================================== -->
  <section class="py-16 sm:py-24 px-4 sm:px-6 relative bg-surface-obsidian" id="features">
    <div class="max-w-6xl mx-auto">
      
      <div class="text-center mb-12 sm:mb-16">
        <span class="font-label text-xs uppercase tracking-widest text-neon-cyan font-bold" data-i18n="features_badge">Fonctionnalités Clés</span>
        <h2 class="font-headline text-2xl sm:text-4xl md:text-5xl font-black text-white mt-2 mb-4" data-i18n="features_title">L'Expérience Télévisuelle Réinventée</h2>
        <p class="font-body text-slate-300 text-sm sm:text-base max-w-2xl mx-auto" data-i18n="features_subtitle">Toutes les technologies essentielles pour transformer votre salon en véritable salle de cinéma connectée.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Feat 1 -->
        <div class="p-6 rounded-2xl glass-card">
          <div class="w-12 h-12 rounded-xl bg-neon-cyan/15 text-neon-cyan flex items-center justify-center mb-4 border border-neon-cyan/30">
            <span class="material-symbols-outlined text-[26px]">translate</span>
          </div>
          <h3 class="font-headline font-bold text-white text-lg mb-2" data-i18n="f1_title">Multi-Pistes Audio &amp; Sous-Titres</h3>
          <p class="text-xs sm:text-sm text-slate-300 leading-relaxed" data-i18n="f1_desc">Changez facilement de langue (Français, Anglais, Arabe, Espagnol...) et activez les sous-titres intégrés en un clic avec la télécommande sans interrompre votre film.</p>
        </div>

        <!-- Feat 2 -->
        <div class="p-6 rounded-2xl glass-card">
          <div class="w-12 h-12 rounded-xl bg-electric-violet/15 text-electric-violet flex items-center justify-center mb-4 border border-electric-violet/30">
            <span class="material-symbols-outlined text-[26px]">favorite</span>
          </div>
          <h3 class="font-headline font-bold text-white text-lg mb-2" data-i18n="f2_title">Favoris Personnalisés &amp; Réorganisation</h3>
          <p class="text-xs sm:text-sm text-slate-300 leading-relaxed" data-i18n="f2_desc">Ajoutez vos chaînes préférées d'un simple clic sur le bouton cœur ❤️ et réorganisez l'ordre de votre bouquet TV directement depuis l'écran.</p>
        </div>

        <!-- Feat 3 -->
        <div class="p-6 rounded-2xl glass-card">
          <div class="w-12 h-12 rounded-xl bg-remote-amber/15 text-remote-amber flex items-center justify-center mb-4 border border-remote-amber/30">
            <span class="material-symbols-outlined text-[26px]">history</span>
          </div>
          <h3 class="font-headline font-bold text-white text-lg mb-2" data-i18n="f3_title">Reprise de Lecture &amp; Historique</h3>
          <p class="text-xs sm:text-sm text-slate-300 leading-relaxed" data-i18n="f3_desc">Ne perdez jamais le fil de vos films et séries : l'application mémorise automatiquement la position exacte de lecture sur chaque épisode.</p>
        </div>

        <!-- Feat 4 -->
        <div class="p-6 rounded-2xl glass-card">
          <div class="w-12 h-12 rounded-xl bg-status-green/15 text-status-green flex items-center justify-center mb-4 border border-status-green/30">
            <span class="material-symbols-outlined text-[26px]">lock_reset</span>
          </div>
          <h3 class="font-headline font-bold text-white text-lg mb-2" data-i18n="f4_title">Contrôle Parental Sécurisé</h3>
          <p class="text-xs sm:text-sm text-slate-300 leading-relaxed" data-i18n="f4_desc">Verrouillez d'un code PIN les catégories adultes ou sensibles pour protéger vos enfants et toute la famille en toute sérénité.</p>
        </div>

        <!-- Feat 5 -->
        <div class="p-6 rounded-2xl glass-card">
          <div class="w-12 h-12 rounded-xl bg-sky-400/15 text-sky-400 flex items-center justify-center mb-4 border border-sky-400/30">
            <span class="material-symbols-outlined text-[26px]">aspect_ratio</span>
          </div>
          <h3 class="font-headline font-bold text-white text-lg mb-2" data-i18n="f5_title">Ratios d'Écran Dynamiques</h3>
          <p class="text-xs sm:text-sm text-slate-300 leading-relaxed" data-i18n="f5_desc">Ajustez l'image à votre convenance : Fit (16:9 d'origine), Fill (Plein écran immersif) ou Zoom cinématique pour supprimer les bandes noires.</p>
        </div>

        <!-- Feat 6 -->
        <div class="p-6 rounded-2xl glass-card">
          <div class="w-12 h-12 rounded-xl bg-pink-500/15 text-pink-400 flex items-center justify-center mb-4 border border-pink-500/30">
            <span class="material-symbols-outlined text-[26px]">sync</span>
          </div>
          <h3 class="font-headline font-bold text-white text-lg mb-2" data-i18n="f6_title">Mise à Jour Cloud Automatique</h3>
          <p class="text-xs sm:text-sm text-slate-300 leading-relaxed" data-i18n="f6_desc">Gérez vos playlists à distance depuis le portail web. Dès que vous ajoutez ou modifiez un lien M3U ou Xtream, l'application se synchronise instantanément.</p>
        </div>

      </div>

    </div>
  </section>

  <!-- ======================================================== -->
  <!-- 3-STEP QUICK INSTALLATION SETUP                          -->
  <!-- ======================================================== -->
  <section class="py-16 sm:py-24 px-4 sm:px-6 relative" id="setup">
    <div class="max-w-6xl mx-auto">
      
      <div class="text-center mb-12 sm:mb-16">
        <span class="font-label text-xs uppercase tracking-widest text-remote-amber font-bold" data-i18n="setup_badge">Prise en Main en 2 Minutes</span>
        <h2 class="font-headline text-2xl sm:text-4xl md:text-5xl font-black text-white mt-2 mb-4" data-i18n="setup_title">Comment Installer Dreamania Player ?</h2>
        <p class="font-body text-slate-300 text-sm sm:text-base max-w-2xl mx-auto" data-i18n="setup_subtitle">Trois étapes simples et rapides sans configuration compliquée sur votre Smart TV ou FireStick.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Step 1 -->
        <div class="p-6 sm:p-8 rounded-2xl glass-card relative border-t-2 border-t-neon-cyan">
          <span class="font-headline text-5xl font-black text-neon-cyan/20 absolute top-4 right-6">01</span>
          <div class="w-10 h-10 rounded-xl bg-neon-cyan/20 text-neon-cyan flex items-center justify-center font-headline font-bold text-base mb-4">1</div>
          <h3 class="font-headline font-bold text-white text-lg mb-2" data-i18n="step_1_t">Téléchargez l'Application</h3>
          <p class="text-xs sm:text-sm text-slate-300 mb-4" data-i18n="step_1_p">Ouvrez l'application gratuite <strong>Downloader</strong> sur votre FireStick ou Android TV et tapez le code officiel :</p>
          <div class="p-3 rounded-xl bg-surface-container border border-remote-amber/40 flex items-center justify-between">
            <span class="text-xs text-slate-400 font-label">Code Downloader :</span>
            <span class="text-remote-amber font-mono font-bold text-lg"><?php echo htmlspecialchars($downloaderCode); ?></span>
          </div>
        </div>

        <!-- Step 2 -->
        <div class="p-6 sm:p-8 rounded-2xl glass-card relative border-t-2 border-t-electric-violet">
          <span class="font-headline text-5xl font-black text-electric-violet/20 absolute top-4 right-6">02</span>
          <div class="w-10 h-10 rounded-xl bg-electric-violet/20 text-electric-violet flex items-center justify-center font-headline font-bold text-base mb-4">2</div>
          <h3 class="font-headline font-bold text-white text-lg mb-2" data-i18n="step_2_t">Notez Votre Adresse MAC</h3>
          <p class="text-xs sm:text-sm text-slate-300 mb-4" data-i18n="step_2_p">Lancez l'application installée. Votre <strong>Adresse MAC</strong> (ex: <code>00:1A:79:...</code>) et votre Clé d'appareil s'affichent automatiquement à l'écran.</p>
          <div class="p-3 rounded-xl bg-surface-container border border-white/10 text-xs text-slate-300">
            <span class="text-status-green font-bold">✓</span> <span data-i18n="step_2_note">Essai gratuit automatique de 7 jours activé dès le premier démarrage.</span>
          </div>
        </div>

        <!-- Step 3 -->
        <div class="p-6 sm:p-8 rounded-2xl glass-card relative border-t-2 border-t-remote-amber">
          <span class="font-headline text-5xl font-black text-remote-amber/20 absolute top-4 right-6">03</span>
          <div class="w-10 h-10 rounded-xl bg-remote-amber/20 text-remote-amber flex items-center justify-center font-headline font-bold text-base mb-4">3</div>
          <h3 class="font-headline font-bold text-white text-lg mb-2" data-i18n="step_3_t">Ajoutez Votre Playlist</h3>
          <p class="text-xs sm:text-sm text-slate-300 mb-4" data-i18n="step_3_p">Rendez-vous sur notre portail <a href="device.php" class="text-neon-cyan underline font-semibold">Mon Appareil</a>, entrez votre MAC et téléversez votre lien M3U ou identifiants Xtream Codes.</p>
          <a href="device.php" class="block text-center py-2.5 rounded-xl bg-remote-amber text-slate-950 font-label font-bold text-xs sm:text-sm hover:brightness-110 transition-all" data-i18n="step_3_btn">Accéder au Portail Appareil →</a>
        </div>

      </div>

    </div>
  </section>

  <!-- ======================================================== -->
  <!-- PRICING & PAYMENT PLANS                                  -->
  <!-- ======================================================== -->
  <section class="py-16 sm:py-24 px-4 sm:px-6 relative bg-surface-obsidian" id="pricing">
    <div class="max-w-6xl mx-auto">
      
      <div class="text-center mb-12 sm:mb-16">
        <span class="font-label text-xs uppercase tracking-widest text-neon-cyan font-bold" data-i18n="pricing_badge">Tarification Transparente</span>
        <h2 class="font-headline text-2xl sm:text-4xl md:text-5xl font-black text-white mt-2 mb-4" data-i18n="pricing_title">Choisissez Votre Formule Dreamania</h2>
        <p class="font-body text-slate-300 text-sm sm:text-base max-w-2xl mx-auto" data-i18n="pricing_subtitle">Activation instantanée après paiement par Virement Bancaire Maroc (RIB CIH) ou PayPal International.</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-4xl mx-auto mb-16">
        
        <!-- Plan 1: 1-Year VIP -->
        <div class="p-6 sm:p-8 rounded-3xl glass-card relative border border-white/10 flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-4">
              <span class="font-headline font-bold text-white text-xl" data-i18n="plan_1y_t">Licence 1 An VIP</span>
              <span class="px-3 py-1 rounded-full bg-surface-container text-slate-300 font-label text-xs font-semibold">12 Mois</span>
            </div>
            <div class="flex items-baseline gap-2 mb-6">
              <span class="font-headline text-4xl sm:text-5xl font-black text-white"><?= $c1y_dh ?> DH</span>
              <span class="text-slate-400 font-headline text-xl">/ <?= $c1y_eur ?> €</span>
            </div>
            <ul class="space-y-3 text-xs sm:text-sm text-slate-300 mb-8">
              <li class="flex items-center gap-2.5">
                <span class="text-status-green font-bold">✓</span> <span data-i18n="p1_f1">Activation garantie 1 appareil pendant 12 mois</span>
              </li>
              <li class="flex items-center gap-2.5">
                <span class="text-status-green font-bold">✓</span> <span data-i18n="p1_f2">Moteur Google Media3 ExoPlayer 4K 60FPS</span>
              </li>
              <li class="flex items-center gap-2.5">
                <span class="text-status-green font-bold">✓</span> <span data-i18n="p1_f3">Guide EPG, multi-audio et sous-titres complets</span>
              </li>
              <li class="flex items-center gap-2.5">
                <span class="text-status-green font-bold">✓</span> <span data-i18n="p1_f4">Mises à jour automatiques incluses</span>
              </li>
            </ul>
          </div>
          <button type="button" onclick="openPaymentModal('1_year', '<?= $c1y_dh ?> DH / <?= $c1y_eur ?> €')" class="w-full py-3.5 rounded-xl bg-surface-container-highest hover:bg-neon-cyan/20 text-white hover:text-neon-cyan border border-white/10 hover:border-neon-cyan/40 font-label font-bold text-sm transition-all" data-i18n="btn_choose_plan">Activer 1 An (<?= $c1y_dh ?> DH / <?= $c1y_eur ?> €)</button>
        </div>

        <!-- Plan 2: Lifetime Pro (Featured) -->
        <div class="p-6 sm:p-8 rounded-3xl glass-card relative border-2 border-neon-cyan shadow-[0_0_30px_rgba(0,229,255,0.25)] flex flex-col justify-between">
          <div class="absolute -top-3.5 right-6 px-3 py-1 rounded-full bg-gradient-to-r from-neon-cyan to-electric-violet text-slate-950 font-label font-black text-xs uppercase tracking-wider" data-i18n="plan_best_badge">Offre Ultime</div>
          <div>
            <div class="flex items-center justify-between mb-4">
              <span class="font-headline font-bold text-white text-xl" data-i18n="plan_life_t">Licence À Vie PRO</span>
              <span class="px-3 py-1 rounded-full bg-neon-cyan/20 text-neon-cyan font-label text-xs font-bold">Sans Expiration</span>
            </div>
            <div class="flex items-baseline gap-2 mb-6">
              <span class="font-headline text-4xl sm:text-5xl font-black text-neon-cyan"><?= $clife_dh ?> DH</span>
              <span class="text-slate-400 font-headline text-xl">/ <?= $clife_eur ?> €</span>
            </div>
            <ul class="space-y-3 text-xs sm:text-sm text-slate-300 mb-8">
              <li class="flex items-center gap-2.5">
                <span class="text-neon-cyan font-bold">✓</span> <span class="text-white font-semibold" data-i18n="p2_f1">Licence Illimitée à Vie (Sans abonnement récurrent)</span>
              </li>
              <li class="flex items-center gap-2.5">
                <span class="text-neon-cyan font-bold">✓</span> <span data-i18n="p2_f2">Toutes les fonctionnalités Pro débloquées</span>
              </li>
              <li class="flex items-center gap-2.5">
                <span class="text-neon-cyan font-bold">✓</span> <span data-i18n="p2_f3">Accès prioritaire aux 4 thèmes d'interface</span>
              </li>
              <li class="flex items-center gap-2.5">
                <span class="text-neon-cyan font-bold">✓</span> <span data-i18n="p2_f4">Support VIP prioritaire via WhatsApp</span>
              </li>
            </ul>
          </div>
          <button type="button" onclick="openPaymentModal('lifetime', '<?= $clife_dh ?> DH / <?= $clife_eur ?> €')" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-neon-cyan to-sky-400 text-slate-950 font-label font-extrabold text-sm shadow-[0_0_20px_rgba(0,229,255,0.4)] hover:shadow-[0_0_30px_rgba(0,229,255,0.7)] hover:scale-105 active:scale-95 transition-all" data-i18n="btn_choose_lifetime">Activer À Vie (<?= $clife_dh ?> DH / <?= $clife_eur ?> €)</button>
        </div>

      </div>

      <!-- Reseller Packs Preview Box -->
      <div class="max-w-4xl mx-auto p-6 sm:p-8 rounded-3xl glass-card border border-purple-500/30 text-center" id="reseller">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-electric-violet/20 text-electric-violet font-label text-xs uppercase font-bold mb-3">
          <span class="material-symbols-outlined text-[16px]">storefront</span>
          <span data-i18n="reseller_badge">Programme Partenaires &amp; Revendeurs</span>
        </div>
        <h3 class="font-headline text-xl sm:text-2xl font-bold text-white mb-2" data-i18n="reseller_title">Vous Êtes Revendeur IPTV ou Grossiste ?</h3>
        <p class="text-xs sm:text-sm text-slate-300 max-w-2xl mx-auto mb-6" data-i18n="reseller_subtitle">Bénéficiez de notre console de gestion dédiée avec des packs de crédits à tarifs dégressifs exceptionnels pour activer les appareils de vos clients en quelques clics.</p>
        
        <!-- Dynamic Reseller Wholesale Packs from Database -->
        <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 mb-6">
          <?php if (!empty($resellerPacks)): ?>
            <?php foreach ($resellerPacks as $crNum => $pack): ?>
              <?php
                $cr = (int)($pack['credits'] ?? $crNum);
                $dh = (int)($pack['dh'] ?? 0);
                $eur = (int)($pack['eur'] ?? 0);
                $isTop = ($cr >= 100);
                $boxClass = $isTop 
                  ? 'text-neon-cyan font-bold border border-neon-cyan/50 shadow-[0_0_15px_rgba(0,229,255,0.3)] bg-neon-cyan/10' 
                  : 'text-slate-200 border border-surface-glass-stroke bg-surface-container';
              ?>
              <span class="px-3.5 py-2 rounded-xl font-mono text-xs sm:text-sm <?= $boxClass ?>">
                <strong class="text-white"><?= $cr ?> Crédits</strong> : <?= $dh ?> DH (<?= $eur ?> €)
              </span>
            <?php endforeach; ?>
          <?php else: ?>
            <span class="px-3.5 py-2 rounded-xl bg-surface-container font-mono text-xs sm:text-sm text-slate-200 border border-surface-glass-stroke">10 Crédits : <?= $c1y_dh * 3 ?> DH</span>
            <span class="px-3.5 py-2 rounded-xl bg-surface-container font-mono text-xs sm:text-sm text-slate-200 border border-surface-glass-stroke">20 Crédits : <?= $c1y_dh * 4 ?> DH</span>
            <span class="px-3.5 py-2 rounded-xl bg-surface-container font-mono text-xs sm:text-sm text-slate-200 border border-surface-glass-stroke">50 Crédits : <?= $c1y_dh * 7 ?> DH</span>
            <span class="px-3.5 py-2 rounded-xl bg-surface-container font-mono text-xs sm:text-sm text-neon-cyan font-bold border border-neon-cyan/40">100 Crédits : <?= $c1y_dh * 12 ?> DH</span>
          <?php endif; ?>
        </div>

        <a href="index.php?panel=1" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-electric-violet to-purple-600 text-white font-label font-bold text-sm shadow-[0_0_20px_rgba(139,92,246,0.4)] hover:shadow-[0_0_30px_rgba(139,92,246,0.7)] hover:scale-105 active:scale-95 transition-all">
          <span class="material-symbols-outlined text-[18px]">vpn_key</span>
          <span data-i18n="btn_access_reseller">Se Connecter au Panel Revendeur →</span>
        </a>
      </div>

    </div>
  </section>

  <!-- ======================================================== -->
  <!-- FAQ ACCORDION                                            -->
  <!-- ======================================================== -->
  <section class="py-16 sm:py-24 px-4 sm:px-6 relative" id="faq">
    <div class="max-w-4xl mx-auto">
      
      <div class="text-center mb-12 sm:mb-16">
        <span class="font-label text-xs uppercase tracking-widest text-neon-cyan font-bold" data-i18n="faq_badge">Questions Fréquentes</span>
        <h2 class="font-headline text-2xl sm:text-4xl md:text-5xl font-black text-white mt-2 mb-4" data-i18n="faq_title">Tout Ce Que Vous Devez Savoir</h2>
        <p class="font-body text-slate-300 text-sm sm:text-base" data-i18n="faq_subtitle">Retrouvez les réponses aux questions les plus courantes sur Dreamania Player.</p>
      </div>

      <div class="space-y-4">
        
        <!-- Q1 -->
        <div class="rounded-2xl glass-card overflow-hidden">
          <button type="button" onclick="toggleFaq(1)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-headline text-base sm:text-lg font-bold text-white hover:text-neon-cyan transition-colors">
            <span data-i18n="faq_q1">Dreamania Player inclut-il des chaînes TV ou du contenu ?</span>
            <span id="faq-icon-1" class="material-symbols-outlined text-[22px] text-neon-cyan transition-transform duration-200">expand_more</span>
          </button>
          <div id="faq-ans-1" class="hidden px-5 pb-5 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-3">
            <span data-i18n="faq_a1">Non. Dreamania Player est un lecteur multimédia pur (Media Player). Nous n'hébergeons ni ne fournissons aucun flux, chaîne ou playlist. L'utilisateur doit importer ses propres playlists M3U ou identifiants Xtream Codes fournis par son abonnement légitime.</span>
          </div>
        </div>

        <!-- Q2 -->
        <div class="rounded-2xl glass-card overflow-hidden">
          <button type="button" onclick="toggleFaq(2)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-headline text-base sm:text-lg font-bold text-white hover:text-neon-cyan transition-colors">
            <span data-i18n="faq_q2">Comment installer l'application sur Amazon Fire TV Stick ?</span>
            <span id="faq-icon-2" class="material-symbols-outlined text-[22px] text-neon-cyan transition-transform duration-200">expand_more</span>
          </button>
          <div id="faq-ans-2" class="hidden px-5 pb-5 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-3">
            <span data-i18n="faq_a2">Sur votre Fire TV Stick, ouvrez l'application gratuite <strong>Downloader</strong> depuis l'Appstore Amazon, saisissez notre code Downloader <code>7454701</code> puis validez. Le téléchargement de <code>DreamaniaPlayer.apk</code> démarrera automatiquement. Veillez à activer l'autorisation « Applications inconnues » dans les Options Développeur.</span>
          </div>
        </div>

        <!-- Q3 -->
        <div class="rounded-2xl glass-card overflow-hidden">
          <button type="button" onclick="toggleFaq(3)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-headline text-base sm:text-lg font-bold text-white hover:text-neon-cyan transition-colors">
            <span data-i18n="faq_q3">Comment changer de langue audio ou activer les sous-titres ?</span>
            <span id="faq-icon-3" class="material-symbols-outlined text-[22px] text-neon-cyan transition-transform duration-200">expand_more</span>
          </button>
          <div id="faq-ans-3" class="hidden px-5 pb-5 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-3">
            <span data-i18n="faq_a3">Pendant la lecture vidéo d'une chaîne ou d'un film VOD, appuyez sur la flèche du bas ou le bouton OK de votre télécommande pour faire apparaître la barre OSD, puis cliquez sur <strong>🔊 Audio Track</strong> pour choisir la langue ou sur <strong>💬 Subtitles</strong> pour activer/désactiver les sous-titres.</span>
          </div>
        </div>

        <!-- Q4 -->
        <div class="rounded-2xl glass-card overflow-hidden">
          <button type="button" onclick="toggleFaq(4)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-headline text-base sm:text-lg font-bold text-white hover:text-neon-cyan transition-colors">
            <span data-i18n="faq_q4">Comment activer ma licence après paiement ?</span>
            <span id="faq-icon-4" class="material-symbols-outlined text-[22px] text-neon-cyan transition-transform duration-200">expand_more</span>
          </button>
          <div id="faq-ans-4" class="hidden px-5 pb-5 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-3">
            <span data-i18n="faq_a4">Après avoir effectué votre paiement par virement CIH ou PayPal, envoyez votre reçu ainsi que votre <strong>Adresse MAC</strong> affichée dans l'application sur notre WhatsApp d'assistance. Votre appareil sera activé immédiatement dans notre base de données.</span>
          </div>
        </div>

        <!-- Q5 -->
        <div class="rounded-2xl glass-card overflow-hidden">
          <button type="button" onclick="toggleFaq(5)" class="w-full p-5 text-left flex items-center justify-between gap-4 font-headline text-base sm:text-lg font-bold text-white hover:text-neon-cyan transition-colors">
            <span data-i18n="faq_q5">Puis-je transférer ma licence sur un autre téléviseur si je change d'appareil ?</span>
            <span id="faq-icon-5" class="material-symbols-outlined text-[22px] text-neon-cyan transition-transform duration-200">expand_more</span>
          </button>
          <div id="faq-ans-5" class="hidden px-5 pb-5 text-xs sm:text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-3">
            <span data-i18n="faq_a5">Oui ! Si vous changez de télévision ou de Fire TV Stick, contactez notre support en précisant votre ancienne adresse MAC et la nouvelle. Votre licence sera transférée gratuitement et sans interruption.</span>
          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- ======================================================== -->
  <!-- FOOTER & MASTER ADMIN ACCESS                             -->
  <!-- ======================================================== -->
  <footer class="pt-16 pb-12 px-4 sm:px-6 border-t border-white/5 bg-[#07070E]">
    <div class="max-w-6xl mx-auto">
      
      <!-- Prominent Master Admin Access Card (As requested: put admin in footer) -->
      <div class="mb-12 p-5 sm:p-6 rounded-2xl bg-gradient-to-r from-surface-container via-surface-container-high to-surface-container border border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-lg">
        <div class="flex items-center gap-3.5 text-center sm:text-left">
          <div class="w-11 h-11 rounded-xl bg-neon-cyan/15 text-neon-cyan flex items-center justify-center border border-neon-cyan/30 shrink-0 shadow-[0_0_15px_rgba(0,229,255,0.2)]">
            <span class="material-symbols-outlined text-[24px]">shield_person</span>
          </div>
          <div>
            <h4 class="font-headline font-bold text-white text-base" data-i18n="admin_footer_t">Console d'Administration Master</h4>
            <p class="text-xs text-slate-400" data-i18n="admin_footer_p">Espace sécurisé réservé aux administrateurs de la plateforme Dreamania (Gestion des licences, tarifs, revendeurs et API).</p>
          </div>
        </div>
        <a href="index.php?panel=1" class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-surface-container-highest hover:bg-neon-cyan text-white hover:text-slate-950 font-label font-bold text-xs sm:text-sm border border-white/10 hover:border-neon-cyan transition-all shadow-md shrink-0">
          <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
          <span data-i18n="btn_admin_login">Connexion Super Admin</span>
        </a>
      </div>

      <!-- Main Footer Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8 mb-12">
        
        <!-- Brand Info -->
        <div class="space-y-3">
          <div class="flex items-center gap-2">
            <img src="assets/img/icon.png" alt="Logo" class="w-7 h-7 object-contain"/>
            <span class="font-headline font-extrabold text-white text-base tracking-wider uppercase">DREAMANIA</span>
          </div>
          <p class="text-xs text-slate-400 leading-relaxed" data-i18n="footer_brand_desc">
            Le lecteur multimédia IPTV &amp; VOD de référence, propulsé par Google Media3 ExoPlayer pour une expérience 4K 60FPS sans compromis.
          </p>
          <div class="flex items-center gap-2 text-xs text-neon-cyan font-mono">
            <span>Code Downloader :</span>
            <span class="font-bold text-white"><?php echo htmlspecialchars($downloaderCode); ?></span>
          </div>
        </div>

        <!-- Portals Navigation -->
        <div>
          <h5 class="font-headline font-bold text-white text-sm mb-3 uppercase tracking-wider" data-i18n="footer_col_portals">Portails &amp; Accès</h5>
          <ul class="space-y-2 text-xs text-slate-400">
            <li><a href="device.php" class="hover:text-neon-cyan transition-colors" data-i18n="f_link_device">Mon Appareil (Playlists TV)</a></li>
            <li><a href="index.php?panel=1" class="hover:text-neon-cyan transition-colors" data-i18n="f_link_reseller">Espace Revendeur</a></li>
            <li><a href="index.php?panel=1" class="hover:text-neon-cyan transition-colors" data-i18n="f_link_admin">Console Super Admin</a></li>
            <li><a href="<?php echo htmlspecialchars($downloadUrl); ?>" class="hover:text-neon-cyan transition-colors" data-i18n="f_link_download">Téléchargement APK Direct</a></li>
          </ul>
        </div>

        <!-- Certified Hardware -->
        <div>
          <h5 class="font-headline font-bold text-white text-sm mb-3 uppercase tracking-wider" data-i18n="footer_col_compat">Matériel Compatible</h5>
          <ul class="space-y-2 text-xs text-slate-400">
            <li>Amazon Fire TV Stick 4K &amp; Max</li>
            <li>Google TV &amp; Chromecast</li>
            <li>NVIDIA Shield TV Pro</li>
            <li>Smart TV Android / Box 4K</li>
            <li>Apple TV / iOS &amp; WebOS</li>
          </ul>
        </div>

        <!-- Contact & Legal -->
        <div>
          <h5 class="font-headline font-bold text-white text-sm mb-3 uppercase tracking-wider" data-i18n="footer_col_support">Assistance &amp; Légal</h5>
          <ul class="space-y-2 text-xs text-slate-400">
            <li><a href="<?php echo htmlspecialchars($paymentConfig['whatsapp_number'] ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $paymentConfig['whatsapp_number']) : '#'); ?>" target="_blank" class="hover:text-neon-cyan transition-colors">Support WhatsApp VIP</a></li>
            <li><a href="#faq" class="hover:text-neon-cyan transition-colors" data-i18n="nav_faq">Foire Aux Questions</a></li>
            <li><span class="text-slate-500">Conforme DMCA &amp; RGPD</span></li>
          </ul>
        </div>

      </div>

      <!-- Legal Disclaimer -->
      <div class="pt-6 border-t border-white/5 text-center text-[11px] text-slate-500 leading-relaxed max-w-4xl mx-auto">
        <p data-i18n="legal_disclaimer">
          Avertissement légal : Dreamania Player Pro est une application logicielle de lecture multimédia pure et neutre. Elle n'héberge, ne vend, ne transmet ni ne fournit aucun flux vidéo, chaîne de télévision ou contenu multimédia sous copyright. Les utilisateurs sont seuls responsables de la légalité des flux et listes M3U qu'ils chargent dans l'application. Tous les noms de marques cités (Amazon Fire TV, Google TV, NVIDIA Shield, Android TV, Apple TV) sont des marques déposées de leurs propriétaires respectifs.
        </p>
        <p class="mt-3 text-slate-400">
          © <?php echo date('Y'); ?> Dreamania Player Pro. Tous droits réservés.
        </p>
      </div>

    </div>
  </footer>

  <!-- ======================================================== -->
  <!-- STICKY MOBILE BOTTOM BAR (High Conversion & Accessibility)-->
  <!-- ======================================================== -->
  <div class="fixed bottom-0 inset-x-0 z-40 lg:hidden bg-[#0A0B14]/95 backdrop-blur-xl border-t border-white/10 px-3 py-2 flex items-center justify-around shadow-[0_-10px_25px_rgba(0,0,0,0.8)]">
    
    <!-- Mon Appareil -->
    <a href="device.php" class="flex flex-col items-center gap-1 text-slate-300 hover:text-remote-amber transition-colors">
      <span class="material-symbols-outlined text-[20px] text-remote-amber">tv</span>
      <span class="text-[10px] font-label font-bold" data-i18n="mob_device">Appareil</span>
    </a>

    <!-- Revendeur -->
    <a href="index.php?panel=1" class="flex flex-col items-center gap-1 text-slate-300 hover:text-electric-violet transition-colors">
      <span class="material-symbols-outlined text-[20px] text-purple-400">storefront</span>
      <span class="text-[10px] font-label font-bold" data-i18n="mob_reseller">Revendeur</span>
    </a>

    <!-- Downloader Code 7454701 -->
    <button type="button" onclick="copyDownloaderCode()" class="flex flex-col items-center gap-1 text-slate-300 hover:text-neon-cyan transition-colors">
      <span class="material-symbols-outlined text-[20px] text-neon-cyan">download</span>
      <span class="text-[10px] font-label font-bold font-mono"><?php echo htmlspecialchars($downloaderCode); ?></span>
    </button>

    <!-- Language Toggle -->
    <button type="button" onclick="toggleLanguage()" class="flex flex-col items-center gap-1 text-slate-300 hover:text-white transition-colors">
      <span class="text-sm">🌐</span>
      <span class="text-[10px] font-label font-bold" id="mob-lang-label">FR / EN</span>
    </button>

  </div>

  <!-- ======================================================== -->
  <!-- PAYMENT MODAL (CIH RIB & PAYPAL INSTANT COPY)            -->
  <!-- ======================================================== -->
  <div id="paymentModal" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-md flex items-center justify-center p-4">
    <div class="relative w-full max-w-lg rounded-3xl glass-card p-6 sm:p-8 border border-white/15 shadow-2xl">
      <button type="button" onclick="closePaymentModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white p-1 rounded-xl bg-white/5">
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>

      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-neon-cyan/20 text-neon-cyan flex items-center justify-center">
          <span class="material-symbols-outlined text-[22px]">credit_card</span>
        </div>
        <div>
          <h3 class="font-headline font-bold text-white text-lg sm:text-xl" data-i18n="modal_payment_t">Activer Votre Licence</h3>
          <p class="text-xs text-slate-300" id="modalPlanLabel">Formule Sélectionnée</p>
        </div>
      </div>

      <!-- Payment Methods Tabs -->
      <div class="space-y-4 mb-6">
        
        <!-- CIH BANK RIB -->
        <div class="p-4 rounded-2xl bg-surface-container border border-white/10">
          <div class="flex items-center justify-between mb-2">
            <span class="font-headline font-bold text-sm text-white flex items-center gap-2">
              <span class="text-remote-amber">🏛️</span> Virement Bancaire Maroc (RIB CIH)
            </span>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-status-green/20 text-status-green">Instant</span>
          </div>
          <div class="bg-black/50 p-2.5 rounded-xl font-mono text-xs text-slate-200 flex items-center justify-between mb-2">
            <span id="ribNumberVal"><?php echo htmlspecialchars($paymentConfig['rib_number'] ?? '230 780 4567890123456789 12'); ?></span>
            <button type="button" onclick="copyText('ribNumberVal', 'RIB copié !')" class="text-neon-cyan hover:text-white text-xs font-bold underline ml-2">Copier</button>
          </div>
          <div class="text-[11px] text-slate-400">
            Bénéficiaire : <strong class="text-slate-200"><?php echo htmlspecialchars($paymentConfig['rib_account_name'] ?? 'DREAMANIA MEDIA'); ?></strong> • Banque : <strong>CIH BANK</strong>
          </div>
        </div>

        <!-- PayPal -->
        <div class="p-4 rounded-2xl bg-surface-container border border-white/10">
          <div class="flex items-center justify-between mb-2">
            <span class="font-headline font-bold text-sm text-white flex items-center gap-2">
              <span class="text-sky-400">💳</span> PayPal International
            </span>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-sky-400/20 text-sky-400">Sécurisé</span>
          </div>
          <div class="bg-black/50 p-2.5 rounded-xl font-mono text-xs text-slate-200 flex items-center justify-between mb-2">
            <span id="paypalEmailVal"><?php echo htmlspecialchars($paymentConfig['paypal_email'] ?? 'contact@lumineasy.com'); ?></span>
            <button type="button" onclick="copyText('paypalEmailVal', 'Email PayPal copié !')" class="text-neon-cyan hover:text-white text-xs font-bold underline ml-2">Copier</button>
          </div>
          <div class="text-[11px] text-slate-400">
            Lien direct : <a href="<?php echo htmlspecialchars($paymentConfig['paypal_me_link'] ?? 'https://paypal.me/dreamaniacode'); ?>" target="_blank" class="text-sky-400 underline">paypal.me/dreamaniacode</a>
          </div>
        </div>

      </div>

      <!-- WhatsApp Confirmation Step -->
      <div class="p-3.5 rounded-xl bg-status-green/10 border border-status-green/30 text-xs text-slate-200 mb-6 flex items-start gap-2.5">
        <span class="text-status-green text-base">📲</span>
        <div>
          <strong class="text-status-green font-bold" data-i18n="modal_confirm_t">Étape Suivante :</strong>
          <span data-i18n="modal_confirm_p">Après votre virement, envoyez votre reçu ainsi que votre Adresse MAC par WhatsApp pour une activation immédiate.</span>
        </div>
      </div>

      <a id="btnWhatsAppSubmit" href="<?php echo htmlspecialchars($paymentConfig['whatsapp_number'] ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $paymentConfig['whatsapp_number']) . '?text=' . urlencode('Bonjour Dreamania, je souhaite activer ma licence.') : '#'); ?>" target="_blank" class="w-full py-3.5 rounded-xl bg-status-green hover:bg-emerald-400 text-slate-950 font-label font-bold text-sm flex items-center justify-center gap-2 shadow-[0_0_20px_rgba(16,185,129,0.4)] transition-all">
        <span class="material-symbols-outlined text-[20px]">chat</span>
        <span data-i18n="btn_confirm_whatsapp">Confirmer sur WhatsApp</span>
      </a>

    </div>
  </div>

  <!-- Toast Notification Container -->
  <div id="toastNotification" class="fixed top-20 right-6 z-50 hidden px-4 py-3 rounded-2xl bg-[#0D0E1A] text-white border border-neon-cyan/50 shadow-[0_0_25px_rgba(0,229,255,0.4)] text-xs font-bold font-label flex items-center gap-2 transition-all">
    <span class="text-neon-cyan">✓</span>
    <span id="toastMessage">Texte copié !</span>
  </div>

  <!-- ======================================================== -->
  <!-- JAVASCRIPT LOGIC & BILINGUAL FR / EN TRANSLATIONS        -->
  <!-- ======================================================== -->
  <script>
    // --- Dynamic Pricing from Database ---
    const dynamicPricing = {
      c1y_dh: <?= json_encode($c1y_dh) ?>,
      c1y_eur: <?= json_encode($c1y_eur) ?>,
      clife_dh: <?= json_encode($clife_dh) ?>,
      clife_eur: <?= json_encode($clife_eur) ?>
    };

    // --- Complete Translation Dictionary ---
    const translations = {
      fr: {
        nav_features: "Fonctionnalités",
        nav_app_tour: "Visite Guidée",
        nav_engine: "Moteur Media3",
        nav_setup: "Installation",
        nav_plans: "Tarifs",
        nav_faq: "FAQ",
        nav_device: "Mon Appareil",
        nav_device_short: "Appareil",
        nav_download: "Télécharger APK",
        hero_badge: "Moteur Broadcast Officiel • Certifié Android TV & Fire TV",
        hero_title_1: "Le Lecteur IPTV & Cinéma ",
        hero_title_hl: "Nouvelle Génération",
        hero_title_2: " Taillé pour Votre Smart TV",
        hero_desc: "Conçu pour un zapping instantané, le streaming sportif 4K 60FPS et la VOD cinéma enrichie TMDb. Propulsé par Google Media3 ExoPlayer avec zéro superflu et une navigation fluide dédiée à la télécommande TV.",
        btn_download_apk: "Télécharger APK (v1.1.0 Pro)",
        btn_device_portal: "Gérer TV / Téléverser Playlist",
        btn_reseller_panel: "Espace Revendeur",
        btn_pricing_link: `Tarifs (${dynamicPricing.c1y_dh} DH / ${dynamicPricing.c1y_eur}€ – ${dynamicPricing.clife_dh} DH / ${dynamicPricing.clife_eur}€)`,
        release_latest_title: "Dernière Version Déployée :",
        code_label: "Code Downloader :",
        release_feat_1: "Sélecteur audio & sous-titres en direct lors de la lecture",
        release_feat_2: "Formatage propre des dates des séries TV (fini les timestamps)",
        release_feat_3: "Navigation télécommande D-Pad ultra-réactive avec focus 3D",
        release_feat_4: "Moteur Media3 ExoPlayer zéro mise en mémoire tampon",
        hardware_title: "Compatibilité Matérielle 100% Certifiée",
        gallery_badge: "Expérience Télécommande 10-Foot UI",
        gallery_title: "Visite Guidée de l'Application",
        gallery_subtitle: "Découvrez l'interface moderne de Dreamania Player conçue exclusivement pour l'ergonomie des téléviseurs et télécommandes.",
        tab_launcher: "Launcher TV",
        tab_epg: "Guide TV & EPG",
        tab_player: "Lecteur & OSD",
        tab_vod: "Cinéma VOD 4K",
        tab_series: "Séries & Saisons",
        tab_themes: "4 Thèmes Cyber",
        desc_launcher_t: "Tableau de Bord TV Haute Précision",
        desc_launcher_p: "Accès direct Télévision en Direct, Films VOD, Séries TV, Favoris ❤️ et statut d'abonnement en temps réel.",
        desc_epg_t: "Guide TV & Grille EPG Dynamique",
        desc_epg_p: "Aperçu direct du programme en cours, barre de progression en temps réel et titre de l'émission suivante.",
        desc_player_t: "Lecteur Vidéo Avancé & Contrôle D-Pad",
        desc_player_p: "Changement de piste audio multilingue, sous-titres, ratio d'écran 16:9 / Zoom, et tiroir de chaînes sans couper la vidéo.",
        desc_vod_t: "Bibliothèque Cinéma 4K Enrichie TMDb",
        desc_vod_p: "Affiches haute résolution, notes IMDb, synopsis complet, casting et reprise automatique de lecture.",
        desc_series_t: "Organisation Intelligente par Saisons & Épisodes",
        desc_series_p: "Navigation D-Pad fluide de saison en saison, durées exactes et dates de diffusion formatées avec soin.",
        desc_themes_t: "4 Thèmes d'Ambiance Personnalisables",
        desc_themes_p: "Basculez instantanément entre Cyber Neon, Obsidian Cinema, Sports Stadium et Dark Obsidian.",
        engine_badge: "Google Media3 ExoPlayer Architecture",
        engine_title: "Performance & Fluidité Sans Équivalent",
        engine_subtitle: "Développé spécifiquement pour éliminer les ralentissements des applications IPTV classiques sur les Fire TV Sticks et box TV abordables.",
        metric_1_t: "Zapping Instantané",
        metric_1_p: "Changement de flux en une fraction de seconde grâce au pré-chargement HTTP 2.0 et au pipeline Media3 optimisé.",
        metric_2_t: "Décodage Matériel Pur",
        metric_2_p: "Pass-through direct vers le GPU de votre Smart TV ou NVIDIA Shield. Zéro perte de trame lors des grands matchs sportifs.",
        metric_3_t: "Poids Plume & Zéro Lag",
        metric_3_p: "Application ultra-légère sans aucun traceur ni publicité encombrante, parfaitement adaptée aux 8 Go de stockage des FireSticks.",
        features_badge: "Fonctionnalités Clés",
        features_title: "L'Expérience Télévisuelle Réinventée",
        features_subtitle: "Toutes les technologies essentielles pour transformer votre salon en véritable salle de cinéma connectée.",
        f1_title: "Multi-Pistes Audio & Sous-Titres",
        f1_desc: "Changez facilement de langue (Français, Anglais, Arabe, Espagnol...) et activez les sous-titres intégrés en un clic avec la télécommande sans interrompre votre film.",
        f2_title: "Favoris Personnalisés & Réorganisation",
        f2_desc: "Ajoutez vos chaînes préférées d'un simple clic sur le bouton cœur ❤️ et réorganisez l'ordre de votre bouquet TV directement depuis l'écran.",
        f3_title: "Reprise de Lecture & Historique",
        f3_desc: "Ne perdez jamais le fil de vos films et séries : l'application mémorise automatiquement la position exacte de lecture sur chaque épisode.",
        f4_title: "Contrôle Parental Sécurisé",
        f4_desc: "Verrouillez d'un code PIN les catégories adultes ou sensibles pour protéger vos enfants et toute la famille en toute sérénité.",
        f5_title: "Ratios d'Écran Dynamiques",
        f5_desc: "Ajustez l'image à votre convenance : Fit (16:9 d'origine), Fill (Plein écran immersif) ou Zoom cinématique pour supprimer les bandes noires.",
        f6_title: "Mise à Jour Cloud Automatique",
        f6_desc: "Gérez vos playlists à distance depuis le portail web. Dès que vous ajoutez ou modifiez un lien M3U ou Xtream, l'application se synchronise instantanément.",
        setup_badge: "Prise en Main en 2 Minutes",
        setup_title: "Comment Installer Dreamania Player ?",
        setup_subtitle: "Trois étapes simples et rapides sans configuration compliquée sur votre Smart TV ou FireStick.",
        step_1_t: "Téléchargez l'Application",
        step_1_p: "Ouvrez l'application gratuite Downloader sur votre FireStick ou Android TV et tapez le code officiel :",
        step_2_t: "Notez Votre Adresse MAC",
        step_2_p: "Lancez l'application installée. Votre Adresse MAC (ex: 00:1A:79:...) et votre Clé d'appareil s'affichent automatiquement à l'écran.",
        step_2_note: "Essai gratuit automatique de 7 jours activé dès le premier démarrage.",
        step_3_t: "Ajoutez Votre Playlist",
        step_3_p: "Rendez-vous sur notre portail Mon Appareil, entrez votre MAC et téléversez votre lien M3U ou identifiants Xtream Codes.",
        step_3_btn: "Accéder au Portail Appareil →",
        pricing_badge: "Tarification Transparente",
        pricing_title: "Choisissez Votre Formule Dreamania",
        pricing_subtitle: "Activation instantanée après paiement par Virement Bancaire Maroc (RIB CIH) ou PayPal International.",
        plan_1y_t: "Licence 1 An VIP",
        p1_f1: "Activation garantie 1 appareil pendant 12 mois",
        p1_f2: "Moteur Google Media3 ExoPlayer 4K 60FPS",
        p1_f3: "Guide EPG, multi-audio et sous-titres complets",
        p1_f4: "Mises à jour automatiques incluses",
        btn_choose_plan: `Activer 1 An (${dynamicPricing.c1y_dh} DH / ${dynamicPricing.c1y_eur} €)`,
        plan_best_badge: "Offre Ultime",
        plan_life_t: "Licence À Vie PRO",
        p2_f1: "Licence Illimitée à Vie (Sans abonnement récurrent)",
        p2_f2: "Toutes les fonctionnalités Pro débloquées",
        p2_f3: "Accès prioritaire aux 4 thèmes d'interface",
        p2_f4: "Support VIP prioritaire via WhatsApp",
        btn_choose_lifetime: `Activer À Vie (${dynamicPricing.clife_dh} DH / ${dynamicPricing.clife_eur} €)`,
        reseller_badge: "Programme Partenaires & Revendeurs",
        reseller_title: "Vous Êtes Revendeur IPTV ou Grossiste ?",
        reseller_subtitle: "Bénéficiez de notre console de gestion dédiée avec des packs de crédits à tarifs dégressifs exceptionnels pour activer les appareils de vos clients en quelques clics.",
        btn_access_reseller: "Se Connecter au Panel Revendeur →",
        faq_badge: "Questions Fréquentes",
        faq_title: "Tout Ce Que Vous Devez Savoir",
        faq_subtitle: "Retrouvez les réponses aux questions les plus courantes sur Dreamania Player.",
        faq_q1: "Dreamania Player inclut-il des chaînes TV ou du contenu ?",
        faq_a1: "Non. Dreamania Player est un lecteur multimédia pur (Media Player). Nous n'hébergeons ni ne fournissons aucun flux, chaîne ou playlist. L'utilisateur doit importer ses propres playlists M3U ou identifiants Xtream Codes fournis par son abonnement légitime.",
        faq_q2: "Comment installer l'application sur Amazon Fire TV Stick ?",
        faq_a2: "Sur votre Fire TV Stick, ouvrez l'application gratuite Downloader depuis l'Appstore Amazon, saisissez notre code Downloader 7454701 puis validez. Le téléchargement de DreamaniaPlayer.apk démarrera automatiquement. Veillez à activer l'autorisation « Applications inconnues » dans les Options Développeur.",
        faq_q3: "Comment changer de langue audio ou activer les sous-titres ?",
        faq_a3: "Pendant la lecture vidéo d'une chaîne ou d'un film VOD, appuyez sur la flèche du bas ou le bouton OK de votre télécommande pour faire apparaître la barre OSD, puis cliquez sur 🔊 Audio Track pour choisir la langue ou sur 💬 Subtitles pour activer/désactiver les sous-titres.",
        faq_q4: "Comment activer ma licence après paiement ?",
        faq_a4: "Après avoir effectué votre paiement par virement CIH ou PayPal, envoyez votre reçu ainsi que votre Adresse MAC affichée dans l'application sur notre WhatsApp d'assistance. Votre appareil sera activé immédiatement dans notre base de données.",
        faq_q5: "Puis-je transférer ma licence sur un autre téléviseur si je change d'appareil ?",
        faq_a5: "Oui ! Si vous changez de télévision ou de Fire TV Stick, contactez notre support en précisant votre ancienne adresse MAC et la nouvelle. Votre licence sera transférée gratuitement et sans interruption.",
        admin_footer_t: "Console d'Administration Master",
        admin_footer_p: "Espace sécurisé réservé aux administrateurs de la plateforme Dreamania (Gestion des licences, tarifs, revendeurs et API).",
        btn_admin_login: "Connexion Super Admin",
        footer_brand_desc: "Le lecteur multimédia IPTV & VOD de référence, propulsé par Google Media3 ExoPlayer pour une expérience 4K 60FPS sans compromis.",
        footer_col_portals: "Portails & Accès",
        f_link_device: "Mon Appareil (Playlists TV)",
        f_link_reseller: "Espace Revendeur",
        f_link_admin: "Console Super Admin",
        f_link_download: "Téléchargement APK Direct",
        footer_col_compat: "Matériel Compatible",
        footer_col_support: "Assistance & Légal",
        legal_disclaimer: "Avertissement légal : Dreamania Player Pro est une application logicielle de lecture multimédia pure et neutre. Elle n'héberge, ne vend, ne transmet ni ne fournit aucun flux vidéo, chaîne de télévision ou contenu multimédia sous copyright. Les utilisateurs sont seuls responsables de la légalité des flux et listes M3U qu'ils chargent dans l'application. Tous les noms de marques cités (Amazon Fire TV, Google TV, NVIDIA Shield, Android TV, Apple TV) sont des marques déposées de leurs propriétaires respectifs.",
        mob_device: "Appareil",
        mob_reseller: "Revendeur",
        modal_payment_t: "Activer Votre Licence",
        modal_confirm_t: "Étape Suivante :",
        modal_confirm_p: "Après votre virement, envoyez votre reçu ainsi que votre Adresse MAC par WhatsApp pour une activation immédiate.",
        btn_confirm_whatsapp: "Confirmer sur WhatsApp"
      },
      en: {
        nav_features: "Features",
        nav_app_tour: "App Tour",
        nav_engine: "Media3 Engine",
        nav_setup: "Setup Guide",
        nav_plans: "Pricing",
        nav_faq: "FAQ",
        nav_device: "Device Portal",
        nav_device_short: "Portal",
        nav_download: "Download APK",
        hero_badge: "Official Broadcast Engine • Certified Android TV & Fire TV",
        hero_title_1: "The Next-Gen IPTV & Cinema ",
        hero_title_hl: "Media Player",
        hero_title_2: " Engineered for Smart TV",
        hero_desc: "Engineered for instantaneous channel zapping, 4K 60FPS sports streaming, and TMDb-enriched cinema VOD. Powered by Google Media3 ExoPlayer with zero bloatware and dedicated Fire TV remote D-Pad navigation.",
        btn_download_apk: "Download APK (v1.1.0 Pro)",
        btn_device_portal: "Manage TV / Upload Playlist",
        btn_reseller_panel: "Reseller Portal",
        btn_pricing_link: `Plans (${dynamicPricing.c1y_dh} DH / €${dynamicPricing.c1y_eur} – ${dynamicPricing.clife_dh} DH / €${dynamicPricing.clife_eur})`,
        release_latest_title: "Latest Official Release:",
        code_label: "Downloader Code:",
        release_feat_1: "Multi-language audio & subtitle switcher during live playback",
        release_feat_2: "Clean formatted TV series release dates (no raw timestamps)",
        release_feat_3: "Ultra-responsive TV remote D-Pad navigation with 3D focus",
        release_feat_4: "Media3 ExoPlayer zero-buffering hardware streaming pipeline",
        hardware_title: "100% Certified Hardware Compatibility",
        gallery_badge: "10-Foot Remote Control UI",
        gallery_title: "Guided Interactive App Tour",
        gallery_subtitle: "Explore Dreamania Player's high-contrast luxury UI designed specifically for television screens and remote controls.",
        tab_launcher: "TV Launcher",
        tab_epg: "Live TV & EPG",
        tab_player: "Player & OSD",
        tab_vod: "4K VOD Movies",
        tab_series: "TV Series & Seasons",
        tab_themes: "4 Cyber Themes",
        desc_launcher_t: "High-Precision Television Dashboard",
        desc_launcher_p: "Instant access to Live TV, VOD Movies, TV Series, Favorites ❤️ and real-time subscription status.",
        desc_epg_t: "Dynamic Live TV & EPG Grid Guide",
        desc_epg_p: "Real-time program schedule preview, broadcast timeline progress bar, and next upcoming show title.",
        desc_player_t: "Advanced Video Player & Remote OSD",
        desc_player_p: "Multi-language audio switcher, subtitle track selection, 16:9 / Zoom aspect ratio, and channel drawer without interrupting playback.",
        desc_vod_t: "TMDb-Enriched 4K Cinema Library",
        desc_vod_p: "High-resolution movie posters, IMDb ratings, complete plot summaries, cast credits, and auto-resume progress.",
        desc_series_t: "Intelligent Season & Episode Navigation",
        desc_series_p: "Seamless D-Pad navigation across seasons, exact episode runtimes, and cleanly formatted release dates.",
        desc_themes_t: "4 Customizable Luxury Mood Themes",
        desc_themes_p: "Switch instantly between Cyber Neon, Obsidian Cinema, Sports Stadium, and Dark Obsidian themes.",
        engine_badge: "Google Media3 ExoPlayer Architecture",
        engine_title: "Unmatched Performance & Fluidity",
        engine_subtitle: "Engineered specifically to eliminate lag and frame drops on budget Amazon Fire TV Sticks and Android TV boxes.",
        metric_1_t: "Instantaneous Zapping",
        metric_1_p: "Lightning-fast stream switching in a fraction of a second via HTTP 2.0 pre-buffering and optimized Media3 pipeline.",
        metric_2_t: "Pure Hardware Decoding",
        metric_2_p: "Direct pass-through to your Smart TV or NVIDIA Shield GPU. Zero frame drops during high-action 4K 60FPS sports broadcasts.",
        metric_3_t: "Featherlight Zero-Lag APK",
        metric_3_p: "Ultra-compact footprint with zero trackers or annoying ads, perfectly optimized for 8GB FireStick storage constraints.",
        features_badge: "Key Pro Capabilities",
        features_title: "The Television Experience Reimagined",
        features_subtitle: "Every cutting-edge feature needed to turn your living room into an authentic connected 4K home cinema.",
        f1_title: "Multi-Audio Tracks & Subtitles",
        f1_desc: "Easily switch audio languages (English, French, Arabic, Spanish...) and toggle subtitles with a single remote click without stopping playback.",
        f2_title: "Custom Favorites & Channel Reorder",
        f2_desc: "Add your favorite channels with a single tap on the heart button ❤️ and reorder channels directly from the on-screen drawer.",
        f3_title: "Playback Resume & Watch History",
        f3_desc: "Never lose your place: Dreamania automatically remembers your exact playback position across every movie and TV series episode.",
        f4_title: "Secure Parental Controls",
        f4_desc: "Lock adult or sensitive categories behind a private 4-digit PIN code to keep your children and family completely safe.",
        f5_title: "Dynamic Aspect Ratio Modes",
        f5_desc: "Customize image presentation on the fly: Fit (original 16:9), Fill (immersive full screen), or Zoom to eliminate black cinema bars.",
        f6_title: "Instant Cloud Synchronization",
        f6_desc: "Manage your playlists remotely from the web portal. As soon as you add or update an M3U or Xtream account, the app syncs immediately.",
        setup_badge: "2-Minute Fast Setup",
        setup_title: "How to Install Dreamania Player?",
        setup_subtitle: "Three straightforward steps without complicated settings on your Smart TV or FireStick.",
        step_1_t: "Download the Application",
        step_1_p: "Open the free Downloader app on your FireStick or Android TV and enter our official code:",
        step_2_t: "Note Your Device MAC Address",
        step_2_p: "Launch the installed app. Your MAC Address (e.g. 00:1A:79:...) and Device Key will appear directly on screen.",
        step_2_note: "Automatic 7-day free trial active upon your very first launch.",
        step_3_t: "Add Your Playlist",
        step_3_p: "Visit our Device Portal, enter your MAC address, and upload your M3U playlist link or Xtream Codes credentials.",
        step_3_btn: "Open Device Portal →",
        pricing_badge: "Transparent Pricing",
        pricing_title: "Choose Your Dreamania Plan",
        pricing_subtitle: "Instant device activation following payment via Bank Transfer (Morocco CIH) or International PayPal.",
        plan_1y_t: "1-Year VIP License",
        p1_f1: "Guaranteed 1-device activation for 12 months",
        p1_f2: "Google Media3 ExoPlayer 4K 60FPS engine",
        p1_f3: "Full EPG guide, multi-audio & subtitle support",
        p1_f4: "Automatic cloud updates included",
        btn_choose_plan: `Activate 1 Year (${dynamicPricing.c1y_dh} DH / €${dynamicPricing.c1y_eur})`,
        plan_best_badge: "Ultimate Offer",
        plan_life_t: "Lifetime PRO License",
        p2_f1: "Unlimited Lifetime License (No recurring subscriptions)",
        p2_f2: "All Pro capabilities permanently unlocked",
        p2_f3: "Priority access to all 4 interface mood themes",
        p2_f4: "Priority VIP assistance via WhatsApp",
        btn_choose_lifetime: `Activate Lifetime (${dynamicPricing.clife_dh} DH / €${dynamicPricing.clife_eur})`,
        reseller_badge: "Partner & Reseller Program",
        reseller_title: "Are You an IPTV Reseller or Wholesaler?",
        reseller_subtitle: "Take advantage of our dedicated management console with tiered credit packs at wholesale rates to activate customer devices in seconds.",
        btn_access_reseller: "Login to Reseller Portal →",
        faq_badge: "Frequently Asked Questions",
        faq_title: "Everything You Need to Know",
        faq_subtitle: "Find answers to the most common questions regarding Dreamania Player.",
        faq_q1: "Does Dreamania Player include TV channels or content?",
        faq_a1: "No. Dreamania Player is a pure multimedia player. We do not host, broadcast, or supply any video streams, TV channels, or playlists. Users must provide their own legitimate M3U playlist or Xtream Codes login.",
        faq_q2: "How do I install Dreamania on Amazon Fire TV Stick?",
        faq_a2: "On your Fire TV Stick, open the free Downloader app from the Amazon Appstore, enter our Downloader code 7454701, and press Go. The download of DreamaniaPlayer.apk will start immediately. Ensure 'Install Unknown Apps' is enabled in Developer Options.",
        faq_q3: "How do I switch audio language or enable subtitles?",
        faq_a3: "During video playback, press Down or OK on your TV remote to reveal the on-screen display (OSD) controls, then click on 🔊 Audio Track to choose your language or 💬 Subtitles to toggle subtitle tracks.",
        faq_q4: "How is my license activated after payment?",
        faq_a4: "After completing payment via CIH Bank transfer or PayPal, send your receipt along with the MAC Address shown in the app to our WhatsApp support line. Your device will be activated immediately in our database.",
        faq_q5: "Can I transfer my license to a new TV if I replace my device?",
        faq_a5: "Yes! If you purchase a new TV or Fire TV Stick, simply message our support team with your previous MAC address and your new one. Your license will be migrated free of charge without downtime.",
        admin_footer_t: "Master Administration Console",
        admin_footer_p: "Secure environment reserved for Dreamania platform administrators (License management, pricing, resellers, and API controls).",
        btn_admin_login: "Super Admin Login",
        footer_brand_desc: "The benchmark IPTV & VOD media player, powered by Google Media3 ExoPlayer for an uncompromising 4K 60FPS TV experience.",
        footer_col_portals: "Portals & Access",
        f_link_device: "Device Portal (TV Playlists)",
        f_link_reseller: "Reseller Portal",
        f_link_admin: "Super Admin Console",
        f_link_download: "Direct APK Download",
        footer_col_compat: "Compatible Devices",
        footer_col_support: "Support & Legal",
        legal_disclaimer: "Legal Disclaimer: Dreamania Player Pro is a pure, neutral media player application. It does not host, distribute, sell, or provide copyrighted media streams or TV channels. Users bear sole responsibility for the legality of the streams and M3U playlists they import into the application. All mentioned trademark names (Amazon Fire TV, Google TV, NVIDIA Shield, Android TV, Apple TV) are trademarks of their respective owners.",
        mob_device: "Portal",
        mob_reseller: "Reseller",
        modal_payment_t: "Activate Your License",
        modal_confirm_t: "Next Step:",
        modal_confirm_p: "Following payment, send your transfer receipt and MAC Address on WhatsApp for instantaneous activation.",
        btn_confirm_whatsapp: "Confirm on WhatsApp"
      }
    };

    let currentLang = 'fr';

    function setLanguage(lang) {
      if (!translations[lang]) return;
      currentLang = lang;
      localStorage.setItem('dreamania_lang', lang);
      document.documentElement.lang = lang;

      // Update button visual states
      const btnFr = document.getElementById('lang-btn-fr');
      const btnEn = document.getElementById('lang-btn-en');
      if (lang === 'fr') {
        btnFr.className = "px-2 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 bg-neon-cyan/20 text-neon-cyan border border-neon-cyan/40 shadow-sm";
        btnEn.className = "px-2 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 text-slate-400 hover:text-white";
      } else {
        btnEn.className = "px-2 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 bg-neon-cyan/20 text-neon-cyan border border-neon-cyan/40 shadow-sm";
        btnFr.className = "px-2 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 text-slate-400 hover:text-white";
      }

      const mobLabel = document.getElementById('mob-lang-label');
      if (mobLabel) mobLabel.innerText = lang.toUpperCase();

      // Apply translations to all data-i18n elements
      const elements = document.querySelectorAll('[data-i18n]');
      elements.forEach(el => {
        const key = el.getAttribute('data-i18n');
        if (translations[lang][key]) {
          el.innerText = translations[lang][key];
        }
      });
    }

    function toggleLanguage() {
      const next = currentLang === 'fr' ? 'en' : 'fr';
      setLanguage(next);
    }

    // --- Interactive Screenshot Tabs ---
    function switchScreenshotTab(tabName) {
      const allTabs = ['launcher', 'epg', 'player', 'vod', 'series', 'themes'];
      allTabs.forEach(name => {
        const screen = document.getElementById('screen-' + name);
        const btn = document.getElementById('tab-btn-' + name);
        if (screen && btn) {
          if (name === tabName) {
            screen.classList.remove('hidden');
            btn.className = "screenshot-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-bold font-label transition-all bg-neon-cyan/20 text-neon-cyan border border-neon-cyan/50 shadow-[0_0_15px_rgba(0,229,255,0.3)]";
          } else {
            screen.classList.add('hidden');
            btn.className = "screenshot-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-bold font-label transition-all bg-surface-container text-slate-300 hover:text-white border border-surface-glass-stroke";
          }
        }
      });
    }

    // --- FAQ Toggle ---
    function toggleFaq(id) {
      const ans = document.getElementById('faq-ans-' + id);
      const icon = document.getElementById('faq-icon-' + id);
      if (ans && icon) {
        if (ans.classList.contains('hidden')) {
          ans.classList.remove('hidden');
          icon.style.transform = 'rotate(180deg)';
        } else {
          ans.classList.add('hidden');
          icon.style.transform = 'rotate(0deg)';
        }
      }
    }

    // --- Copy Utilities & Toast ---
    function showToast(message) {
      const toast = document.getElementById('toastNotification');
      const msg = document.getElementById('toastMessage');
      if (toast && msg) {
        msg.innerText = message;
        toast.classList.remove('hidden');
        setTimeout(() => {
          toast.classList.add('hidden');
        }, 3200);
      }
    }

    function copyText(elementId, successMsg) {
      const el = document.getElementById(elementId);
      if (el) {
        navigator.clipboard.writeText(el.innerText.trim()).then(() => {
          showToast(successMsg || 'Copié dans le presse-papier !');
        });
      }
    }

    function copyDownloaderCode() {
      const code = "<?php echo htmlspecialchars($downloaderCode); ?>";
      navigator.clipboard.writeText(code).then(() => {
        showToast('Code Downloader ' + code + ' copié !');
      });
    }

    // --- Payment Modal Logic ---
    function openPaymentModal(planId, priceLabel) {
      const modal = document.getElementById('paymentModal');
      const label = document.getElementById('modalPlanLabel');
      const btnWa = document.getElementById('btnWhatsAppSubmit');
      if (modal && label) {
        const title = planId === 'lifetime' ? 'Licence À Vie PRO (' + priceLabel + ')' : 'Licence 1 An VIP (' + priceLabel + ')';
        label.innerText = title;
        if (btnWa) {
          const baseWa = "<?php echo htmlspecialchars($paymentConfig['whatsapp_number'] ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $paymentConfig['whatsapp_number']) : '#'); ?>";
          const text = encodeURIComponent("Bonjour Dreamania, je souhaite activer ma " + title + ".");
          btnWa.href = baseWa + "?text=" + text;
        }
        modal.classList.remove('hidden');
      }
    }

    function closePaymentModal() {
      const modal = document.getElementById('paymentModal');
      if (modal) modal.classList.add('hidden');
    }

    // Initialize Language from localStorage if present
    document.addEventListener('DOMContentLoaded', () => {
      const saved = localStorage.getItem('dreamania_lang');
      if (saved && (saved === 'en' || saved === 'fr')) {
        setLanguage(saved);
      }
    });
  </script>
</body>
</html>
