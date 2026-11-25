<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Models\{Database as DB, Occurrence};
use App\Middleware\Auth;
use App\Services\{Validation as V, HttpError, OccurrenceState, OccurrenceService, RateLimiter};

final class OccurrenceController extends Controller {
    public function listing(): void { $this->view('listing',['title'=>'Ocorrências da comunidade','result'=>Occurrence::listing($_GET)]+Occurrence::taxonomy()); }
    public function map(): void { $this->view('map',['title'=>'Mapa da cidade']+Occurrence::taxonomy()); }
    public function api(): void { $this->json(Occurrence::listing($_GET,true)); }
    public function detail(): void {
        $user=Auth::user(); $row=Occurrence::find(V::id($_GET['id']??null),$user);
        $private=$user && (in_array($user['perfil'],['admin','operador'],true) || (int)$user['id']===(int)$row['usuario_id']);
        $history=DB::all('SELECT status_novo,justificativa,criado_em FROM historico_ocorrencias WHERE ocorrencia_id=?' . ($private?' AND publico=0':' AND publico=1') . ' ORDER BY id DESC',[$row['id']]);
        $this->view('detail',['title'=>$row['titulo'],'row'=>$row,'history'=>$history,'images'=>DB::all('SELECT id FROM ocorrencia_imagens WHERE ocorrencia_id=?',[$row['id']]),
            'confirmations'=>DB::one('SELECT COUNT(*) total FROM confirmacoes WHERE ocorrencia_id=?',[$row['id']])['total'],
            'similar'=>$user && in_array($user['perfil'],['admin','operador'],true)?Occurrence::similar($row):[]]);
    }
    public function form(): void {
        $row=empty($_GET['id'])?[]:Occurrence::find(V::id($_GET['id']),Auth::require());
        if ($row && ((int)$row['usuario_id']!==(int)Auth::require()['id'] || !in_array($row['status'],['PENDENTE','CORRECAO_SOLICITADA'],true))) throw new HttpError(403,'Edição não permitida.');
        $this->view('occurrence-form',['title'=>$row?'Editar ocorrência':'Registrar ocorrência','row'=>$row]+Occurrence::taxonomy());
    }
    public function save(): void {
        $user=Auth::require(); RateLimiter::check('occurrence:' . $user['id'],12,3600);
        $id=(new OccurrenceService())->save($_POST,$_FILES['fotos']??[], $user,empty($_POST['id'])?null:V::id($_POST['id']));
        flash('Ocorrência enviada para moderação. Ela ainda não está pública.'); redirect('/ocorrencia',['id'=>$id]);
    }
    public function similar(): void {
        [$lat,$lng]=V::coordinates($_GET['latitude']??null,$_GET['longitude']??null);
        $this->json(['rows'=>Occurrence::similar(['categoria_id'=>V::id($_GET['categoria_id']??null),'latitude'=>$lat,'longitude'=>$lng])]);
    }
    public function image(): void {
        $image=DB::one('SELECT * FROM ocorrencia_imagens WHERE id=?',[V::id($_GET['id']??null)]);
        if (!$image) throw new HttpError(404,'Imagem não encontrada.');
        Occurrence::find((int)$image['ocorrencia_id'],Auth::user());
        $file=ROOT . '/storage/uploads/' . basename($image['caminho_arquivo']);
        if (!is_file($file)) throw new HttpError(404,'Imagem indisponível.');
        header('Content-Type: image/jpeg'); header('Content-Disposition: inline; filename="fotografia.jpg"'); readfile($file);
    }
    public function action(): void {
        $user=Auth::require(); $id=V::id($_POST['id']??null); $row=Occurrence::find($id,$user);
        if (!in_array($row['status'],OccurrenceState::PUBLIC,true)) throw new HttpError(422,'Esta ocorrência não está publicada.');
        $action=$_POST['acao']??''; RateLimiter::check('participation:' . $user['id'],20,3600);
        if ($action==='confirmar') {
            if ((int)$row['usuario_id']===(int)$user['id']) throw new HttpError(422,'Você não pode confirmar sua própria ocorrência.');
            try { DB::insert('confirmacoes',['ocorrencia_id'=>$id,'usuario_id'=>$user['id']]); }
            catch (\PDOException $e) { if ($e->getCode()==='23000') throw new HttpError(422,'Você já confirmou esta ocorrência.'); throw $e; }
            flash('Confirmação comunitária registrada. Ela não equivale a validação oficial.');
        } elseif (in_array($action,['denunciar','resolver'],true)) {
            $data=['ocorrencia_id'=>$id,'usuario_id'=>$user['id'],'descricao'=>V::text($_POST,'descricao',10,2000)];
            if ($action==='denunciar') {
                $motivo=$_POST['motivo']??'';
                if (!in_array($motivo,['Informação falsa','Conteúdo inadequado','Dados pessoais','Duplicidade'],true)) throw new HttpError(422,'Selecione um motivo válido.');
                $data['motivo']=$motivo;
            } elseif ($row['status']==='RESOLVIDA') throw new HttpError(422,'A ocorrência já está resolvida.');
            DB::insert($action==='denunciar'?'denuncias':'solicitacoes_resolucao',$data); flash('Solicitação enviada para análise administrativa.');
        } else throw new HttpError(422,'Ação inválida.');
        redirect('/ocorrencia',['id'=>$id]);
    }
}
