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
$officialDomain = 'https://dm.lumineasy.com';
$downloadUrl = 'download';
$portalUrl = 'device.php';
?>
<!DOCTYPE html>
<html class="dark" lang="fr">
<head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta content="web_standard" name="shell-type"/>
  <title>Dreamania Player Pro • Middleware IPTV & Cinéma 4K</title>
  <link href="https://fonts.googleapis.com" rel="preconnect"/>
  <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@600;700&family=Outfit:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
  <link rel="icon" type="image/png" href="assets/img/icon.png"/>
  <style>
    @layer base {
      html, body { margin:0; padding:0; }
      body { overscroll-behavior: none; }
      main > :first-child { margin-top: 0 !important; }
      main > :last-child { margin-bottom: 0 !important; }
    }
    ::-webkit-scrollbar { display: none; }
    .portal-glow-yellow:hover {
      box-shadow: 0 0 35px rgba(245, 158, 11, 0.45);
      border-color: rgba(245, 158, 11, 0.8);
    }
    .portal-glow-purple:hover {
      box-shadow: 0 0 35px rgba(139, 92, 246, 0.45);
      border-color: rgba(139, 92, 246, 0.8);
    }
    .portal-glow-cyan:hover {
      box-shadow: 0 0 35px rgba(56, 189, 248, 0.45);
      border-color: rgba(56, 189, 248, 0.8);
    }
    .portal-glow-green:hover {
      box-shadow: 0 0 35px rgba(16, 185, 129, 0.45);
      border-color: rgba(16, 185, 129, 0.8);
    }
  </style>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "surface-glass-stroke": "rgba(255, 255, 255, 0.08)",
            "secondary": "#d2bbff",
            "remote-key-yellow": "#F59E0B",
            "primary-fixed": "#9cf0ff",
            "surface-obsidian-subtle": "#0D0E1A",
            "inverse-primary": "#006875",
            "tertiary-fixed": "#ffddb8",
            "electric-violet-glow": "#8B5CF6",
            "surface-container-high": "#292933",
            "surface-dim": "#13121c",
            "remote-key-red": "#EF4444",
            "surface-container-low": "#1b1b24",
            "on-tertiary": "#472a00",
            "on-secondary-fixed-variant": "#5a00c6",
            "error": "#ffb4ab",
            "tertiary-container": "#ffc681",
            "background": "#13121c",
            "primary-fixed-dim": "#00daf3",
            "secondary-container": "#6001d1",
            "primary-container": "#00e5ff",
            "primary": "#c3f5ff",
            "surface-container": "#1f1f29",
            "surface-container-lowest": "#0d0d17",
            "on-background": "#e4e1ef",
            "surface-container-highest": "#34343e",
            "tertiary-fixed-dim": "#ffb95f",
            "outline": "#849396",
            "surface-glass-fill": "rgba(13, 14, 26, 0.65)",
            "outline-variant": "#3b494c",
            "neon-cyan-bright": "#38BDF8",
            "status-active-green": "#10B981",
            "surface": "#13121c"
          },
          fontFamily: {
            "headline-md": ["Outfit"],
            "body-lg": ["Plus Jakarta Sans"],
            "label-lg": ["Inter"],
            "headline-lg": ["Outfit"],
            "body-md": ["Plus Jakarta Sans"],
            "headline-xl": ["Outfit"],
            "body-sm": ["Plus Jakarta Sans"],
            "label-md": ["Inter"],
            "label-sm": ["Inter"],
            "headline-sm": ["Outfit"]
          }
        }
      }
    };
  </script>
</head>
<body class="bg-background font-body-md text-on-surface antialiased selection:bg-primary-container selection:text-on-primary-container min-h-screen pb-16 md:pb-0">

<!-- ======================================================== -->
<!-- FLOATING CYBER DOCK NAVIGATION (DESKTOP & TABLET)        -->
<!-- ======================================================== -->
<header class="fixed top-2 sm:top-3 left-0 right-0 z-50 px-3 sm:px-6 pointer-events-none">
  <div class="max-w-7xl mx-auto flex items-center justify-between gap-2 sm:gap-4 p-2 sm:p-2.5 rounded-2xl bg-surface-obsidian-subtle/90 backdrop-blur-2xl border border-surface-glass-stroke shadow-[0_12px_40px_rgba(0,0,0,0.65)] pointer-events-auto">
    
    <!-- Brand Logo -->
    <div class="flex items-center gap-2 sm:gap-3 pl-1 sm:pl-2">
      <a class="flex items-center gap-2 group relative" href="landing.php">
        <div class="relative w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-surface-container-highest/60 flex items-center justify-center p-1 border border-neon-cyan-bright/30 shadow-[0_0_20px_rgba(56,189,248,0.4)] group-hover:shadow-[0_0_28px_rgba(0,229,255,0.7)] transition-all">
          <img alt="Dreamania Logo" class="w-full h-full object-contain filter drop-shadow-[0_0_8px_rgba(0,229,255,0.8)]" src="assets/img/icon.png"/>
        </div>
        <div class="flex flex-col">
          <span class="font-headline-sm text-sm sm:text-lg font-extrabold text-white tracking-wider uppercase drop-shadow-[0_0_12px_rgba(0,229,255,0.5)]">DREAMANIA</span>
          <span class="text-[9px] sm:text-[10px] tracking-widest text-neon-cyan-bright font-bold font-label-sm uppercase -mt-1">BROADCAST PRO</span>
        </div>
      </a>
    </div>

    <!-- Centered Nav Links -->
    <nav class="hidden xl:flex items-center gap-1 p-1 rounded-xl bg-surface-container-lowest/80 border border-surface-glass-stroke">
      <a class="px-3 py-1.5 rounded-lg text-on-surface-variant font-label-md text-xs hover:text-white hover:bg-surface-container/60 transition-all" href="#features" data-i18n="nav_features">Fonctionnalités</a>
      <a class="px-3 py-1.5 rounded-lg text-on-surface-variant font-label-md text-xs hover:text-white hover:bg-surface-container/60 transition-all" href="#screenshots" data-i18n="nav_app_tour">Visite Guidée</a>
      <a class="px-3 py-1.5 rounded-lg text-on-surface-variant font-label-md text-xs hover:text-white hover:bg-surface-container/60 transition-all" href="#broadcast-hub" data-i18n="nav_engine">Moteur Media3</a>
      <a class="px-3 py-1.5 rounded-lg text-on-surface-variant font-label-md text-xs hover:text-white hover:bg-surface-container/60 transition-all" href="#pricing" data-i18n="nav_plans">Tarifs</a>
      <a class="px-3 py-1.5 rounded-lg text-on-surface-variant font-label-md text-xs hover:text-white hover:bg-surface-container/60 transition-all" href="#faq" data-i18n="nav_faq">FAQ</a>
    </nav>

    <!-- High-Visibility Portal Actions & Language Toggle -->
    <div class="flex items-center gap-1.5 sm:gap-2 pr-1">
      
      <!-- Bilingual Switcher (FR / EN) -->
      <div class="flex items-center p-0.5 rounded-xl bg-surface-container-highest/80 border border-surface-glass-stroke shrink-0">
        <button type="button" onclick="setLanguage('fr')" id="lang-btn-fr" class="px-2 py-1 rounded-lg text-[11px] font-bold transition-all flex items-center gap-1 bg-neon-cyan-bright/20 text-neon-cyan-bright border border-neon-cyan-bright/40 shadow-sm">
          <span>🇫🇷</span> <span class="hidden sm:inline">FR</span>
        </button>
        <button type="button" onclick="setLanguage('en')" id="lang-btn-en" class="px-2 py-1 rounded-lg text-[11px] font-bold transition-all flex items-center gap-1 text-on-surface-variant hover:text-white">
          <span>🇬🇧</span> <span class="hidden sm:inline">EN</span>
        </button>
      </div>

      <!-- Mon Appareil (Device Portal Direct Access) -->
      <a href="device.php" class="flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-gradient-to-r from-remote-key-yellow to-tertiary-fixed-dim text-surface-container-lowest font-label-md text-xs sm:text-label-md font-bold shadow-[0_0_15px_rgba(245,158,11,0.4)] hover:brightness-110 active:scale-95 transition-all" title="Téléverser playlists & activer votre téléviseur">
        <span class="material-symbols-outlined text-[16px] sm:text-[18px]">tv</span>
        <span class="hidden sm:inline" data-i18n="nav_device">Mon Appareil</span>
        <span class="sm:hidden" data-i18n="nav_device_short">Appareil</span>
      </a>

      <!-- Espace Revendeur (Reseller Portal Direct Access) -->
      <a href="index.php?panel=1" class="flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-surface-container-high hover:bg-surface-container-highest text-secondary border border-electric-violet-glow/40 font-label-md text-xs font-bold shadow-[0_0_12px_rgba(139,92,246,0.3)] hover:brightness-110 active:scale-95 transition-all" title="Connexion Revendeurs & Grossistes">
        <span class="material-symbols-outlined text-[16px]">storefront</span>
        <span class="hidden md:inline" data-i18n="nav_reseller_portal">Revendeur</span>
      </a>

      <!-- Panel Admin (Admin Portal Direct Access) -->
      <a href="index.php?admin=1" class="hidden sm:flex items-center gap-1 px-2.5 py-1.5 sm:py-2 rounded-xl bg-surface-container-high hover:bg-surface-container-highest text-primary border border-neon-cyan-bright/30 font-label-md text-xs font-bold hover:brightness-110 active:scale-95 transition-all" title="Panneau d'administration global">
        <span class="material-symbols-outlined text-[16px]">shield_person</span>
        <span class="hidden lg:inline" data-i18n="nav_admin">Admin</span>
      </a>

      <!-- Download Direct APK & Code 7454701 -->
      <a href="download" class="flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-gradient-to-r from-secondary-container via-electric-violet-glow to-primary-container text-on-primary-container font-label-md text-xs font-bold shadow-[0_0_18px_rgba(139,92,246,0.45)] hover:shadow-[0_0_24px_rgba(0,229,255,0.6)] active:scale-95 transition-all" title="Télécharger l'APK directement">
        <span class="material-symbols-outlined text-[16px]">download</span>
        <span class="hidden md:inline">APK (<?= htmlspecialchars($downloaderCode) ?>)</span>
        <span class="md:hidden">APK</span>
      </a>

    </div>
  </div>
</header>

<main class="w-full pt-24 sm:pt-28 bg-background">
<div class="flex flex-col w-full text-on-surface">

  <!-- ======================================================== -->
  <!-- HERO SECTION WITH OFFICIAL ASSETS & BROADCAST HUB        -->
  <!-- ======================================================== -->
  <section class="relative w-full overflow-hidden pb-12 sm:pb-16">
    <!-- Ambient Glow Elements -->
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_60%_at_50%_-15%,rgba(139,92,246,0.32),rgba(19,18,28,0))] pointer-events-none"></div>
    <div class="absolute top-1/4 right-5 w-96 h-96 rounded-full bg-neon-cyan-bright/15 blur-[130px] pointer-events-none"></div>
    <div class="absolute top-1/3 left-5 w-80 h-80 rounded-full bg-electric-violet-glow/20 blur-[130px] pointer-events-none"></div>
    
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 sm:pt-8 flex flex-col items-center text-center">
      
      <!-- Official Android TV Release Badge -->
      <div class="inline-flex items-center gap-2 px-3.5 sm:px-4 py-1.5 rounded-full bg-surface-obsidian-subtle/95 border border-primary-container/30 shadow-[0_0_25px_rgba(56,189,248,0.35)] mb-5">
        <img alt="Dreamania Animated Symbol" class="w-4 h-4 object-contain animate-spin" src="assets/img/icon.png" style="animation-duration: 8s;"/>
        <span class="w-2 h-2 rounded-full bg-status-active-green animate-ping"></span>
        <span class="font-label-sm text-[11px] sm:text-xs uppercase tracking-widest text-primary-fixed font-bold" data-i18n="hero_badge">
          MOTEUR BROADCAST OFFICIEL • CERTIFIÉ ANDROID TV & FIRE TV
        </span>
      </div>

      <!-- Main Headline with Cyber Neon Focus -->
      <h1 class="font-headline-xl text-3xl sm:text-5xl md:text-[60px] md:leading-[1.1] max-w-5xl tracking-tight text-white font-extrabold mb-5">
        <span data-i18n="hero_title_1">Le Lecteur IPTV & Cinéma</span>
        <span class="bg-gradient-to-r from-neon-cyan-bright via-primary to-electric-violet-glow bg-clip-text text-transparent drop-shadow-[0_0_35px_rgba(0,229,255,0.5)]" data-i18n="hero_title_grad">
          Nouvelle Génération
        </span>
        <span data-i18n="hero_title_2">Taillé pour Votre Smart TV</span>
      </h1>

      <!-- Subtitle -->
      <p class="font-body-lg text-sm sm:text-lg text-on-surface-variant max-w-3xl leading-relaxed mb-7" data-i18n="hero_desc">
        Conçu pour un zapping instantané, le streaming sportif 4K 60FPS et la VOD cinéma enrichie TMDb. Propulsé par Google Media3 ExoPlayer avec zéro superflu et une navigation fluide dédiée à la télécommande TV.
      </p>

      <!-- Primary Action Buttons Row -->
      <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 mb-8">
        <a class="px-5 sm:px-6 py-3 rounded-xl bg-gradient-to-r from-secondary-container via-electric-violet-glow to-primary-container text-on-primary-container font-label-lg text-xs sm:text-sm font-bold shadow-[0_0_25px_rgba(139,92,246,0.55)] hover:shadow-[0_0_35px_rgba(0,229,255,0.7)] hover:scale-105 active:scale-95 transition-all flex items-center gap-2" href="download">
          <span class="material-symbols-outlined text-[20px]">download</span>
          <span data-i18n="hero_btn_download">Télécharger APK (v1.0.0 Pro)</span>
        </a>
        <a class="px-5 sm:px-6 py-3 rounded-xl bg-gradient-to-r from-remote-key-yellow to-tertiary-fixed-dim text-surface-container-lowest font-label-lg text-xs sm:text-sm font-bold shadow-[0_0_20px_rgba(245,158,11,0.45)] hover:brightness-110 hover:scale-105 active:scale-95 transition-all flex items-center gap-2" href="device.php">
          <span class="material-symbols-outlined text-[20px]">tv</span>
          <span data-i18n="hero_btn_manage">Gérer TV / Téléverser Playlist</span>
        </a>
        <a class="px-4 sm:px-5 py-3 rounded-xl bg-surface-container-high/80 hover:bg-surface-container-highest text-primary font-label-lg text-xs sm:text-sm border border-surface-glass-stroke shadow-md hover:shadow-[0_0_20px_rgba(195,245,255,0.25)] transition-all flex items-center gap-2" href="#pricing">
          <span class="material-symbols-outlined text-[18px] text-neon-cyan-bright">credit_card</span>
          <span data-i18n="hero_btn_pricing">Tarifs (50 DH / 5€ – 150 DH / 15€)</span>
        </a>
      </div>

      <!-- ======================================================== -->
      <!-- ULTRA-PROMINENT QUICK ACCESS PORTALS HUB (4 CARDS)       -->
      <!-- ======================================================== -->
      <div class="w-full max-w-6xl mt-4 mb-12">
        <div class="p-4 sm:p-6 rounded-3xl bg-surface-container-lowest/80 backdrop-blur-xl border border-surface-glass-stroke shadow-2xl">
          
          <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pb-5 mb-5 border-b border-surface-glass-stroke text-left">
            <div>
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-status-active-green shadow-[0_0_10px_#10B981]"></span>
                <span class="font-headline-sm text-base sm:text-lg font-bold text-white" data-i18n="portal_hub_title">
                  Portails d'Accès & Espaces Dédiés
                </span>
              </div>
              <p class="font-body-sm text-xs text-on-surface-variant mt-0.5" data-i18n="portal_hub_desc">
                Choisissez directement votre espace : Particuliers, Revendeurs grossistes ou Administration.
              </p>
            </div>
            <div class="flex items-center gap-2">
              <span class="px-3 py-1 rounded-full bg-surface-container-high text-neon-cyan-bright font-mono text-xs font-bold border border-neon-cyan-bright/30">
                Code Downloader : <?= htmlspecialchars($downloaderCode) ?>
              </span>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-left">
            
            <!-- PORTAL 1: Mon Appareil (Device Portal) -->
            <div class="group relative p-5 rounded-2xl bg-gradient-to-b from-surface-container-low to-surface-container-lowest border border-remote-key-yellow/40 portal-glow-yellow transition-all duration-300 flex flex-col justify-between">
              <div>
                <div class="flex items-center justify-between mb-3">
                  <div class="w-10 h-10 rounded-xl bg-remote-key-yellow/15 border border-remote-key-yellow/30 flex items-center justify-center text-remote-key-yellow">
                    <span class="material-symbols-outlined text-[24px]">tv</span>
                  </div>
                  <span class="px-2 py-0.5 rounded-full bg-remote-key-yellow/20 text-remote-key-yellow font-label-sm text-[10px] font-bold uppercase tracking-wider" data-i18n="card_device_badge">
                    Clients Particuliers
                  </span>
                </div>
                <h3 class="font-headline-sm text-base font-bold text-white mb-1.5" data-i18n="card_device_title">
                  Mon Appareil & Playlists
                </h3>
                <p class="font-body-sm text-xs text-on-surface-variant leading-relaxed mb-4" data-i18n="card_device_desc">
                  Entrez votre adresse MAC et Device Key pour téléverser vos listes M3U ou Xtream et activer votre abonnement TV.
                </p>
              </div>
              <a href="device.php" class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-remote-key-yellow to-tertiary-fixed-dim text-surface-container-lowest font-label-md text-xs font-bold text-center flex items-center justify-center gap-1.5 shadow-[0_0_15px_rgba(245,158,11,0.3)] hover:brightness-110 active:scale-95 transition-all">
                <span data-i18n="card_device_btn">Accéder à Mon Appareil</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
              </a>
            </div>

            <!-- PORTAL 2: Espace Revendeur -->
            <div class="group relative p-5 rounded-2xl bg-gradient-to-b from-surface-container-low to-surface-container-lowest border border-electric-violet-glow/40 portal-glow-purple transition-all duration-300 flex flex-col justify-between">
              <div>
                <div class="flex items-center justify-between mb-3">
                  <div class="w-10 h-10 rounded-xl bg-electric-violet-glow/15 border border-electric-violet-glow/30 flex items-center justify-center text-secondary">
                    <span class="material-symbols-outlined text-[24px]">storefront</span>
                  </div>
                  <span class="px-2 py-0.5 rounded-full bg-electric-violet-glow/20 text-secondary font-label-sm text-[10px] font-bold uppercase tracking-wider" data-i18n="card_reseller_badge">
                    Revendeurs & B2B
                  </span>
                </div>
                <h3 class="font-headline-sm text-base font-bold text-white mb-1.5" data-i18n="card_reseller_title">
                  Espace Revendeur
                </h3>
                <p class="font-body-sm text-xs text-on-surface-variant leading-relaxed mb-4" data-i18n="card_reseller_desc">
                  Connectez-vous à votre panneau revendeur pour gérer vos crédits (1 crédit = 1 an) et activer vos clients instantanément.
                </p>
              </div>
              <a href="index.php?panel=1" class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-secondary-container via-electric-violet-glow to-primary-container text-on-primary-container font-label-md text-xs font-bold text-center flex items-center justify-center gap-1.5 shadow-[0_0_15px_rgba(139,92,246,0.3)] hover:brightness-110 active:scale-95 transition-all">
                <span data-i18n="card_reseller_btn">Connexion Revendeur</span>
                <span class="material-symbols-outlined text-[16px]">login</span>
              </a>
            </div>

            <!-- PORTAL 3: Panel Admin -->
            <div class="group relative p-5 rounded-2xl bg-gradient-to-b from-surface-container-low to-surface-container-lowest border border-neon-cyan-bright/40 portal-glow-cyan transition-all duration-300 flex flex-col justify-between">
              <div>
                <div class="flex items-center justify-between mb-3">
                  <div class="w-10 h-10 rounded-xl bg-neon-cyan-bright/15 border border-neon-cyan-bright/30 flex items-center justify-center text-neon-cyan-bright">
                    <span class="material-symbols-outlined text-[24px]">shield_person</span>
                  </div>
                  <span class="px-2 py-0.5 rounded-full bg-neon-cyan-bright/20 text-neon-cyan-bright font-label-sm text-[10px] font-bold uppercase tracking-wider" data-i18n="card_admin_badge">
                    Supervision Master
                  </span>
                </div>
                <h3 class="font-headline-sm text-base font-bold text-white mb-1.5" data-i18n="card_admin_title">
                  Panel Administrateur
                </h3>
                <p class="font-body-sm text-xs text-on-surface-variant leading-relaxed mb-4" data-i18n="card_admin_desc">
                  Gestion globale du serveur, routes DNS, validation des paiements RIB/PayPal et paramétrage des tarifs.
                </p>
              </div>
              <a href="index.php?admin=1" class="w-full py-2.5 px-3 rounded-xl bg-surface-container-highest hover:bg-surface-bright text-primary border border-neon-cyan-bright/40 font-label-md text-xs font-bold text-center flex items-center justify-center gap-1.5 shadow-[0_0_15px_rgba(56,189,248,0.25)] hover:brightness-110 active:scale-95 transition-all">
                <span data-i18n="card_admin_btn">Accès Admin Master</span>
                <span class="material-symbols-outlined text-[16px]">lock_open</span>
              </a>
            </div>

            <!-- PORTAL 4: Code Downloader -->
            <div class="group relative p-5 rounded-2xl bg-gradient-to-b from-surface-container-low to-surface-container-lowest border border-status-active-green/40 portal-glow-green transition-all duration-300 flex flex-col justify-between">
              <div>
                <div class="flex items-center justify-between mb-3">
                  <div class="w-10 h-10 rounded-xl bg-status-active-green/15 border border-status-active-green/30 flex items-center justify-center text-status-active-green">
                    <span class="material-symbols-outlined text-[24px]">bolt</span>
                  </div>
                  <span class="px-2 py-0.5 rounded-full bg-status-active-green/20 text-status-active-green font-label-sm text-[10px] font-bold uppercase tracking-wider" data-i18n="card_tv_badge">
                    Fire TV & Android Box
                  </span>
                </div>
                <h3 class="font-headline-sm text-base font-bold text-white mb-1.5">
                  <span data-i18n="card_tv_title">Code Downloader : </span><span class="text-status-active-green font-mono"><?= htmlspecialchars($downloaderCode) ?></span>
                </h3>
                <p class="font-body-sm text-xs text-on-surface-variant leading-relaxed mb-4" data-i18n="card_tv_desc">
                  Tapez simplement ce code dans l'application Downloader sur votre Fire TV Stick ou Box TV pour installer l'application.
                </p>
              </div>
              <div class="flex items-center gap-2">
                <button onclick="copyToClipboard('<?= htmlspecialchars($downloaderCode) ?>', 'Code Downloader copié !')" class="px-3 py-2.5 rounded-xl bg-surface-container-highest border border-status-active-green/30 text-status-active-green font-bold text-xs hover:brightness-125 transition-all flex items-center gap-1" title="Copier le code">
                  <span class="material-symbols-outlined text-[15px]">content_copy</span>
                  <span><?= htmlspecialchars($downloaderCode) ?></span>
                </button>
                <a href="download" class="flex-1 py-2.5 px-3 rounded-xl bg-status-active-green text-surface-container-lowest font-label-md text-xs font-bold text-center flex items-center justify-center gap-1 shadow-[0_0_15px_rgba(16,185,129,0.3)] hover:brightness-110 active:scale-95 transition-all">
                  <span data-i18n="card_tv_btn">Télécharger APK</span>
                  <span class="material-symbols-outlined text-[15px]">download</span>
                </a>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Tech Specs Monospace Strip -->
      <div class="w-full max-w-5xl p-3 sm:p-4 rounded-2xl bg-surface-container-lowest/85 backdrop-blur-md border border-surface-glass-stroke shadow-xl flex flex-wrap items-center justify-center gap-x-6 gap-y-3 font-label-md text-xs sm:text-sm text-on-surface-variant mb-12">
        <div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-remote-key-yellow text-[16px]">bolt</span><span data-i18n="spec_version">Version: <strong class="text-primary font-semibold">v1.0.0 Pro</strong></span></div>
        <span class="text-outline-variant hidden sm:inline">•</span>
        <div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-secondary text-[16px]">speed</span><span data-i18n="spec_zapping">Zapping: <strong class="text-neon-cyan-bright font-semibold">0.24s Instantané</strong></span></div>
        <span class="text-outline-variant hidden sm:inline">•</span>
        <div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-neon-cyan-bright text-[16px]">tv</span><span data-i18n="spec_compat">Compatibilité: <strong class="text-on-surface font-semibold">Fire TV, Android TV, Google TV</strong></span></div>
        <span class="text-outline-variant hidden sm:inline">•</span>
        <div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-status-active-green text-[16px]">security</span><span data-i18n="spec_no_ads">Sans Publicité • Middleware Propre</span></div>
        <span class="text-outline-variant hidden sm:inline">•</span>
        <div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-remote-key-red text-[16px]">favorite</span><span data-i18n="spec_fav">Favoris ❤️ Télécommande 1 Touche</span></div>
      </div>

      <!-- High-End Bezel TV Mockup with Real ExoPlayer & Genuine Metadata -->
      <div class="w-full max-w-6xl relative group">
        <!-- Ambient Neon Halo -->
        <div class="absolute -inset-4 bg-gradient-to-r from-electric-violet-glow/45 via-neon-cyan-bright/35 to-remote-key-yellow/30 rounded-3xl blur-2xl opacity-80 group-hover:opacity-100 transition-opacity duration-700"></div>
        
        <!-- TV Outer Shell Bezel -->
        <div class="relative rounded-2xl bg-[#090812] border-2 border-surface-container-highest/80 p-3 sm:p-5 shadow-2xl overflow-hidden">
          
          <!-- TV Header Bar (App Launcher UI) -->
          <div class="flex items-center justify-between pb-3 sm:pb-4 border-b border-surface-container-highest/60 font-label-md text-xs sm:text-sm">
            <div class="flex items-center gap-3">
              <img alt="Dreamania Pro Banner" class="h-6 w-auto object-contain hidden sm:block rounded" src="assets/img/banner.png"/>
              <span class="w-2.5 h-2.5 rounded-full bg-status-active-green shadow-[0_0_8px_#10B981]"></span>
              <span class="font-headline-sm text-sm sm:text-base text-primary font-bold tracking-wider">DREAMANIA 4K PRO</span>
              <span class="px-2 py-0.5 rounded bg-surface-container-high text-neon-cyan-bright font-label-sm text-[10px] border border-neon-cyan-bright/30">MEDIA3 ENGINE</span>
            </div>
            <div class="flex items-center gap-3 font-label-sm text-xs text-on-surface-variant">
              <span class="flex items-center gap-1 text-remote-key-yellow font-bold"><span class="material-symbols-outlined text-[16px]">verified</span> VIP LIFETIME</span>
              <span class="text-on-surface font-mono">21:42:09</span>
            </div>
          </div>

          <!-- TV Screen Frame with Live Match & Player HUD -->
          <div class="relative w-full aspect-[16/9] rounded-xl overflow-hidden bg-surface-dim">
            <img class="w-full h-full object-cover" alt="Dreamania Player Screen Dashboard" src="assets/img/screenshots/screen_hero_dashboard.png"/>
            <!-- HUD Frosted Scrim Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-surface-container-lowest via-surface-container-lowest/60 to-transparent flex flex-col justify-between p-3 sm:p-6">
              
              <!-- Top Codec & Buffer Chips -->
              <div class="flex items-center justify-between">
                <div class="flex flex-wrap items-center gap-2">
                  <span class="px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-md bg-surface-obsidian-subtle/95 border border-neon-cyan-bright/40 text-neon-cyan-bright font-label-sm text-[11px] backdrop-blur-md shadow-md">4K UHD 60FPS HDR10</span>
                  <span class="px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-md bg-surface-obsidian-subtle/95 border border-electric-violet-glow/40 text-secondary font-label-sm text-[11px] backdrop-blur-md shadow-md">DOLBY ATMOS 5.1</span>
                  <span class="px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-md bg-surface-obsidian-subtle/95 border border-status-active-green/40 text-status-active-green font-label-sm text-[11px] backdrop-blur-md shadow-md">BUFFER: 9.8s STABLE</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="px-2.5 py-0.5 sm:px-3 sm:py-1 rounded bg-remote-key-red/90 text-white font-label-sm text-[11px] uppercase font-bold flex items-center gap-1.5 shadow-[0_0_12px_rgba(239,68,68,0.6)]">
                    <span class="w-2 h-2 rounded-full bg-white animate-ping"></span> DIRECT
                  </span>
                </div>
              </div>

              <!-- Lower EPG Stream Info Drawer with Remote Hardware Color Action Mapping -->
              <div class="p-3 sm:p-5 rounded-xl bg-surface-container-lowest/90 backdrop-blur-xl border border-surface-glass-stroke shadow-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                <div class="flex items-start gap-3">
                  <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-secondary-container to-surface-container-high border border-neon-cyan-bright/40 flex items-center justify-center shrink-0 shadow-lg">
                    <img alt="Power Core" class="w-6 h-6 sm:w-8 sm:h-8 object-contain" src="assets/img/icon.png"/>
                  </div>
                  <div>
                    <div class="flex items-center gap-2 mb-1">
                      <span class="font-headline-sm text-sm sm:text-base text-on-surface font-bold">beIN SPORTS 1 UHD (4K HDR)</span>
                      <span class="material-symbols-outlined text-remote-key-red text-[20px]" style="font-variation-settings: 'FILL' 1;">favorite</span>
                    </div>
                    <p class="font-body-sm text-xs text-on-surface-variant">UEFA Champions League • Live • Audio Track 1 (Français / AC3)</p>
                    <div class="w-full sm:w-80 h-1.5 bg-surface-container-highest rounded-full mt-2 overflow-hidden">
                      <div class="h-full bg-gradient-to-r from-neon-cyan-bright via-primary-container to-electric-violet-glow w-[88%] rounded-full shadow-[0_0_10px_#00e5ff]"></div>
                    </div>
                  </div>
                </div>
                <!-- Remote Color Hardware Hotkeys -->
                <div class="flex items-center gap-2 shrink-0 flex-wrap">
                  <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-surface-container border border-surface-glass-stroke">
                    <span class="w-2.5 h-2.5 rounded-full bg-remote-key-red shadow-[0_0_8px_#EF4444]"></span>
                    <span class="font-label-sm text-[11px] text-on-surface font-semibold" data-i18n="remote_red">Rouge: Favori (❤️)</span>
                  </div>
                  <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-surface-container border border-surface-glass-stroke">
                    <span class="w-2.5 h-2.5 rounded-full bg-remote-key-yellow shadow-[0_0_8px_#F59E0B]"></span>
                    <span class="font-label-sm text-[11px] text-on-surface font-semibold" data-i18n="remote_yellow">Jaune: Réordonner</span>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ======================================================== -->
  <!-- CYBER NEON BROADCAST HUB & BENCHMARKS                     -->
  <!-- ======================================================== -->
  <section class="w-full py-12 sm:py-16 bg-surface-container-lowest/80 border-y border-surface-glass-stroke relative" id="broadcast-hub">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col items-center text-center mb-12">
        <span class="font-label-md text-xs sm:text-sm uppercase tracking-widest text-neon-cyan-bright mb-2 font-bold" data-i18n="bench_sub">
          MOTEUR BROADCAST HAUTE PERFORMANCE
        </span>
        <h2 class="font-headline-lg text-2xl sm:text-3xl text-on-surface font-bold tracking-tight mb-3" data-i18n="bench_title">
          Infrastructure Zéro Latence pour le Sport & Cinéma
        </h2>
        <p class="font-body-lg text-sm sm:text-base text-on-surface-variant max-w-3xl" data-i18n="bench_desc">
          Les tests comparatifs avec les lecteurs IPTV classiques prouvent que l'architecture Media3 de Dreamania Player zappe jusqu'à 4x plus vite sans saut d'image.
        </p>
      </div>

      <!-- Broadcast Speed Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5 sm:gap-6 mb-10">
        <!-- Benchmark 1 -->
        <div class="p-5 sm:p-6 rounded-2xl bg-surface-container-low/80 border border-surface-glass-stroke shadow-xl flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-3">
              <span class="font-label-sm uppercase tracking-wider text-neon-cyan-bright font-bold text-xs" data-i18n="bench1_title">Vitesse de Zapping</span>
              <span class="px-2 py-0.5 rounded bg-status-active-green/20 text-status-active-green font-label-sm text-xs font-bold" data-i18n="bench1_speed">4.2x PLUS RAPIDE</span>
            </div>
            <div class="text-3xl sm:text-4xl font-headline-xl text-primary font-extrabold mb-1">0.24s</div>
            <p class="font-body-sm text-xs sm:text-sm text-on-surface-variant" data-i18n="bench1_desc">
              Le pré-tamponnage TS/HLS permet de zapper instantanément d'une chaîne à l'autre sans écran noir.
            </p>
          </div>
          <div class="w-full bg-surface-container-highest h-2 rounded-full mt-5 overflow-hidden">
            <div class="bg-gradient-to-r from-neon-cyan-bright to-primary w-[94%] h-full rounded-full"></div>
          </div>
        </div>

        <!-- Benchmark 2 -->
        <div class="p-5 sm:p-6 rounded-2xl bg-surface-container-low/80 border border-surface-glass-stroke shadow-xl flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-3">
              <span class="font-label-sm uppercase tracking-wider text-secondary font-bold text-xs" data-i18n="bench2_title">Indexation des Listes</span>
              <span class="px-2 py-0.5 rounded bg-secondary-container/30 text-secondary font-label-sm text-xs font-bold" data-i18n="bench2_badge">50K+ CHAÎNES</span>
            </div>
            <div class="text-3xl sm:text-4xl font-headline-xl text-secondary font-extrabold mb-1">1.18s</div>
            <p class="font-body-sm text-xs sm:text-sm text-on-surface-variant" data-i18n="bench2_desc">
              Indexation JSON ultra-rapide pour les playlists Xtream volumineuses et fichiers M3U de plusieurs mégaoctets.
            </p>
          </div>
          <div class="w-full bg-surface-container-highest h-2 rounded-full mt-5 overflow-hidden">
            <div class="bg-gradient-to-r from-secondary to-electric-violet-glow w-[91%] h-full rounded-full"></div>
          </div>
        </div>

        <!-- Benchmark 3 -->
        <div class="p-5 sm:p-6 rounded-2xl bg-surface-container-low/80 border border-surface-glass-stroke shadow-xl flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-3">
              <span class="font-label-sm uppercase tracking-wider text-remote-key-yellow font-bold text-xs" data-i18n="bench3_title">Empreinte Mémoire</span>
              <span class="px-2 py-0.5 rounded bg-remote-key-yellow/20 text-remote-key-yellow font-label-sm text-xs font-bold" data-i18n="bench3_badge">ZÉRO SURCHARGE</span>
            </div>
            <div class="text-3xl sm:text-4xl font-headline-xl text-remote-key-yellow font-extrabold mb-1">14.5 MB</div>
            <p class="font-body-sm text-xs sm:text-sm text-on-surface-variant" data-i18n="bench3_desc">
              Taille d'APK ultra-légère optimisée pour les Fire TV Sticks d'entrée de gamme à mémoire restreinte.
            </p>
          </div>
          <div class="w-full bg-surface-container-highest h-2 rounded-full mt-5 overflow-hidden">
            <div class="bg-gradient-to-r from-remote-key-yellow to-tertiary-fixed-dim w-[85%] h-full rounded-full"></div>
          </div>
        </div>
      </div>

      <!-- Leanback Experience Banner -->
      <div class="p-5 sm:p-7 rounded-3xl bg-gradient-to-r from-surface-container-low via-surface-obsidian-subtle to-surface-container-low border border-surface-glass-stroke flex flex-col md:flex-row items-center justify-between gap-5 shadow-2xl">
        <div class="flex items-center gap-4">
          <img alt="Android TV Banner" class="w-32 sm:w-40 rounded-xl border border-neon-cyan-bright/30 shadow-[0_0_20px_rgba(56,189,248,0.3)] object-contain" src="assets/img/banner.png"/>
          <div>
            <h3 class="font-headline-sm text-base sm:text-lg text-white font-bold" data-i18n="leanback_title">Expérience Leanback Certifiée</h3>
            <p class="font-body-sm text-xs sm:text-sm text-on-surface-variant max-w-lg mt-0.5" data-i18n="leanback_desc">
              Conçu et testé rigoureusement pour les interfaces TV sous Android 5.0 jusqu'à Android 14+.
            </p>
          </div>
        </div>
        <a class="px-4 py-2 rounded-xl bg-surface-container-high hover:bg-surface-container-highest text-primary font-label-md text-xs sm:text-sm border border-neon-cyan-bright/30 transition-all flex items-center gap-2 shrink-0 shadow-md" href="download">
          <span class="material-symbols-outlined text-[18px]">verified</span>
          <span data-i18n="leanback_btn">Télécharger l'APK Leanback</span>
        </a>
      </div>

    </div>
  </section>

  <!-- ======================================================== -->
  <!-- FEATURES SECTION: ENGINEERED FOR POWER                   -->
  <!-- ======================================================== -->
  <section class="w-full py-12 sm:py-16 bg-surface-container-lowest/60 relative" id="features">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col items-center text-center mb-12">
        <span class="font-label-md text-xs sm:text-sm uppercase tracking-widest text-neon-cyan-bright mb-2" data-i18n="feat_sub">
          CONÇU POUR LA PERFORMANCE
        </span>
        <h2 class="font-headline-lg text-2xl sm:text-3xl text-on-surface font-bold tracking-tight mb-3" data-i18n="feat_title">
          Développé pour la Perfection
        </h2>
        <p class="font-body-lg text-sm sm:text-base text-on-surface-variant max-w-2xl" data-i18n="feat_desc">
          Découvrez un middleware IPTV et un lecteur multimédia Android sans compromis sur la vitesse ou le design.
        </p>
      </div>

      <!-- 6 Futuristic Glassmorphic Feature Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
        
        <!-- Feature 1: Favorites & Reorder -->
        <div class="p-6 rounded-2xl bg-surface-container-low/70 hover:bg-surface-container/80 backdrop-blur-md transition-all duration-300 hover:scale-[1.02] shadow-xl flex flex-col justify-between border border-surface-glass-stroke">
          <div>
            <div class="w-12 h-12 rounded-xl bg-remote-key-red/20 border border-remote-key-red/30 flex items-center justify-center text-remote-key-red mb-4">
              <span class="material-symbols-outlined text-[24px]">favorite</span>
            </div>
            <h3 class="font-headline-sm text-lg font-bold text-white mb-2" data-i18n="feat1_title">Favoris & Réorganisation</h3>
            <p class="font-body-sm text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="feat1_desc">
              Épinglez n'importe quelle chaîne dans vos Favoris d'une seule touche (Bouton Rouge ❤️). Réorganisez les chaînes à votre guise avec le Bouton Jaune de la télécommande.
            </p>
          </div>
          <div class="mt-4 pt-3 border-t border-surface-glass-stroke text-remote-key-red text-xs font-bold flex items-center gap-1.5">
            <span>❤️ Raccourcis Télécommande Dédiés</span>
          </div>
        </div>

        <!-- Feature 2: Media3 ExoPlayer Engine -->
        <div class="p-6 rounded-2xl bg-surface-container-low/70 hover:bg-surface-container/80 backdrop-blur-md transition-all duration-300 hover:scale-[1.02] shadow-xl flex flex-col justify-between border border-surface-glass-stroke">
          <div>
            <div class="w-12 h-12 rounded-xl bg-neon-cyan-bright/20 border border-neon-cyan-bright/30 flex items-center justify-center text-neon-cyan-bright mb-4">
              <span class="material-symbols-outlined text-[24px]">play_circle</span>
            </div>
            <h3 class="font-headline-sm text-lg font-bold text-white mb-2" data-i18n="feat2_title">Moteur Google Media3</h3>
            <p class="font-body-sm text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="feat2_desc">
              Décodage matériel natif pour les flux H.265/HEVC, 4K UHD, HDR10 et Dolby Vision avec sélection multi-pistes audio et sous-titres à la volée.
            </p>
          </div>
          <div class="mt-4 pt-3 border-t border-surface-glass-stroke text-neon-cyan-bright text-xs font-bold flex items-center gap-1.5">
            <span>⚡ 4K 60FPS • Zéro Décalage</span>
          </div>
        </div>

        <!-- Feature 3: Xtream & M3U Universal -->
        <div class="p-6 rounded-2xl bg-surface-container-low/70 hover:bg-surface-container/80 backdrop-blur-md transition-all duration-300 hover:scale-[1.02] shadow-xl flex flex-col justify-between border border-surface-glass-stroke">
          <div>
            <div class="w-12 h-12 rounded-xl bg-secondary/20 border border-secondary/30 flex items-center justify-center text-secondary mb-4">
              <span class="material-symbols-outlined text-[24px]">queue_music</span>
            </div>
            <h3 class="font-headline-sm text-lg font-bold text-white mb-2" data-i18n="feat3_title">Xtream Codes & M3U</h3>
            <p class="font-body-sm text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="feat3_desc">
              Prise en charge intégrale des identifiants Xtream Codes API et des URL M3U/M3U8 avec gestion de plusieurs comptes simultanés sans réinitialisation.
            </p>
          </div>
          <div class="mt-4 pt-3 border-t border-surface-glass-stroke text-secondary text-xs font-bold flex items-center gap-1.5">
            <span>📁 Multi-Playlists & Contrôle Parental</span>
          </div>
        </div>

        <!-- Feature 4: EPG & TMDb Enrichment -->
        <div class="p-6 rounded-2xl bg-surface-container-low/70 hover:bg-surface-container/80 backdrop-blur-md transition-all duration-300 hover:scale-[1.02] shadow-xl flex flex-col justify-between border border-surface-glass-stroke">
          <div>
            <div class="w-12 h-12 rounded-xl bg-tertiary-fixed-dim/20 border border-tertiary-fixed-dim/30 flex items-center justify-center text-remote-key-yellow mb-4">
              <span class="material-symbols-outlined text-[24px]">calendar_view_day</span>
            </div>
            <h3 class="font-headline-sm text-lg font-bold text-white mb-2" data-i18n="feat4_title">Guide EPG & TMDb VOD</h3>
            <p class="font-body-sm text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="feat4_desc">
              Grille électronique des programmes en direct avec affichage du programme en cours, synopsis complets, notes, durée des épisodes et jaquettes cinéma HD.
            </p>
          </div>
          <div class="mt-4 pt-3 border-t border-surface-glass-stroke text-remote-key-yellow text-xs font-bold flex items-center gap-1.5">
            <span>🎬 Métadonnées Cinéma & Séries TV</span>
          </div>
        </div>

        <!-- Feature 5: 4 Visual Themes -->
        <div class="p-6 rounded-2xl bg-surface-container-low/70 hover:bg-surface-container/80 backdrop-blur-md transition-all duration-300 hover:scale-[1.02] shadow-xl flex flex-col justify-between border border-surface-glass-stroke">
          <div>
            <div class="w-12 h-12 rounded-xl bg-electric-violet-glow/20 border border-electric-violet-glow/30 flex items-center justify-center text-electric-violet-glow mb-4">
              <span class="material-symbols-outlined text-[24px]">palette</span>
            </div>
            <h3 class="font-headline-sm text-lg font-bold text-white mb-2" data-i18n="feat5_title">4 Thèmes Visuels Glassmorphism</h3>
            <p class="font-body-sm text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="feat5_desc">
              Personnalisez votre interface selon vos préférences : Dreamania Purple, Cyber Neon, Onyx Dark (zéro fuite de gris sur les TV OLED) et Royal Gold.
            </p>
          </div>
          <div class="mt-4 pt-3 border-t border-surface-glass-stroke text-electric-violet-glow text-xs font-bold flex items-center gap-1.5">
            <span>✨ Mode OLED & Glassmorphism 3D</span>
          </div>
        </div>

        <!-- Feature 6: D-Pad Control -->
        <div class="p-6 rounded-2xl bg-surface-container-low/70 hover:bg-surface-container/80 backdrop-blur-md transition-all duration-300 hover:scale-[1.02] shadow-xl flex flex-col justify-between border border-surface-glass-stroke">
          <div>
            <div class="w-12 h-12 rounded-xl bg-status-active-green/20 border border-status-active-green/30 flex items-center justify-center text-status-active-green mb-4">
              <span class="material-symbols-outlined text-[24px]">dvr</span>
            </div>
            <h3 class="font-headline-sm text-lg font-bold text-white mb-2" data-i18n="feat6_title">Navigation Télécommande Fluide</h3>
            <p class="font-body-sm text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="feat6_desc">
              Fini la perte de contrôle : la télécommande répond instantanément même pendant l'affichage des bandeaux OSD et le défilement rapide des chaînes.
            </p>
          </div>
          <div class="mt-4 pt-3 border-t border-surface-glass-stroke text-status-active-green text-xs font-bold flex items-center gap-1.5">
            <span>🎯 D-Pad & Détection Tactile Mobile</span>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ======================================================== -->
  <!-- SCREENSHOTS SHOWCASE                                     -->
  <!-- ======================================================== -->
  <section class="w-full py-12 sm:py-16 bg-surface-container-lowest/80 border-y border-surface-glass-stroke relative" id="screenshots">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col items-center text-center mb-10">
        <span class="font-label-md text-xs sm:text-sm uppercase tracking-widest text-neon-cyan-bright mb-2 font-bold" data-i18n="tour_sub">
          APERÇU RÉEL DE L'INTERFACE
        </span>
        <h2 class="font-headline-lg text-2xl sm:text-3xl text-on-surface font-bold tracking-tight mb-3" data-i18n="tour_title">
          Visite Visuelle de Dreamania Player Pro
        </h2>
        <p class="font-body-lg text-sm sm:text-base text-on-surface-variant max-w-2xl" data-i18n="tour_desc">
          Captures d'écran authentiques capturées sur téléviseur Android TV 4K et Fire TV.
        </p>
      </div>

      <!-- Tab Controls -->
      <div class="flex items-center justify-center gap-2 p-1.5 rounded-2xl bg-surface-container/70 border border-surface-glass-stroke max-w-xl mx-auto mb-8 flex-wrap" id="tab-controls">
        <button class="tab-btn px-4 py-2 rounded-xl font-label-md text-xs font-bold transition-all bg-surface-container-high text-primary shadow-sm" data-tab="tab-livetv" data-i18n="tab_live">
          📺 Direct & Guide EPG
        </button>
        <button class="tab-btn px-4 py-2 rounded-xl font-label-md text-xs font-bold transition-all text-on-surface-variant hover:text-white" data-tab="tab-vod" data-i18n="tab_movies">
          🎬 Cinéma & VOD
        </button>
        <button class="tab-btn px-4 py-2 rounded-xl font-label-md text-xs font-bold transition-all text-on-surface-variant hover:text-white" data-tab="tab-series" data-i18n="tab_series">
          🍿 Séries TV & Saisons
        </button>
        <button class="tab-btn px-4 py-2 rounded-xl font-label-md text-xs font-bold transition-all text-on-surface-variant hover:text-white" data-tab="tab-osd" data-i18n="tab_osd">
          ⚡ Lecteur & Pistes Audio
        </button>
      </div>

      <!-- Tab Panels -->
      <div id="tab-content" class="w-full">
        <!-- Live TV Panel -->
        <div id="tab-livetv" class="tab-panel">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            <div class="lg:col-span-8 rounded-2xl overflow-hidden shadow-2xl border border-surface-glass-stroke">
              <img class="w-full aspect-[16/10] object-cover" src="assets/img/screenshots/screen_livetv_guide.png" alt="Guide TV et Chaines"/>
            </div>
            <div class="lg:col-span-4 flex flex-col gap-3">
              <span class="text-neon-cyan-bright font-bold text-xs uppercase tracking-wider" data-i18n="shot_live_badge">ZAPPING & FAVORIS</span>
              <h3 class="font-headline-md text-xl font-bold text-white" data-i18n="shot_live_title">Guide des Chaînes EPG Temps Réel</h3>
              <p class="font-body-md text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="shot_live_desc">
                Accédez instantanément à vos catégories avec un aperçu direct du programme en cours et la possibilité de réordonner les chaînes ou de les ajouter en favoris d'une seule touche.
              </p>
            </div>
          </div>
        </div>

        <!-- VOD Movies Panel -->
        <div id="tab-vod" class="tab-panel hidden">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            <div class="lg:col-span-8 rounded-2xl overflow-hidden shadow-2xl border border-surface-glass-stroke">
              <img class="w-full aspect-[16/10] object-cover" src="assets/img/screenshots/screen_vod_movies.png" alt="Films Cinéma VOD"/>
            </div>
            <div class="lg:col-span-4 flex flex-col gap-3">
              <span class="text-secondary font-bold text-xs uppercase tracking-wider" data-i18n="shot_vod_badge">CINÉMA 4K</span>
              <h3 class="font-headline-md text-xl font-bold text-white" data-i18n="shot_vod_title">Catalogue VOD Enrichi TMDb</h3>
              <p class="font-body-md text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="shot_vod_desc">
                Fiches détaillées pour chaque film : affiche officielle HD, résumé, casting, note des spectateurs et reprise automatique de lecture là où vous vous êtes arrêté.
              </p>
            </div>
          </div>
        </div>

        <!-- Series Detail Panel -->
        <div id="tab-series" class="tab-panel hidden">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            <div class="lg:col-span-8 rounded-2xl overflow-hidden shadow-2xl border border-surface-glass-stroke">
              <img class="w-full aspect-[16/10] object-cover" src="assets/img/screenshots/screen_series_detail.png" alt="Séries TV Saisons"/>
            </div>
            <div class="lg:col-span-4 flex flex-col gap-3">
              <span class="text-remote-key-yellow font-bold text-xs uppercase tracking-wider" data-i18n="shot_series_badge">SÉRIES & ÉPISODES</span>
              <h3 class="font-headline-md text-xl font-bold text-white" data-i18n="shot_series_title">Navigation par Saisons & Durées</h3>
              <p class="font-body-md text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="shot_series_desc">
                Interface responsive plein écran pour vos séries TV. Affichage de la durée exacte de chaque épisode, date de diffusion et basculement automatique au prochain épisode.
              </p>
            </div>
          </div>
        </div>

        <!-- OSD Player Panel -->
        <div id="tab-osd" class="tab-panel hidden">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            <div class="lg:col-span-8 rounded-2xl overflow-hidden shadow-2xl border border-surface-glass-stroke">
              <img class="w-full aspect-[16/10] object-cover" src="assets/img/screenshots/screen_player_osd.png" alt="Lecteur OSD Audio"/>
            </div>
            <div class="lg:col-span-4 flex flex-col gap-3">
              <span class="text-status-active-green font-bold text-xs uppercase tracking-wider" data-i18n="shot_osd_badge">CONTRÔLE INTUITIF</span>
              <h3 class="font-headline-md text-xl font-bold text-white" data-i18n="shot_osd_title">Bandeau OSD Non-Bloquant</h3>
              <p class="font-body-md text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="shot_osd_desc">
                Contrôles ultra-complets sans interrompre le streaming : sélection facile des langues audio (Dolby Atmos, AC3), sous-titres et ratio d'aspect.
              </p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ======================================================== -->
  <!-- 3-STEP SETUP SECTION                                     -->
  <!-- ======================================================== -->
  <section class="w-full py-12 sm:py-16 bg-surface-container-lowest/70 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col items-center text-center mb-12">
        <span class="font-label-md text-xs sm:text-sm uppercase tracking-widest text-neon-cyan-bright mb-2" data-i18n="setup_sub">
          INSTALLATION SIMPLE EN 3 ÉTAPES
        </span>
        <h2 class="font-headline-lg text-2xl sm:text-3xl text-on-surface font-bold tracking-tight mb-3" data-i18n="setup_title">
          Prêt et Opérationnel en Moins de 2 Minutes
        </h2>
        <p class="font-body-lg text-sm sm:text-base text-on-surface-variant max-w-2xl" data-i18n="setup_desc">
          Démarrer avec Dreamania Player est un jeu d'enfant. Aucune manipulation complexe requise.
        </p>
      </div>

      <!-- Steps Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <!-- Step 01 -->
        <div class="p-6 rounded-2xl bg-surface-container-low/90 backdrop-blur-md shadow-xl flex flex-col border border-surface-glass-stroke">
          <div class="text-4xl font-extrabold font-headline-xl text-primary/30 leading-none mb-3">01</div>
          <h3 class="font-headline-md text-lg font-bold text-white mb-2" data-i18n="step1_title">Téléchargez & Installez l'APK</h3>
          <p class="font-body-md text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="step1_desc">
            Téléchargez <code class="px-2 py-0.5 rounded bg-surface-container font-mono text-neon-cyan-bright text-xs">DreamaniaPlayer.apk</code> ou ouvrez l'application <strong class="text-on-surface">Downloader</strong> sur votre TV et tapez le code <strong class="text-primary font-mono"><?= htmlspecialchars($downloaderCode) ?></strong>.
          </p>
        </div>

        <!-- Step 02 -->
        <div class="p-6 rounded-2xl bg-surface-container-low/90 backdrop-blur-md shadow-xl flex flex-col border border-surface-glass-stroke">
          <div class="text-4xl font-extrabold font-headline-xl text-secondary/30 leading-none mb-3">02</div>
          <h3 class="font-headline-md text-lg font-bold text-white mb-2" data-i18n="step2_title">Connectez votre Playlist</h3>
          <p class="font-body-md text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="step2_desc">
            Saisissez vos identifiants <strong class="text-on-surface">Xtream Codes</strong> ou votre lien <strong class="text-on-surface">M3U</strong> directement sur l'écran ou depuis notre portail web <a href="device.php" class="text-neon-cyan-bright underline">Mon Appareil</a>.
          </p>
        </div>

        <!-- Step 03 -->
        <div class="p-6 rounded-2xl bg-surface-container-low/90 backdrop-blur-md shadow-xl flex flex-col border border-surface-glass-stroke">
          <div class="text-4xl font-extrabold font-headline-xl text-remote-key-yellow/30 leading-none mb-3">03</div>
          <h3 class="font-headline-md text-lg font-bold text-white mb-2" data-i18n="step3_title">Profitez du Streaming 4K</h3>
          <p class="font-body-md text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="step3_desc">
            Naviguez dans vos chaînes en direct, films et séries avec le guide EPG automatique et vos favoris épinglés.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- ======================================================== -->
  <!-- PRICING & PLANS SECTION                                  -->
  <!-- ======================================================== -->
  <section class="w-full py-12 sm:py-16 relative" id="pricing">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col items-center text-center mb-12">
        <span class="font-label-md text-xs sm:text-sm uppercase tracking-widest text-primary mb-2" data-i18n="pricing_sub">
          SIMPLE & TRANSPARENT
        </span>
        <h2 class="font-headline-lg text-2xl sm:text-3xl text-on-surface font-bold tracking-tight mb-3" data-i18n="pricing_title">
          Tarifs Particuliers & Packs Revendeurs
        </h2>
        <p class="font-body-lg text-sm sm:text-base text-on-surface-variant max-w-2xl" data-i18n="pricing_desc">
          Activez votre téléviseur directement ou commandez des crédits grossistes revendeur au meilleur tarif.
        </p>
      </div>

      <!-- Dual Column Comparison: Smart TV Owners vs Resellers -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch mb-12">
        
        <!-- COLUMN A: For Smart TV Owners -->
        <div class="rounded-3xl bg-surface-container-low/85 p-6 sm:p-8 shadow-2xl backdrop-blur-md flex flex-col justify-between border border-surface-glass-stroke" id="device-activation">
          <div>
            <div class="flex items-center justify-between mb-4">
              <span class="px-3 py-1 rounded-full bg-surface-container text-neon-cyan-bright font-label-sm text-xs uppercase tracking-wider font-bold" data-i18n="user_plan_badge">
                POUR PARTICULIERS & CLIENTS TV 📺
              </span>
              <span class="text-remote-key-yellow font-label-sm text-xs font-semibold" data-i18n="user_plan_sub">Activation par MAC</span>
            </div>
            
            <h3 class="font-headline-md text-xl sm:text-2xl text-on-surface font-bold mb-2" data-i18n="user_plan_title">
              Activation Client Final
            </h3>
            <p class="font-body-md text-xs sm:text-sm text-on-surface-variant mb-6 leading-relaxed" data-i18n="user_plan_desc">
              Inspiré du portail iboplayer.com : entrez l'adresse MAC et la clé de votre TV, ajoutez vos listes M3U/Xtream et profitez du streaming 4K.
            </p>

            <!-- Plans Selector Card -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
              <!-- 1 Year Plan -->
              <div class="p-4 rounded-2xl bg-surface-container/70 shadow-md flex flex-col justify-between border border-surface-glass-stroke">
                <div>
                  <span class="font-label-sm text-xs text-on-surface-variant uppercase" data-i18n="plan_1y_label">Abonnement 1 An</span>
                  <div class="font-headline-md text-xl sm:text-2xl text-primary font-bold my-1"><?= htmlspecialchars($pricing['client_1y_intl']) ?></div>
                  <div class="font-body-sm text-xs text-on-surface-variant"><?= (int)$pricing['client_1y_dh'] ?> DH • <span data-i18n="plan_1y_sub">12 Mois d'accès complet</span></div>
                </div>
              </div>
              
              <!-- VIP Lifetime Plan -->
              <div class="p-4 rounded-2xl bg-surface-container-highest/80 shadow-[0_0_20px_rgba(139,92,246,0.3)] flex flex-col justify-between relative overflow-hidden border border-electric-violet-glow/40">
                <div class="absolute top-2 right-2 px-2 py-0.5 rounded-full bg-electric-violet-glow text-on-primary-container font-label-sm text-[10px] uppercase font-bold tracking-wider">
                  POPULAIRE
                </div>
                <div>
                  <span class="font-label-sm text-xs text-secondary uppercase font-bold" data-i18n="plan_life_label">Licence À Vie (VIP)</span>
                  <div class="font-headline-md text-xl sm:text-2xl text-secondary font-bold my-1"><?= htmlspecialchars($pricing['client_life_intl']) ?></div>
                  <div class="font-body-sm text-xs text-secondary-fixed"><?= (int)$pricing['client_life_dh'] ?> DH • <span data-i18n="plan_life_sub">Sans expiration</span></div>
                </div>
              </div>
            </div>

            <!-- Features Checkmark List -->
            <div class="flex flex-col gap-2.5 mb-6 text-xs sm:text-sm text-on-surface">
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-status-active-green text-[18px]">check_circle</span>
                <span data-i18n="user_feat_1">Listes de lecture M3U & Xtream Codes illimitées</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-status-active-green text-[18px]">check_circle</span>
                <span data-i18n="user_feat_2">Protection parentale par code PIN (PIN 0000 par défaut)</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-status-active-green text-[18px]">check_circle</span>
                <span data-i18n="user_feat_3">Mise à jour automatique du guide EPG & réorganisation des chaînes</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-status-active-green text-[18px]">check_circle</span>
                <span data-i18n="user_feat_4">Paiement sécurisé par PayPal & Virement Bancaire (RIB Maroc)</span>
              </div>
            </div>
          </div>

          <a class="w-full py-3.5 rounded-xl bg-gradient-to-r from-remote-key-yellow to-tertiary-fixed-dim text-surface-container-lowest font-label-lg text-xs sm:text-sm font-bold shadow-[0_0_20px_rgba(245,158,11,0.45)] hover:brightness-110 active:scale-95 transition-all text-center flex items-center justify-center gap-2" href="device.php">
            <span class="material-symbols-outlined text-[18px]">tv</span>
            <span data-i18n="btn_manage_device">Gérer Mon Appareil & Activer</span>
          </a>
        </div>

        <!-- COLUMN B: For Resellers & Distributors -->
        <div class="rounded-3xl bg-surface-container-low/85 p-6 sm:p-8 shadow-2xl backdrop-blur-md flex flex-col justify-between border border-surface-glass-stroke" id="reseller">
          <div>
            <div class="flex items-center justify-between mb-4">
              <span class="px-3 py-1 rounded-full bg-surface-container text-secondary font-label-sm text-xs uppercase tracking-wider font-bold" data-i18n="reseller_plan_badge">
                POUR REVENDEURS & DISTRIBUTEURS ⚡
              </span>
              <span class="text-neon-cyan-bright font-label-sm text-xs font-semibold" data-i18n="reseller_plan_sub">1 Crédit = 1 An d'Activation</span>
            </div>
            
            <h3 class="font-headline-md text-xl sm:text-2xl text-on-surface font-bold mb-2" data-i18n="reseller_plan_title">
              Packs Crédits Revendeurs Grossistes
            </h3>
            <p class="font-body-md text-xs sm:text-sm text-on-surface-variant mb-5 leading-relaxed" data-i18n="reseller_plan_desc">
              Développez votre activité avec notre panneau web revendeur dédié. Activez les téléviseurs de vos clients en quelques clics.
            </p>

            <!-- Reseller Tiered Pricing Table -->
            <div class="space-y-2 mb-5 font-label-md text-xs sm:text-sm">
              <div class="flex items-center justify-between p-2.5 sm:p-3 rounded-xl bg-surface-container/60 border border-surface-glass-stroke">
                <span class="font-semibold text-white">10 Crédits</span>
                <span class="text-primary font-bold"><?= htmlspecialchars($pricing['reseller_packs'][10]['label']) ?></span>
                <span class="text-on-surface-variant text-xs">1.5€ / 15 DH par cr</span>
              </div>
              <div class="flex items-center justify-between p-2.5 sm:p-3 rounded-xl bg-surface-container/60 border border-surface-glass-stroke">
                <span class="font-semibold text-white">20 Crédits</span>
                <span class="text-primary font-bold"><?= htmlspecialchars($pricing['reseller_packs'][20]['label']) ?></span>
                <span class="text-on-surface-variant text-xs">0.9€ / 9 DH par cr</span>
              </div>
              <div class="flex items-center justify-between p-2.5 sm:p-3 rounded-xl bg-surface-container/60 border border-surface-glass-stroke">
                <span class="font-semibold text-white">30 Crédits</span>
                <span class="text-primary font-bold"><?= htmlspecialchars($pricing['reseller_packs'][30]['label']) ?></span>
                <span class="text-on-surface-variant text-xs">0.8€ / 8 DH par cr</span>
              </div>
              <div class="flex items-center justify-between p-2.5 sm:p-3 rounded-xl bg-surface-container/60 border border-surface-glass-stroke">
                <span class="font-semibold text-white">50 Crédits</span>
                <span class="text-primary font-bold"><?= htmlspecialchars($pricing['reseller_packs'][50]['label']) ?></span>
                <span class="text-on-surface-variant text-xs">0.7€ / 7 DH par cr</span>
              </div>
              <div class="flex items-center justify-between p-2.5 sm:p-3 rounded-xl bg-surface-container-highest/90 shadow-[0_0_15px_rgba(56,189,248,0.25)] border border-neon-cyan-bright/40">
                <span class="font-bold text-neon-cyan-bright">100 Crédits (Grossiste VIP)</span>
                <span class="text-primary-fixed font-bold"><?= htmlspecialchars($pricing['reseller_packs'][100]['label']) ?></span>
                <span class="text-status-active-green text-xs font-bold">0.6€ / 6 DH (-40%)</span>
              </div>
            </div>

            <!-- Reseller Benefits -->
            <div class="flex flex-col gap-2 mb-6 text-xs sm:text-sm text-on-surface">
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary text-[18px]">verified</span>
                <span data-i18n="reseller_feat_1">Activation immédiate des téléviseurs de vos clients via le panel web</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary text-[18px]">verified</span>
                <span data-i18n="reseller_feat_2">Contrôle total sur les adresses MAC et playlists associées</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary text-[18px]">verified</span>
                <span data-i18n="reseller_feat_3">Marges bénéficiaires élevées pour installateurs et revendeurs</span>
              </div>
            </div>
          </div>

          <div class="flex flex-col sm:flex-row gap-2">
            <a class="flex-1 py-3.5 rounded-xl bg-gradient-to-r from-secondary-container via-electric-violet-glow to-primary-container text-on-primary-container font-label-lg text-xs sm:text-sm font-bold shadow-[0_0_20px_rgba(139,92,246,0.45)] hover:scale-105 active:scale-95 transition-all text-center flex items-center justify-center gap-1.5" href="index.php?panel=1">
              <span class="material-symbols-outlined text-[18px]">login</span>
              <span data-i18n="btn_reseller_panel">Connexion Espace Revendeur</span>
            </a>
            <button onclick="document.getElementById('payment-modal').classList.remove('hidden')" class="px-4 py-3.5 rounded-xl bg-surface-container-highest hover:bg-surface-bright text-primary border border-surface-glass-stroke font-bold text-xs sm:text-sm transition-all flex items-center justify-center gap-1.5">
              <span class="material-symbols-outlined text-[18px]">payments</span>
              <span data-i18n="btn_order_pack">Commander un Pack</span>
            </button>
          </div>
        </div>

      </div>

      <!-- Payment Gateways Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
        <div onclick="document.getElementById('payment-modal').classList.remove('hidden')" class="p-5 rounded-2xl bg-surface-container-low/80 border border-surface-glass-stroke backdrop-blur-md flex items-center gap-3.5 cursor-pointer hover:border-primary/50 transition-all">
          <div class="w-11 h-11 rounded-xl bg-surface-container flex items-center justify-center text-[22px] border border-surface-glass-stroke shadow-inner">
            🏦
          </div>
          <div>
            <h4 class="font-headline-sm text-sm sm:text-base text-white font-bold" data-i18n="pay_rib_title">Virement Bancaire (RIB Maroc)</h4>
            <p class="font-body-sm text-xs text-on-surface-variant"><?= htmlspecialchars($paymentConfig['rib_bank_name']) ?> • Attijariwafa • CIH</p>
          </div>
        </div>

        <div onclick="document.getElementById('payment-modal').classList.remove('hidden')" class="p-5 rounded-2xl bg-surface-container-low/80 border border-surface-glass-stroke backdrop-blur-md flex items-center gap-3.5 cursor-pointer hover:border-primary/50 transition-all">
          <div class="w-11 h-11 rounded-xl bg-surface-container flex items-center justify-center text-[22px] border border-surface-glass-stroke shadow-inner">
            🅿️
          </div>
          <div>
            <h4 class="font-headline-sm text-sm sm:text-base text-white font-bold" data-i18n="pay_paypal_title">PayPal International & Cartes</h4>
            <p class="font-body-sm text-xs text-on-surface-variant">Paiement mondial instantané sécurisé</p>
          </div>
        </div>

        <div onclick="document.getElementById('payment-modal').classList.remove('hidden')" class="p-5 rounded-2xl bg-surface-container-low/80 border border-surface-glass-stroke backdrop-blur-md flex items-center gap-3.5 cursor-pointer hover:border-primary/50 transition-all">
          <div class="w-11 h-11 rounded-xl bg-surface-container flex items-center justify-center text-[22px] border border-surface-glass-stroke shadow-inner">
            💬
          </div>
          <div>
            <h4 class="font-headline-sm text-sm sm:text-base text-white font-bold" data-i18n="pay_wa_title">Confirmation WhatsApp 24/7</h4>
            <p class="font-body-sm text-xs text-on-surface-variant">Validation rapide & assistance humaine</p>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ======================================================== -->
  <!-- DOWNLOAD SECTION WITH OFFICIAL CODE & QR CODE             -->
  <!-- ======================================================== -->
  <section class="w-full py-12 sm:py-16 bg-surface-container-lowest/80 relative" id="download">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="relative rounded-3xl bg-gradient-to-br from-surface-container-low via-surface-container to-surface-container-lowest p-6 sm:p-12 shadow-2xl overflow-hidden flex flex-col lg:flex-row items-center justify-between gap-8 sm:gap-12 border border-surface-glass-stroke">
        
        <div class="max-w-2xl flex flex-col gap-4 relative z-10 text-left">
          <span class="font-label-md text-xs sm:text-sm uppercase tracking-widest text-primary font-bold" data-i18n="dl_badge">
            TÉLÉCHARGEMENT DIRECT DE L'APPLICATION
          </span>
          <h2 class="font-headline-lg text-2xl sm:text-3xl text-white font-bold tracking-tight" data-i18n="dl_title">
            Prêt pour l'Expérience IPTV Ultime ?
          </h2>
          <p class="font-body-lg text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="dl_desc">
            Téléchargez Dreamania Player dès aujourd'hui. Profitez d'une lecture 4K fluide, du classement personnalisable des chaînes et du multi-audio sans publicité.
          </p>
          
          <div class="flex flex-wrap items-center gap-3 pt-2">
            <a class="px-5 py-3 rounded-xl bg-gradient-to-r from-secondary-container via-electric-violet-glow to-primary-container text-on-primary-container font-label-lg text-xs sm:text-sm font-bold shadow-[0_0_24px_rgba(139,92,246,0.45)] hover:scale-105 active:scale-95 transition-all flex items-center gap-2" href="download">
              <span class="material-symbols-outlined text-[18px]">download</span>
              <span data-i18n="dl_btn_apk">Télécharger Dreamania Player APK</span>
            </a>
            <a class="px-4 py-3 rounded-xl bg-surface-container-high/80 hover:bg-surface-container-highest text-secondary font-label-lg text-xs sm:text-sm transition-all flex items-center gap-2 border border-surface-glass-stroke" href="index.php?panel=1">
              <span class="material-symbols-outlined text-[18px] text-neon-cyan-bright">storefront</span>
              <span data-i18n="dl_btn_reseller">Espace Revendeur</span>
            </a>
            <a class="px-4 py-3 rounded-xl bg-surface-container-high/80 hover:bg-surface-container-highest text-primary font-label-lg text-xs sm:text-sm transition-all flex items-center gap-2 border border-surface-glass-stroke" href="index.php?admin=1">
              <span class="material-symbols-outlined text-[18px]">shield_person</span>
              <span data-i18n="dl_btn_admin">Panel Admin</span>
            </a>
          </div>
        </div>

        <!-- High-Tech QR Code Component -->
        <div class="w-full sm:w-auto p-5 rounded-2xl bg-surface-container-lowest/90 backdrop-blur-xl shadow-2xl flex flex-col items-center text-center relative z-10 border border-surface-glass-stroke">
          <div class="p-3 bg-white rounded-xl shadow-md mb-3">
            <svg class="w-32 h-32" fill="none" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
              <rect fill="white" height="100" width="100"></rect>
              <rect fill="#0D0E1A" height="24" width="24" x="10" y="10"></rect>
              <rect fill="white" height="16" width="16" x="14" y="14"></rect>
              <rect fill="#0D0E1A" height="8" width="8" x="18" y="18"></rect>
              <rect fill="#0D0E1A" height="24" width="24" x="66" y="10"></rect>
              <rect fill="white" height="16" width="16" x="70" y="14"></rect>
              <rect fill="#0D0E1A" height="8" width="8" x="74" y="18"></rect>
              <rect fill="#0D0E1A" height="24" width="24" x="10" y="66"></rect>
              <rect fill="white" height="16" width="16" x="14" y="70"></rect>
              <rect fill="#0D0E1A" height="8" width="8" x="18" y="74"></rect>
              <rect fill="#0D0E1A" height="16" width="4" x="38" y="10"></rect>
              <rect fill="#0D0E1A" height="4" width="8" x="46" y="14"></rect>
              <rect fill="#0D0E1A" height="12" width="4" x="58" y="10"></rect>
              <rect fill="#0D0E1A" height="4" width="12" x="38" y="30"></rect>
              <rect fill="#0D0E1A" height="8" width="4" x="46" y="38"></rect>
              <rect fill="#0D0E1A" height="4" width="8" x="10" y="42"></rect>
              <rect fill="#0D0E1A" height="16" width="4" x="22" y="38"></rect>
              <rect fill="#0D0E1A" height="4" width="12" x="30" y="46"></rect>
              <rect fill="#0D0E1A" height="8" width="8" x="54" y="34"></rect>
              <rect fill="#0D0E1A" height="4" width="20" x="70" y="38"></rect>
              <rect fill="#0D0E1A" height="12" width="8" x="66" y="46"></rect>
              <rect fill="#0D0E1A" height="8" width="8" x="82" y="46"></rect>
              <rect fill="#0D0E1A" height="4" width="16" x="38" y="58"></rect>
              <rect fill="#0D0E1A" height="12" width="8" x="42" y="66"></rect>
              <rect fill="#0D0E1A" height="16" width="4" x="54" y="62"></rect>
              <rect fill="#0D0E1A" height="4" width="20" x="38" y="82"></rect>
              <rect fill="#0D0E1A" height="4" width="12" x="66" y="66"></rect>
              <rect fill="#0D0E1A" height="4" width="16" x="74" y="74"></rect>
              <rect fill="#0D0E1A" height="16" width="4" x="86" y="62"></rect>
              <rect fill="#0D0E1A" height="8" width="8" x="66" y="82"></rect>
              <rect fill="#0D0E1A" height="4" width="8" x="82" y="86"></rect>
            </svg>
          </div>
          <span class="font-headline-sm text-xs font-bold text-white" data-i18n="qr_title">
            Scannez pour Télécharger
          </span>
          <p class="font-body-sm text-[11px] text-on-surface-variant max-w-[200px] mt-1" data-i18n="qr_desc">
            Pointez votre smartphone ou saisissez le code <strong><?= htmlspecialchars($downloaderCode) ?></strong> sur Fire TV.
          </p>
        </div>

      </div>
    </div>
  </section>

  <!-- ======================================================== -->
  <!-- FAQ ACCORDION                                            -->
  <!-- ======================================================== -->
  <section class="w-full py-12 sm:py-16 relative" id="faq">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex flex-col items-center text-center mb-10">
        <span class="font-label-md text-xs sm:text-sm uppercase tracking-widest text-neon-cyan-bright mb-2 font-bold" data-i18n="faq_sub">
          QUESTIONS FRÉQUEMMENT POSÉES
        </span>
        <h2 class="font-headline-lg text-2xl sm:text-3xl text-on-surface font-bold tracking-tight mb-3" data-i18n="faq_title">
          Vous avez des questions ? Nous avons les réponses
        </h2>
        <p class="font-body-lg text-sm sm:text-base text-on-surface-variant" data-i18n="faq_desc">
          Retrouvez les informations clés concernant l'installation, les playlists et les raccourcis.
        </p>
      </div>

      <div class="space-y-3" id="faq-accordion">
        <!-- Question 1 -->
        <div class="rounded-2xl bg-surface-container-low/70 overflow-hidden shadow-md border border-surface-glass-stroke">
          <button class="faq-toggle w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 font-headline-sm text-sm sm:text-base text-white font-semibold hover:text-primary transition-colors">
            <span data-i18n="faq1_q">Comment installer Dreamania Player sur Amazon Fire TV Stick ?</span>
            <span class="material-symbols-outlined text-[20px] text-neon-cyan-bright transition-transform duration-200">expand_more</span>
          </button>
          <div class="faq-content hidden px-4 sm:px-5 pb-5 pt-1 font-body-md text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="faq1_a">
            Ouvrez l'application gratuite <strong>Downloader</strong> sur votre Fire TV, saisissez simplement notre code court <strong>7454701</strong> ou l'adresse <code>https://dm.lumineasy.com/download</code>, puis validez. L'installation démarrera automatiquement.
          </div>
        </div>

        <!-- Question 2 -->
        <div class="rounded-2xl bg-surface-container-low/70 overflow-hidden shadow-md border border-surface-glass-stroke">
          <button class="faq-toggle w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 font-headline-sm text-sm sm:text-base text-white font-semibold hover:text-primary transition-colors">
            <span data-i18n="faq2_q">L'application Dreamania Player inclut-elle des chaînes de télévision ?</span>
            <span class="material-symbols-outlined text-[20px] text-neon-cyan-bright transition-transform duration-200">expand_more</span>
          </button>
          <div class="faq-content hidden px-4 sm:px-5 pb-5 pt-1 font-body-md text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="faq2_a">
            Non. Dreamania Player est un lecteur multimédia haute performance pur. Il n'héberge et ne fournit aucun flux vidéo. Vous devez utiliser vos propres identifiants d'abonnement légaux via l'API Xtream Codes ou au format M3U.
          </div>
        </div>

        <!-- Question 3 -->
        <div class="rounded-2xl bg-surface-container-low/70 overflow-hidden shadow-md border border-surface-glass-stroke">
          <button class="faq-toggle w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 font-headline-sm text-sm sm:text-base text-white font-semibold hover:text-primary transition-colors">
            <span data-i18n="faq3_q">Comment ajouter une chaîne aux Favoris avec la télécommande ?</span>
            <span class="material-symbols-outlined text-[20px] text-neon-cyan-bright transition-transform duration-200">expand_more</span>
          </button>
          <div class="faq-content hidden px-4 sm:px-5 pb-5 pt-1 font-body-md text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="faq3_a">
            Dans la liste des chaînes, sélectionnez simplement la chaîne de votre choix et appuyez sur le <strong>Bouton Rouge</strong> ou effectuez un appui long sur le bouton <strong>OK</strong> de votre télécommande. Le cœur passe au rouge (❤️) et la chaîne apparaît instantanément en tête de liste dans "❤️ Favoris".
          </div>
        </div>

        <!-- Question 4 -->
        <div class="rounded-2xl bg-surface-container-low/70 overflow-hidden shadow-md border border-surface-glass-stroke">
          <button class="faq-toggle w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 font-headline-sm text-sm sm:text-base text-white font-semibold hover:text-primary transition-colors">
            <span data-i18n="faq4_q">Comment réorganiser ou déplacer l'ordre des chaînes ?</span>
            <span class="material-symbols-outlined text-[20px] text-neon-cyan-bright transition-transform duration-200">expand_more</span>
          </button>
          <div class="faq-content hidden px-4 sm:px-5 pb-5 pt-1 font-body-md text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="faq4_a">
            Appuyez sur le <strong>Bouton Jaune</strong> de votre télécommande sur n'importe quelle chaîne. Un panneau s'ouvre pour vous permettre de la monter, la descendre, la placer tout en haut ou activer le mode interactif au curseur.
          </div>
        </div>

        <!-- Question 5 -->
        <div class="rounded-2xl bg-surface-container-low/70 overflow-hidden shadow-md border border-surface-glass-stroke">
          <button class="faq-toggle w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 font-headline-sm text-sm sm:text-base text-white font-semibold hover:text-primary transition-colors">
            <span data-i18n="faq5_q">Comment fonctionne le système de crédits pour les revendeurs ?</span>
            <span class="material-symbols-outlined text-[20px] text-neon-cyan-bright transition-transform duration-200">expand_more</span>
          </button>
          <div class="faq-content hidden px-4 sm:px-5 pb-5 pt-1 font-body-md text-xs sm:text-sm text-on-surface-variant leading-relaxed" data-i18n="faq5_a">
            Pour les revendeurs, 1 crédit équivaut à 1 an d'activation pour un téléviseur client. Vous achetez des packs de crédits avec des remises quantitatives (à partir de 10 crédits), puis vous activez directement les adresses MAC de vos clients depuis votre portail revendeur web.
          </div>
        </div>
      </div>

    </div>
  </section>

</div>
</main>

<!-- ======================================================== -->
<!-- FOOTER WITH DEDICATED PORTAL ACCESS COLUMN               -->
<!-- ======================================================== -->
<footer class="w-full bg-surface-container-lowest relative border-t border-surface-glass-stroke">
  <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 mb-10">
      
      <!-- Brand & Mission -->
      <div class="lg:col-span-2 flex flex-col gap-3">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-surface-container-high flex items-center justify-center p-1 border border-neon-cyan-bright/30 shadow-[0_0_15px_rgba(56,189,248,0.35)]">
            <img alt="Dreamania Logo" class="w-full h-full object-contain" src="assets/img/icon.png"/>
          </div>
          <span class="font-headline-sm text-lg text-primary tracking-wide">DREAMANIA PLAYER PRO</span>
        </div>
        <p class="font-body-sm text-xs sm:text-sm text-on-surface-variant max-w-md leading-relaxed" data-i18n="footer_about">
          Moteur multimédia ultra-réactif pour Smart TV et mobiles calibré pour une latence minimale, décodage matériel 4K et expérience cinéma sans compromis.
        </p>
        <div class="flex items-center gap-2 mt-1">
          <span class="px-2.5 py-1 rounded bg-surface-container text-neon-cyan-bright font-label-sm text-[10px] uppercase tracking-wider border border-surface-glass-stroke">4K UHD 60FPS</span>
          <span class="px-2.5 py-1 rounded bg-surface-container text-secondary font-label-sm text-[10px] uppercase tracking-wider border border-surface-glass-stroke">DOLBY ATMOS</span>
          <span class="px-2.5 py-1 rounded bg-surface-container text-status-active-green font-label-sm text-[10px] uppercase tracking-wider border border-surface-glass-stroke">D-PAD COMPATIBLE</span>
        </div>
      </div>

      <!-- Column 1: Direct Portal Access (HIGH VISIBILITY) -->
      <div class="flex flex-col gap-2">
        <span class="font-label-lg text-xs sm:text-sm text-white uppercase tracking-wider mb-2 font-bold" data-i18n="footer_col_portals">Portails & Accès Direct</span>
        <a class="font-body-sm text-xs sm:text-sm text-remote-key-yellow hover:underline flex items-center gap-1.5" href="device.php">
          <span>📺</span> <span data-i18n="footer_link_device">Mon Appareil (Playlists TV)</span>
        </a>
        <a class="font-body-sm text-xs sm:text-sm text-secondary hover:underline flex items-center gap-1.5" href="index.php?panel=1">
          <span>💼</span> <span data-i18n="footer_link_reseller">Espace Revendeur & Crédits</span>
        </a>
        <a class="font-body-sm text-xs sm:text-sm text-primary hover:underline flex items-center gap-1.5" href="index.php?admin=1">
          <span>⚙️</span> <span data-i18n="footer_link_admin">Panel Administrateur</span>
        </a>
        <a class="font-body-sm text-xs sm:text-sm text-status-active-green hover:underline flex items-center gap-1.5" href="download">
          <span>⚡</span> <span>Code Downloader (<?= htmlspecialchars($downloaderCode) ?>)</span>
        </a>
      </div>

      <!-- Column 2: Produit & Tarifs -->
      <div class="flex flex-col gap-2">
        <span class="font-label-lg text-xs sm:text-sm text-white uppercase tracking-wider mb-2 font-bold" data-i18n="footer_col_product">Produit & Tarifs</span>
        <a class="font-body-sm text-xs text-on-surface-variant hover:text-primary transition-colors" href="#features" data-i18n="nav_features">Fonctionnalités</a>
        <a class="font-body-sm text-xs text-on-surface-variant hover:text-primary transition-colors" href="#screenshots" data-i18n="nav_app_tour">Captures d'Écran</a>
        <a class="font-body-sm text-xs text-on-surface-variant hover:text-primary transition-colors" href="#pricing" data-i18n="footer_link_sub">Abonnements 1 An & Lifetime</a>
        <a class="font-body-sm text-xs text-on-surface-variant hover:text-primary transition-colors" href="download" data-i18n="footer_link_dl">Téléchargement APK Direct</a>
      </div>

      <!-- Column 3: Compatibilité -->
      <div class="flex flex-col gap-2">
        <span class="font-label-lg text-xs sm:text-sm text-white uppercase tracking-wider mb-2 font-bold" data-i18n="footer_col_compat">Compatibilité</span>
        <span class="font-body-sm text-xs text-on-surface-variant">Amazon Fire TV Stick 4K / Max</span>
        <span class="font-body-sm text-xs text-on-surface-variant">Android TV & Google TV</span>
        <span class="font-body-sm text-xs text-on-surface-variant">NVIDIA Shield Pro</span>
        <span class="font-body-sm text-xs text-on-surface-variant">Smartphones & Tablettes Android</span>
      </div>

    </div>

    <!-- Legal Disclaimer Notice -->
    <div class="p-4 rounded-xl bg-surface-container-low/70 mb-6 border border-surface-glass-stroke">
      <div class="flex items-start gap-3">
        <span class="material-symbols-outlined text-remote-key-yellow text-[20px] shrink-0 mt-0.5">shield</span>
        <div class="flex flex-col gap-1">
          <span class="font-label-md text-xs text-white uppercase tracking-wider font-bold" data-i18n="legal_title">Avis Légal & Clause de Non-Responsabilité</span>
          <p class="font-body-sm text-[11px] sm:text-xs text-on-surface-variant leading-relaxed" data-i18n="legal_desc">
            Dreamania Player ne fournit, n'héberge, ne vend et ne diffuse aucun contenu audiovisuel ou flux multimédia. Dreamania Player est strictement une application logicielle de lecture vidéo. Les utilisateurs doivent fournir leurs propres listes de lecture légalement acquises via Xtream Codes ou M3U. Dreamania Player n'approuve ni ne cautionne aucune diffusion non autorisée.
          </p>
        </div>
      </div>
    </div>

    <!-- Bottom Copyright -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-surface-glass-stroke text-xs text-on-surface-variant">
      <span>© 2026 Dreamania Player. Propulsé par DreamaniaCode. Tous droits réservés.</span>
      <div class="flex items-center gap-4">
        <a class="hover:text-neon-cyan-bright transition-colors" href="#faq" data-i18n="nav_faq">Assistance FAQ</a>
        <a class="hover:text-neon-cyan-bright transition-colors" href="device.php" data-i18n="nav_device">Espace TV</a>
        <a class="hover:text-neon-cyan-bright transition-colors" href="index.php?panel=1" data-i18n="nav_reseller_portal">Revendeurs</a>
      </div>
    </div>
  </div>
</footer>

<!-- ======================================================== -->
<!-- STICKY MOBILE BOTTOM PORTAL BAR (FOR SMARTPHONES)        -->
<!-- ======================================================== -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-surface-obsidian-subtle/95 backdrop-blur-xl border-t border-surface-glass-stroke px-2 py-2 shadow-[0_-8px_24px_rgba(0,0,0,0.7)]">
  <div class="flex items-center justify-around text-center">
    <!-- Mon Appareil -->
    <a href="device.php" class="flex flex-col items-center gap-0.5 text-on-surface-variant hover:text-remote-key-yellow transition-colors py-1">
      <span class="material-symbols-outlined text-[20px] text-remote-key-yellow">tv</span>
      <span class="text-[10px] font-bold text-white" data-i18n="mobile_nav_device">Appareil</span>
    </a>
    <!-- Espace Revendeur -->
    <a href="index.php?panel=1" class="flex flex-col items-center gap-0.5 text-on-surface-variant hover:text-secondary transition-colors py-1">
      <span class="material-symbols-outlined text-[20px] text-secondary">storefront</span>
      <span class="text-[10px] font-bold text-white" data-i18n="mobile_nav_reseller">Revendeur</span>
    </a>
    <!-- Panel Admin -->
    <a href="index.php?admin=1" class="flex flex-col items-center gap-0.5 text-on-surface-variant hover:text-neon-cyan-bright transition-colors py-1">
      <span class="material-symbols-outlined text-[20px] text-neon-cyan-bright">shield_person</span>
      <span class="text-[10px] font-bold text-white" data-i18n="mobile_nav_admin">Admin</span>
    </a>
    <!-- Code 7454701 / APK -->
    <a href="download" class="flex flex-col items-center gap-0.5 text-on-surface-variant hover:text-status-active-green transition-colors py-1">
      <span class="material-symbols-outlined text-[20px] text-status-active-green">download</span>
      <span class="text-[10px] font-bold text-white font-mono"><?= htmlspecialchars($downloaderCode) ?></span>
    </a>
    <!-- Language Toggle Switch -->
    <button type="button" onclick="toggleLanguage()" class="flex flex-col items-center gap-0.5 text-on-surface-variant hover:text-primary transition-colors py-1">
      <span class="text-[16px] leading-none mt-0.5" id="mobile-lang-flag">🇫🇷</span>
      <span class="text-[10px] font-bold text-neon-cyan-bright" id="mobile-lang-label">FR/EN</span>
    </button>
  </div>
</nav>

<!-- ======================================================== -->
<!-- INTERACTIVE PAYMENT INFO MODAL / DRAWER                  -->
<!-- ======================================================== -->
<div id="payment-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/85 backdrop-blur-md hidden">
  <div class="relative w-full max-w-xl rounded-2xl bg-surface-container-low border border-surface-glass-stroke p-5 sm:p-7 shadow-2xl">
    <button onclick="document.getElementById('payment-modal').classList.add('hidden')" class="absolute top-4 right-4 text-on-surface-variant hover:text-white p-2 rounded-lg bg-surface-container" title="Fermer">
      <span class="material-symbols-outlined text-[20px]">close</span>
    </button>
    <div class="flex items-center gap-3 mb-5">
      <div class="w-10 h-10 rounded-xl bg-surface-container-high flex items-center justify-center border border-neon-cyan-bright/30 text-neon-cyan-bright">
        <span class="material-symbols-outlined">payments</span>
      </div>
      <div>
        <h3 class="font-headline-sm text-base sm:text-lg font-bold text-white" data-i18n="modal_pay_title">Modes de Paiement & Validation</h3>
        <p class="font-body-sm text-xs text-on-surface-variant" data-i18n="modal_pay_sub">Réglez par Virement Bancaire (RIB Maroc) ou PayPal International</p>
      </div>
    </div>

    <!-- Payment Tabs -->
    <div class="flex gap-2 p-1 rounded-xl bg-surface-container-lowest border border-surface-glass-stroke mb-4">
      <button onclick="switchPaymentTab('rib')" id="tab-rib" class="flex-1 py-2 text-center text-xs font-bold rounded-lg bg-surface-container-high text-primary transition-all">
        🏦 Virement Bancaire (RIB Maroc)
      </button>
      <button onclick="switchPaymentTab('paypal')" id="tab-paypal" class="flex-1 py-2 text-center text-xs font-bold rounded-lg text-on-surface-variant hover:text-white transition-all">
        🅿️ PayPal International
      </button>
    </div>

    <!-- Content: RIB -->
    <div id="content-rib" class="space-y-2.5 font-body-sm text-xs sm:text-sm">
      <div class="p-3 rounded-xl bg-surface-container/70 border border-surface-glass-stroke">
        <span class="text-[11px] text-on-surface-variant block mb-0.5">Banque</span>
        <span class="font-bold text-white"><?= htmlspecialchars($paymentConfig['rib_bank_name']) ?></span>
      </div>
      <div class="p-3 rounded-xl bg-surface-container/70 border border-surface-glass-stroke">
        <span class="text-[11px] text-on-surface-variant block mb-0.5">Titulaire du compte</span>
        <span class="font-bold text-white"><?= htmlspecialchars($paymentConfig['rib_account_name']) ?></span>
      </div>
      <div class="p-3 rounded-xl bg-surface-container/70 border border-surface-glass-stroke flex items-center justify-between">
        <div>
          <span class="text-[11px] text-on-surface-variant block mb-0.5">Numéro de Compte (RIB 24 chiffres)</span>
          <span class="font-mono font-bold text-neon-cyan-bright tracking-wider text-xs sm:text-sm" id="rib-number"><?= htmlspecialchars($paymentConfig['rib_number']) ?></span>
        </div>
        <button onclick="copyToClipboard('<?= htmlspecialchars($paymentConfig['rib_number']) ?>', 'Numéro RIB copié !')" class="px-3 py-1.5 rounded-lg bg-surface-container-highest text-primary text-xs font-bold hover:brightness-125 transition-all">
          Copier
        </button>
      </div>
      <div class="p-3 rounded-xl bg-surface-container/70 border border-surface-glass-stroke">
        <span class="text-[11px] text-on-surface-variant block mb-0.5">Code SWIFT / BIC</span>
        <span class="font-mono text-white"><?= htmlspecialchars($paymentConfig['rib_swift']) ?></span>
      </div>
    </div>

    <!-- Content: PayPal -->
    <div id="content-paypal" class="space-y-3 font-body-sm text-xs sm:text-sm hidden">
      <div class="p-3.5 rounded-xl bg-surface-container/70 border border-surface-glass-stroke flex items-center justify-between">
        <div>
          <span class="text-[11px] text-on-surface-variant block mb-0.5">Adresse Email PayPal</span>
          <span class="font-bold text-primary" id="paypal-email"><?= htmlspecialchars($paymentConfig['paypal_email']) ?></span>
        </div>
        <button onclick="copyToClipboard('<?= htmlspecialchars($paymentConfig['paypal_email']) ?>', 'Email PayPal copié !')" class="px-3 py-1.5 rounded-lg bg-surface-container-highest text-primary text-xs font-bold hover:brightness-125 transition-all">
          Copier
        </button>
      </div>
      <a href="<?= htmlspecialchars($paymentConfig['paypal_me_link']) ?>" target="_blank" rel="noopener" class="w-full py-3 rounded-xl bg-[#0070BA] text-white font-bold flex items-center justify-center gap-2 text-xs shadow-lg hover:brightness-110 transition-all">
        <span>Ouvrir PayPal.me</span>
        <span class="material-symbols-outlined text-[16px]">open_in_new</span>
      </a>
    </div>

    <!-- Footer Action -->
    <div class="mt-5 pt-3.5 border-t border-surface-glass-stroke flex flex-col sm:flex-row items-center justify-between gap-3">
      <span class="text-xs text-on-surface-variant text-center sm:text-left" data-i18n="modal_wa_hint">Envoyez la capture du reçu pour activation immédiate :</span>
      <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $paymentConfig['whatsapp_number']) ?>" target="_blank" rel="noopener" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-[#25D366] text-white font-bold text-xs flex items-center justify-center gap-1.5 hover:brightness-110 transition-all">
        <span>Confirmation WhatsApp</span>
      </a>
    </div>
  </div>
</div>

<!-- ======================================================== -->
<!-- BILINGUAL TRANSLATION ENGINE & JAVASCRIPT LOGIC          -->
<!-- ======================================================== -->
<script>
const translations = {
  fr: {
    nav_features: "Fonctionnalités",
    nav_app_tour: "Visite Guidée",
    nav_engine: "Moteur Media3",
    nav_plans: "Tarifs",
    nav_faq: "FAQ",
    nav_device: "Mon Appareil",
    nav_device_short: "Appareil",
    nav_reseller_portal: "Revendeur",
    nav_admin: "Admin",
    mobile_nav_device: "Appareil",
    mobile_nav_reseller: "Revendeur",
    mobile_nav_admin: "Admin",
    hero_badge: "MOTEUR BROADCAST OFFICIEL • CERTIFIÉ ANDROID TV & FIRE TV",
    hero_title_1: "Le Lecteur IPTV & Cinéma ",
    hero_title_grad: "Nouvelle Génération ",
    hero_title_2: "Taillé pour Votre Smart TV",
    hero_desc: "Conçu pour un zapping instantané, le streaming sportif 4K 60FPS et la VOD cinéma enrichie TMDb. Propulsé par Google Media3 ExoPlayer avec zéro superflu et une navigation fluide dédiée à la télécommande TV.",
    hero_btn_download: "Télécharger APK (v1.0.0 Pro)",
    hero_btn_manage: "Gérer TV / Téléverser Playlist",
    hero_btn_pricing: "Tarifs (50 DH / 5€ – 150 DH / 15€)",
    portal_hub_title: "Portails d'Accès & Espaces Dédiés",
    portal_hub_desc: "Choisissez directement votre espace : Particuliers, Revendeurs grossistes ou Administration.",
    card_device_badge: "Clients Particuliers",
    card_device_title: "Mon Appareil & Playlists",
    card_device_desc: "Entrez votre adresse MAC et Device Key pour téléverser vos listes M3U ou Xtream et activer votre abonnement TV.",
    card_device_btn: "Accéder à Mon Appareil",
    card_reseller_badge: "Revendeurs & B2B",
    card_reseller_title: "Espace Revendeur",
    card_reseller_desc: "Connectez-vous à votre panneau revendeur pour gérer vos crédits (1 crédit = 1 an) et activer vos clients instantanément.",
    card_reseller_btn: "Connexion Revendeur",
    card_admin_badge: "Supervision Master",
    card_admin_title: "Panel Administrateur",
    card_admin_desc: "Gestion globale du serveur, routes DNS, validation des paiements RIB/PayPal et paramétrage des tarifs.",
    card_admin_btn: "Accès Admin Master",
    card_tv_badge: "Fire TV & Android Box",
    card_tv_title: "Code Downloader : ",
    card_tv_desc: "Tapez simplement ce code dans l'application Downloader sur votre Fire TV Stick ou Box TV pour installer l'application.",
    card_tv_btn: "Télécharger APK",
    spec_version: "Version: <strong class="text-primary font-semibold">v1.0.0 Pro</strong>",
    spec_zapping: "Zapping: <strong class="text-neon-cyan-bright font-semibold">0.24s Instantané</strong>",
    spec_compat: "Compatibilité: <strong class="text-on-surface font-semibold">Fire TV, Android TV, Google TV</strong>",
    spec_no_ads: "Sans Publicité • Middleware Propre",
    spec_fav: "Favoris ❤️ Télécommande 1 Touche",
    remote_red: "Rouge: Favori (❤️)",
    remote_yellow: "Jaune: Réordonner",
    bench_sub: "MOTEUR BROADCAST HAUTE PERFORMANCE",
    bench_title: "Infrastructure Zéro Latence pour le Sport & Cinéma",
    bench_desc: "Les tests comparatifs avec les lecteurs IPTV classiques prouvent que l'architecture Media3 de Dreamania Player zappe jusqu'à 4x plus vite sans saut d'image.",
    bench1_title: "Vitesse de Zapping",
    bench1_speed: "4.2x PLUS RAPIDE",
    bench1_desc: "Le pré-tamponnage TS/HLS permet de zapper instantanément d'une chaîne à l'autre sans écran noir.",
    bench2_title: "Indexation des Listes",
    bench2_badge: "50K+ CHAÎNES",
    bench2_desc: "Indexation JSON ultra-rapide pour les playlists Xtream volumineuses et fichiers M3U de plusieurs mégaoctets.",
    bench3_title: "Empreinte Mémoire",
    bench3_badge: "ZÉRO SURCHARGE",
    bench3_desc: "Taille d'APK ultra-légère optimisée pour les Fire TV Sticks d'entrée de gamme à mémoire restreinte.",
    leanback_title: "Expérience Leanback Certifiée",
    leanback_desc: "Conçu et testé rigoureusement pour les interfaces TV sous Android 5.0 jusqu'à Android 14+.",
    leanback_btn: "Télécharger l'APK Leanback",
    feat_sub: "CONÇU POUR LA PERFORMANCE",
    feat_title: "Développé pour la Perfection",
    feat_desc: "Découvrez un middleware IPTV et un lecteur multimédia Android sans compromis sur la vitesse ou le design.",
    feat1_title: "Favoris & Réorganisation",
    feat1_desc: "Épinglez n'importe quelle chaîne dans vos Favoris d'une seule touche (Bouton Rouge ❤️). Réorganisez les chaînes à votre guise avec le Bouton Jaune de la télécommande.",
    feat2_title: "Moteur Google Media3",
    feat2_desc: "Décodage matériel natif pour les flux H.265/HEVC, 4K UHD, HDR10 et Dolby Vision avec sélection multi-pistes audio et sous-titres à la volée.",
    feat3_title: "Xtream Codes & M3U",
    feat3_desc: "Prise en charge intégrale des identifiants Xtream Codes API et des URL M3U/M3U8 avec gestion de plusieurs comptes simultanés sans réinitialisation.",
    feat4_title: "Guide EPG & TMDb VOD",
    feat4_desc: "Grille électronique des programmes en direct avec affichage du programme en cours, synopsis complets, notes, durée des épisodes et jaquettes cinéma HD.",
    feat5_title: "4 Thèmes Visuels Glassmorphism",
    feat5_desc: "Personnalisez votre interface selon vos préférences : Dreamania Purple, Cyber Neon, Onyx Dark (zéro fuite de gris sur les TV OLED) et Royal Gold.",
    feat6_title: "Navigation Télécommande Fluide",
    feat6_desc: "Fini la perte de contrôle : la télécommande répond instantanément même pendant l'affichage des bandeaux OSD et le défilement rapide des chaînes.",
    tour_sub: "APERÇU RÉEL DE L'INTERFACE",
    tour_title: "Visite Visuelle de Dreamania Player Pro",
    tour_desc: "Captures d'écran authentiques capturées sur téléviseur Android TV 4K et Fire TV.",
    tab_live: "📺 Direct & Guide EPG",
    tab_movies: "🎬 Cinéma & VOD",
    tab_series: "🍿 Séries TV & Saisons",
    tab_osd: "⚡ Lecteur & Pistes Audio",
    shot_live_badge: "ZAPPING & FAVORIS",
    shot_live_title: "Guide des Chaînes EPG Temps Réel",
    shot_live_desc: "Accédez instantanément à vos catégories avec un aperçu direct du programme en cours et la possibilité de réordonner les chaînes ou de les ajouter en favoris d'une seule touche.",
    shot_vod_badge: "CINÉMA 4K",
    shot_vod_title: "Catalogue VOD Enrichi TMDb",
    shot_vod_desc: "Fiches détaillées pour chaque film : affiche officielle HD, résumé, casting, note des spectateurs et reprise automatique de lecture là où vous vous êtes arrêté.",
    shot_series_badge: "SÉRIES & ÉPISODES",
    shot_series_title: "Navigation par Saisons & Durées",
    shot_series_desc: "Interface responsive plein écran pour vos séries TV. Affichage de la durée exacte de chaque épisode, date de diffusion et basculement automatique au prochain épisode.",
    shot_osd_badge: "CONTRÔLE INTUITIF",
    shot_osd_title: "Bandeau OSD Non-Bloquant",
    shot_osd_desc: "Contrôles ultra-complets sans interrompre le streaming : sélection facile des langues audio (Dolby Atmos, AC3), sous-titres et ratio d'aspect.",
    setup_sub: "INSTALLATION SIMPLE EN 3 ÉTAPES",
    setup_title: "Prêt et Opérationnel en Moins de 2 Minutes",
    setup_desc: "Démarrer avec Dreamania Player est un jeu d'enfant. Aucune manipulation complexe requise.",
    step1_title: "Téléchargez & Installez l'APK",
    step1_desc: "Téléchargez DreamaniaPlayer.apk ou ouvrez l'application Downloader sur votre TV et tapez le code 7454701.",
    step2_title: "Connectez votre Playlist",
    step2_desc: "Saisissez vos identifiants Xtream Codes ou votre lien M3U directement sur l'écran ou depuis notre portail web Mon Appareil.",
    step3_title: "Profitez du Streaming 4K",
    step3_desc: "Naviguez dans vos chaînes en direct, films et séries avec le guide EPG automatique et vos favoris épinglés.",
    pricing_sub: "SIMPLE & TRANSPARENT",
    pricing_title: "Tarifs Particuliers & Packs Revendeurs",
    pricing_desc: "Activez votre téléviseur directement ou commandez des crédits grossistes revendeur au meilleur tarif.",
    user_plan_badge: "POUR PARTICULIERS & CLIENTS TV 📺",
    user_plan_sub: "Activation par MAC",
    user_plan_title: "Activation Client Final",
    user_plan_desc: "Inspiré du portail iboplayer.com : entrez l'adresse MAC et la clé de votre TV, ajoutez vos listes M3U/Xtream et profitez du streaming 4K.",
    plan_1y_label: "Abonnement 1 An",
    plan_1y_sub: "12 Mois d'accès complet",
    plan_life_label: "Licence À Vie (VIP)",
    plan_life_sub: "Sans expiration",
    user_feat_1: "Listes de lecture M3U & Xtream Codes illimitées",
    user_feat_2: "Protection parentale par code PIN (PIN 0000 par défaut)",
    user_feat_3: "Mise à jour automatique du guide EPG & réorganisation des chaînes",
    user_feat_4: "Paiement sécurisé par PayPal & Virement Bancaire (RIB Maroc)",
    btn_manage_device: "Gérer Mon Appareil & Activer",
    reseller_plan_badge: "POUR REVENDEURS & DISTRIBUTEURS ⚡",
    reseller_plan_sub: "1 Crédit = 1 An d'Activation",
    reseller_plan_title: "Packs Crédits Revendeurs Grossistes",
    reseller_plan_desc: "Développez votre activité avec notre panneau web revendeur dédié. Activez les téléviseurs de vos clients en quelques clics.",
    reseller_feat_1: "Activation immédiate des téléviseurs de vos clients via le panel web",
    reseller_feat_2: "Contrôle total sur les adresses MAC et playlists associées",
    reseller_feat_3: "Marges bénéficiaires élevées pour installateurs et revendeurs",
    btn_reseller_panel: "Connexion Espace Revendeur",
    btn_order_pack: "Commander un Pack",
    pay_rib_title: "Virement Bancaire (RIB Maroc)",
    pay_paypal_title: "PayPal International & Cartes",
    pay_wa_title: "Confirmation WhatsApp 24/7",
    dl_badge: "TÉLÉCHARGEMENT DIRECT DE L'APPLICATION",
    dl_title: "Prêt pour l'Expérience IPTV Ultime ?",
    dl_desc: "Téléchargez Dreamania Player dès aujourd'hui. Profitez d'une lecture 4K fluide, du classement personnalisable des chaînes et du multi-audio sans publicité.",
    dl_btn_apk: "Télécharger Dreamania Player APK",
    dl_btn_reseller: "Espace Revendeur",
    dl_btn_admin: "Panel Admin",
    qr_title: "Scannez pour Télécharger",
    qr_desc: "Pointez votre smartphone ou saisissez le code 7454701 sur Fire TV.",
    faq_sub: "QUESTIONS FRÉQUEMMENT POSÉES",
    faq_title: "Vous avez des questions ? Nous avons les réponses",
    faq_desc: "Retrouvez les informations clés concernant l'installation, les playlists et les raccourcis.",
    faq1_q: "Comment installer Dreamania Player sur Amazon Fire TV Stick ?",
    faq1_a: "Ouvrez l'application gratuite Downloader sur votre Fire TV, saisissez simplement notre code court 7454701 ou l'adresse https://dm.lumineasy.com/download, puis validez. L'installation démarrera automatiquement.",
    faq2_q: "L'application Dreamania Player inclut-elle des chaînes de télévision ?",
    faq2_a: "Non. Dreamania Player est un lecteur multimédia haute performance pur. Il n'héberge et ne fournit aucun flux vidéo. Vous devez utiliser vos propres identifiants d'abonnement légaux via l'API Xtream Codes ou au format M3U.",
    faq3_q: "Comment ajouter une chaîne aux Favoris avec la télécommande ?",
    faq3_a: "Dans la liste des chaînes, sélectionnez simplement la chaîne de votre choix et appuyez sur le Bouton Rouge ou effectuez un appui long sur le bouton OK de votre télécommande. Le cœur passe au rouge (❤️) et la chaîne apparaît instantanément en tête de liste dans '❤️ Favoris'.",
    faq4_q: "Comment réorganiser ou déplacer l'ordre des chaînes ?",
    faq4_a: "Appuyez sur le Bouton Jaune de votre télécommande sur n'importe quelle chaîne. Un panneau s'ouvre pour vous permettre de la monter, la descendre, la placer tout en haut ou activer le mode interactif au curseur.",
    faq5_q: "Comment fonctionne le système de crédits pour les revendeurs ?",
    faq5_a: "Pour les revendeurs, 1 crédit équivaut à 1 an d'activation pour un téléviseur client. Vous achetez des packs de crédits avec des remises quantitatives (à partir de 10 crédits), puis vous activez directement les adresses MAC de vos clients depuis votre portail revendeur web.",
    footer_about: "Moteur multimédia ultra-réactif pour Smart TV et mobiles calibré pour une latence minimale, décodage matériel 4K et expérience cinéma sans compromis.",
    footer_col_portals: "Portails & Accès Direct",
    footer_link_device: "Mon Appareil (Playlists TV)",
    footer_link_reseller: "Espace Revendeur & Crédits",
    footer_link_admin: "Panel Administrateur",
    footer_col_product: "Produit & Tarifs",
    footer_link_sub: "Abonnements 1 An & Lifetime",
    footer_link_dl: "Téléchargement APK Direct",
    footer_col_compat: "Compatibilité",
    legal_title: "Avis Légal & Clause de Non-Responsabilité",
    legal_desc: "Dreamania Player ne fournit, n'héberge, ne vend et ne diffuse aucun contenu audiovisuel ou flux multimédia. Dreamania Player est strictement une application logicielle de lecture vidéo. Les utilisateurs doivent fournir leurs propres listes de lecture légalement acquises via Xtream Codes ou M3U. Dreamania Player n'approuve ni ne cautionne aucune diffusion non autorisée.",
    modal_pay_title: "Modes de Paiement & Validation",
    modal_pay_sub: "Réglez par Virement Bancaire (RIB Maroc) ou PayPal International",
    modal_wa_hint: "Envoyez la capture du reçu pour activation immédiate :"
  },
  en: {
    nav_features: "Features",
    nav_app_tour: "App Tour",
    nav_engine: "Media3 Engine",
    nav_plans: "Plans",
    nav_faq: "FAQ",
    nav_device: "My Device",
    nav_device_short: "Device",
    nav_reseller_portal: "Reseller",
    nav_admin: "Admin",
    mobile_nav_device: "Device",
    mobile_nav_reseller: "Reseller",
    mobile_nav_admin: "Admin",
    hero_badge: "OFFICIAL BROADCAST ENGINE • ANDROID TV & FIRE TV CERTIFIED",
    hero_title_1: "The Next–Generation ",
    hero_title_grad: "IPTV Cinema Hub ",
    hero_title_2: "Built for Your Smart TV",
    hero_desc: "Engineered for instantaneous zapping, 4K 60FPS sports streaming, and seamless TMDb cinema VOD. Powered by Google Media3 ExoPlayer with zero bloatware and dedicated Fire TV remote D-Pad navigation.",
    hero_btn_download: "Download APK (v1.0.0 Pro)",
    hero_btn_manage: "Manage TV / Upload Playlist",
    hero_btn_pricing: "Pricing (€5 / 50 DH – €15 / 150 DH)",
    portal_hub_title: "Dedicated Access Portals & Hubs",
    portal_hub_desc: "Direct access for TV users, wholesale resellers, and master platform administrators.",
    card_device_badge: "End-Users & TV Owners",
    card_device_title: "My Device & Playlists",
    card_device_desc: "Enter your MAC Address & Device Key to upload M3U or Xtream playlists and activate your TV subscription.",
    card_device_btn: "Access My Device Portal",
    card_reseller_badge: "Resellers & B2B",
    card_reseller_title: "Reseller Portal",
    card_reseller_desc: "Log in to your wholesale panel to manage credits (1 credit = 1 year) and activate customer devices in seconds.",
    card_reseller_btn: "Reseller Login",
    card_admin_badge: "Master Control",
    card_admin_title: "Admin Control Panel",
    card_admin_desc: "Global server supervision, DNS routing, manual device activations, RIB/PayPal validation, and settings.",
    card_admin_btn: "Master Admin Login",
    card_tv_badge: "Fire TV & Android Box",
    card_tv_title: "Downloader Code: ",
    card_tv_desc: "Simply enter this code into the free Downloader app on your Fire TV Stick or Android TV box to install.",
    card_tv_btn: "Download APK",
    spec_version: "Version: <strong class="text-primary font-semibold">v1.0.0 Pro</strong>",
    spec_zapping: "Zapping Latency: <strong class="text-neon-cyan-bright font-semibold">0.24s Instant</strong>",
    spec_compat: "Compatibility: <strong class="text-on-surface font-semibold">Fire TV, Android TV, Google TV</strong>",
    spec_no_ads: "Zero Ads • Clean Middleware",
    spec_fav: "One-Touch ❤️ Remote Pinning",
    remote_red: "Red: Favorite (❤️)",
    remote_yellow: "Yellow: Reorder",
    bench_sub: "HIGH PERFORMANCE BROADCAST ENGINE",
    bench_title: "Zero Latency Cinema & Live Sports Infrastructure",
    bench_desc: "Benchmark testing against legacy IPTV players proves Dreamania Player's custom Media3 caching architecture switches channels up to 4x faster with zero frame drops.",
    bench1_title: "Channel Zapping Speed",
    bench1_speed: "4.2x FASTER",
    bench1_desc: "Instant TS/HLS pre-buffering pipeline allows seamless channel flipping without blank screens.",
    bench2_title: "Playlist Parsing Time",
    bench2_badge: "50K+ CHANNELS",
    bench2_desc: "Instant JSON indexing for massive Xtream Codes playlists and multi-megabyte M3U files.",
    bench3_title: "Hardware Resource Footprint",
    bench3_badge: "ZERO BLOAT",
    bench3_desc: "Featherlight APK size optimized for budget Fire TV Sticks with low memory thresholds.",
    leanback_title: "Certified Leanback Experience",
    leanback_desc: "Designed and tested specifically for TV launchers with Android 5.0 through Android 14+ compatibility.",
    leanback_btn: "Download Leanback APK",
    feat_sub: "ENGINEERED FOR POWER",
    feat_title: "Built From The Ground Up for Perfection",
    feat_desc: "Experience an IPTV middleware and Android media player that refuses to compromise on speed, design, or user convenience.",
    feat1_title: "Favorites & Channel Reorder",
    feat1_desc: "Pin any channel to Favorites with one press (Red button ❤️). Reorder channels to your preference with the Yellow button on your remote.",
    feat2_title: "Media3 ExoPlayer Engine",
    feat2_desc: "Native hardware-accelerated decoding for H.265/HEVC, 4K UHD, HDR10, and Dolby Vision with multi-audio commentary tracks and subtitles.",
    feat3_title: "Xtream Codes & M3U Universal",
    feat3_desc: "Native support for Xtream Codes API (Server URL, User, Pass) and M3U/M3U8 URLs with seamless multi-account switching.",
    feat4_title: "Smart EPG & TMDb Enrichment",
    feat4_desc: "Live electronic program guide with current broadcast status, TMDb metadata, episode durations, ratings, and HD poster art.",
    feat5_title: "4 Handcrafted Visual Themes",
    feat5_desc: "Customize your look: Dreamania Purple, Cyber Neon, Onyx Dark (zero gray bleed on OLED screens), and Royal Gold.",
    feat6_title: "Zero-Stutter D-Pad Navigation",
    feat6_desc: "No more losing remote control: the D-Pad navigates reliably even with on-screen display (OSD) overlays open.",
    tour_sub: "GENUINE INTERFACE PREVIEW",
    tour_title: "Visual Tour of Dreamania Player Pro",
    tour_desc: "Authentic 1080p screenshots captured directly from 4K Android TV and Fire TV devices.",
    tab_live: "📺 Live TV & EPG Guide",
    tab_movies: "🎬 Cinema & Movies",
    tab_series: "🍿 TV Series & Seasons",
    tab_osd: "⚡ Player & Audio Tracks",
    shot_live_badge: "FAST ZAPPING & FAVORITES",
    shot_live_title: "Real-Time EPG Channel Guide",
    shot_live_desc: "Instant access to all channel categories with live program previews, one-touch favorite pinning, and reordering.",
    shot_vod_badge: "4K CINEMA",
    shot_vod_title: "TMDb-Enriched Cinema VOD",
    shot_vod_desc: "Comprehensive film cards: HD official poster, plot summary, viewer ratings, and automatic resume playback.",
    shot_series_badge: "SERIES & EPISODES",
    shot_series_title: "Season & Episode Duration Navigation",
    shot_series_desc: "Full-screen responsive layout for TV shows showing exact episode runtimes, original air dates, and next episode auto-play.",
    shot_osd_badge: "INTUITIVE CONTROLS",
    shot_osd_title: "Non-Blocking Player OSD",
    shot_osd_desc: "Fine-tune playback without stopping your stream: select Dolby audio tracks, external subtitles, and screen aspect ratios.",
    setup_sub: "SIMPLE 3-STEP SETUP",
    setup_title: "Up & Streaming in Under 2 Minutes",
    setup_desc: "Getting started with Dreamania Player is straightforward. No complex configurations or rooting required.",
    step1_title: "Download & Install APK",
    step1_desc: "Download DreamaniaPlayer.apk or open the Downloader app on your TV and type code 7454701.",
    step2_title: "Connect Your Playlist",
    step2_desc: "Enter your Xtream Codes or M3U playlist link on your TV screen or via our web portal My Device.",
    step3_title: "Enjoy 4K Cinema Streaming",
    step3_desc: "Browse your Live Channels, Movies, and TV Shows with automated EPG updates and personalized favorites.",
    pricing_sub: "SIMPLE & TRANSPARENT",
    pricing_title: "Pricing & Plans",
    pricing_desc: "Activate your Smart TV device directly or purchase wholesale reseller credits at the best market rates.",
    user_plan_badge: "FOR SMART TV OWNERS 📺",
    user_plan_sub: "Instant MAC Activation",
    user_plan_title: "End-User Activation",
    user_plan_desc: "Inspired by the simple iboplayer.com workflow: enter your TV's MAC Address & Device Key, upload any M3U or Xtream playlist, and enjoy instant 4K playback.",
    plan_1y_label: "1 Year Plan",
    plan_1y_sub: "12 Months Full Access",
    plan_life_label: "VIP Lifetime",
    plan_life_sub: "Never Expires • Lifetime Updates",
    user_feat_1: "Unlimited M3U & Xtream Codes playlists",
    user_feat_2: "Pin code parental protection (PIN 0000 by default)",
    user_feat_3: "Real-time EPG & Channel rearrangement",
    user_feat_4: "Secure payment via PayPal & Moroccan Bank Transfer (RIB)",
    btn_manage_device: "Manage Device & Activate",
    reseller_plan_badge: "FOR RESELLERS & DISTRIBUTORS ⚡",
    reseller_plan_sub: "1 Credit = 1 Year Activation",
    reseller_plan_title: "Reseller Wholesale Credits",
    reseller_plan_desc: "Launch your own IPTV activation business with a dedicated reseller web panel. Activate client devices in seconds with high profit margins.",
    reseller_feat_1: "Instant client activation via web panel",
    reseller_feat_2: "Full control over client playlists & device MACs",
    reseller_feat_3: "High profit margins for installers & retailers",
    btn_reseller_panel: "Reseller Portal Login",
    btn_order_pack: "Order Credit Pack",
    pay_rib_title: "Moroccan Bank Transfer (RIB)",
    pay_paypal_title: "PayPal & International Cards",
    pay_wa_title: "24/7 WhatsApp Confirmation",
    dl_badge: "INSTANT APP DOWNLOAD",
    dl_title: "Ready for the Ultimate IPTV Experience?",
    dl_desc: "Download Dreamania Player today. Enjoy seamless 4K playback, customizable channel ordering, and multi-audio support with zero bloatware.",
    dl_btn_apk: "Download Dreamania Player APK",
    dl_btn_reseller: "Reseller Portal",
    dl_btn_admin: "Admin Panel",
    qr_title: "Scan to Download",
    qr_desc: "Point your phone camera or enter code 7454701 into your Fire TV Downloader app.",
    faq_sub: "FREQUENTLY ASKED QUESTIONS",
    faq_title: "Got Questions? We Have Answers",
    faq_desc: "Find quick answers regarding installation, playlist compatibility, and player controls.",
    faq1_q: "How do I install Dreamania Player on Amazon Fire TV Stick?",
    faq1_a: "Open the free Downloader app on your Fire TV, enter our official short code 7454701 or URL https://dm.lumineasy.com/download, and select Install. Ensure 'Install Unknown Apps' is enabled in your Fire TV Developer Options.",
    faq2_q: "Does Dreamania Player include any built-in TV channels?",
    faq2_a: "No. Dreamania Player is a pure, high-performance media player. It does not provide or host any media streams. You must enter your own legal IPTV playlist credentials via Xtream Codes API or M3U playlist format.",
    faq3_q: "How do I add channels to my Favorites?",
    faq3_a: "While in the channel list, select any channel and press the Red button or long-press the OK button on your remote. The heart turns red (❤️) and the channel will automatically appear in your pinned '❤️ Favorites' category!",
    faq4_q: "How do I reorder or move channels in the list?",
    faq4_a: "Select a channel and press the Yellow button on your TV remote. A convenient modal appears allowing you to move the channel Up, Down, Move to Top, or enter interactive remote mode.",
    faq5_q: "How does the reseller credit system work?",
    faq5_a: "For resellers, 1 credit equals 1 year of activation for a client device. You purchase credit packages with tiered wholesale discounts (starting at 10 credits), and activate client MAC addresses directly from your web portal.",
    footer_about: "Next-generation, ultra-responsive smart TV and mobile media engine calibrated for low latency, hardware decoding, and cinematic home theater experiences.",
    footer_col_portals: "Portals & Direct Access",
    footer_link_device: "My Device (TV Playlists)",
    footer_link_reseller: "Reseller Portal & Credits",
    footer_link_admin: "Admin Control Panel",
    footer_col_product: "Product & Plans",
    footer_link_sub: "1-Year & Lifetime Subscriptions",
    footer_link_dl: "Direct APK Download",
    footer_col_compat: "Compatibility",
    legal_title: "Legal Notice & IPTV Disclaimer",
    legal_desc: "Dreamania Player does not provide, host, or contain any media or content. Dreamania Player is strictly an advanced media playback utility software. Users must supply their own legally obtained streams, M3U playlists, or Xtream Codes credentials. Dreamania Player endorses no illegal streaming or copyright infringements.",
    modal_pay_title: "Payment Methods & Verification",
    modal_pay_sub: "Pay via Moroccan Bank Transfer (RIB) or International PayPal",
    modal_wa_hint: "Send proof of transfer for instant activation:"
  }
};

let currentLang = 'fr';

function setLanguage(lang) {
  if (!translations[lang]) return;
  currentLang = lang;
  try {
    localStorage.setItem('dreamania_lang', lang);
  } catch (e) {}

  // Update HTML lang attribute
  document.documentElement.lang = lang;

  // Update header buttons
  const btnFr = document.getElementById('lang-btn-fr');
  const btnEn = document.getElementById('lang-btn-en');
  if (btnFr && btnEn) {
    if (lang === 'fr') {
      btnFr.className = "px-2 py-1 rounded-lg text-[11px] font-bold transition-all flex items-center gap-1 bg-neon-cyan-bright/20 text-neon-cyan-bright border border-neon-cyan-bright/40 shadow-sm";
      btnEn.className = "px-2 py-1 rounded-lg text-[11px] font-bold transition-all flex items-center gap-1 text-on-surface-variant hover:text-white";
    } else {
      btnEn.className = "px-2 py-1 rounded-lg text-[11px] font-bold transition-all flex items-center gap-1 bg-neon-cyan-bright/20 text-neon-cyan-bright border border-neon-cyan-bright/40 shadow-sm";
      btnFr.className = "px-2 py-1 rounded-lg text-[11px] font-bold transition-all flex items-center gap-1 text-on-surface-variant hover:text-white";
    }
  }

  // Update mobile toggle label
  const mobileFlag = document.getElementById('mobile-lang-flag');
  const mobileLabel = document.getElementById('mobile-lang-label');
  if (mobileFlag) mobileFlag.textContent = lang === 'fr' ? '🇫🇷' : '🇬🇧';
  if (mobileLabel) mobileLabel.textContent = lang.toUpperCase();

  // Apply translations to all data-i18n elements
  const dict = translations[lang];
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    if (dict[key] !== undefined) {
      if (dict[key].includes('<') && dict[key].includes('>')) {
        el.innerHTML = dict[key];
      } else {
        el.textContent = dict[key];
      }
    }
  });
}

function toggleLanguage() {
  setLanguage(currentLang === 'fr' ? 'en' : 'fr');
}

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
  let savedLang = 'fr';
  try {
    savedLang = localStorage.getItem('dreamania_lang') || 'fr';
  } catch (e) {}
  setLanguage(savedLang);

  // Screenshots Tab Switching
  document.querySelectorAll('#tab-controls .tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const targetTab = btn.getAttribute('data-tab');
      document.querySelectorAll('#tab-controls .tab-btn').forEach(b => {
        b.classList.remove('bg-surface-container-high', 'text-primary', 'shadow-sm');
        b.classList.add('text-on-surface-variant');
      });
      btn.classList.add('bg-surface-container-high', 'text-primary', 'shadow-sm');
      btn.classList.remove('text-on-surface-variant');

      document.querySelectorAll('#tab-content .tab-panel').forEach(panel => {
        if (panel.id === targetTab) {
          panel.classList.remove('hidden');
        } else {
          panel.classList.add('hidden');
        }
      });
    });
  });

  // FAQ Accordion Interaction
  document.querySelectorAll('#faq-accordion .faq-toggle').forEach(button => {
    button.addEventListener('click', () => {
      const content = button.nextElementSibling;
      const icon = button.querySelector('.material-symbols-outlined');
      const isOpen = !content.classList.contains('hidden');

      document.querySelectorAll('#faq-accordion .faq-content').forEach(c => c.classList.add('hidden'));
      document.querySelectorAll('#faq-accordion .material-symbols-outlined').forEach(i => i.style.transform = 'rotate(0deg)');

      if (!isOpen) {
        content.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
      }
    });
  });
});

function switchPaymentTab(tab) {
  if (tab === 'rib') {
    document.getElementById('content-rib').classList.remove('hidden');
    document.getElementById('content-paypal').classList.add('hidden');
    document.getElementById('tab-rib').classList.add('bg-surface-container-high', 'text-primary');
    document.getElementById('tab-rib').classList.remove('text-on-surface-variant');
    document.getElementById('tab-paypal').classList.remove('bg-surface-container-high', 'text-primary');
    document.getElementById('tab-paypal').classList.add('text-on-surface-variant');
  } else {
    document.getElementById('content-rib').classList.add('hidden');
    document.getElementById('content-paypal').classList.remove('hidden');
    document.getElementById('tab-paypal').classList.add('bg-surface-container-high', 'text-primary');
    document.getElementById('tab-paypal').classList.remove('text-on-surface-variant');
    document.getElementById('tab-rib').classList.remove('bg-surface-container-high', 'text-primary');
    document.getElementById('tab-rib').classList.add('text-on-surface-variant');
  }
}

function copyToClipboard(text, msg) {
  navigator.clipboard.writeText(text).then(() => {
    showToast(msg || 'Copié dans le presse-papier !');
  }).catch(() => {
    alert(text);
  });
}

function showToast(msg) {
  let toast = document.getElementById('live-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'live-toast';
    toast.className = 'fixed bottom-20 md:bottom-6 right-6 z-50 px-4 py-2.5 rounded-xl bg-neon-cyan-bright text-surface-container-lowest font-bold text-xs shadow-2xl transition-all transform duration-300';
    document.body.appendChild(toast);
  }
  toast.textContent = msg;
  toast.style.opacity = '1';
  toast.style.transform = 'translateY(0)';
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(10px)';
  }, 2500);
}
</script>

</body>
</html>
