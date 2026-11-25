<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli')exit;
require dirname(__DIR__).'/app/bootstrap.php';
echo App\Services\QuotaImporter::import()." cotas importadas. Registros existentes foram preservados.\n";
