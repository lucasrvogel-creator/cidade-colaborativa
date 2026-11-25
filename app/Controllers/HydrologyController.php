<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Models\{Database as DB, Occurrence};
use App\Services\{DatabaseHydrology, Validation as V, HttpError, OccurrenceService};
use App\Middleware\Auth;

final class HydrologyController extends Controller {
    public function quotas(): void {
        $search=is_string($_GET['busca']??null)?trim($_GET['busca']):'';
        $page=max(1,(int)($_GET['page']??1)); $offset=($page-1)*50;
        $staff=Auth::user(); $admin=$staff && $staff['perfil']==='admin';
        $where=$admin?'1=1':'ativo=1'; $params=[];
        if ($search!=='') {$where.=' AND rua LIKE ?'; $params[]='%'.$search.'%';}
        $this->view('quotas',['title'=>'Cotas de inundação por rua','search'=>$search,'page'=>$page,'admin'=>$admin,
            'count'=>DB::one("SELECT COUNT(*) total FROM cotas_inundacao WHERE $where",$params)['total'],
            'rows'=>DB::all("SELECT * FROM cotas_inundacao WHERE $where ORDER BY rua LIMIT 50 OFFSET $offset",$params)]);
    }
    public function page(): void {
        $stations=DB::all('SELECT DISTINCT estacao,referencia FROM niveis_rio UNION SELECT DISTINCT estacao_referencia estacao,referencia FROM cotas_inundacao ORDER BY estacao');
        $station=is_string($_GET['estacao']??null)?$_GET['estacao']:($stations[0]['estacao']??'');
        $reference=is_string($_GET['referencia']??null)?$_GET['referencia']:($stations[0]['referencia']??'');
        $provider=new DatabaseHydrology(); $result=null;
        if (isset($_GET['nivel'])) {
            if (!is_numeric($_GET['nivel'])) throw new HttpError(422,'Nível inválido.');
            $result=$provider->simulate($station,$reference,(float)$_GET['nivel'],($_GET['demonstracao']??'0')==='1');
        }
        $this->view('hydrology',['title'=>'Enchentes: informação com responsabilidade','stations'=>$stations,'station'=>$station,'reference'=>$reference,'history'=>$provider->history($station,$reference),'result'=>$result]);
    }
    public function admin(): void {
        $this->view('admin/hydrology',['title'=>'Dados hidrológicos','levels'=>DB::all('SELECT * FROM niveis_rio ORDER BY registrado_em DESC LIMIT 50'),'quotas'=>DB::all('SELECT * FROM cotas_inundacao ORDER BY atualizado_em DESC LIMIT 50')]+Occurrence::taxonomy());
    }
    public function save(): void {
        if (($_POST['acao']??'')==='ativar_cota') {
            $id=V::id($_POST['id']??null);
            DB::transaction(function () use ($id) {
                if (!DB::one('SELECT id FROM cotas_inundacao WHERE id=?',[$id])) throw new HttpError(404,'Cota não encontrada.');
                DB::query('UPDATE cotas_inundacao SET ativo=? WHERE id=?',[isset($_POST['ativo'])?1:0,$id]);
                OccurrenceService::audit(Auth::require('admin')['id'],'situacao_cota','cotas_inundacao',$id,'Disponibilidade atualizada.');
            }); flash('Cota atualizada.'); redirect('/cotas');
        }
        $type=$_POST['tipo']??''; if (!in_array($type,['nivel','cota'],true)) throw new HttpError(422,'Tipo de dado inválido.');
        $level=$_POST['valor']??null; if (!is_numeric($level) || (float)$level<0 || (float)$level>100) throw new HttpError(422,'Valor deve estar entre 0 e 100 metros.');
        $data=['referencia'=>V::text($_POST,'referencia',2,120),'fonte'=>V::text($_POST,'fonte',3,190),'url_fonte'=>V::sourceUrl(V::text($_POST,'url_fonte',8,500)),'demonstracao'=>isset($_POST['demonstracao'])?1:0];
        $station=V::text($_POST,'estacao',2,120);
        if ($type==='nivel') {
            $date=V::text($_POST,'registrado_em',16,19); $parsed=\DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $date);
            if (!$parsed || $parsed->format('Y-m-d\TH:i')!==$date || $parsed>new \DateTimeImmutable()) throw new HttpError(422,'Informe uma data/hora de medição válida, não futura.');
            $data+=['estacao'=>$station,'nivel_metros'=>(float)$level,'registrado_em'=>$parsed->format('Y-m-d H:i:s')];
        } else $data+=['estacao_referencia'=>$station,'cota_metros'=>(float)$level,'bairro_id'=>V::id($_POST['bairro_id']??null),'rua'=>V::text($_POST,'rua',3,160),'trecho'=>V::text($_POST,'trecho',3,255)];
        DB::transaction(function () use ($type,$data) {
            $table=$type==='nivel'?'niveis_rio':'cotas_inundacao'; $id=DB::insert($table,$data);
            OccurrenceService::audit(Auth::require('admin')['id'],'inserir_dado_hidrologico',$table,$id,'Registro administrativo com fonte e referência.');
        }); flash('Dado registrado com fonte e referência.'); redirect('/admin/enchentes');
    }
}
