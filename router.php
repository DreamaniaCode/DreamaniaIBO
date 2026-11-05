<?php
/**
 * Dreamania IBO Pro - Router for PHP Built-in Server
 * Usage: php -S 0.0.0.0:80 router.php
 */

declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '/';

// Serve existing static files directly
if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false;
}

// API and Download Routes
$apiRoutes = [
    '/authenticate',
    '/playlists',
    '/playlists/delete',
    '/delete',
    '/parent-control/update',
    '/player_api.php',
    '/download',
    '/app.apk',
    '/DreamaniaPlayer.apk',
    '/DreamaniaIBOPro.apk',
    '/7454701',
    '/api.php'
];

foreach ($apiRoutes as $route) {
    if ($uri === $route || str_starts_with($uri, $route . '/')) {
        require __DIR__ . '/api.php';
        exit;
    }
}

if ($uri === '/qr.php') {
    require __DIR__ . '/qr.php';
    exit;
}

if ($uri === '/device' || $uri === '/upload' || $uri === '/manage' || $uri === '/manage-playlist' || $uri === '/playlist') {
    require __DIR__ . '/device.php';
    exit;
}

if ($uri === '/credits') {
    require __DIR__ . '/credits.php';
    exit;
}

if ($uri === '/admin' || $uri === '/portal' || $uri === '/panel') {
    $_GET['panel'] = '1';
    require __DIR__ . '/index.php';
    exit;
}

if ($uri === '/landing') {
    require __DIR__ . '/landing.php';
    exit;
}

// Default to main panel / landing
require __DIR__ . '/index.php';
