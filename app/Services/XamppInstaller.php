<?php
declare(strict_types=1);
namespace App\Services;

use App\Models\Database as DB;
use App\Middleware\Auth;
use PDO;
use Throwable;

final class XamppInstaller {
    public function show(?string $error = null, array $values = []): void {
        $local = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', '::ffff:127.0.0.1'], true);
        if (!$local) {
            http_response_code(403);
            $error = 'A configuração inicial só pode ser feita neste computador. Abra o projeto usando localhost.';
        }
        $values += [
            'mysql_host' => '127.0.0.1', 'mysql_port' => '3306', 'database' => 'cidadecolaborativa',
            'mysql_username' => 'root', 'admin_name' => 'Administrador local',
            'admin_email' => 'admin@cidadecolaborativa.local',
        ];
        require ROOT . '/app/Views/setup.php';
    }

    public function install(): never {
        $this->requireLocal();
        Auth::csrf();
        if (is_file(ROOT . '/.env')) redirect('/');

        $values = [
            'mysql_host' => trim((string)($_POST['mysql_host'] ?? '')),
            'mysql_port' => trim((string)($_POST['mysql_port'] ?? '')),
            'database' => trim((string)($_POST['database'] ?? '')),
            'mysql_username' => trim((string)($_POST['mysql_username'] ?? '')),
            'admin_name' => trim((string)($_POST['admin_name'] ?? '')),
            'admin_email' => mb_strtolower(trim((string)($_POST['admin_email'] ?? ''))),
        ];

        try {
            $this->validate($values, (string)($_POST['admin_password'] ?? ''), (string)($_POST['admin_password_confirmation'] ?? ''));
            $this->createProject($values, (string)($_POST['mysql_password'] ?? ''), (string)($_POST['admin_password'] ?? ''));
            flash('Instalação concluída. Entre com a conta de administrador que você acabou de criar.');
            redirect('/entrar');
        } catch (Throwable $error) {
            error_log('[CidadeColaborativa setup] ' . $error->getMessage());
            $message = $error instanceof HttpError
                ? $error->getMessage()
                : 'Não foi possível preparar o banco. Inicie o MySQL no XAMPP e confira host, porta e credenciais.';
            http_response_code($error instanceof HttpError ? $error->status : 422);
            $this->show($message, $values);
            exit;
        }
    }

    private function validate(array $values, string $password, string $confirmation): void {
        if (!in_array($values['mysql_host'], ['localhost', '127.0.0.1'], true)) {
            throw new HttpError(422, 'O banco precisa estar neste computador (localhost).');
        }
        if (filter_var($values['mysql_port'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]) === false) {
            throw new HttpError(422, 'Informe uma porta MySQL válida.');
        }
        if (!preg_match('/^[A-Za-z0-9_]{1,64}$/', $values['database'])) {
            throw new HttpError(422, 'Use somente letras, números e sublinhado no nome do banco.');
        }
        if ($values['mysql_username'] === '' || strlen($values['mysql_username']) > 64) {
            throw new HttpError(422, 'Informe o usuário administrador do MySQL.');
        }
        if (mb_strlen($values['admin_name']) < 2 || mb_strlen($values['admin_name']) > 120) {
            throw new HttpError(422, 'O nome do administrador deve ter entre 2 e 120 caracteres.');
        }
        if (!filter_var($values['admin_email'], FILTER_VALIDATE_EMAIL) || strlen($values['admin_email']) > 190) {
            throw new HttpError(422, 'Informe um e-mail válido para a administração.');
        }
        if (strlen($password) < 10 || strlen($password) > 72 || !hash_equals($password, $confirmation)) {
            throw new HttpError(422, 'A senha administrativa deve ter entre 10 e 72 bytes e coincidir com a confirmação.');
        }
    }

    private function createProject(array $values, string $mysqlPassword, string $adminPassword): void {
        $host = $values['mysql_host'];
        $port = (int)$values['mysql_port'];
        $database = $values['database'];
        $dbUser = 'cc_' . bin2hex(random_bytes(5));
        $dbPassword = bin2hex(random_bytes(24));
        $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $values['mysql_username'], $mysqlPassword, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        $lookup = $pdo->prepare('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME=?');
        $lookup->execute([$database]);
        $existing = $lookup->fetchColumn();
        $createdDatabase = false;
        $createdUser = false;
        if ($existing) {
            $check = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4", $values['mysql_username'], $mysqlPassword, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            if ($check->query('SHOW TABLES')->fetchColumn() !== false) {
                throw new HttpError(422, 'Este banco já contém tabelas. Escolha um banco vazio para a instalação.');
            }
        } else {
            $pdo->exec("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $createdDatabase = true;
        }

        try {
            $pdo->exec('CREATE USER ' . $pdo->quote($dbUser) . '@' . $pdo->quote('127.0.0.1') . ' IDENTIFIED BY ' . $pdo->quote($dbPassword));
            $createdUser = true;
            $pdo->exec("GRANT ALL PRIVILEGES ON `$database`.* TO " . $pdo->quote($dbUser) . '@' . $pdo->quote('127.0.0.1'));

            $config = [
                'APP_ENV' => 'local', 'APP_URL' => 'http://localhost' . base_path(),
                'APP_NAME' => 'CidadeColaborativa', 'DB_HOST' => '127.0.0.1',
                'DB_PORT' => (string)$port, 'DB_DATABASE' => $database,
                'DB_USERNAME' => $dbUser, 'DB_PASSWORD' => $dbPassword,
            ];
            foreach ($config as $key => $value) putenv($key . '=' . $value);

            $app = DB::connection();
            $app->exec(file_get_contents(ROOT . '/database/schema.sql'));
            $app->exec(file_get_contents(ROOT . '/database/seed.sql'));
            $app->exec('CREATE TABLE IF NOT EXISTS migrations (nome VARCHAR(190) PRIMARY KEY, executada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP)');
            foreach (glob(ROOT . '/database/migrations/*.sql') as $file) {
                $name = basename($file);
                if (DB::one('SELECT nome FROM migrations WHERE nome=?', [$name])) continue;
                $app->exec(file_get_contents($file));
                DB::insert('migrations', ['nome' => $name]);
            }
            QuotaImporter::import();

            $adminId = DB::insert('usuarios', [
                'nome' => $values['admin_name'], 'email' => $values['admin_email'],
                'senha_hash' => password_hash($adminPassword, PASSWORD_DEFAULT), 'perfil' => 'admin',
            ]);
            DB::insert('auditoria', [
                'usuario_id' => $adminId, 'acao' => 'criar_admin_instalacao',
                'entidade' => 'usuarios', 'entidade_id' => $adminId,
                'detalhes' => 'Administrador inicial criado pelo assistente local do XAMPP.',
            ]);

            $lines = [];
            foreach ($config as $key => $value) $lines[] = $key . '=' . $value;
            if (file_put_contents(ROOT . '/.env', implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX) === false) {
                throw new \RuntimeException('Não foi possível gravar .env. Confira a permissão de escrita da pasta do projeto.');
            }
        } catch (Throwable $error) {
            if ($createdUser) $pdo->exec('DROP USER IF EXISTS ' . $pdo->quote($dbUser) . '@' . $pdo->quote('127.0.0.1'));
            if ($createdDatabase) $pdo->exec("DROP DATABASE IF EXISTS `$database`");
            throw $error;
        }
    }

    private function requireLocal(): void {
        if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', '::ffff:127.0.0.1'], true)) {
            throw new HttpError(403, 'A instalação só pode ser executada neste computador.');
        }
    }
}
