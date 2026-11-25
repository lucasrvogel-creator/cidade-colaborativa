<?php
declare(strict_types=1);
namespace App\Services;

final class RateLimiter {
    public static function check(string $key, int $limit, int $seconds): void {
        $file = ROOT . '/storage/limits/' . hash('sha256', $key) . '.json';
        $handle = fopen($file, 'c+');
        if (!$handle || !flock($handle, LOCK_EX)) throw new HttpError(503, 'Não foi possível verificar o limite de requisições.');
        try {
            $events = json_decode(stream_get_contents($handle), true) ?: [];
            $events = array_values(array_filter($events, fn($time) => $time > time() - $seconds));
            if (count($events) >= $limit) throw new HttpError(429, 'Muitas tentativas em pouco tempo. Aguarde alguns minutos.');
            $events[] = time(); rewind($handle); ftruncate($handle, 0); fwrite($handle, json_encode($events));
        } finally { flock($handle, LOCK_UN); fclose($handle); }
    }
}
