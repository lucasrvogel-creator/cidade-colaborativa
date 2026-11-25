# Explicação dos códigos

## Caminho de uma requisição

`public/index.php` recebe a requisição. `app/bootstrap.php` carrega configuração, autoload de classes, sessões e cabeçalhos. `routes/web.php` relaciona método HTTP e rota ao controlador e à permissão exigida. O controlador consulta modelos ou serviços e envia variáveis para uma view PHP, que produz HTML. `public/router.php` adapta esse fluxo ao servidor embutido do PHP.

`app/Helpers/functions.php` reúne funções compartilhadas: `e()` escapa textos para HTML, `url()` monta URLs, `csrf()` gera o campo de proteção de formulários, `flash()` apresenta mensagens após redirecionamentos e `datebr()` formata datas.

## Banco e modelos

`app/Models/Database.php` centraliza PDO, consultas preparadas, inserções e transações. Parâmetros separam dados do usuário do comando SQL. Transações confirmam todas as alterações relacionadas ou desfazem a operação em caso de falha.

`database/schema.sql` define usuários, bairros, categorias, subcategorias, ocorrências, imagens, confirmações, histórico, denúncias, sugestões de resolução, notificações, avisos, níveis, cotas e auditoria. Chaves estrangeiras preservam relações. `database/seed.sql` inclui categorias e registros explicitamente demonstrativos.

`Occurrence.php` implementa consultas, filtros, paginação, visibilidade e busca de registros semelhantes. Visitantes veem situações públicas; o autor e a equipe autorizada consultam registros privados. `Indicators.php` calcula agregações por situação, categoria, bairro e período.

## Login e permissões

`app/Middleware/Auth.php` consulta a conta em cada requisição autenticada. Suspender uma conta revoga inclusive sessões existentes. A permissão `admin` exige administrador; `staff` aceita administrador ou operador; `citizen` impede o operador de usar o fluxo de cadastro de ocorrências. Os formulários POST exigem token CSRF.

`AuthController.php` trata cadastro, login e logout. O cadastro público cria somente cidadãos, independentemente de campos extras enviados. Senhas são armazenadas como hash. Após login, o administrador vai ao painel, o operador à fila de chamados e o cidadão à sua área.

O menu muda conforme o perfil, mas a proteção também está no servidor: esconder um botão não substitui a autorização das rotas.

## Ocorrências

`OccurrenceController.php` atende listagem, mapa, API, detalhes, formulário, fotografias e participação comunitária. `CitizenController.php` organiza registros pessoais, notificações e conta.

`OccurrenceService.php` valida e grava relatos. Categoria e bairro precisam estar ativos; a subcategoria deve pertencer à categoria escolhida. O autor pode editar apenas registros próprios pendentes ou devolvidos para correção.

`OccurrenceState.php` define as mudanças de situação permitidas. `OccurrenceService::transition()` exige administrador ou operador, verifica a mudança, registra justificativa, atualiza datas, cria histórico, notificação e auditoria. Justificativas completas ficam privadas; o histórico público usa mensagem genérica. Duplicidades precisam apontar para um registro principal público e válido.

Exemplo: o cidadão envia uma ocorrência pendente. O operador revisa e publica com justificativa. O autor recebe a atualização. A resolução posterior também precisa respeitar a máquina de estados e deixa rastros no banco.

## Administração

`AdminController.php` gerencia bairros, categorias, subcategorias, usuários, avisos, denúncias, sugestões e auditoria. A escolha de tabela é limitada a uma lista fixa. Bairros e categorias podem ser criados, renomeados e desativados sem apagar o histórico das ocorrências.

`saveUser()` cria contas nos três perfis e altera perfil ou situação de outras contas. Impede retirar a própria permissão nessa tela. Anonimização exige solicitação do titular e confirmação de revisão. Operações ficam na auditoria.

Avisos contêm título, descrição e fonte; podem ser publicados e arquivados. Pareceres sobre denúncias e sugestões não mudam automaticamente os chamados: a atualização é registrada separadamente.

## Cotas e hidrologia

`HydrologyController.php` organiza histórico, simulação, cadastro administrativo e consulta paginada por rua. Na tela `/cotas`, o administrador gerencia disponibilidade; o público vê apenas registros ativos.

`DatabaseHydrology.php` implementa `HydrologyProvider` usando o banco local. A simulação compara nível hipotético e cotas da mesma estação, referência e tipo. A junção com bairros é opcional: a tabela oficial não informa esses vínculos. Mínimos ausentes não viram zero; cotas desativadas não entram na comparação.

`database/data/cotas-defesa-civil.json` contém 555 ruas da fonte oficial, consultada em 25/11/2026, com metadados. `scripts/extract-official-quotas.mjs` extrai os registros de um arquivo público salvo localmente, sem executar seu JavaScript, e exige 555 nomes únicos.

`QuotaImporter.php` lê o JSON e cria uma chave por URL da fonte e nome da rua. Registros existentes são preservados, evitando duplicação. Valores ausentes, máximos abertos e bairros não informados são mantidos. `scripts/import-quotas.php` executa esse serviço.

`scripts/migrate.php` aplica os arquivos ainda não registrados na tabela de controle. A migração inicial adiciona operador e campos das cotas. DDL MySQL possui commit implícito: fazer backup antes de atualizar bancos importantes. `scripts/install.php` cria a estrutura em banco vazio, aplica migrações e importa cotas.

Não há atualização automática, medição em tempo real ou previsão hidrológica. A simulação não indica segurança nem rotas de evacuação.

## Interface

`app/Views/layout.php` define a estrutura e o menu lateral por perfil. `home.php` exibe indicadores, mapa e registros recentes. `app/Views/admin/` reúne telas administrativas. `public/assets/css/app.css` define tons azuis, campos, estados e adaptação para celular.

`public/assets/js/app.js` inicializa Leaflet, carrega ocorrências por região e filtros, monta marcadores, permite escolher coordenadas, mostra prévias de fotografias e prepara gráficos Chart.js. Textos recebidos são inseridos com nós de texto, sem executar HTML de relatos.

`scripts/copy-assets.mjs` copia bibliotecas do npm para a pasta pública. Não existe aplicação React ativa; Node é usado somente para preparar recursos estáticos.

## Validações e segurança

`Validation.php` centraliza textos, identificadores, coordenadas, senhas e URLs. `RateLimiter.php` limita operações por IP ou usuário. `ImageUpload.php` confere tamanho, MIME e dimensões, recodifica imagens em JPEG e gera nomes aleatórios. Fotografias ficam fora do diretório público e passam pela checagem de visibilidade antes da entrega.

`HttpError.php` representa falhas HTTP esperadas. Erros inesperados não expõem detalhes internos na página.

## Testes e limites

`tests/run.php` verifica estados, coordenadas, senhas, escape HTML e arquivos. `tests/http.php` faz requisições reais para testar acesso por perfil, cadastro, moderação, imagens, notificações, auditoria e cotas; remove seus registros temporários ao final. O resultado fica em `docs/testes/resultado-http.json`.

Esta explicação descreve o código atual. A especificação acadêmica tem escopo mais amplo. Entrevistas, validação com órgãos públicos, integração automática e avaliação em produção não foram realizadas. O histórico Git registra as etapas implementadas.

## Instalação inicial no XAMPP

Quando `.env` ainda não existe, `public/index.php` apresenta o assistente `XamppInstaller`. Ele só aceita configuração por `localhost`, pede as credenciais administrativas do MySQL e a nova conta de administrador da aplicação, cria o esquema, carrega os dados demonstrativos e importa as cotas. Depois cria um usuário MySQL restrito ao banco do projeto e grava a configuração em `.env`, que permanece ignorado pelo Git. O assistente exige um banco vazio; ele não altera bancos com tabelas existentes. As bibliotecas usadas em runtime ficam versionadas em `public/assets/vendor`, portanto não é necessário executar Node/npm em outra máquina para abrir o sistema.
