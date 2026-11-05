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

<html class="dark" lang="en"><head><meta charset="utf-8"/><meta content="width=device-width, initial-scale=1.0" name="viewport"/><meta content="web_standard" name="shell-type"/><title>Dreamania Player Pro</title><link href="https://fonts.googleapis.com" rel="preconnect"/><link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/><link href="https://fonts.googleapis.com/css2?family=Inter:wght@600;700&amp;family=Outfit:wght@600;700;800&amp;family=Plus+Jakarta+Sans:wght@400;500;600&amp;display=swap" rel="stylesheet"/><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/><style>@layer base{html,body{margin:0;padding:0;}body{overscroll-behavior:none;}main>:first-child{margin-top:0!important;}main>:last-child{margin-bottom:0!important;}}::-webkit-scrollbar{display:none;}</style><script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script><script id="tailwind-config">tailwind.config={darkMode:"class",theme:{extend:{colors:{"surface-glass-stroke":"rgba(255, 255, 255, 0.08)","secondary":"#d2bbff","remote-key-yellow":"#F59E0B","primary-fixed":"#9cf0ff","surface-obsidian-subtle":"#0D0E1A","inverse-primary":"#006875","tertiary-fixed":"#ffddb8","electric-violet-glow":"#8B5CF6","surface-container-high":"#292933","surface-dim":"#13121c","remote-key-red":"#EF4444","surface-container-low":"#1b1b24","on-tertiary":"#472a00","on-secondary-fixed-variant":"#5a00c6","error":"#ffb4ab","on-tertiary-fixed-variant":"#653e00","tertiary-container":"#ffc681","background":"#13121c","on-error-container":"#ffdad6","primary-fixed-dim":"#00daf3","on-secondary":"#3f008e","secondary-container":"#6001d1","primary-container":"#00e5ff","on-primary-fixed-variant":"#004f58","primary":"#c3f5ff","error-container":"#93000a","surface-container":"#1f1f29","on-primary-fixed":"#001f24","on-surface":"#e4e1ef","on-primary-container":"#00626e","surface-container-lowest":"#0d0d17","on-background":"#e4e1ef","on-tertiary-container":"#7e4e00","inverse-on-surface":"#302f3a","surface-container-highest":"#34343e","tertiary-fixed-dim":"#ffb95f","outline":"#849396","on-primary":"#00363d","surface-glass-fill":"rgba(13, 14, 26, 0.65)","outline-variant":"#3b494c","secondary-fixed-dim":"#d2bbff","surface-variant":"#34343e","neon-cyan-bright":"#38BDF8","surface-tint":"#00daf3","on-secondary-fixed":"#25005a","secondary-fixed":"#eaddff","surface-bright":"#393843","on-error":"#690005","on-surface-variant":"#bac9cc","on-tertiary-fixed":"#2a1700","on-secondary-container":"#c9aeff","status-active-green":"#10B981","tertiary":"#ffe9d3","surface":"#13121c","inverse-surface":"#e4e1ef"},borderRadius:{"DEFAULT":"0.25rem","lg":"0.5rem","xl":"0.75rem","full":"9999px"},spacing:{"space-sm":"0.75rem","space-md":"1.25rem","space-xs":"0.375rem","space-xl":"3rem","space-lg":"2rem","margin":"2.5rem","gutter":"1.5rem"},fontFamily:{"headline-md":["Outfit"],"body-lg":["Plus Jakarta Sans"],"label-lg":["Inter"],"headline-lg":["Outfit"],"body-md":["Plus Jakarta Sans"],"headline-xl":["Outfit"],"body-sm":["Plus Jakarta Sans"],"label-md":["Inter"],"label-sm":["Inter"],"headline-sm":["Outfit"],"headline-xl-mobile":["Outfit"],"headline-lg-mobile":["Outfit"]},fontSize:{"headline-md":["28px",{"lineHeight":"36px","fontWeight":"600"}],"body-lg":["18px",{"lineHeight":"28px","fontWeight":"400"}],"label-lg":["14px",{"lineHeight":"20px","fontWeight":"600"}],"headline-lg":["40px",{"lineHeight":"48px","fontWeight":"700"}],"body-md":["15px",{"lineHeight":"22px","fontWeight":"400"}],"headline-xl":["56px",{"lineHeight":"64px","fontWeight":"800"}],"body-sm":["13px",{"lineHeight":"18px","fontWeight":"400"}],"label-md":["12px",{"lineHeight":"16px","fontWeight":"600"}],"label-sm":["11px",{"lineHeight":"14px","fontWeight":"700"}],"headline-sm":["22px",{"lineHeight":"28px","fontWeight":"600"}],"headline-xl-mobile":["36px",{"lineHeight":"42px","fontWeight":"800"}],"headline-lg-mobile":["28px",{"lineHeight":"34px","fontWeight":"700"}]}}}};</script></head><body class="bg-background font-body-md text-on-surface antialiased selection:bg-primary-container selection:text-on-primary-container min-h-screen">
<!-- ======================================================== -->
<!-- FLOATING CYBER DOCK NAVIGATION                            -->
<!-- ======================================================== -->
<header class="fixed top-3 left-0 right-0 z-50 px-4 sm:px-6 pointer-events-none">
<div class="max-w-7xl mx-auto flex items-center justify-between gap-4 p-2 sm:p-2.5 rounded-2xl bg-surface-obsidian-subtle/85 backdrop-blur-2xl border border-surface-glass-stroke shadow-[0_12px_40px_rgba(0,0,0,0.65)] pointer-events-auto">
<!-- Brand Logo with Pulsing Neon Aura -->
<div class="flex items-center gap-3 pl-2">
<a class="flex items-center gap-2.5 group relative" href="device.php">
<div class="relative w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-surface-container-highest/60 flex items-center justify-center p-1 border border-neon-cyan-bright/30 shadow-[0_0_20px_rgba(56,189,248,0.4)] group-hover:shadow-[0_0_28px_rgba(0,229,255,0.7)] transition-all">
<img alt="Dreamania Player Official Logo" class="w-full h-full object-contain filter drop-shadow-[0_0_8px_rgba(0,229,255,0.8)]" src="assets/img/icon.png"/>
</div>
<div class="flex flex-col">
<span class="font-headline-sm text-base sm:text-lg font-extrabold text-white tracking-wider uppercase drop-shadow-[0_0_12px_rgba(0,229,255,0.5)]">DREAMANIA</span>
<span class="text-[10px] tracking-widest text-neon-cyan-bright font-bold font-label-sm uppercase -mt-1">BROADCAST PRO</span>
</div>
</a>
<!-- Animated Icon Version Badge -->
<div class="hidden md:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-container/70 border border-primary-container/20 shadow-inner">
<img alt="Animated Core" class="w-4 h-4 object-contain animate-spin" src="assets/img/icon.png" style="animation-duration: 9s;"/>
<span class="w-1.5 h-1.5 rounded-full bg-status-active-green animate-pulse"></span>
<span class="font-label-sm text-[11px] uppercase tracking-wider text-neon-cyan-bright font-semibold">v1.0.0 PRO</span>
</div>
</div>
<!-- Centered Pill Nav -->
<nav class="hidden lg:flex items-center gap-1 p-1 rounded-xl bg-surface-container-lowest/80 border border-surface-glass-stroke">
<a class="px-3.5 py-1.5 rounded-lg text-primary font-label-md text-label-md bg-surface-container-high/80 shadow-[0_0_12px_rgba(56,189,248,0.3)] transition-all" href="#features">Features</a>
<a class="px-3.5 py-1.5 rounded-lg text-on-surface-variant font-label-md text-label-md hover:text-on-surface hover:bg-surface-container/60 transition-all" href="#screenshots">App Tour</a>
<a class="px-3.5 py-1.5 rounded-lg text-on-surface-variant font-label-md text-label-md hover:text-on-surface hover:bg-surface-container/60 transition-all" href="#broadcast-hub">Live Engine</a>
<a class="px-3.5 py-1.5 rounded-lg text-on-surface-variant font-label-md text-label-md hover:text-on-surface hover:bg-surface-container/60 transition-all" href="#pricing">Plans</a>
<a class="px-3.5 py-1.5 rounded-lg text-on-surface-variant font-label-md text-label-md hover:text-on-surface hover:bg-surface-container/60 transition-all" href="#reseller">Reseller</a>
<a class="px-3.5 py-1.5 rounded-lg text-on-surface-variant font-label-md text-label-md hover:text-on-surface hover:bg-surface-container/60 transition-all" href="#faq">FAQ</a>
</nav>
<!-- High-Conversion Dual CTA -->
<div class="flex items-center gap-2 sm:gap-2.5 pr-1">
<a class="flex items-center gap-1.5 px-3 sm:px-4 py-2 rounded-xl bg-gradient-to-r from-remote-key-yellow to-tertiary-fixed-dim text-surface-container-lowest font-label-md text-label-md font-bold shadow-[0_0_18px_rgba(245,158,11,0.45)] hover:shadow-[0_0_26px_rgba(245,158,11,0.7)] hover:brightness-110 active:scale-95 transition-all" href="#device-activation">
<span class="material-symbols-outlined text-[18px]">tv</span>
<span class="hidden sm:inline">Device Portal</span>
<span class="sm:hidden">Portal</span>
</a>
<a class="flex items-center gap-1.5 px-3.5 sm:px-4 py-2 rounded-xl bg-gradient-to-r from-secondary-container via-electric-violet-glow to-primary-container text-on-primary-container font-label-md text-label-md font-bold shadow-[0_0_24px_rgba(139,92,246,0.5)] hover:shadow-[0_0_32px_rgba(0,229,255,0.7)] hover:scale-105 active:scale-95 transition-all" href="#download">
<span class="material-symbols-outlined text-[18px]">bolt</span>
<span>Download APK</span>
</a>
</div>
</div>
</header>
<main class="w-full pt-28 bg-background"><div class="flex flex-col w-full text-on-surface">
<!-- ======================================================== -->
<!-- HERO SECTION WITH OFFICIAL ASSETS & BROADCAST HUB        -->
<!-- ======================================================== -->
<section class="relative w-full overflow-hidden pb-space-xl">
<!-- WebGL Ambient Shader Layer -->

<!-- Ambient Glow Elements -->
<div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_60%_at_50%_-15%,rgba(139,92,246,0.32),rgba(19,18,28,0))] pointer-events-none"></div>
<div class="absolute top-1/4 right-5 w-96 h-96 rounded-full bg-neon-cyan-bright/15 blur-[130px] pointer-events-none"></div>
<div class="absolute top-1/3 left-5 w-80 h-80 rounded-full bg-electric-violet-glow/20 blur-[130px] pointer-events-none"></div>
<div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-10 flex flex-col items-center text-center">
<!-- Official Android TV Release Badge with Animated Icon -->
<div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-surface-obsidian-subtle/95 border border-primary-container/30 shadow-[0_0_25px_rgba(56,189,248,0.35)] mb-6">
<img alt="Dreamania Animated Symbol" class="w-4 h-4 object-contain animate-spin" src="assets/img/icon.png" style="animation-duration: 8s;"/>
<span class="w-2 h-2 rounded-full bg-status-active-green animate-ping"></span>
<span class="font-label-sm text-label-sm uppercase tracking-widest text-primary-fixed font-bold">
      OFFICIAL BROADCAST ENGINE • ANDROID TV &amp; FIRE TV CERTIFIED
    </span>
</div>
<!-- Main Headline with Cyber Neon Broadcast Focus -->
<h1 class="font-headline-xl text-headline-xl md:text-[62px] md:leading-[1.1] max-w-5xl tracking-tight text-on-surface font-extrabold mb-6">
    The Next–Generation IPTV Cinema
    <span class="bg-gradient-to-r from-neon-cyan-bright via-primary to-electric-violet-glow bg-clip-text text-transparent drop-shadow-[0_0_35px_rgba(0,229,255,0.5)]">
      Broadcast Hub
    </span>
    Built for Your Smart TV
  </h1>
<!-- Subtitle -->
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-3xl leading-relaxed mb-8">
    Engineered for instantaneous zapping, 4K 60FPS sports streaming, and seamless TMDb cinema VOD. Powered by Google Media3 ExoPlayer with zero bloatware and dedicated Fire TV remote D-Pad navigation.
  </p>
<!-- CTA Button Row -->
<div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6 mb-10">
<a class="group relative px-6 py-3.5 rounded-xl bg-gradient-to-r from-secondary-container via-electric-violet-glow to-primary-container text-on-primary-container font-label-lg text-label-lg font-bold shadow-[0_0_30px_rgba(139,92,246,0.55)] hover:shadow-[0_0_42px_rgba(0,229,255,0.7)] hover:scale-105 active:scale-95 transition-all flex items-center gap-2" href="#download">
<span class="material-symbols-outlined text-[20px]">download</span>
<span>Download APK (v1.0.0 Pro)</span>
</a>
<a class="px-6 py-3.5 rounded-xl bg-gradient-to-r from-remote-key-yellow to-tertiary-fixed-dim text-surface-container-lowest font-label-lg text-label-lg font-bold shadow-[0_0_24px_rgba(245,158,11,0.5)] hover:brightness-110 hover:scale-105 active:scale-95 transition-all flex items-center gap-2" href="#device-activation">
<span class="material-symbols-outlined text-[20px]">tv</span>
<span>Manage TV Device / Upload Playlist</span>
</a>
<a class="px-6 py-3.5 rounded-xl bg-surface-container-high/70 hover:bg-surface-container-highest text-primary font-label-lg text-label-lg border border-surface-glass-stroke shadow-md hover:shadow-[0_0_20px_rgba(195,245,255,0.25)] transition-all flex items-center gap-2" href="#pricing">
<span class="material-symbols-outlined text-[20px] text-neon-cyan-bright">credit_card</span>
<span>Pricing (€5 / 50 DH – €15 / 150 DH)</span>
</a>
</div>
<!-- Tech Specs Monospace Strip -->
<div class="w-full max-w-5xl p-3 sm:p-4 rounded-2xl bg-surface-container-lowest/85 backdrop-blur-md border border-surface-glass-stroke shadow-xl flex flex-wrap items-center justify-center gap-x-6 gap-y-3 font-label-md text-label-md text-on-surface-variant mb-14">
<div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-remote-key-yellow text-[16px]">bolt</span><span>Version: <strong class="text-primary font-semibold">v1.0.0 Pro</strong></span></div>
<span class="text-outline-variant hidden sm:inline">•</span>
<div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-secondary text-[16px]">speed</span><span>Zapping Latency: <strong class="text-neon-cyan-bright font-semibold">0.24s Instant</strong></span></div>
<span class="text-outline-variant hidden sm:inline">•</span>
<div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-neon-cyan-bright text-[16px]">tv</span><span>Compatibility: <strong class="text-on-surface font-semibold">Fire TV, Android TV, Google TV</strong></span></div>
<span class="text-outline-variant hidden sm:inline">•</span>
<div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-status-active-green text-[16px]">security</span><span>Zero Ads • Clean Middleware</span></div>
<span class="text-outline-variant hidden sm:inline">•</span>
<div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-remote-key-red text-[16px]">favorite</span><span>One-Touch ❤️ Remote Pinning</span></div>
</div>
<!-- High-End Bezel TV Mockup with Real ExoPlayer & Genuine Metadata -->
<div class="w-full max-w-6xl relative group">
<!-- Ambient Neon Halo -->
<div class="absolute -inset-4 bg-gradient-to-r from-electric-violet-glow/45 via-neon-cyan-bright/35 to-remote-key-yellow/30 rounded-3xl blur-2xl opacity-80 group-hover:opacity-100 transition-opacity duration-700"></div>
<!-- TV Outer Shell Bezel -->
<div class="relative rounded-2xl bg-[#090812] border-2 border-surface-container-highest/80 p-3 sm:p-5 shadow-2xl overflow-hidden">
<!-- TV Header Bar (App Launcher UI) -->
<div class="flex items-center justify-between pb-3 sm:pb-4 border-b border-surface-container-highest/60 font-label-md text-label-md">
<div class="flex items-center gap-3">
<img alt="Dreamania Pro Banner" class="h-6 w-auto object-contain hidden sm:block rounded" src="assets/img/banner.png"/>
<span class="w-2.5 h-2.5 rounded-full bg-status-active-green shadow-[0_0_8px_#10B981]"></span>
<span class="font-headline-sm text-headline-sm text-primary font-bold tracking-wider">DREAMANIA 4K PRO</span>
<span class="px-2 py-0.5 rounded bg-surface-container-high text-neon-cyan-bright font-label-sm text-label-sm border border-neon-cyan-bright/30">MEDIA3 ENGINE</span>
</div>
<div class="flex items-center gap-3 font-label-sm text-on-surface-variant">
<span class="flex items-center gap-1 text-remote-key-yellow font-bold"><span class="material-symbols-outlined text-[16px]">verified</span> VIP LIFETIME</span>
<span class="text-on-surface font-mono">21:42:09</span>
</div>
</div>
<!-- TV Screen Frame with Live Match & Player HUD -->
<div class="relative w-full aspect-[16/9] rounded-xl overflow-hidden bg-surface-dim">
<img class="w-full h-full object-cover" data-alt="Cinematic high quality 4K smart television screen mock-up showing futuristic cyberpunk IPTV interface with glowing purple and cyan channel rails, live UEFA football match stream in backdrop, detailed electronic program guide overlay with frosted glass widgets, and neon status badges." src="assets/img/screenshots/screen_hero_dashboard.png"/>
<!-- HUD Frosted Scrim Overlay -->
<div class="absolute inset-0 bg-gradient-to-t from-surface-container-lowest via-surface-container-lowest/60 to-transparent flex flex-col justify-between p-4 sm:p-8">
<!-- Top Codec & Buffer Chips -->
<div class="flex items-center justify-between">
<div class="flex flex-wrap items-center gap-2">
<span class="px-3 py-1 rounded-md bg-surface-obsidian-subtle/95 border border-neon-cyan-bright/40 text-neon-cyan-bright font-label-sm text-label-sm backdrop-blur-md shadow-md">4K UHD 60FPS HDR10</span>
<span class="px-3 py-1 rounded-md bg-surface-obsidian-subtle/95 border border-electric-violet-glow/40 text-secondary font-label-sm text-label-sm backdrop-blur-md shadow-md">DOLBY ATMOS 5.1</span>
<span class="px-3 py-1 rounded-md bg-surface-obsidian-subtle/95 border border-status-active-green/40 text-status-active-green font-label-sm text-label-sm backdrop-blur-md shadow-md">BUFFER: 9.8s HEALTHY</span>
</div>
<div class="flex items-center gap-2">
<span class="px-3 py-1 rounded bg-remote-key-red/90 text-white font-label-sm text-label-sm uppercase font-bold flex items-center gap-1.5 shadow-[0_0_12px_rgba(239,68,68,0.6)]">
<span class="w-2 h-2 rounded-full bg-white animate-ping"></span> LIVE
              </span>
</div>
</div>
<!-- Lower EPG Stream Info Drawer with Remote Hardware Color Action Mapping -->
<div class="p-4 sm:p-6 rounded-xl bg-surface-container-lowest/90 backdrop-blur-xl border border-surface-glass-stroke shadow-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
<div class="flex items-start gap-4">
<div class="w-12 h-12 rounded-xl bg-gradient-to-br from-secondary-container to-surface-container-high border border-neon-cyan-bright/40 flex items-center justify-center shrink-0 shadow-lg">
<img alt="Power Core" class="w-8 h-8 object-contain" src="assets/img/icon.png"/>
</div>
<div>
<div class="flex items-center gap-2 mb-1">
<span class="font-headline-sm text-headline-sm text-on-surface font-bold">beIN SPORTS 1 UHD (4K HDR)</span>
<span class="material-symbols-outlined text-remote-key-red text-[22px]" style="font-variation-settings: 'FILL' 1;">favorite</span>
</div>
<p class="font-body-sm text-body-sm text-on-surface-variant">UEFA Champions League • Quarter Finals • 74' Live • English Commentary Track 1</p>
<!-- Stream Buffer Progress Bar -->
<div class="w-full sm:w-80 h-2 bg-surface-container-highest rounded-full mt-2.5 overflow-hidden">
<div class="h-full bg-gradient-to-r from-neon-cyan-bright via-primary-container to-electric-violet-glow w-[88%] rounded-full shadow-[0_0_10px_#00e5ff]"></div>
</div>
</div>
</div>
<!-- Remote Color Hardware Hotkeys -->
<div class="flex items-center gap-2.5 shrink-0 flex-wrap">
<div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container border border-surface-glass-stroke">
<span class="w-3 h-3 rounded-full bg-remote-key-red shadow-[0_0_8px_#EF4444]"></span>
<span class="font-label-sm text-label-sm text-on-surface font-semibold">Red: Favorite (❤️)</span>
</div>
<div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container border border-surface-glass-stroke">
<span class="w-3 h-3 rounded-full bg-remote-key-yellow shadow-[0_0_8px_#F59E0B]"></span>
<span class="font-label-sm text-label-sm text-on-surface font-semibold">Yellow: Reorder</span>
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
<section class="w-full py-space-xl bg-surface-container-lowest/80 border-y border-surface-glass-stroke relative" id="broadcast-hub">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="flex flex-col items-center text-center mb-14">
<span class="font-label-md text-label-md uppercase tracking-widest text-neon-cyan-bright mb-2 font-bold">
        CYBER NEON BROADCAST HUB
      </span>
<h2 class="font-headline-lg text-headline-lg text-on-surface font-bold tracking-tight mb-4">
        Zero Latency Cinema &amp; Live Sports Infrastructure
      </h2>
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-3xl">
        Benchmark testing against legacy IPTV players proves Dreamania Player's custom Media3 caching architecture switches channels up to 4x faster with zero frame drops.
      </p>
</div>
<!-- Broadcast Speed Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
<!-- Benchmark 1 -->
<div class="p-6 rounded-2xl bg-surface-container-low/80 border border-surface-glass-stroke shadow-xl flex flex-col justify-between">
<div>
<div class="flex items-center justify-between mb-4">
<span class="font-label-sm uppercase tracking-wider text-neon-cyan-bright font-bold">Channel Zapping Speed</span>
<span class="px-2 py-0.5 rounded bg-status-active-green/20 text-status-active-green font-label-sm text-xs font-bold">4.2x FASTER</span>
</div>
<div class="text-4xl font-headline-xl text-primary font-extrabold mb-1">0.24s</div>
<p class="font-body-sm text-body-sm text-on-surface-variant">Instant TS/HLS pre-buffering pipeline allows seamless channel flipping without blank screens.</p>
</div>
<div class="w-full bg-surface-container-highest h-2 rounded-full mt-6 overflow-hidden">
<div class="bg-gradient-to-r from-neon-cyan-bright to-primary w-[94%] h-full rounded-full"></div>
</div>
</div>
<!-- Benchmark 2 -->
<div class="p-6 rounded-2xl bg-surface-container-low/80 border border-surface-glass-stroke shadow-xl flex flex-col justify-between">
<div>
<div class="flex items-center justify-between mb-4">
<span class="font-label-sm uppercase tracking-wider text-secondary font-bold">Playlist Parsing Time</span>
<span class="px-2 py-0.5 rounded bg-secondary-container/30 text-secondary font-label-sm text-xs font-bold">50K+ CHANNELS</span>
</div>
<div class="text-4xl font-headline-xl text-secondary font-extrabold mb-1">1.18s</div>
<p class="font-body-sm text-body-sm text-on-surface-variant">Instant JSON indexing for massive Xtream Codes playlists and multi-megabyte M3U files.</p>
</div>
<div class="w-full bg-surface-container-highest h-2 rounded-full mt-6 overflow-hidden">
<div class="bg-gradient-to-r from-secondary to-electric-violet-glow w-[91%] h-full rounded-full"></div>
</div>
</div>
<!-- Benchmark 3 -->
<div class="p-6 rounded-2xl bg-surface-container-low/80 border border-surface-glass-stroke shadow-xl flex flex-col justify-between">
<div>
<div class="flex items-center justify-between mb-4">
<span class="font-label-sm uppercase tracking-wider text-remote-key-yellow font-bold">Hardware Resource Footprint</span>
<span class="px-2 py-0.5 rounded bg-remote-key-yellow/20 text-remote-key-yellow font-label-sm text-xs font-bold">ZERO BLOAT</span>
</div>
<div class="text-4xl font-headline-xl text-remote-key-yellow font-extrabold mb-1">14.5 MB</div>
<p class="font-body-sm text-body-sm text-on-surface-variant">Featherlight APK size optimized for budget Fire TV Sticks with low memory thresholds.</p>
</div>
<div class="w-full bg-surface-container-highest h-2 rounded-full mt-6 overflow-hidden">
<div class="bg-gradient-to-r from-remote-key-yellow to-tertiary-fixed-dim w-[85%] h-full rounded-full"></div>
</div>
</div>
</div>
<!-- Official TV Banner Showcase Card -->
<div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-surface-container-low via-surface-obsidian-subtle to-surface-container-low border border-surface-glass-stroke flex flex-col md:flex-row items-center justify-between gap-6 shadow-2xl">
<div class="flex items-center gap-5">
<img alt="Official Android TV Banner" class="w-36 sm:w-44 rounded-xl border border-neon-cyan-bright/30 shadow-[0_0_20px_rgba(56,189,248,0.3)] object-contain" src="assets/img/banner.png"/>
<div>
<h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Certified Leanback Experience</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant max-w-lg">Designed and tested specifically for TV launchers with Android 5.0 through Android 14+ compatibility.</p>
</div>
</div>
<a class="px-5 py-2.5 rounded-xl bg-surface-container-high hover:bg-surface-container-highest text-primary font-label-md text-label-md border border-neon-cyan-bright/30 transition-all flex items-center gap-2 shrink-0 shadow-md" href="#download">
<span class="material-symbols-outlined text-[18px]">verified</span>
<span>Download Leanback APK</span>
</a>
</div>
</div>
</section>
<!-- ======================================================== -->
<!-- FEATURES SECTION: ENGINEERED FOR POWER                   -->
<!-- ======================================================== -->
<section class="w-full py-space-xl bg-surface-container-lowest/60 relative" id="features">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<!-- Section Header -->
<div class="flex flex-col items-center text-center mb-16">
<span class="font-label-md text-label-md uppercase tracking-widest text-neon-cyan-bright mb-2">
          ENGINEERED FOR POWER
        </span>
<h2 class="font-headline-lg text-headline-lg text-on-surface font-bold tracking-tight mb-4">
          Built From The Ground Up for Perfection
        </h2>
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">
          Experience an IPTV middleware and Android media player that refuses to compromise on speed, design, or user convenience.
        </p>
</div>
<!-- 6 Futuristic Glassmorphic Feature Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
<!-- Feature 1: Favorites & Channel Reorder -->
<div class="group p-6 rounded-2xl bg-surface-container-low/70 hover:bg-surface-container/80 backdrop-blur-md transition-all duration-300 hover:scale-[1.02] shadow-xl hover:shadow-[0_0_30px_rgba(239,68,68,0.25)] flex flex-col justify-between border border-surface-glass-stroke">
<div>
<div class="flex items-center justify-between mb-4">
<div class="w-12 h-12 rounded-xl bg-remote-key-red/15 text-remote-key-red flex items-center justify-center shadow-inner">
<span class="material-symbols-outlined text-[26px]" style="font-variation-settings: 'FILL' 1;">favorite</span>
</div>
<span class="px-2.5 py-0.5 rounded-full bg-remote-key-red/20 text-remote-key-red font-label-sm text-label-sm tracking-wider uppercase">
                NEW IN V1.0.0
              </span>
</div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-2 font-bold group-hover:text-primary transition-colors">
              Favorites &amp; Channel Reorder
            </h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
              Add any channel to your favorites with a single heart button click. Reorder channels up and down directly from your TV remote D-Pad for a customized channel lineup.
            </p>
</div>
<div class="mt-6 pt-4 border-t border-surface-container-highest/50 flex items-center justify-between font-label-sm text-label-sm text-secondary">
<span>D-Pad Up/Down Move Mode</span>
<span class="material-symbols-outlined text-[18px]">arrow_forward</span>
</div>
</div>
<!-- Feature 2: Zero-Buffering Stream Engine -->
<div class="group p-6 rounded-2xl bg-surface-container-low/70 hover:bg-surface-container/80 backdrop-blur-md transition-all duration-300 hover:scale-[1.02] shadow-xl hover:shadow-[0_0_30px_rgba(56,189,248,0.25)] flex flex-col justify-between border border-surface-glass-stroke">
<div>
<div class="flex items-center justify-between mb-4">
<div class="w-12 h-12 rounded-xl bg-neon-cyan-bright/15 text-neon-cyan-bright flex items-center justify-center shadow-inner">
<span class="material-symbols-outlined text-[26px]">offline_bolt</span>
</div>
<span class="px-2.5 py-0.5 rounded-full bg-neon-cyan-bright/20 text-neon-cyan-bright font-label-sm text-label-sm tracking-wider uppercase">
                MEDIA3 EXOPLAYER
              </span>
</div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-2 font-bold group-hover:text-primary transition-colors">
              Zero-Buffering Stream Engine
            </h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
              Powered by Google's latest Media3 video stack with hardware decoding acceleration. Handles massive 50,000+ channel playlists and instant zapping with no lag.
            </p>
</div>
<div class="mt-6 pt-4 border-t border-surface-container-highest/50 flex items-center justify-between font-label-sm text-label-sm text-primary">
<span>Sub-second Channel Switching</span>
<span class="material-symbols-outlined text-[18px]">arrow_forward</span>
</div>
</div>
<!-- Feature 3: Cinema VOD & Episode Tracker -->
<div class="group p-6 rounded-2xl bg-surface-container-low/70 hover:bg-surface-container/80 backdrop-blur-md transition-all duration-300 hover:scale-[1.02] shadow-xl hover:shadow-[0_0_30px_rgba(245,158,11,0.25)] flex flex-col justify-between border border-surface-glass-stroke">
<div>
<div class="flex items-center justify-between mb-4">
<div class="w-12 h-12 rounded-xl bg-remote-key-yellow/15 text-remote-key-yellow flex items-center justify-center shadow-inner">
<span class="material-symbols-outlined text-[26px]">movie</span>
</div>
<span class="px-2.5 py-0.5 rounded-full bg-remote-key-yellow/20 text-remote-key-yellow font-label-sm text-label-sm tracking-wider uppercase">
                TMDB POWERED
              </span>
</div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-2 font-bold group-hover:text-primary transition-colors">
              Cinema VOD &amp; Episode Tracker
            </h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
              Browse movies and series with official TMDb ratings, high-res posters, cast info, and synopsis. Track multi-season series with resume playback progress bars.
            </p>
</div>
<div class="mt-6 pt-4 border-t border-surface-container-highest/50 flex items-center justify-between font-label-sm text-label-sm text-tertiary-fixed-dim">
<span>Automated Metadata Sync</span>
<span class="material-symbols-outlined text-[18px]">arrow_forward</span>
</div>
</div>
<!-- Feature 4: Multi-Audio & Subtitle Picker -->
<div class="group p-6 rounded-2xl bg-surface-container-low/70 hover:bg-surface-container/80 backdrop-blur-md transition-all duration-300 hover:scale-[1.02] shadow-xl hover:shadow-[0_0_30px_rgba(139,92,246,0.25)] flex flex-col justify-between border border-surface-glass-stroke">
<div>
<div class="flex items-center justify-between mb-4">
<div class="w-12 h-12 rounded-xl bg-electric-violet-glow/15 text-electric-violet-glow flex items-center justify-center shadow-inner">
<span class="material-symbols-outlined text-[26px]">surround_sound</span>
</div>
<span class="px-2.5 py-0.5 rounded-full bg-electric-violet-glow/20 text-secondary font-label-sm text-label-sm tracking-wider uppercase">
                DOLBY 5.1
              </span>
</div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-2 font-bold group-hover:text-primary transition-colors">
              Multi-Audio &amp; Subtitle Picker
            </h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
              Switch between English, Spanish, French, or stadium commentary tracks on the fly. Select embedded and external subtitles without interrupting playback.
            </p>
</div>
<div class="mt-6 pt-4 border-t border-surface-container-highest/50 flex items-center justify-between font-label-sm text-label-sm text-secondary">
<span>Instant Audio Switching</span>
<span class="material-symbols-outlined text-[18px]">arrow_forward</span>
</div>
</div>
<!-- Feature 5: Signature Visual Themes -->
<div class="group p-6 rounded-2xl bg-surface-container-low/70 hover:bg-surface-container/80 backdrop-blur-md transition-all duration-300 hover:scale-[1.02] shadow-xl hover:shadow-[0_0_30px_rgba(210,187,255,0.25)] flex flex-col justify-between border border-surface-glass-stroke">
<div>
<div class="flex items-center justify-between mb-4">
<div class="w-12 h-12 rounded-xl bg-secondary/15 text-secondary flex items-center justify-center shadow-inner">
<span class="material-symbols-outlined text-[26px]">palette</span>
</div>
<span class="px-2.5 py-0.5 rounded-full bg-secondary/20 text-secondary font-label-sm text-label-sm tracking-wider uppercase">
                4 PALETTES
              </span>
</div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-2 font-bold group-hover:text-primary transition-colors">
              Signature Visual Themes
            </h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
              Choose from 4 handcrafted color schemes: Dreamania Purple, Cyber Neon, Onyx Dark (OLED Cinema), and Royal Gold. Switch anytime with a single click.
            </p>
</div>
<div class="mt-6 pt-4 border-t border-surface-container-highest/50 flex items-center justify-between font-label-sm text-label-sm text-secondary-fixed">
<span>OLED True-Black Optimizations</span>
<span class="material-symbols-outlined text-[18px]">arrow_forward</span>
</div>
</div>
<!-- Feature 6: Seamless D-Pad TV Navigation -->
<div class="group p-6 rounded-2xl bg-surface-container-low/70 hover:bg-surface-container/80 backdrop-blur-md transition-all duration-300 hover:scale-[1.02] shadow-xl hover:shadow-[0_0_30px_rgba(16,185,129,0.25)] flex flex-col justify-between border border-surface-glass-stroke">
<div>
<div class="flex items-center justify-between mb-4">
<div class="w-12 h-12 rounded-xl bg-status-active-green/15 text-status-active-green flex items-center justify-center shadow-inner">
<span class="material-symbols-outlined text-[26px]">gamepad</span>
</div>
<span class="px-2.5 py-0.5 rounded-full bg-status-active-green/20 text-status-active-green font-label-sm text-label-sm tracking-wider uppercase">
                100% REMOTE FRIENDLY
              </span>
</div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-2 font-bold group-hover:text-primary transition-colors">
              Seamless D-Pad TV Navigation
            </h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
              Designed specifically for Fire TV Stick, Google TV, NVIDIA Shield, and Android boxes. High-contrast focus rings and quick shortcuts for every remote button.
            </p>
</div>
<div class="mt-6 pt-4 border-t border-surface-container-highest/50 flex items-center justify-between font-label-sm text-label-sm text-status-active-green">
<span>Zero Mouse/Pointer Needed</span>
<span class="material-symbols-outlined text-[18px]">arrow_forward</span>
</div>
</div>
</div>
</div>
</section>
<!-- ======================================================== -->
<!-- SCREENSHOTS & LIVE APPLICATION SHOWCASE TABS             -->
<!-- ======================================================== -->
<section class="w-full py-space-xl relative" id="screenshots">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="flex flex-col items-center text-center mb-12">
<span class="font-label-md text-label-md uppercase tracking-widest text-secondary mb-2">
          REAL APPLICATION SCREENSHOTS
        </span>
<h2 class="font-headline-lg text-headline-lg text-on-surface font-bold tracking-tight mb-4">
          See Dreamania Player in Action
        </h2>
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-3xl">
          Explore genuine 1080p and 4K captures of the Dreamania Player interface across live television, movie posters, playback OSD, and settings.
        </p>
<!-- Interactive Tab Pill Selector -->
<div class="flex flex-wrap items-center justify-center gap-2 mt-8 p-1.5 rounded-2xl bg-surface-container-lowest/80 backdrop-blur-lg shadow-inner border border-surface-glass-stroke" id="tab-controls">
<button class="tab-btn px-4 py-2 rounded-xl font-label-md text-label-md transition-all duration-200 bg-surface-container-high text-primary shadow-sm" data-tab="tab-launcher">
            📺 Main Launcher
          </button>
<button class="tab-btn px-4 py-2 rounded-xl font-label-md text-label-md transition-all duration-200 text-on-surface-variant hover:text-on-surface" data-tab="tab-epg">
            ❤️ Live TV &amp; EPG
          </button>
<button class="tab-btn px-4 py-2 rounded-xl font-label-md text-label-md transition-all duration-200 text-on-surface-variant hover:text-on-surface" data-tab="tab-player">
            ⚡ 4K Player &amp; Audio
          </button>
<button class="tab-btn px-4 py-2 rounded-xl font-label-md text-label-md transition-all duration-200 text-on-surface-variant hover:text-on-surface" data-tab="tab-vod">
            🎬 Cinema VOD
          </button>
<button class="tab-btn px-4 py-2 rounded-xl font-label-md text-label-md transition-all duration-200 text-on-surface-variant hover:text-on-surface" data-tab="tab-series">
            🍿 Series &amp; Episodes
          </button>
<button class="tab-btn px-4 py-2 rounded-xl font-label-md text-label-md transition-all duration-200 text-on-surface-variant hover:text-on-surface" data-tab="tab-themes">
            🎨 4 UI Themes
          </button>
</div>
</div>
<!-- Tab Content Cards -->
<div class="w-full" id="tab-content">
<!-- TAB 1: Main Launcher -->
<div class="tab-panel transition-opacity duration-300" id="tab-launcher">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center rounded-2xl bg-surface-container-low/80 p-6 sm:p-10 shadow-2xl backdrop-blur-md border border-surface-glass-stroke">
<div class="lg:col-span-7 rounded-xl overflow-hidden shadow-2xl relative group">
<img class="w-full aspect-[16/10] object-cover rounded-xl group-hover:scale-[1.01] transition-transform duration-500" data-alt="High resolution screenshot of Dreamania Player main launcher on Android TV with electric purple neon headers, prominent category tiles for Live TV, Movies, Series, Multi-Screen, and Account Expiry badge." src="assets/img/screenshots/screen_hero_dashboard.png"/>
<div class="absolute bottom-3 right-3 px-3 py-1 rounded-md bg-surface-obsidian-subtle/85 text-neon-cyan-bright font-label-sm text-label-sm backdrop-blur-md border border-neon-cyan-bright/30">
                1080p FHD UI
              </div>
</div>
<div class="lg:col-span-5 flex flex-col gap-4">
<span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">10-FOOT TV LAUNCHER</span>
<h3 class="font-headline-md text-headline-md text-on-surface font-bold">Futuristic Android TV Dashboard</h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Clean category navigation, quick status bar with VIP subscription expiration, digital clock, and D-Pad focus ring calibrated for ultra-fast response times.
              </p>
<div class="flex flex-col gap-2.5 mt-2">
<div class="flex items-center gap-2 text-on-surface font-label-md">
<span class="material-symbols-outlined text-status-active-green text-[18px]">check_circle</span>
<span>Instant access to Live Channels, Cinema &amp; Series</span>
</div>
<div class="flex items-center gap-2 text-on-surface font-label-md">
<span class="material-symbols-outlined text-status-active-green text-[18px]">check_circle</span>
<span>Device MAC and Expiration display directly on home</span>
</div>
</div>
</div>
</div>
</div>
<!-- TAB 2: Live TV & EPG -->
<div class="tab-panel hidden transition-opacity duration-300" id="tab-epg">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center rounded-2xl bg-surface-container-low/80 p-6 sm:p-10 shadow-2xl backdrop-blur-md border border-surface-glass-stroke">
<div class="lg:col-span-7 rounded-xl overflow-hidden shadow-2xl relative group">
<img class="w-full aspect-[16/10] object-cover rounded-xl group-hover:scale-[1.01] transition-transform duration-500" data-alt="Screenshot of Dreamania Player Live TV channel guide and Electronic Program Guide showing heart favorites category at top, detailed soccer match synopsis, timeline progress bar, and remote key guidance." src="assets/img/screenshots/screen_livetv_guide.png"/>
<div class="absolute bottom-3 right-3 px-3 py-1 rounded-md bg-surface-obsidian-subtle/85 text-remote-key-red font-label-sm text-label-sm backdrop-blur-md border border-remote-key-red/30">
                ❤️ Favorites Pinned
              </div>
</div>
<div class="lg:col-span-5 flex flex-col gap-4">
<span class="font-label-sm text-label-sm uppercase tracking-widest text-remote-key-red font-bold">SMART CHANNEL GUIDE</span>
<h3 class="font-headline-md text-headline-md text-on-surface font-bold">Live TV Guide with Pinned Favorites &amp; Reordering</h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Featuring category sidebar with ❤️ Favorites at top, real-time EPG program descriptions, resolution badges, and remote move mode for moving channels on your Fire TV.
              </p>
<div class="flex items-center gap-3 mt-2">
<span class="px-3 py-1.5 rounded-lg bg-surface-container text-remote-key-red font-label-sm font-semibold flex items-center gap-1.5 border border-remote-key-red/30">
<span class="w-2.5 h-2.5 rounded-full bg-remote-key-red"></span> Red Key: Favorite
                </span>
<span class="px-3 py-1.5 rounded-lg bg-surface-container text-remote-key-yellow font-label-sm font-semibold flex items-center gap-1.5 border border-remote-key-yellow/30">
<span class="w-2.5 h-2.5 rounded-full bg-remote-key-yellow"></span> Yellow Key: Move Channel
                </span>
</div>
</div>
</div>
</div>
<!-- TAB 3: 4K Player & Audio -->
<div class="tab-panel hidden transition-opacity duration-300" id="tab-player">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center rounded-2xl bg-surface-container-low/80 p-6 sm:p-10 shadow-2xl backdrop-blur-md border border-surface-glass-stroke">
<div class="lg:col-span-7 rounded-xl overflow-hidden shadow-2xl relative group">
<img class="w-full aspect-[16/10] object-cover rounded-xl group-hover:scale-[1.01] transition-transform duration-500" data-alt="Screenshot of 4K 60FPS video playback in Dreamania Player with sleek frosted glass bottom OSD showing buffer health graph, Dolby 5.1 audio track selection menu, and subtitle sync controls." src="assets/img/screenshots/screen_player_osd.png"/>
<div class="absolute bottom-3 right-3 px-3 py-1 rounded-md bg-surface-obsidian-subtle/85 text-primary-fixed font-label-sm text-label-sm backdrop-blur-md border border-primary/30">
                Media3 Hardware Acceleration
              </div>
</div>
<div class="lg:col-span-5 flex flex-col gap-4">
<span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">CINEMA PLAYBACK OSD</span>
<h3 class="font-headline-md text-headline-md text-on-surface font-bold">ExoPlayer 4K 60FPS with Live OSD &amp; Quick Drawer</h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Top information bar with live stream quality indicator, Dolby Atmos audio, subtitle switcher, and left-side quick channel drawer so you never have to stop watching to browse.
              </p>
<div class="flex flex-col gap-2 mt-2">
<div class="flex items-center gap-2 text-on-surface font-label-md">
<span class="material-symbols-outlined text-secondary text-[18px]">graphic_eq</span>
<span>Dolby Digital Plus &amp; Multi-lingual commentary switcher</span>
</div>
<div class="flex items-center gap-2 text-on-surface font-label-md">
<span class="material-symbols-outlined text-primary text-[18px]">subtitles</span>
<span>Instant embedded and external SRT subtitle selector</span>
</div>
</div>
</div>
</div>
</div>
<!-- TAB 4: Cinema VOD -->
<div class="tab-panel hidden transition-opacity duration-300" id="tab-vod">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center rounded-2xl bg-surface-container-low/80 p-6 sm:p-10 shadow-2xl backdrop-blur-md border border-surface-glass-stroke">
<div class="lg:col-span-7 rounded-xl overflow-hidden shadow-2xl relative group">
<img class="w-full aspect-[16/10] object-cover rounded-xl group-hover:scale-[1.01] transition-transform duration-500" data-alt="Screenshot of Movie VOD gallery poster wall in Dreamania Player with TMDb star ratings, director names, release years, synopsis overlay, and trailer preview button." src="assets/img/screenshots/screen_vod_movies.png"/>
<div class="absolute bottom-3 right-3 px-3 py-1 rounded-md bg-surface-obsidian-subtle/85 text-remote-key-yellow font-label-sm text-label-sm backdrop-blur-md border border-remote-key-yellow/30">
                TMDb Rated 8.9/10
              </div>
</div>
<div class="lg:col-span-5 flex flex-col gap-4">
<span class="font-label-sm text-label-sm uppercase tracking-widest text-remote-key-yellow font-bold">VOD POSTER GALLERY</span>
<h3 class="font-headline-md text-headline-md text-on-surface font-bold">Cinema VOD Movie Poster Wall</h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                High-resolution cover flow with TMDb star ratings, category filters, plot synopsis preview, and instant trailer playback with multi-resolution streaming support.
              </p>
</div>
</div>
</div>
<!-- TAB 5: Series & Episodes -->
<div class="tab-panel hidden transition-opacity duration-300" id="tab-series">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center rounded-2xl bg-surface-container-low/80 p-6 sm:p-10 shadow-2xl backdrop-blur-md border border-surface-glass-stroke">
<div class="lg:col-span-7 rounded-xl overflow-hidden shadow-2xl relative group">
<img class="w-full aspect-[16/10] object-cover rounded-xl group-hover:scale-[1.01] transition-transform duration-500" data-alt="Screenshot of television series viewer with organized season tabs, episode thumbnails with durations, resume playback progress bar, and next episode auto-play prompt." src="assets/img/screenshots/screen_series_detail.png"/>
<div class="absolute bottom-3 right-3 px-3 py-1 rounded-md bg-surface-obsidian-subtle/85 text-secondary font-label-sm text-label-sm backdrop-blur-md border border-secondary/30">
                Multi-Season Tracker
              </div>
</div>
<div class="lg:col-span-5 flex flex-col gap-4">
<span class="font-label-sm text-label-sm uppercase tracking-widest text-secondary font-bold">EPISODE MANAGER</span>
<h3 class="font-headline-md text-headline-md text-on-surface font-bold">Multi-Season Series &amp; Episode Tracker</h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Organized season tabs, episode thumbnails with duration, and visual progress bar allowing you to resume exactly where you left off on any TV device.
              </p>
</div>
</div>
</div>
<!-- TAB 6: 4 UI Themes -->
<div class="tab-panel hidden transition-opacity duration-300" id="tab-themes">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center rounded-2xl bg-surface-container-low/80 p-6 sm:p-10 shadow-2xl backdrop-blur-md border border-surface-glass-stroke">
<div class="lg:col-span-7 rounded-xl overflow-hidden shadow-2xl relative group">
<img class="w-full aspect-[16/10] object-cover rounded-xl group-hover:scale-[1.01] transition-transform duration-500" data-alt="Screenshot showcasing four side-by-side color palettes of Dreamania Player: Dreamania Purple, Cyber Neon, Onyx Dark OLED, and Royal Gold with sleek glowing accents." src="assets/img/screenshots/screen_themes_showcase.png"/>
<div class="absolute bottom-3 right-3 px-3 py-1 rounded-md bg-surface-obsidian-subtle/85 text-primary font-label-sm text-label-sm backdrop-blur-md border border-primary/30">
                Custom Visual Engine
              </div>
</div>
<div class="lg:col-span-5 flex flex-col gap-4">
<span class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">CUSTOM PALETTES</span>
<h3 class="font-headline-md text-headline-md text-on-surface font-bold">4 Handcrafted Visual Themes</h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                Switch seamlessly between Dreamania Purple, Cyber Neon, Onyx Dark (optimized for OLED TVs with zero gray bleed), and Royal Gold.
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
<section class="w-full py-space-xl bg-surface-container-lowest/70 relative">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="flex flex-col items-center text-center mb-16">
<span class="font-label-md text-label-md uppercase tracking-widest text-neon-cyan-bright mb-2">
          SIMPLE 3-STEP SETUP
        </span>
<h2 class="font-headline-lg text-headline-lg text-on-surface font-bold tracking-tight mb-4">
          Up &amp; Streaming in Under 2 Minutes
        </h2>
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">
          Getting started with Dreamania Player is straightforward. No complex configurations or rooting required.
        </p>
</div>
<!-- Steps Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
<!-- Step 01 -->
<div class="relative p-8 rounded-2xl bg-surface-container-low/90 backdrop-blur-md shadow-xl flex flex-col border border-surface-glass-stroke">
<div class="text-[52px] font-extrabold font-headline-xl text-primary/20 leading-none mb-4">
            01
          </div>
<h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-3">
            Download &amp; Install APK
          </h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
            Download <code class="px-2 py-0.5 rounded bg-surface-container font-mono text-neon-cyan-bright text-body-sm">DreamaniaPlayer.apk</code> directly from our official portal or use the <strong class="text-on-surface">Downloader app</strong> on your Fire TV Stick / Android TV box.
          </p>
</div>
<!-- Step 02 -->
<div class="relative p-8 rounded-2xl bg-surface-container-low/90 backdrop-blur-md shadow-xl flex flex-col border border-surface-glass-stroke">
<div class="text-[52px] font-extrabold font-headline-xl text-secondary/20 leading-none mb-4">
            02
          </div>
<h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-3">
            Connect Your Playlist
          </h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
            Log in using your <strong class="text-on-surface">Xtream Codes API</strong> (Server URL, Username, Password) or enter your <strong class="text-on-surface">M3U playlist link</strong>. Multi-playlist support lets you store several accounts.
          </p>
</div>
<!-- Step 03 -->
<div class="relative p-8 rounded-2xl bg-surface-container-low/90 backdrop-blur-md shadow-xl flex flex-col border border-surface-glass-stroke">
<div class="text-[52px] font-extrabold font-headline-xl text-remote-key-yellow/20 leading-none mb-4">
            03
          </div>
<h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-3">
            Enjoy 4K Cinema Streaming
          </h3>
<p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
            Browse your Live Channels, Cinema Movies, and TV Shows in high definition with automated EPG updates and personalized favorites.
          </p>
</div>
</div>
<!-- Universal Hardware Support Banner -->
<div class="p-6 sm:p-8 rounded-2xl bg-surface-container-high/40 backdrop-blur-lg flex flex-col lg:flex-row items-center justify-between gap-6 shadow-lg border border-surface-glass-stroke">
<div>
<h4 class="font-headline-sm text-headline-sm text-on-surface font-bold mb-1">
            Universal Android Hardware Support
          </h4>
<p class="font-body-sm text-body-sm text-on-surface-variant">
            Fully tested and hardware-accelerated across all major Android platforms.
          </p>
</div>
<div class="flex flex-wrap items-center gap-3">
<span class="px-3.5 py-1.5 rounded-xl bg-surface-container text-on-surface font-label-md text-label-md flex items-center gap-2 border border-surface-glass-stroke">
<span class="material-symbols-outlined text-remote-key-yellow text-[18px]">tv</span> Amazon Fire TV
          </span>
<span class="px-3.5 py-1.5 rounded-xl bg-surface-container text-on-surface font-label-md text-label-md flex items-center gap-2 border border-surface-glass-stroke">
<span class="material-symbols-outlined text-neon-cyan-bright text-[18px]">bolt</span> Google TV
          </span>
<span class="px-3.5 py-1.5 rounded-xl bg-surface-container text-on-surface font-label-md text-label-md flex items-center gap-2 border border-surface-glass-stroke">
<span class="material-symbols-outlined text-status-active-green text-[18px]">sports_esports</span> NVIDIA Shield
          </span>
<span class="px-3.5 py-1.5 rounded-xl bg-surface-container text-on-surface font-label-md text-label-md flex items-center gap-2 border border-surface-glass-stroke">
<span class="material-symbols-outlined text-secondary text-[18px]">package_2</span> Android TV Box
          </span>
<span class="px-3.5 py-1.5 rounded-xl bg-surface-container text-on-surface font-label-md text-label-md flex items-center gap-2 border border-surface-glass-stroke">
<span class="material-symbols-outlined text-primary text-[18px]">smartphone</span> Smartphones &amp; Tabs
          </span>
</div>
</div>
</div>
</section>
<!-- ======================================================== -->
<!-- PRICING & PLANS SECTION                                  -->
<!-- ======================================================== -->
<section class="w-full py-space-xl relative" id="pricing">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="flex flex-col items-center text-center mb-16">
<span class="font-label-md text-label-md uppercase tracking-widest text-primary mb-2">
          SIMPLE &amp; TRANSPARENT
        </span>
<h2 class="font-headline-lg text-headline-lg text-on-surface font-bold tracking-tight mb-4">
          Pricing &amp; Plans
        </h2>
<p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl">
          Activate your Smart TV device directly or purchase wholesale reseller credits at the best market rates.
        </p>
</div>
<!-- Dual Column Comparison: Smart TV Owners vs Resellers -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch mb-16">
<!-- COLUMN A: For Smart TV Owners -->
<div class="rounded-3xl bg-surface-container-low/85 p-8 sm:p-10 shadow-2xl backdrop-blur-md flex flex-col justify-between border border-surface-glass-stroke" id="device-activation">
<div>
<div class="flex items-center justify-between mb-4">
<span class="px-3 py-1 rounded-full bg-surface-container text-neon-cyan-bright font-label-sm text-label-sm uppercase tracking-wider font-bold">
                FOR SMART TV OWNERS 📺
              </span>
<span class="text-remote-key-yellow font-label-sm text-label-sm">Instant MAC Activation</span>
</div>
<h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-3">
              End-User Activation
            </h3>
<p class="font-body-md text-body-md text-on-surface-variant mb-8 leading-relaxed">
              Inspired by the simple iboplayer.com workflow: enter your TV's MAC Address &amp; Device Key, upload any M3U or Xtream playlist, and enjoy instant 4K playback.
            </p>
<!-- Plans Selector Card -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
<!-- 1 Year Plan -->
<div class="p-5 rounded-2xl bg-surface-container/70 shadow-md flex flex-col justify-between border border-surface-glass-stroke">
<div>
<span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Standard</span>
<div class="font-headline-md text-headline-md text-on-surface font-bold mt-1">1 Year Plan</div>
<div class="font-headline-md text-headline-md text-primary font-bold my-2"><?= htmlspecialchars($pricing['client_1y_intl']) ?></div>
<div class="font-body-sm text-body-sm text-on-surface-variant"><?= (int)$pricing['client_1y_dh'] ?> DH • 12 Months Access</div>
</div>
</div>
<!-- VIP Lifetime Plan (Highlighted) -->
<div class="p-5 rounded-2xl bg-surface-container-highest/80 shadow-[0_0_25px_rgba(139,92,246,0.35)] flex flex-col justify-between relative overflow-hidden border border-electric-violet-glow/40">
<div class="absolute top-2 right-2 px-2 py-0.5 rounded-full bg-electric-violet-glow text-on-primary-container font-label-sm text-[10px] uppercase font-bold tracking-wider">
                  POPULAR
                </div>
<div>
<span class="font-label-sm text-label-sm text-secondary uppercase font-bold">Best Value</span>
<div class="font-headline-md text-headline-md text-on-surface font-bold mt-1">VIP Lifetime</div>
<div class="font-headline-md text-headline-md text-secondary font-bold my-2"><?= htmlspecialchars($pricing['client_life_intl']) ?></div>
<div class="font-body-sm text-body-sm text-secondary-fixed"><?= (int)$pricing['client_life_dh'] ?> DH • Never Expires</div>
</div>
</div>
</div>
<!-- Features Checkmark List -->
<div class="flex flex-col gap-3 mb-8">
<div class="flex items-center gap-2.5 font-label-md text-label-md text-on-surface">
<span class="material-symbols-outlined text-status-active-green text-[18px]">check_circle</span>
<span>Unlimited M3U &amp; Xtream Codes playlists</span>
</div>
<div class="flex items-center gap-2.5 font-label-md text-label-md text-on-surface">
<span class="material-symbols-outlined text-status-active-green text-[18px]">check_circle</span>
<span>Pin code parental protection (PIN 0000)</span>
</div>
<div class="flex items-center gap-2.5 font-label-md text-label-md text-on-surface">
<span class="material-symbols-outlined text-status-active-green text-[18px]">check_circle</span>
<span>Real-time EPG &amp; Channel rearrangement</span>
</div>
<div class="flex items-center gap-2.5 font-label-md text-label-md text-on-surface">
<span class="material-symbols-outlined text-status-active-green text-[18px]">check_circle</span>
<span>Payment via PayPal &amp; Bank Transfer (RIB Maroc)</span>
</div>
</div>
</div>
<a class="w-full py-4 rounded-xl bg-gradient-to-r from-remote-key-yellow to-tertiary-fixed-dim text-surface-container-lowest font-label-lg text-label-lg font-bold shadow-[0_0_24px_rgba(245,158,11,0.45)] hover:brightness-110 active:scale-95 transition-all text-center flex items-center justify-center gap-2" href="device.php">
<span class="material-symbols-outlined text-[20px]">tv</span>
<span>Manage Device &amp; Activate (<?= htmlspecialchars($pricing['client_1y_intl']) ?> - <?= (int)$pricing['client_1y_dh'] ?> DH)</span>
</a>
</div>
<!-- COLUMN B: For Resellers & Distributors -->
<div class="rounded-3xl bg-surface-container-low/85 p-8 sm:p-10 shadow-2xl backdrop-blur-md flex flex-col justify-between border border-surface-glass-stroke" id="reseller">
<div>
<div class="flex items-center justify-between mb-4">
<span class="px-3 py-1 rounded-full bg-surface-container text-secondary font-label-sm text-label-sm uppercase tracking-wider font-bold">
                FOR RESELLERS &amp; DISTRIBUTORS ⚡
              </span>
<span class="text-neon-cyan-bright font-label-sm text-label-sm font-semibold">1 Credit = 1 Year Activation</span>
</div>
<h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-3">
              Reseller Wholesale Credits
            </h3>
<p class="font-body-md text-body-md text-on-surface-variant mb-6 leading-relaxed">
              Launch your own IPTV activation business with a dedicated reseller web panel. Activate client devices in seconds with high profit margins.
            </p>
<!-- Reseller Tiered Pricing Table -->
<div class="space-y-2 mb-6 font-label-md text-label-md">
<div class="flex items-center justify-between p-3 rounded-xl bg-surface-container/60 border border-surface-glass-stroke">
<span class="font-semibold text-on-surface">10 Credits</span>
<span class="text-primary font-bold"><?= htmlspecialchars($pricing['reseller_packs'][10]['label']) ?></span>
<span class="text-on-surface-variant text-label-sm">1.5€ / 15 DH per cr</span>
</div>
<div class="flex items-center justify-between p-3 rounded-xl bg-surface-container/60 border border-surface-glass-stroke">
<span class="font-semibold text-on-surface">20 Credits</span>
<span class="text-primary font-bold"><?= htmlspecialchars($pricing['reseller_packs'][20]['label']) ?></span>
<span class="text-on-surface-variant text-label-sm">0.9€ / 9 DH per cr</span>
</div>
<div class="flex items-center justify-between p-3 rounded-xl bg-surface-container/60 border border-surface-glass-stroke">
<span class="font-semibold text-on-surface">30 Credits</span>
<span class="text-primary font-bold"><?= htmlspecialchars($pricing['reseller_packs'][30]['label']) ?></span>
<span class="text-on-surface-variant text-label-sm">0.8€ / 8 DH per cr</span>
</div>
<div class="flex items-center justify-between p-3 rounded-xl bg-surface-container/60 border border-surface-glass-stroke">
<span class="font-semibold text-on-surface">50 Credits</span>
<span class="text-primary font-bold"><?= htmlspecialchars($pricing['reseller_packs'][50]['label']) ?></span>
<span class="text-on-surface-variant text-label-sm">0.7€ / 7 DH per cr</span>
</div>
<!-- VIP Wholesale Tier -->
<div class="flex items-center justify-between p-3 rounded-xl bg-surface-container-highest/90 shadow-[0_0_15px_rgba(56,189,248,0.25)] border border-neon-cyan-bright/40">
<span class="font-bold text-neon-cyan-bright">100 Credits (Wholesale VIP)</span>
<span class="text-primary-fixed font-bold">€60.00 / $60.00 (600 DH)</span>
<span class="text-status-active-green text-label-sm font-bold">0.6€ / 6 DH per cr (-40%)</span>
</div>
</div>
<!-- Reseller Benefits -->
<div class="flex flex-col gap-2 mb-8">
<div class="flex items-center gap-2.5 font-label-md text-label-md text-on-surface">
<span class="material-symbols-outlined text-secondary text-[18px]">verified</span>
<span>Instant client activation via web panel</span>
</div>
<div class="flex items-center gap-2.5 font-label-md text-label-md text-on-surface">
<span class="material-symbols-outlined text-secondary text-[18px]">verified</span>
<span>Full control over client playlists &amp; device MACs</span>
</div>
<div class="flex items-center gap-2.5 font-label-md text-label-md text-on-surface">
<span class="material-symbols-outlined text-secondary text-[18px]">verified</span>
<span>High profit margins for installers &amp; retailers</span>
</div>
</div>
</div>
<a class="w-full py-4 rounded-xl bg-gradient-to-r from-secondary-container via-electric-violet-glow to-primary-container text-on-primary-container font-label-lg text-label-lg font-bold shadow-[0_0_24px_rgba(139,92,246,0.45)] hover:scale-105 active:scale-95 transition-all text-center flex items-center justify-center gap-2" href="device.php">
<span class="material-symbols-outlined text-[20px]">bolt</span>
<span>Order Reseller Credits</span>
</a>
</div>
</div>
<!-- Transparent Moroccan Bank (RIB) / PayPal Pricing Checkout -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
<div class="p-6 rounded-2xl bg-surface-container-low/80 border border-surface-glass-stroke backdrop-blur-md flex items-center gap-4">
<div class="w-12 h-12 rounded-xl bg-surface-container flex items-center justify-center text-primary text-[24px] border border-surface-glass-stroke shadow-inner">
            🏦
          </div>
<div>
<h4 class="font-headline-sm text-headline-sm text-on-surface font-bold">Virement Bancaire (RIB Maroc)</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant">CIH Bank, Attijariwafa, Bank of Africa</p>
</div>
</div>
<div class="p-6 rounded-2xl bg-surface-container-low/80 border border-surface-glass-stroke backdrop-blur-md flex items-center gap-4">
<div class="w-12 h-12 rounded-xl bg-surface-container flex items-center justify-center text-primary text-[24px] border border-surface-glass-stroke shadow-inner">
            🅿️
          </div>
<div>
<h4 class="font-headline-sm text-headline-sm text-on-surface font-bold">PayPal &amp; Credit Cards</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant">Worldwide instant automated checkout</p>
</div>
</div>
<div class="p-6 rounded-2xl bg-surface-container-low/80 border border-surface-glass-stroke backdrop-blur-md flex items-center gap-4">
<div class="w-12 h-12 rounded-xl bg-surface-container flex items-center justify-center text-primary text-[24px] border border-surface-glass-stroke shadow-inner">
            💬
          </div>
<div>
<h4 class="font-headline-sm text-headline-sm text-on-surface font-bold">24/7 WhatsApp Confirmation</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant">Fast verification &amp; instant human support</p>
</div>
</div>
</div>
</div>
</section>
<!-- ======================================================== -->
<!-- DOWNLOAD CTA & QR CODE BANNER SECTION                    -->
<!-- ======================================================== -->
<section class="w-full py-space-xl bg-surface-container-lowest/80 relative" id="download">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="relative rounded-3xl bg-gradient-to-br from-surface-container-low via-surface-container to-surface-container-lowest p-8 sm:p-14 shadow-2xl overflow-hidden flex flex-col lg:flex-row items-center justify-between gap-12 border border-surface-glass-stroke">
<!-- Ambient Radial Glow -->
<div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-electric-violet-glow/20 blur-[100px] pointer-events-none"></div>
<div class="max-w-2xl flex flex-col gap-6 relative z-10">
<span class="font-label-md text-label-md uppercase tracking-widest text-primary font-bold">
            INSTANT APP DOWNLOAD
          </span>
<h2 class="font-headline-lg text-headline-lg text-on-surface font-bold tracking-tight">
            Ready for the Ultimate IPTV Experience?
          </h2>
<p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed">
            Download Dreamania Player today. Enjoy seamless 4K playback, customizable channel ordering, and multi-audio support with zero bloatware.
          </p>
<div class="flex flex-wrap items-center gap-4 pt-2">
<a class="px-6 py-3.5 rounded-xl bg-gradient-to-r from-secondary-container via-electric-violet-glow to-primary-container text-on-primary-container font-label-lg text-label-lg font-bold shadow-[0_0_24px_rgba(139,92,246,0.45)] hover:scale-105 active:scale-95 transition-all flex items-center gap-2" href="device.php">
<span class="material-symbols-outlined text-[20px]">download</span>
<span>Download Dreamania Player APK (v1.0.0)</span>
</a>
<a class="px-6 py-3.5 rounded-xl bg-surface-container-high/70 hover:bg-surface-container-highest text-secondary font-label-lg text-label-lg transition-all flex items-center gap-2 border border-surface-glass-stroke" href="#reseller">
<span class="material-symbols-outlined text-[20px] text-neon-cyan-bright">admin_panel_settings</span>
<span>Reseller / Admin Portal</span>
</a>
</div>
</div>
<!-- High-Tech QR Code Component -->
<div class="w-full sm:w-auto p-6 rounded-2xl bg-surface-container-lowest/90 backdrop-blur-xl shadow-2xl flex flex-col items-center text-center relative z-10 border border-surface-glass-stroke">
<div class="p-3 bg-white rounded-xl shadow-md mb-4">
<svg class="w-36 h-36" fill="none" viewbox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
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
<span class="font-headline-sm text-headline-sm text-on-surface font-bold text-sm">
            Scan to Download on TV / Mobile
          </span>
<p class="font-body-sm text-body-sm text-on-surface-variant max-w-[210px] mt-1 leading-snug">
            Point your phone camera or Fire TV Downloader app at this QR code.
          </p>
</div>
</div>
</div>
</section>
<!-- ======================================================== -->
<!-- FREQUENTLY ASKED QUESTIONS SECTION                       -->
<!-- ======================================================== -->
<section class="w-full py-space-xl relative" id="faq">
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="flex flex-col items-center text-center mb-12">
<span class="font-label-md text-label-md uppercase tracking-widest text-neon-cyan-bright mb-2">
          FREQUENTLY ASKED QUESTIONS
        </span>
<h2 class="font-headline-lg text-headline-lg text-on-surface font-bold tracking-tight mb-4">
          Got Questions? We Have Answers
        </h2>
<p class="font-body-lg text-body-lg text-on-surface-variant">
          Find quick answers regarding installation, playlist compatibility, and player controls.
        </p>
</div>
<!-- Interactive FAQ Accordion -->
<div class="space-y-4" id="faq-accordion">
<!-- Question 1 -->
<div class="rounded-2xl bg-surface-container-low/70 overflow-hidden transition-all shadow-md border border-surface-glass-stroke">
<button class="faq-toggle w-full p-6 text-left flex items-center justify-between gap-4 font-headline-sm text-headline-sm text-on-surface font-semibold hover:text-primary transition-colors">
<span>How do I install Dreamania Player on Amazon Fire TV Stick?</span>
<span class="material-symbols-outlined text-[24px] text-neon-cyan-bright transition-transform duration-200">expand_more</span>
</button>
<div class="faq-content hidden px-6 pb-6 pt-1 font-body-md text-body-md text-on-surface-variant leading-relaxed">
            To install on Fire TV, simply open the free <strong class="text-on-surface">Downloader app</strong> from the Amazon Appstore, enter our download URL <code class="px-2 py-0.5 rounded bg-surface-container font-mono text-neon-cyan-bright">https://dm.lumineasy.com/download</code> (or scan the QR code above), and select Install. Ensure "Install Unknown Apps" is turned ON in your Fire TV Developer Options.
          </div>
</div>
<!-- Question 2 -->
<div class="rounded-2xl bg-surface-container-low/70 overflow-hidden transition-all shadow-md border border-surface-glass-stroke">
<button class="faq-toggle w-full p-6 text-left flex items-center justify-between gap-4 font-headline-sm text-headline-sm text-on-surface font-semibold hover:text-primary transition-colors">
<span>Does Dreamania Player include any built-in TV channels?</span>
<span class="material-symbols-outlined text-[24px] text-neon-cyan-bright transition-transform duration-200">expand_more</span>
</button>
<div class="faq-content hidden px-6 pb-6 pt-1 font-body-md text-body-md text-on-surface-variant leading-relaxed">
            No. Dreamania Player is a pure, high-performance media player. It does not provide or host any media streams. You must enter your own legal IPTV playlist credentials via Xtream Codes API or M3U playlist format.
          </div>
</div>
<!-- Question 3 -->
<div class="rounded-2xl bg-surface-container-low/70 overflow-hidden transition-all shadow-md border border-surface-glass-stroke">
<button class="faq-toggle w-full p-6 text-left flex items-center justify-between gap-4 font-headline-sm text-headline-sm text-on-surface font-semibold hover:text-primary transition-colors">
<span>How do I add channels to my Favorites?</span>
<span class="material-symbols-outlined text-[24px] text-neon-cyan-bright transition-transform duration-200">expand_more</span>
</button>
<div class="faq-content hidden px-6 pb-6 pt-1 font-body-md text-body-md text-on-surface-variant leading-relaxed">
            While in the channel list, select any channel and press the <strong class="text-remote-key-red">Red button</strong> or <strong class="text-on-surface">Long-Press the OK button</strong> on your remote. The heart indicator turns red (❤️), and the channel will automatically appear in your pinned "❤️ Favorites" category at the top!
          </div>
</div>
<!-- Question 4 -->
<div class="rounded-2xl bg-surface-container-low/70 overflow-hidden transition-all shadow-md border border-surface-glass-stroke">
<button class="faq-toggle w-full p-6 text-left flex items-center justify-between gap-4 font-headline-sm text-headline-sm text-on-surface font-semibold hover:text-primary transition-colors">
<span>How do I reorder or move channels in the list?</span>
<span class="material-symbols-outlined text-[24px] text-neon-cyan-bright transition-transform duration-200">expand_more</span>
</button>
<div class="faq-content hidden px-6 pb-6 pt-1 font-body-md text-body-md text-on-surface-variant leading-relaxed">
            Select a channel and press the <strong class="text-remote-key-yellow">Yellow button</strong> on your TV remote. A convenient modal appears allowing you to move the channel Up, Down, Move to Top, or enter interactive remote mode to position the channel wherever you prefer.
          </div>
</div>
<!-- Question 5 -->
<div class="rounded-2xl bg-surface-container-low/70 overflow-hidden transition-all shadow-md border border-surface-glass-stroke">
<button class="faq-toggle w-full p-6 text-left flex items-center justify-between gap-4 font-headline-sm text-headline-sm text-on-surface font-semibold hover:text-primary transition-colors">
<span>Can I switch between multiple audio languages or subtitles?</span>
<span class="material-symbols-outlined text-[24px] text-neon-cyan-bright transition-transform duration-200">expand_more</span>
</button>
<div class="faq-content hidden px-6 pb-6 pt-1 font-body-md text-body-md text-on-surface-variant leading-relaxed">
            Yes! While streaming, press OK or Down on your TV remote to open the bottom player OSD. Select the <strong class="text-on-surface">Audio</strong> or <strong class="text-on-surface">Subtitles</strong> button to choose from all available Dolby Surround tracks and language commentaries.
          </div>
</div>
</div>
</div>
</section>
<!-- Interactive JavaScript -->
<script>
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
</script>
</div></main>
<!-- ======================================================== -->
<!-- FOOTER                                                   -->
<!-- ======================================================== -->
<footer class="w-full bg-surface-container-lowest relative border-t border-surface-glass-stroke">
<div class="w-full px-margin py-space-xl">
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-space-lg mb-space-xl">
<div class="lg:col-span-2 flex flex-col gap-space-sm">
<div class="flex items-center gap-space-sm">
<div class="w-10 h-10 rounded-xl bg-surface-container-high flex items-center justify-center p-1 border border-neon-cyan-bright/30 shadow-[0_0_15px_rgba(56,189,248,0.35)]">
<img alt="Dreamania Player Authentic Logo" class="w-full h-full object-contain" src="assets/img/icon.png"/>
</div>
<span class="font-headline-sm text-headline-sm text-primary tracking-wide">DREAMANIA PLAYER</span>
</div>
<p class="font-body-sm text-body-sm text-on-surface-variant max-w-md">Next-generation, ultra-responsive smart TV and mobile media engine calibrated for low latency, hardware decoding, and cinematic home theater experiences.</p>
<div class="flex items-center gap-2.5 mt-2">
<span class="px-2.5 py-1 rounded bg-surface-container text-neon-cyan-bright font-label-sm text-label-sm uppercase tracking-wider border border-surface-glass-stroke">4K UHD 60FPS</span>
<span class="px-2.5 py-1 rounded bg-surface-container text-secondary font-label-sm text-label-sm uppercase tracking-wider border border-surface-glass-stroke">DOLBY VISION / ATMOS</span>
<span class="px-2.5 py-1 rounded bg-surface-container text-status-active-green font-label-sm text-label-sm uppercase tracking-wider border border-surface-glass-stroke">D-PAD OPTIMIZED</span>
</div>
</div>
<div class="flex flex-col gap-space-xs">
<span class="font-label-lg text-label-lg text-on-surface uppercase tracking-wider mb-2">Product</span>
<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-primary transition-colors" href="#features">Core Capabilities</a>
<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-primary transition-colors" href="#screenshots">10-Foot UI Showcase</a>
<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-primary transition-colors" href="#pricing">Lifetime &amp; Yearly Subscriptions</a>
<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-primary transition-colors" href="#download">Direct APK Download</a>
</div>
<div class="flex flex-col gap-space-xs">
<span class="font-label-lg text-label-lg text-on-surface uppercase tracking-wider mb-2">Compatibility</span>
<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-primary transition-colors" href="device.php">Android TV &amp; Google TV</a>
<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-primary transition-colors" href="device.php">Amazon Fire TV Stick 4K</a>
<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-primary transition-colors" href="device.php">NVIDIA Shield Pro</a>
<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-primary transition-colors" href="device.php">Smart TV WebOS / Tizen Apps</a>
</div>
<div class="flex flex-col gap-space-xs">
<span class="font-label-lg text-label-lg text-on-surface uppercase tracking-wider mb-2">Resellers &amp; B2B</span>
<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-primary transition-colors" href="#reseller">Wholesale Credit System</a>
<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-primary transition-colors" href="#reseller">Sub-reseller Hierarchy</a>
<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-primary transition-colors" href="#reseller">API Integration Docs</a>
<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-primary transition-colors" href="#faq">Activation Troubleshooting</a>
</div>
</div>
<div class="p-space-md rounded-xl bg-surface-container-low/70 mb-space-lg border border-surface-glass-stroke">
<div class="flex items-start gap-space-sm">
<span class="material-symbols-outlined text-remote-key-yellow text-[22px] shrink-0 mt-0.5">shield</span>
<div class="flex flex-col gap-1">
<span class="font-label-md text-label-md text-on-surface uppercase tracking-wider">Legal Notice &amp; IPTV Disclaimer</span>
<p class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">Dreamania Player does not provide, host, or contain any media or content. Dreamania Player is strictly an advanced media playback utility software. Users must supply their own legally obtained streams, M3U playlists, or Xtream Codes credentials. Dreamania Player endorses no illegal streaming or copyright infringements.</p>
</div>
</div>
</div>
<div class="flex flex-col sm:flex-row items-center justify-between gap-space-sm pt-space-md bg-surface-container-lowest border-t border-surface-glass-stroke">
<span class="font-body-sm text-body-sm text-on-surface-variant">© 2026 Dreamania Player. Powered by DreamaniaCode. All rights reserved.</span>
<div class="flex items-center gap-space-md">
<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-neon-cyan-bright transition-colors" href="#faq">Documentation</a>
<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-neon-cyan-bright transition-colors" href="#faq">Support Desk</a>
<a class="font-body-sm text-body-sm text-on-surface-variant hover:text-neon-cyan-bright transition-colors" href="https://t.me" rel="noopener" target="_blank">Telegram Group</a>
</div>
</div>
</div>
</footer>
</body></html>