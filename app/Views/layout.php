<!doctype html>
<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title ?? 'Participação cidadã') ?> · CidadeColaborativa</title>
<meta name="description" content="Plataforma acadêmica de participação comunitária e monitoramento urbano em Rio do Sul.">
<meta name="csrf-token" content="<?= e($_SESSION['csrf']??'') ?>">
<link rel="stylesheet" href="/assets/vendor/bootstrap/css/bootstrap.min.css"><link rel="stylesheet" href="/assets/vendor/icons/bootstrap-icons.min.css">
<link rel="stylesheet" href="/assets/vendor/leaflet/leaflet.css"><link rel="stylesheet" href="/assets/css/app.css">
<script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js" defer></script><script src="/assets/vendor/leaflet/leaflet.js" defer></script>
<script src="/assets/vendor/chart/chart.umd.js" defer></script><script src="/assets/js/app.js" defer></script></head>
<body><a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
<div class="community-strip"><div class="container">PROJETO ACADÊMICO · UNIDAVI <span>Rio do Sul, Santa Catarina</span></div></div>
<header class="site-header"><nav class="navbar navbar-expand-lg container" aria-label="Navegação principal">
<a class="navbar-brand brand" href="<?= e(url('/')) ?>"><span class="brand-icon"><i class="bi bi-buildings" aria-hidden="true"></i></span><span>Cidade<span class="brand-accent">Colaborativa</span><small>JUNTOS, CUIDAMOS DO NOSSO LUGAR</small></span></a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Abrir navegação"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="nav"><ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
<?php foreach (['/mapa'=>'Mapa','/ocorrencias'=>'Ocorrências','/indicadores'=>'Indicadores','/enchentes'=>'Enchentes'] as $link=>$label): ?><li class="nav-item"><a class="nav-link" href="<?= e(url($link)) ?>"><?= e($label) ?></a></li><?php endforeach ?>
<?php if ($user): ?><li class="nav-item"><a class="nav-link" href="<?= e(url('/painel')) ?>">Minha área</a></li><?php if ($user['perfil']==='admin'): ?><li><a class="nav-link" href="<?= e(url('/admin')) ?>">Administração</a></li><?php endif ?><li><form method="post" action="<?= e(url('/sair')) ?>"><?= csrf() ?><button class="btn btn-outline-secondary btn-sm">Sair</button></form></li>
<?php else: ?><li><a class="btn btn-outline-primary ms-lg-3" href="<?= e(url('/entrar')) ?>">Entrar <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></li><?php endif ?></ul></div></nav></header>
<main id="conteudo" class="container main-content">
<?php if (isset($_SESSION['flash'])): $flash=$_SESSION['flash']; unset($_SESSION['flash']); ?><div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div><?php endif ?>
<?= $content ?>
</main>
<footer class="site-footer"><div class="container"><div class="row g-4"><div class="col-lg-6"><strong>CidadeColaborativa</strong><p>Uma iniciativa acadêmica para aproximar pessoas e melhorar o lugar onde vivemos.</p><small>Não é um canal oficial da prefeitura ou da Defesa Civil. Registrar um relato não abre um chamado público oficial.</small></div><div class="col-lg-3"><a href="<?= e(url('/sobre')) ?>">Sobre o projeto</a><a href="<?= e(url('/avisos')) ?>">Avisos à comunidade</a></div><div class="col-lg-3"><a href="<?= e(url('/privacidade')) ?>">Termos e privacidade</a><span>Rio do Sul · SC</span></div></div><div class="footer-bottom">Participação comunitária, informação responsável. <span>Projeto de extensão · Programação Web I</span></div></div></footer>
</body></html>
