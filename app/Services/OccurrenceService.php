<?php
declare(strict_types=1);
namespace App\Services;
use App\Models\Database as DB;

final class OccurrenceService {
    public function validate(array $input): array {
        $data=['titulo'=>Validation::text($input,'titulo',5,160),'descricao'=>Validation::text($input,'descricao',20,5000),
            'categoria_id'=>Validation::id($input['categoria_id']??null),'bairro_id'=>Validation::id($input['bairro_id']??null),
            'endereco_referencia'=>Validation::text($input,'endereco_referencia',0,255)];
        [$data['latitude'],$data['longitude']]=Validation::coordinates($input['latitude']??null,$input['longitude']??null);
        if (!DB::one('SELECT id FROM categorias WHERE id=? AND ativo=1',[$data['categoria_id']]) || !DB::one('SELECT id FROM bairros WHERE id=? AND ativo=1',[$data['bairro_id']])) throw new HttpError(422,'Selecione categoria e bairro ativos.');
        $data['subcategoria_id']=empty($input['subcategoria_id'])?null:Validation::id($input['subcategoria_id']);
        if ($data['subcategoria_id'] && !DB::one('SELECT id FROM subcategorias WHERE id=? AND categoria_id=? AND ativo=1',[$data['subcategoria_id'],$data['categoria_id']])) throw new HttpError(422,'A subcategoria não pertence à categoria selecionada.');
        if (empty($input['responsabilidade'])) throw new HttpError(422,'Confirme sua responsabilidade pelo relato e pelas fotografias.');
        return $data;
    }
    public function save(array $input, array $files, array $user, ?int $id=null): int {
        $data=$this->validate($input); $images=[];
        try {
            return DB::transaction(function () use ($data,$files,$user,$id,&$images) {
                $existing=0; $old=null;
                if ($id) {
                    $old=DB::one('SELECT * FROM ocorrencias WHERE id=? FOR UPDATE',[$id]);
                    if (!$old || (int)$old['usuario_id']!==(int)$user['id'] || !in_array($old['status'],['PENDENTE','CORRECAO_SOLICITADA'],true)) throw new HttpError(403,'Você só pode editar suas ocorrências pendentes ou devolvidas para correção.');
                    $existing=(int)DB::one('SELECT COUNT(*) total FROM ocorrencia_imagens WHERE ocorrencia_id=?',[$id])['total'];
                }
                $images=ImageUpload::receive($files,$existing);
                if ($id) {
                    $sets=implode(',',array_map(fn($key)=>"$key=?",array_keys($data)));
                    DB::query("UPDATE ocorrencias SET $sets,status='PENDENTE' WHERE id=?",[...array_values($data),$id]);
                } else { $id=DB::insert('ocorrencias',$data+['usuario_id'=>$user['id']]); }
                foreach ($images as $image) DB::insert('ocorrencia_imagens',$image+['ocorrencia_id'=>$id]);
                DB::insert('historico_ocorrencias',['ocorrencia_id'=>$id,'usuario_id'=>$user['id'],'status_anterior'=>$old['status']??null,'status_novo'=>'PENDENTE','justificativa'=>$old?'Registro editado pelo autor e enviado para análise.':'Registro enviado para análise.','publico'=>0]);
                return $id;
            });
        } catch (\Throwable $e) { ImageUpload::cleanup($images); throw $e; }
    }
    public function transition(int $id, string $to, string $reason, array $admin, ?int $principal=null): void {
        if (!in_array($admin['perfil'],['admin','operador'],true)) throw new HttpError(403,'Apenas administradores podem moderar.');
        DB::transaction(function () use ($id,$to,$reason,$admin,$principal) {
            $old=DB::one('SELECT * FROM ocorrencias WHERE id=? FOR UPDATE',[$id]);
            if (!$old) throw new HttpError(404,'Ocorrência não encontrada.');
            if ($old['status']==='CORRECAO_SOLICITADA') throw new HttpError(422,'Aguarde o autor reenviar a ocorrência.');
            OccurrenceState::validate($old['status'],$to,$reason);
            if ($to==='DUPLICADA') {
                if (!$principal || $principal===$id || !DB::one("SELECT id FROM ocorrencias WHERE id=? AND status IN ('PUBLICADA','EM_ACOMPANHAMENTO','RESOLVIDA') FOR UPDATE",[$principal])) throw new HttpError(422,'Vincule a uma ocorrência principal pública e diferente desta.');
                if (DB::one('SELECT id FROM ocorrencias WHERE ocorrencia_principal_id=? LIMIT 1',[$id])) throw new HttpError(422,'Este registro já é principal de outras duplicadas.');
            }
            $public=in_array($to,OccurrenceState::PUBLIC,true);
            DB::query('UPDATE ocorrencias SET status=?,ocorrencia_principal_id=?,publicado_em=CASE WHEN ?=1 THEN COALESCE(publicado_em,NOW()) ELSE publicado_em END,resolvido_em=CASE WHEN ?=1 THEN NOW() ELSE NULL END WHERE id=?',[$to,$to==='DUPLICADA'?$principal:null,(int)$public,(int)($to==='RESOLVIDA'),$id]);
            // A justificativa pode conter dados privados: apenas mensagem genérica vai ao histórico público.
            DB::insert('historico_ocorrencias',['ocorrencia_id'=>$id,'usuario_id'=>$admin['id'],'status_anterior'=>$old['status'],'status_novo'=>$to,'justificativa'=>$reason,'publico'=>0]);
            if ($public) DB::insert('historico_ocorrencias',['ocorrencia_id'=>$id,'usuario_id'=>$admin['id'],'status_anterior'=>null,'status_novo'=>$to,'justificativa'=>'Situação atualizada pela moderação da plataforma.','publico'=>1]);
            DB::insert('notificacoes',['usuario_id'=>$old['usuario_id'],'ocorrencia_id'=>$id,'titulo'=>'Atualização da ocorrência','mensagem'=>status_label($to) . ': ' . $reason]);
            self::audit($admin['id'],'alterar_situacao','ocorrencias',$id,$old['status'] . ' → ' . $to);
        });
    }
    public static function audit(int $user,string $action,string $entity,int $id,string $details): void {
        DB::insert('auditoria',['usuario_id'=>$user,'acao'=>$action,'entidade'=>$entity,'entidade_id'=>$id,'detalhes'=>$details]);
    }
}
