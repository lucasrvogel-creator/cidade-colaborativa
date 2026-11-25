# CidadeColaborativa

Plataforma acadêmica de monitoramento urbano de Rio do Sul em PHP MVC, PDO e MySQL. Interface Bootstrap, mapas Leaflet/OpenStreetMap e gráficos Chart.js.

## Perfis de acesso

- Cidadão: registra e acompanha ocorrências.
- Operador: consulta chamados e atualiza situações com justificativa.
- Administrador: gerencia chamados, bairros, categorias, usuários, permissões, avisos e dados hidrológicos; consulta auditoria.

## Instalação

Requisitos: PHP 8.2+ com pdo_mysql, mbstring, fileinfo e GD; MySQL 8; Node/npm para preparar os arquivos estáticos. Ambiente validado: PHP 8.3 e MySQL 8.4.

Crie um banco vazio, copie `.env.example` para `.env` e configure a conexão. Execute:

```sh
npm ci
npm run assets
php scripts/install.php
php scripts/create-admin.php
php -S 127.0.0.1:8080 -t public public/router.php
```

Abra http://127.0.0.1:8080. Para uploads, configure `upload_max_filesize=5M`, `post_max_size=20M` e um `upload_tmp_dir` gravável no PHP. O servidor web deve servir somente `public/`. Não existe senha administrativa padrão versionada.

Em instalações existentes, faça backup e execute:

```sh
php scripts/migrate.php
php scripts/import-quotas.php
```

## Cotas oficiais

Inclui 555 ruas da tabela pública da Defesa Civil de Rio do Sul, consultada em 25/11/2026, referência régua da Ponte Dom Tito Buss. Preserva mínimos, máximos, valores ausentes e “20,00+”. A fonte não informa bairros nem data de atualização da tabela. Importação idempotente, sem sincronização automática ou níveis em tempo real.

Fonte: https://defesacivil.riodosul.sc.gov.br/

## Testes

Com banco de desenvolvimento e servidor ativos:

```sh
php tests/run.php
php tests/http.php
node --check public/assets/js/app.js
```

Os testes HTTP exigem `APP_ENV=local`, criam registros temporários e os removem ao final. Não executar em produção. Resultado em `docs/testes/resultado-http.json`.

## Documentação

- [Explicação dos códigos](docs/explicacao-dos-codigos.md)
- [Especificação](docs/requisitos/especificacao.md)
- [Planejamento](docs/requisitos/planejamento.md)
- [Protótipos iniciais](docs/prototipos/index.html)

O histórico preserva a base inicial React e sua substituição por PHP após a definição dos requisitos. Dependências, credenciais, sessões, banco local e uploads ficam fora do Git. A especificação é o escopo planejado e não comprova conclusão de todas as entregas. Este sistema acadêmico não é um canal oficial da Prefeitura ou Defesa Civil.
