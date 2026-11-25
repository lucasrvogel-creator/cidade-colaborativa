<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Models\{Database as DB, Occurrence, Indicators};
use App\Middleware\Auth;
use App\Services\{Validation as V, HttpError, OccurrenceService};

final class AdminController extends Controller {
    public function dashboard(): void {
        $stats=Indicators::public();
        $stats['admin']=DB::one("SELECT COALESCE(SUM(status='PENDENTE'),0) pendentes,COALESCE(SUM(status='REJEITADA'),0) rejeitadas,ROUND(AVG(TIMESTAMPDIFF(HOUR,criado_em,publicado_em)),1) moderacao_horas,ROUND(AVG(TIMESTAMPDIFF(HOUR,publicado_em,resolvido_em)),1) resolucao_horas FROM ocorrencias");
        $this->view('admin/dashboard',['title'=>'Visão da administração','stats'=>$stats,
            'reports'=>DB::one("SELECT COUNT(*) total FROM denuncias WHERE status='PENDENTE'")['total'],
            'requests'=>DB::one("SELECT COUNT(*) total FROM solicitacoes_resolucao WHERE status='PENDENTE'")['total']]);
    }
    public function occurrences(): void {
        $status=$_GET['situacao']??'PENDENTE'; $where=in_array($status,array_keys(\App\Services\OccurrenceState::TRANSITIONS),true)?'WHERE o.status=?':'';
        $page=max(1,(int)($_GET['page']??1)); $offset=($page-1)*30;
        $rows=DB::all("SELECT o.*,b.nome bairro,c.nome categoria FROM ocorrencias o JOIN bairros b ON b.id=o.bairro_id JOIN categorias c ON c.id=o.categoria_id $where ORDER BY o.criado_em DESC LIMIT 30 OFFSET $offset",$where?[$status]:[]);
        $this->view('admin/occurrences',['title'=>'Moderação de ocorrências','rows'=>$rows,'status'=>$status,'page'=>$page]);
    }
    public function moderate(): void {
        $id=V::id($_POST['id']??null); $reason=V::text($_POST,'justificativa',5,2000);
        (new OccurrenceService())->transition($id,(string)($_POST['situacao']??''),$reason,Auth::require('admin'),empty($_POST['principal_id'])?null:V::id($_POST['principal_id']));
        flash('Situação atualizada; histórico e notificação registrados.'); redirect('/ocorrencia',['id'=>$id]);
    }
    private function taxonomyType(): string {
        $type=$_GET['tipo']??$_POST['tipo']??'categorias';
        if (!in_array($type,['categorias','subcategorias','bairros'],true)) throw new HttpError(404,'Cadastro não encontrado.'); return $type;
    }
    public function taxonomy(): void {
        $type=$this->taxonomyType();
        $this->view('admin/taxonomy',['title'=>'Gerenciar ' . $type,'type'=>$type,'rows'=>DB::all("SELECT * FROM $type ORDER BY nome"),'categories'=>DB::all('SELECT * FROM categorias ORDER BY nome')]);
    }
    public function saveTaxonomy(): void {
        $type=$this->taxonomyType(); $name=V::text($_POST,'nome',2,120); $id=empty($_POST['id'])?null:V::id($_POST['id']); $active=isset($_POST['ativo'])?1:0;
        DB::transaction(function () use ($type,$name,$id,$active) {
            if ($id) {
                if (!DB::one("SELECT id FROM $type WHERE id=?",[$id])) throw new HttpError(404,'Registro não encontrado.');
                DB::query("UPDATE $type SET nome=?,ativo=? WHERE id=?",[$name,$active,$id]);
            } else {
                $data=['nome'=>$name,'ativo'=>$active];
                if ($type==='subcategorias') $data['categoria_id']=V::id($_POST['categoria_id']??null);
                $id=DB::insert($type,$data);
            }
            OccurrenceService::audit(Auth::require('admin')['id'],'salvar_cadastro',$type,$id,'Nome e situação atualizados.');
        });
        flash('Cadastro salvo. Registros históricos são preservados.'); redirect('/admin/cadastros',['tipo'=>$type]);
    }
    public function users(): void { $this->view('admin/users',['title'=>'Usuários e privacidade','rows'=>DB::all('SELECT id,nome,email,perfil,status,exclusao_solicitada_em FROM usuarios ORDER BY id DESC LIMIT 200')]); }
    public function saveUser(): void {
        $admin=Auth::require('admin'); $id=V::id($_POST['id']??null);
        if ($id===(int)$admin['id']) throw new HttpError(422,'Não é permitido alterar sua própria permissão ou situação por esta tela.');
        DB::transaction(function () use ($id,$admin) {
            $row=DB::one('SELECT * FROM usuarios WHERE id=? FOR UPDATE',[$id]); if (!$row) throw new HttpError(404,'Usuário não encontrado.');
            if (($_POST['acao']??'')==='anonimizar') {
                if (!$row['exclusao_solicitada_em'] || empty($_POST['revisao'])) throw new HttpError(422,'É necessária uma solicitação do titular e confirmação de revisão de textos e fotografias.');
                DB::query("UPDATE usuarios SET nome='Pessoa anonimizada',email=?,senha_hash=?,status='anonimizado',perfil='cidadao' WHERE id=?",['anonimo-'.$id.'@example.invalid',password_hash(bin2hex(random_bytes(32)),PASSWORD_DEFAULT),$id]);
            } else {
                $role=$_POST['perfil']??''; $status=$_POST['status']??'';
                if (!in_array($role,['cidadao','admin'],true) || !in_array($status,['ativo','suspenso'],true) || $row['status']==='anonimizado') throw new HttpError(422,'Perfil ou situação inválida.');
                DB::query('UPDATE usuarios SET perfil=?,status=? WHERE id=?',[$role,$status,$id]);
            }
            OccurrenceService::audit($admin['id'],'gerenciar_usuario','usuarios',$id,'Operação: ' . (($_POST['acao']??'')==='anonimizar'?'anonimização':'permissões/situação'));
        }); flash('Usuário atualizado.'); redirect('/admin/usuarios');
    }
    private function queueType(): string {
        $type=$_GET['tipo']??$_POST['tipo']??'denuncias'; if (!in_array($type,['denuncias','solicitacoes_resolucao'],true)) throw new HttpError(404,'Fila inválida.'); return $type;
    }
    public function queue(): void {
        $type=$this->queueType(); $this->view('admin/queue',['title'=>$type==='denuncias'?'Denúncias':'Sugestões de resolução','type'=>$type,'rows'=>DB::all("SELECT q.*,o.titulo FROM $type q JOIN ocorrencias o ON o.id=q.ocorrencia_id ORDER BY q.status='PENDENTE' DESC,q.id DESC LIMIT 100")]);
    }
    public function decideQueue(): void {
        $type=$this->queueType(); $id=V::id($_POST['id']??null); $reason=V::text($_POST,'parecer',5,2000); $status=$_POST['status']??'';
        if (!in_array($status,['RESOLVIDA','ARQUIVADA'],true)) throw new HttpError(422,'Parecer inválido.');
        DB::transaction(function () use ($type,$id,$reason,$status) {
            $row=DB::one("SELECT * FROM $type WHERE id=? AND status='PENDENTE' FOR UPDATE",[$id]); if (!$row) throw new HttpError(422,'Solicitação já analisada ou inexistente.');
            DB::query("UPDATE $type SET status=?,parecer=?,administrador_id=?,analisado_em=NOW() WHERE id=?",[$status,$reason,Auth::require('admin')['id'],$id]);
            OccurrenceService::audit(Auth::require('admin')['id'],'analisar_solicitacao',$type,$id,$status);
        }); flash('Análise registrada. A situação da ocorrência é alterada separadamente na tela de moderação.'); redirect('/admin/solicitacoes',['tipo'=>$type]);
    }
    public function audit(): void { $this->view('admin/audit',['title'=>'Trilha de auditoria','rows'=>DB::all('SELECT a.*,u.nome FROM auditoria a JOIN usuarios u ON u.id=a.usuario_id ORDER BY a.id DESC LIMIT 200')]); }
    public function notices(): void { $this->view('admin/notices',['title'=>'Gerenciar avisos','rows'=>DB::all('SELECT * FROM avisos ORDER BY id DESC LIMIT 100')]); }
    public function saveNotice(): void {
        DB::transaction(function () {
            if (!empty($_POST['id'])) { $id=V::id($_POST['id']); DB::query("UPDATE avisos SET status='ARQUIVADO' WHERE id=?",[$id]); }
            else { $id=DB::insert('avisos',['administrador_id'=>Auth::require('admin')['id'],'titulo'=>V::text($_POST,'titulo',5,160),'descricao'=>V::text($_POST,'descricao',10,3000),'fonte'=>V::text($_POST,'fonte',3,190),'url_fonte'=>V::sourceUrl(V::text($_POST,'url_fonte',8,500))]); }
            OccurrenceService::audit(Auth::require('admin')['id'],'salvar_aviso','avisos',$id,'Publicação ou arquivamento de aviso.');
        }); flash('Aviso atualizado.'); redirect('/admin/avisos');
    }
}
