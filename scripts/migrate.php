<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit;
require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Models\Database as DB;

DB::query('CREATE TABLE IF NOT EXISTS migrations (nome VARCHAR(190) PRIMARY KEY, executada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP)');
foreach (glob(ROOT . '/database/migrations/*.sql') as $file) {
    $name = basename($file);
    if (DB::one('SELECT nome FROM migrations WHERE nome=?', [$name])) continue;
    DB::connection()->exec(file_get_contents($file));
    DB::insert('migrations', ['nome' => $name]);
    echo "Aplicada: $name\n";
}
