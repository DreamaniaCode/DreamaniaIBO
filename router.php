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

// API Routes
$apiRoutes = [
    '/authenticate',
    '/playlists',
    '/playlists/delete',
    '/delete',
    '/parent-control/update',
    '/player_api.php',
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

// Default to main panel
require __DIR__ . '/index.php';

