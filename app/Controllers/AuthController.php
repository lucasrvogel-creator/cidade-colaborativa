<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Models\Database as DB;
use App\Services\{Validation as V, HttpError, RateLimiter};

final class AuthController extends Controller {
    public function loginForm(): void { $this->view('auth', ['title'=>'Entrar','register'=>false]); }
    public function registerForm(): void { $this->view('auth', ['title'=>'Criar minha conta','register'=>true]); }
    public function register(): void {
        RateLimiter::check('register:' . ($_SERVER['REMOTE_ADDR'] ?? ''), 5, 3600);
        $name = V::text($_POST,'nome',2,120); $email = mb_strtolower(V::text($_POST,'email',5,190));
        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new HttpError(422,'Informe um e-mail válido.');
        if (empty($_POST['termos'])) throw new HttpError(422,'Leia e aceite os termos para criar sua conta.');
        $password = V::password($_POST);
        try { DB::insert('usuarios',['nome'=>$name,'email'=>$email,'senha_hash'=>password_hash($password,PASSWORD_DEFAULT)]); }
        catch (\PDOException $e) { if ($e->getCode() === '23000') throw new HttpError(422,'Não foi possível cadastrar estes dados. Se já possui uma conta, tente entrar.'); throw $e; }
        flash('Conta criada. Entre para registrar sua primeira ocorrência.'); redirect('/entrar');
    }
    public function login(): void {
        $email = mb_strtolower(V::text($_POST,'email',5,190));
        RateLimiter::check('login-ip:' . ($_SERVER['REMOTE_ADDR'] ?? ''), 20, 900);
        RateLimiter::check('login-account:' . $email, 8, 900);
        $user = DB::one('SELECT * FROM usuarios WHERE email=?',[$email]);
        $dummy = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        $valid = password_verify((string)($_POST['senha'] ?? ''), $user['senha_hash'] ?? $dummy);
        if (!$valid || !$user || $user['status'] !== 'ativo') {
            flash('E-mail ou senha inválidos, ou conta indisponível.', 'danger');
            redirect('/entrar');
        }
        session_regenerate_id(true); $_SESSION['user_id']=$user['id']; $_SESSION['csrf']=bin2hex(random_bytes(32));
        redirect($user['perfil'] === 'admin' ? '/admin' : ($user['perfil'] === 'operador' ? '/admin/ocorrencias' : '/painel'));
    }
    public function logout(): void {
        $_SESSION=[]; session_regenerate_id(true); $_SESSION['csrf']=bin2hex(random_bytes(32)); redirect('/');
    }
}
