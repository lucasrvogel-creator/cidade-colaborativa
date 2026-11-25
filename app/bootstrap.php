<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = ROOT . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) require $file;
    }
});
if (is_file(ROOT . '/.env')) {
    foreach (file(ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        if (getenv(trim($key)) === false) putenv(trim($key) . '=' . trim($value, " \t\n\r\0\x0B\"'"));
    }
}
date_default_timezone_set('America/Sao_Paulo');
require ROOT . '/app/Helpers/functions.php';
foreach (['uploads', 'logs', 'sessions', 'limits'] as $dir) {
    if (!is_dir(ROOT . '/storage/' . $dir)) mkdir(ROOT . '/storage/' . $dir, 0700, true);
}
if (PHP_SAPI !== 'cli') {
    ini_set('display_errors', '0');
    ini_set('session.use_strict_mode', '1');
    session_save_path(ROOT . '/storage/sessions');
    session_name('cidade_session');
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'path' => '/']);
    session_start();
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob: https://tile.openstreetmap.org; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");
    header('Cache-Control: no-store');
}
