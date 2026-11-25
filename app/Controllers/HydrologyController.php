<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Models\{Database as DB, Occurrence};
use App\Services\{DatabaseHydrology, Validation as V, HttpError, OccurrenceService};
use App\Middleware\Auth;

final class HydrologyController extends Controller {
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
