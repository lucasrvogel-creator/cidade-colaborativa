<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Middleware\Auth;

class Controller {
    public function view(string $template, array $data = []): void {
        // Views are selected by controllers, never by request input.
        extract($data, EXTR_SKIP);
        try { $user = Auth::user(); } catch (\Throwable) { $user = null; }
        ob_start(); require ROOT . '/app/Views/' . $template . '.php'; $content = ob_get_clean();
        require ROOT . '/app/Views/layout.php';
    }
    protected function json(array $data): void { header('Content-Type: application/json; charset=utf-8'); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR); }
}
