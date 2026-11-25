<?php
declare(strict_types=1);
namespace App\Models;
final class Indicators {
    public static function public(): array {
        $scope="status IN ('PUBLICADA','EM_ACOMPANHAMENTO','RESOLVIDA')";
        return [
            'totals'=>Database::one("SELECT COUNT(*) total,COALESCE(SUM(status='RESOLVIDA'),0) resolvidas,COALESCE(SUM(status='EM_ACOMPANHAMENTO'),0) acompanhamento,COALESCE(SUM(demonstracao=1),0) demonstracao FROM ocorrencias WHERE $scope"),
            'categories'=>Database::all("SELECT c.nome label,COUNT(*) total FROM ocorrencias o JOIN categorias c ON c.id=o.categoria_id WHERE o.$scope GROUP BY c.id,c.nome ORDER BY total DESC"),
            'neighborhoods'=>Database::all("SELECT b.nome label,COUNT(*) total FROM ocorrencias o JOIN bairros b ON b.id=o.bairro_id WHERE o.$scope GROUP BY b.id,b.nome ORDER BY total DESC"),
            'months'=>Database::all("SELECT DATE_FORMAT(publicado_em,'%Y-%m') label,COUNT(*) total FROM ocorrencias WHERE $scope GROUP BY label ORDER BY label DESC LIMIT 12")];
    }
}
