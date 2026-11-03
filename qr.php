<?php
/**
 * Dreamania IBO Pro - QR Code Generator
 * Outputs inline SVG or redirects to high-speed QR rendering
 */

declare(strict_types=1);

$text = $_GET['text'] ?? ($_GET['data'] ?? '');
if (empty($text)) {
    $text = "Dreamania IBO Pro";
}

// Redirect or stream QR from reliable fast generator
$encoded = urlencode($text);
header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=86400');

// Stream SVG QR code from API or generate placeholder SVG
$svgUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&format=svg&data=" . $encoded;

$ctx = stream_context_create([
    'http' => ['timeout' => 3],
    'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false]
]);

$svg = @file_get_contents($svgUrl, false, $ctx);
if ($svg !== false && str_contains($svg, '<svg')) {
    echo $svg;
    exit;
}

// Fallback pure SVG display with text
echo '<svg xmlns="http://www.w3.org/2000/svg" width="250" height="250" viewBox="0 0 250 250">
  <rect width="250" height="250" fill="#111827" rx="12"/>
  <text x="50%" y="45%" text-anchor="middle" fill="#fff" font-family="sans-serif" font-size="14" font-weight="bold">Dreamania QR</text>
  <text x="50%" y="60%" text-anchor="middle" fill="#9ca3af" font-family="sans-serif" font-size="11">' . htmlspecialchars(substr($text, 0, 30)) . '</text>
</svg>';

