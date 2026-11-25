<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Preparar CidadeColaborativa</title>
  <link rel="stylesheet" href="<?= e(asset_url('/assets/vendor/bootstrap/css/bootstrap.min.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset_url('/assets/css/app.css')) ?>?v=<?= (int) filemtime(ROOT.'/public/assets/css/app.css') ?>">
</head>
<body>
  <main class="container py-5" style="max-width:760px">
    <p class="eyebrow">PRIMEIRA EXECUÇÃO</p>
    <h1>Preparar o projeto no XAMPP</h1>
    <p class="text-secondary mb-4">O banco e a conta administrativa serão criados uma única vez. Inicie o Apache e o MySQL no XAMPP antes de continuar. Use um banco vazio.</p>
    <?php if ($error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif ?>
    <form class="surface form-stack" method="post" action="<?= e(url('/')) ?>">
      <?= csrf() ?>
      <h2 class="h4">Conexão MySQL local</h2>
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="mysql_host">Host</label><input class="form-control" id="mysql_host" name="mysql_host" value="<?= e($values['mysql_host']) ?>" required></div>
        <div class="col-md-6"><label class="form-label" for="mysql_port">Porta</label><input class="form-control" id="mysql_port" name="mysql_port" type="number" min="1" max="65535" value="<?= e($values['mysql_port']) ?>" required></div>
        <div class="col-md-6"><label class="form-label" for="database">Nome do banco</label><input class="form-control" id="database" name="database" value="<?= e($values['database']) ?>" required></div>
        <div class="col-md-6"><label class="form-label" for="mysql_username">Usuário MySQL</label><input class="form-control" id="mysql_username" name="mysql_username" value="<?= e($values['mysql_username']) ?>" autocomplete="username" required></div>
        <div class="col-12"><label class="form-label" for="mysql_password">Senha MySQL</label><input class="form-control" id="mysql_password" name="mysql_password" type="password" autocomplete="current-password"><small class="text-secondary">No XAMPP padrão, o usuário root geralmente não tem senha.</small></div>
      </div>
      <hr>
      <h2 class="h4">Conta administrativa</h2>
      <div><label class="form-label" for="admin_name">Nome</label><input class="form-control" id="admin_name" name="admin_name" value="<?= e($values['admin_name']) ?>" maxlength="120" autocomplete="name" required></div>
      <div><label class="form-label" for="admin_email">E-mail</label><input class="form-control" id="admin_email" name="admin_email" type="email" value="<?= e($values['admin_email']) ?>" maxlength="190" autocomplete="email" required></div>
      <div><label class="form-label" for="admin_password">Senha administrativa</label><input class="form-control" id="admin_password" name="admin_password" type="password" minlength="10" maxlength="72" autocomplete="new-password" required><small class="text-secondary">Use de 10 a 72 caracteres. Esta senha não será exibida nem gravada em texto puro.</small></div>
      <div><label class="form-label" for="admin_password_confirmation">Confirme a senha</label><input class="form-control" id="admin_password_confirmation" name="admin_password_confirmation" type="password" minlength="10" maxlength="72" autocomplete="new-password" required></div>
      <button class="btn btn-primary" type="submit">Instalar e abrir o sistema</button>
    </form>
    <p class="small text-secondary mt-3">A configuração inicial só é aceita de localhost. A aplicação cria um usuário MySQL próprio e mantém a senha em um arquivo local ignorado pelo Git.</p>
  </main>
</body>
</html>
