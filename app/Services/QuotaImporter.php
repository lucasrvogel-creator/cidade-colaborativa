<?php
declare(strict_types=1);
namespace App\Services;
use App\Models\Database as DB;
final class QuotaImporter {
 public static function import(): int {
  $snapshot=json_decode(file_get_contents(ROOT.'/database/data/cotas-defesa-civil.json'),true,512,JSON_THROW_ON_ERROR);
  return DB::transaction(function()use($snapshot){
   $count=0;
   foreach($snapshot['registros'] as $row){
    $key=hash('sha256',$snapshot['url'].'|'.$row['rua']);
    if(DB::one('SELECT id FROM cotas_inundacao WHERE chave_fonte=?',[$key]))continue;
    DB::insert('cotas_inundacao',['bairro_id'=>null,'rua'=>$row['rua'],'trecho'=>'Logradouro conforme tabela de origem; trecho não especificado',
     'cota_metros'=>$row['minima'],'cota_maxima'=>$row['maxima'],'maxima_aberta'=>(int)$row['maxima_aberta'],
     'estacao_referencia'=>$snapshot['estacao'],'referencia'=>$snapshot['referencia'],'fonte'=>$snapshot['fonte'],
     'url_fonte'=>$snapshot['url'],'demonstracao'=>0,'chave_fonte'=>$key,'atualizado_em'=>$snapshot['consultado_em'].' 00:00:00']);$count++;
   }return $count;
  });
 }
}
