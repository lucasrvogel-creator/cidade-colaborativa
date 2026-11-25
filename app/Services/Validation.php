<?php
declare(strict_types=1);
namespace App\Services;

final class Validation {
    public static function text(array $input, string $key, int $min, int $max): string {
        $value = trim(is_string($input[$key] ?? null) ? $input[$key] : '');
        if (mb_strlen($value) < $min || mb_strlen($value) > $max) throw new HttpError(422, "O campo $key deve conter entre $min e $max caracteres.");
        return $value;
    }
    public static function id(mixed $value): int {
        if (filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) throw new HttpError(422, 'Identificador inválido.');
        return (int) $value;
    }
    public static function coordinates(mixed $lat, mixed $lng): array {
        if (!is_numeric($lat) || !is_numeric($lng) || !is_finite((float)$lat) || !is_finite((float)$lng)
            || (float)$lat < -90 || (float)$lat > 90 || (float)$lng < -180 || (float)$lng > 180) throw new HttpError(422, 'Selecione uma localização válida no mapa.');
        return [(float)$lat, (float)$lng];
    }
    public static function password(array $input): string {
        $value = self::text($input, 'senha', 10, 72);
        if (strlen($value) > 72 || !hash_equals($value, (string)($input['confirmacao'] ?? ''))) throw new HttpError(422, 'As senhas devem ser iguais e ter no máximo 72 bytes.');
        return $value;
    }
    public static function sourceUrl(string $value): string {
        if (!filter_var($value, FILTER_VALIDATE_URL) || !in_array(parse_url($value, PHP_URL_SCHEME), ['https', 'http'], true)) throw new HttpError(422, 'Informe um endereço de fonte HTTP ou HTTPS válido.');
        return $value;
    }
}
