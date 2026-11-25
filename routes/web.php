<?php
declare(strict_types=1);
use App\Controllers\{PageController as Page,AuthController as Auth,OccurrenceController as Occ,CitizenController as Citizen,AdminController as Admin,HydrologyController as Hydro};
return [
 'GET /'=>[Page::class,'home',null], 'GET /sobre'=>[Page::class,'about',null], 'GET /privacidade'=>[Page::class,'privacy',null],
 'GET /avisos'=>[Page::class,'noticesPage',null], 'GET /indicadores'=>[Page::class,'indicators',null], 'GET /api/indicadores'=>[Page::class,'indicatorsApi',null],
 'GET /entrar'=>[Auth::class,'loginForm',null], 'POST /entrar'=>[Auth::class,'login',null],
 'GET /cadastro'=>[Auth::class,'registerForm',null], 'POST /cadastro'=>[Auth::class,'register',null], 'POST /sair'=>[Auth::class,'logout','user'],
 'GET /ocorrencias'=>[Occ::class,'listing',null], 'GET /mapa'=>[Occ::class,'map',null], 'GET /api/ocorrencias'=>[Occ::class,'api',null],
 'GET /ocorrencia'=>[Occ::class,'detail',null], 'GET /imagem'=>[Occ::class,'image',null],
 'GET /nova'=>[Occ::class,'form','citizen'], 'GET /editar'=>[Occ::class,'form','citizen'], 'POST /ocorrencia'=>[Occ::class,'save','citizen'],
 'GET /api/semelhantes'=>[Occ::class,'similar','user'], 'POST /participar'=>[Occ::class,'action','citizen'],
 'GET /painel'=>[Citizen::class,'dashboard','user'], 'GET /minhas-ocorrencias'=>[Citizen::class,'dashboard','user'],
 'GET /notificacoes'=>[Citizen::class,'notifications','user'], 'POST /notificacoes'=>[Citizen::class,'read','user'],
 'GET /perfil'=>[Citizen::class,'profile','user'], 'POST /perfil'=>[Citizen::class,'update','user'],
 'GET /admin'=>[Admin::class,'dashboard','admin'], 'GET /admin/ocorrencias'=>[Admin::class,'occurrences','staff'], 'POST /admin/moderar'=>[Admin::class,'moderate','staff'],
 'GET /admin/cadastros'=>[Admin::class,'taxonomy','admin'], 'POST /admin/cadastros'=>[Admin::class,'saveTaxonomy','admin'],
 'GET /admin/usuarios'=>[Admin::class,'users','admin'], 'POST /admin/usuarios'=>[Admin::class,'saveUser','admin'],
 'GET /admin/solicitacoes'=>[Admin::class,'queue','admin'], 'POST /admin/solicitacoes'=>[Admin::class,'decideQueue','admin'],
 'GET /admin/auditoria'=>[Admin::class,'audit','admin'], 'GET /admin/avisos'=>[Admin::class,'notices','admin'], 'POST /admin/avisos'=>[Admin::class,'saveNotice','admin'],
 'GET /cotas'=>[Hydro::class,'quotas',null],
 'GET /enchentes'=>[Hydro::class,'page',null], 'GET /simulador'=>[Hydro::class,'page',null],
 'GET /admin/enchentes'=>[Hydro::class,'admin','admin'], 'POST /admin/enchentes'=>[Hydro::class,'save','admin'],
];
