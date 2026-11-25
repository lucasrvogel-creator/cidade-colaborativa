<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__) . '/app/bootstrap.php';
use App\Models\Database as DB;

if (DB::all('SHOW TABLES')) { fwrite(STDERR, "Instalação interrompida: use um banco vazio.\n"); exit(1); }
DB::connection()->exec(file_get_contents(ROOT . '/database/schema.sql'));
DB::connection()->exec(file_get_contents(ROOT . '/database/seed.sql'));
echo "Estrutura instalada com dados explicitamente demonstrativos.\n";
