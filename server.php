<?php

$publicPath = getcwd();
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');

$types = [
    'js' => 'text/javascript; charset=utf-8',
    'mjs' => 'text/javascript; charset=utf-8',
    'css' => 'text/css; charset=utf-8',
    'json' => 'application/json',
    'svg' => 'image/svg+xml',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'avif' => 'image/avif',
    'ico' => 'image/x-icon',
    'woff' => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf' => 'font/ttf',
    'otf' => 'font/otf',
    'txt' => 'text/plain; charset=utf-8',
    'webmanifest' => 'application/manifest+json',
];

$file = $uri !== '/' ? realpath($publicPath.$uri) : false;
$extension = $file ? strtolower(pathinfo($file, PATHINFO_EXTENSION)) : '';

if (
    $file === false
    || ! is_file($file)
    || ! str_starts_with($file, rtrim(realpath($publicPath), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR)
    || ! isset($types[$extension])
    || ! in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD'], true)
) {
    // Compress pages and API responses the way the nginx configuration does.
    ini_set('zlib.output_compression', '1');

    return require __DIR__.'/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php';
}

$immutable = str_starts_with($uri, '/build/assets/');
$body = $file;
$encoding = null;
$accepted = [];
foreach (explode(',', strtolower($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '')) as $part) {
    $pieces = array_map('trim', explode(';', $part));
    $quality = preg_match('/^q=([\d.]+)$/', $pieces[1] ?? '', $q) ? (float) $q[1] : 1.0;
    $accepted[$pieces[0]] = $quality;
}
foreach (['br' => '.br', 'gzip' => '.gz'] as $name => $suffix) {
    if (($accepted[$name] ?? 0) > 0 && is_file($file.$suffix)) {
        $body = $file.$suffix;
        $encoding = $name;
        break;
    }
}

$etag = sprintf('"%x-%x%s"', filemtime($body), filesize($body), $encoding ? '-'.$encoding : '');

header('Content-Type: '.$types[$extension]);
header('Vary: Accept-Encoding');
header('ETag: '.$etag);
header('Cache-Control: '.($immutable ? 'public, max-age=31536000, immutable' : 'public, no-cache'));
header('X-Content-Type-Options: nosniff');

$match = array_map('trim', explode(',', $_SERVER['HTTP_IF_NONE_MATCH'] ?? ''));
if (in_array($etag, $match, true) || in_array('W/'.$etag, $match, true)) {
    http_response_code(304);

    return true;
}

if ($encoding) {
    header('Content-Encoding: '.$encoding);
}
header('Content-Length: '.filesize($body));

if ($_SERVER['REQUEST_METHOD'] !== 'HEAD') {
    readfile($body);
}

return true;
