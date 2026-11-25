# Guia de instalação do projeto

Instale Node/npm e execute:

```sh
npm ci
npm run assets
php scripts/install.php
php scripts/create-admin.php
```

Em instalações existentes, faça backup antes de executar as migrações e a importação de cotas:

```sh
php scripts/migrate.php
php scripts/import-quotas.php
```

## Perfis de acesso

- Cidadão: registra e acompanha ocorrências.
- Operador: consulta chamados e atualiza situações com justificativa.
- Administrador: gerencia chamados, bairros, categorias, usuários, permissões, avisos e dados hidrológicos; consulta auditoria.

## Instalação no XAMPP

Requisitos: XAMPP com PHP 8.2+ e extensões `pdo_mysql`, `mbstring`, `fileinfo` e `gd`; MySQL ou MariaDB 10.4+. O Apache e o MySQL precisam estar iniciados. Os arquivos de Bootstrap, Bootstrap Icons, Leaflet e Chart.js já estão no repositório; Node/npm não são necessários para executar o sistema.

1. Clone o repositório em uma pasta dentro de `C:\xampp\htdocs` (por exemplo, `C:\xampp\htdocs\cidade-colaborativa`).
2. No XAMPP Control Panel, inicie **Apache** e **MySQL**.
3. Abra `http://localhost/cidade-colaborativa/public/` no navegador.
4. Na primeira visita, o assistente prepara o banco e solicita as credenciais da conta administrativa. No XAMPP padrão, o usuário MySQL costuma ser `root`, porta `3306` e senha vazia. Se sua instalação usa outra configuração, informe os dados locais do MySQL.

O assistente só funciona por `localhost`, exige um banco vazio e cria um usuário MySQL próprio para a aplicação. A configuração resultante fica em `.env`, ignorado pelo Git. A senha administrativa é escolhida durante a instalação e armazenada somente como hash. Em clones colocados em outra subpasta, ajuste o endereço no navegador para refletir o nome dessa pasta.

Não é necessário executar Composer, npm, scripts PHP ou criar o banco manualmente para uma instalação nova. Os arquivos estáticos estão versionados e os dados de referência são carregados pelo assistente.

Para uploads, configure `upload_max_filesize=5M`, `post_max_size=20M` e um `upload_tmp_dir` gravável no PHP. O servidor web deve expor a pasta `public/` como parte do endereço do projeto.