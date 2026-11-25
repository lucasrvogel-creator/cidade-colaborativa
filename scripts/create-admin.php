<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__) . '/app/bootstrap.php';
use App\Models\Database as DB;
use App\Services\Validation;

echo "Nome do administrador: "; $name = trim(fgets(STDIN));
echo "E-mail: "; $email = trim(fgets(STDIN));
echo "Senha (10 a 72 caracteres; entrada visível neste terminal local): "; $password = trim(fgets(STDIN));
Validation::text(['nome' => $name], 'nome', 2, 120);
Validation::password(['senha' => $password, 'confirmacao' => $password]);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) exit("E-mail inválido.\n");
if (DB::one('SELECT id FROM usuarios WHERE email=?', [$email])) exit("Já existe uma conta com este e-mail.\n");
$id = DB::insert('usuarios', ['nome'=>$name,'email'=>mb_strtolower($email),'senha_hash'=>password_hash($password,PASSWORD_DEFAULT),'perfil'=>'admin']);
DB::insert('auditoria',['usuario_id'=>$id,'acao'=>'criar_admin_cli','entidade'=>'usuarios','entidade_id'=>$id,'detalhes'=>'Administrador criado pelo operador local via CLI.']);
echo "Administrador criado.\n";
