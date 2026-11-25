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
<aside class="portal-sidebar"><a class="portal-brand" href="<?= e(url('/')) ?>"><i class="bi bi-buildings" aria-hidden="true"></i><span>CidadeColaborativa<small>RIO DO SUL · SC</small></span></a>
<button class="btn btn-outline-light d-lg-none m-3" data-bs-toggle="collapse" data-bs-target="#portal-menu" aria-controls="portal-menu" aria-expanded="false">Menu de navegação</button>
<nav class="collapse d-lg-block" id="portal-menu" aria-label="Navegação principal">
<?php $groups=['CONSULTA PÚBLICA'=>['/'=>'Visão geral','/mapa'=>'Mapa de ocorrências','/ocorrencias'=>'Ocorrências','/indicadores'=>'Indicadores','/avisos'=>'Avisos e informações','/enchentes'=>'Níveis e simulação','/cotas'=>'Cotas por rua']];
if($user && $user['perfil']==='admin') $groups['ADMINISTRAÇÃO']=['/admin'=>'Painel administrativo','/admin/ocorrencias'=>'Gerenciar chamados','/admin/cadastros?tipo=bairros'=>'Bairros','/admin/cadastros?tipo=categorias'=>'Categorias','/admin/cadastros?tipo=subcategorias'=>'Subcategorias','/admin/usuarios'=>'Usuários e permissões','/admin/avisos'=>'Cadastrar informações','/admin/enchentes'=>'Dados hidrológicos','/admin/solicitacoes'=>'Denúncias','/admin/solicitacoes?tipo=solicitacoes_resolucao'=>'Sugestões de resolução','/admin/auditoria'=>'Auditoria'];
elseif($user && $user['perfil']==='operador') $groups['ATENDIMENTO']=['/admin/ocorrencias'=>'Consultar e atualizar chamados'];
elseif($user) $groups['ÁREA DO CIDADÃO']=['/nova'=>'Registrar ocorrência','/painel'=>'Meus registros','/notificacoes'=>'Notificações'];
foreach($groups as $heading=>$links): ?><p class="menu-heading"><?= e($heading) ?></p><?php foreach($links as $route=>$label): $parts=explode('?',$route,2);$params=[];if(isset($parts[1]))parse_str($parts[1],$params);$active=($_GET['r']??'/')===$parts[0] && (!isset($params['tipo']) || ($_GET['tipo']??'categorias')===$params['tipo']); ?><a class="portal-link <?= $active?'active':'' ?>" <?= $active?'aria-current="page"':'' ?> href="<?= e(url($parts[0],$params)) ?>"><?= e($label) ?></a><?php endforeach ?><?php endforeach ?>
<div class="sidebar-account"><?php if($user): ?><span><?= e($user['nome']) ?></span><small><?= e(['admin'=>'Administrador','operador'=>'Operador','cidadao'=>'Cidadão'][$user['perfil']]) ?></small><a href="<?= e(url('/perfil')) ?>">Minha conta</a><form method="post" action="<?= e(url('/sair')) ?>"><?= csrf() ?><button class="btn btn-sm btn-outline-light mt-2">Sair</button></form><?php else: ?><a class="btn btn-light" href="<?= e(url('/entrar')) ?>">Entrar</a> <a href="<?= e(url('/cadastro')) ?>">Cadastrar-se</a><?php endif ?></div></nav></aside>
<div class="portal-body"><header class="portal-topbar"><span>Monitoramento urbano</span><span>Rio do Sul / Santa Catarina</span></header><div class="academic-notice">Projeto acadêmico · Este sistema não é um canal oficial da Prefeitura ou da Defesa Civil.</div>
<main id="conteudo" class="container main-content">
<?php if (isset($_SESSION['flash'])): $flash=$_SESSION['flash']; unset($_SESSION['flash']); ?><div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div><?php endif ?>
<?= $content ?>
</main><footer class="portal-footer"><span>CidadeColaborativa · Programação Web I</span><a href="<?= e(url('/sobre')) ?>">Sobre o projeto</a><a href="<?= e(url('/privacidade')) ?>">Termos e privacidade</a></footer></div></body></html>
