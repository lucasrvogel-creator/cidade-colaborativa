<?php
declare(strict_types=1);
namespace App\Services;
use App\Models\Database as DB;
final class DatabaseHydrology implements HydrologyProvider {
    public function history(string $station,string $reference): array {
        return DB::all('SELECT * FROM niveis_rio WHERE estacao=? AND referencia=? ORDER BY registrado_em DESC LIMIT 200',[$station,$reference]);
    }
    public function simulate(string $station,string $reference,float $level,bool $demo): array {
        if (!is_finite($level) || $level<0 || $level>100) throw new HttpError(422,'Informe um nível hipotético entre 0 e 100 metros.');
        if (!DB::one('SELECT id FROM cotas_inundacao WHERE ativo=1 AND estacao_referencia=? AND referencia=? AND demonstracao=? LIMIT 1',[$station,$reference,(int)$demo])) throw new HttpError(422,'Não há cotas compatíveis para esta estação, referência e tipo de dado.');
        return DB::all('SELECT c.*,b.nome bairro FROM cotas_inundacao c LEFT JOIN bairros b ON b.id=c.bairro_id WHERE c.ativo=1 AND estacao_referencia=? AND referencia=? AND demonstracao=? AND cota_metros<=? ORDER BY cota_metros,rua',[$station,$reference,(int)$demo,$level]);
    }
}
