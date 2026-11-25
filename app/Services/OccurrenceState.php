<?php
declare(strict_types=1);
namespace App\Services;
final class OccurrenceState {
    public const PUBLIC = ['PUBLICADA', 'EM_ACOMPANHAMENTO', 'RESOLVIDA'];
    public const TRANSITIONS = [
        'PENDENTE' => ['PUBLICADA', 'REJEITADA', 'CORRECAO_SOLICITADA', 'DUPLICADA'],
        'CORRECAO_SOLICITADA' => ['PENDENTE'],
        'PUBLICADA' => ['EM_ACOMPANHAMENTO', 'RESOLVIDA', 'ARQUIVADA', 'DUPLICADA'],
        'EM_ACOMPANHAMENTO' => ['RESOLVIDA', 'PUBLICADA', 'ARQUIVADA'],
        'RESOLVIDA' => ['EM_ACOMPANHAMENTO'], 'REJEITADA' => [], 'DUPLICADA' => [], 'ARQUIVADA' => []];
    public static function validate(string $from, string $to, string $reason): void {
        if (!in_array($to, self::TRANSITIONS[$from] ?? [], true)) throw new HttpError(422, 'Transição de situação não permitida.');
        if (mb_strlen(trim($reason)) < 5) throw new HttpError(422, 'Informe uma justificativa com pelo menos cinco caracteres.');
    }
}
