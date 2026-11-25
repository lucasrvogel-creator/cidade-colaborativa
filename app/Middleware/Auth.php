<?php
declare(strict_types=1);
namespace App\Middleware;
use App\Models\Database as DB;
use App\Services\HttpError;

final class Auth {
    public static function user(): ?array {
        if (empty($_SESSION['user_id'])) return null;
        $user = DB::one('SELECT id,nome,email,perfil,status,exclusao_solicitada_em FROM usuarios WHERE id=?', [$_SESSION['user_id']]);
        if (!$user || $user['status'] !== 'ativo') { unset($_SESSION['user_id']); return null; }
        return $user;
    }
    public static function require(string $role = 'user'): array {
        $user = self::user();
        if (!$user) throw new HttpError(401, 'Entre em uma conta ativa para continuar.');
        if ($role === 'admin' && $user['perfil'] !== 'admin') throw new HttpError(403, 'Esta área é exclusiva da administração.');
        return $user;
    }
    public static function csrf(): void {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) throw new HttpError(419, 'Sua sessão expirou ou o formulário é inválido. Atualize a página e tente novamente.');
    }
}
