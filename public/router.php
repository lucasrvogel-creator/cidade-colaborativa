<?php
// Router exclusivo para o servidor de desenvolvimento PHP.
$file = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($file && str_starts_with($file, __DIR__ . DIRECTORY_SEPARATOR) && is_file($file) && pathinfo($file, PATHINFO_EXTENSION) !== 'php') return false;
require __DIR__ . '/index.php';
