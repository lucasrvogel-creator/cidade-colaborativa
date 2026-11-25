<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\HttpError;
use App\Middleware\Auth;

try {
    $routes = require ROOT . '/routes/web.php';
    $path = $_GET['r'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $key = $_SERVER['REQUEST_METHOD'] . ' ' . $path;
    if (!is_string($path) || !isset($routes[$key])) throw new HttpError(404, 'Página não encontrada.');
    [$class, $method, $role] = $routes[$key];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') Auth::csrf();
    if ($role) Auth::require($role);
    (new $class())->$method();
} catch (Throwable $error) {
    $status = $error instanceof HttpError ? $error->status : 500;
    http_response_code($status);
    $message = $status === 500 ? 'Não foi possível concluir a operação. Verifique a configuração local ou tente novamente mais tarde.' : $error->getMessage();
    if ($status === 500) error_log(date('c') . ' ' . get_class($error) . ' código=' . $error->getCode() . ' arquivo=' . $error->getFile() . ':' . $error->getLine() . PHP_EOL, 3, ROOT . '/storage/logs/errors.log');
    if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
        header('Content-Type: application/json; charset=utf-8'); echo json_encode(['error' => $message]);
    } else { (new App\Controllers\Controller())->view('error', ['title' => 'Não foi possível continuar', 'message' => $message]); }
}
