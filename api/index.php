<?php

$publicDirectory = realpath(__DIR__.'/../public');
$requestPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$assetPath = realpath($publicDirectory.$requestPath);
$assetContentTypes = [
    'css' => 'text/css; charset=UTF-8',
    'js' => 'text/javascript; charset=UTF-8',
    'ico' => 'image/x-icon',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'svg' => 'image/svg+xml',
    'webp' => 'image/webp',
    'woff' => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf' => 'font/ttf',
    'eot' => 'application/vnd.ms-fontobject',
];
$assetExtension = strtolower(pathinfo($assetPath ?: '', PATHINFO_EXTENSION));

if (
    $publicDirectory !== false
    && $assetPath !== false
    && str_starts_with($assetPath, $publicDirectory.DIRECTORY_SEPARATOR)
    && is_file($assetPath)
    && isset($assetContentTypes[$assetExtension])
) {
    header('Content-Type: '.$assetContentTypes[$assetExtension]);
    header('Cache-Control: public, max-age=3600');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
        readfile($assetPath);
    }

    exit;
}

require __DIR__.'/../public/index.php';
