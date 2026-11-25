<?php
declare(strict_types=1);
namespace App\Services;
interface HydrologyProvider {
    public function history(string $station, string $reference): array;
    public function simulate(string $station, string $reference, float $level, bool $demo): array;
}
