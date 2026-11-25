<?php
// Conveniência de desenvolvimento: requer MySQL local em 3307 recém-inicializado.
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit;
$root=dirname(__DIR__);
if (is_file($root.'/.env')) exit(".env já existe; configuração preservada.\n");
$pdo=new PDO('mysql:host=127.0.0.1;port=3307;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE DATABASE cidadecolaborativa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$password=bin2hex(random_bytes(24));
$pdo->exec("CREATE USER 'cidade_app'@'127.0.0.1' IDENTIFIED BY " . $pdo->quote($password));
$pdo->exec("GRANT ALL ON cidadecolaborativa.* TO 'cidade_app'@'127.0.0.1'");
file_put_contents($root.'/.env',"APP_ENV=local\nAPP_URL=http://127.0.0.1:8080\nDB_HOST=127.0.0.1\nDB_PORT=3307\nDB_DATABASE=cidadecolaborativa\nDB_USERNAME=cidade_app\nDB_PASSWORD=$password\n");
$rootPassword=bin2hex(random_bytes(24));
$pdo->exec("ALTER USER 'root'@'localhost' IDENTIFIED BY " . $pdo->quote($rootPassword));
file_put_contents($root.'/storage/mysql-local-client.ini',"[client]\nuser=root\npassword=$rootPassword\nhost=127.0.0.1\nport=3307\n");
echo "Banco isolado criado; credenciais aleatórias salvas apenas nos arquivos locais ignorados.\n";
