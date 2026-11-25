<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Models\Database as DB;
use App\Middleware\Auth;
use App\Services\{Validation as V, HttpError};

final class CitizenController extends Controller {
    public function dashboard(): void {
        $user=Auth::require();
        $this->view('citizen',['title'=>'Minha participação','rows'=>DB::all('SELECT * FROM ocorrencias WHERE usuario_id=? ORDER BY criado_em DESC LIMIT 100',[$user['id']]),
            'notifications'=>DB::all('SELECT * FROM notificacoes WHERE usuario_id=? ORDER BY criado_em DESC LIMIT 100',[$user['id']])]);
    }
    public function notifications(): void { $this->dashboard(); }
    public function read(): void { DB::query('UPDATE notificacoes SET lida_em=NOW() WHERE usuario_id=? AND lida_em IS NULL',[Auth::require()['id']]); flash('Notificações marcadas como lidas.'); redirect('/painel'); }
    public function profile(): void { $this->view('profile',['title'=>'Meu perfil']); }
    public function update(): void {
        $user=Auth::require(); $stored=DB::one('SELECT senha_hash FROM usuarios WHERE id=?',[$user['id']]);
        if (!password_verify((string)($_POST['senha_atual']??''),$stored['senha_hash'])) throw new HttpError(422,'Senha atual inválida.');
        if (($_POST['acao']??'')==='excluir') {
            DB::query('UPDATE usuarios SET exclusao_solicitada_em=NOW() WHERE id=?',[$user['id']]); flash('Solicitação registrada. A administração revisará a anonimização dos relatos e fotografias.'); redirect('/perfil');
        }
        $name=V::text($_POST,'nome',2,120); $email=mb_strtolower(V::text($_POST,'email',5,190));
        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new HttpError(422,'E-mail inválido.');
        $hash=empty($_POST['senha'])?$stored['senha_hash']:password_hash(V::password($_POST),PASSWORD_DEFAULT);
        try { DB::query('UPDATE usuarios SET nome=?,email=?,senha_hash=? WHERE id=?',[$name,$email,$hash,$user['id']]); }
        catch (\PDOException $e) { if ($e->getCode()==='23000') throw new HttpError(422,'Não foi possível atualizar estes dados.'); throw $e; }
        session_regenerate_id(true); flash('Perfil atualizado.'); redirect('/perfil');
    }
}
