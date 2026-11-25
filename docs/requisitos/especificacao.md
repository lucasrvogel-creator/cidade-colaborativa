# Projeto CidadeColaborativa — Plataforma Colaborativa de Monitoramento Urbano

## 1. Contextualização

Desenvolva um sistema web denominado CidadeColaborativa, destinado inicialmente ao município de Rio do Sul, Santa Catarina, como projeto de extensão da disciplina de Programação Web I do curso de Sistemas de Informação da UNIDAVI.

O sistema deverá permitir que os cidadãos registrem, localizem, consultem e acompanhem problemas urbanos, promovendo a participação comunitária e facilitando a identificação das necessidades do município.

A plataforma deverá possuir uma interface pública, um ambiente autenticado para cidadãos e um painel administrativo para gerenciamento das informações.

O projeto não representa oficialmente a prefeitura ou a Defesa Civil e não deverá afirmar que possui integração com órgãos públicos sem que ela esteja efetivamente implementada e autorizada.

## 2. Problema identificado

A população encontra dificuldades para compartilhar, centralizar e acompanhar informações relacionadas aos problemas urbanos.

Informações frequentemente ficam dispersas em redes sociais, grupos de mensagens e canais de comunicação diferentes, dificultando sua consulta e acompanhamento.

O sistema deverá centralizar essas informações em uma plataforma acessível, organizada e colaborativa.

## 3. Objetivos

### 3.1. Objetivo geral

Desenvolver uma plataforma web colaborativa para registro, monitoramento e acompanhamento de problemas urbanos em Rio do Sul.

### 3.2. Objetivos específicos

- Incentivar a participação comunitária.
- Centralizar informações sobre problemas urbanos.
- Permitir a localização geográfica das ocorrências.
- Facilitar a identificação de regiões com maior concentração de problemas.
- Permitir o acompanhamento das ocorrências pela população.
- Disponibilizar indicadores e estatísticas municipais.
- Auxiliar na divulgação de informações relacionadas às enchentes.
- Aplicar os conhecimentos adquiridos na disciplina de Programação Web I.

## 4. Escopo do projeto

O sistema deverá ser desenvolvido inicialmente para Rio do Sul, mas sua estrutura deverá permitir futuras expansões para outros municípios.

O projeto deverá priorizar simplicidade, facilidade de manutenção, segurança e experiência do usuário.

Não desenvolver funcionalidades desnecessárias que aumentem a complexidade do sistema sem proporcionar benefícios concretos.

## 5. Tecnologias obrigatórias

### Backend

- PHP 8.2 ou superior.
- PHP orientado a objetos.
- PDO para comunicação com o banco de dados.
- Arquitetura MVC simplificada.
- Sessões PHP para autenticação.
- Composer para gerenciamento de dependências, caso necessário.

### Frontend

- HTML5.
- CSS3.
- JavaScript.
- Bootstrap 5 para componentes responsivos.
- Bootstrap Icons ou Lucide Icons.
- Fetch API para requisições assíncronas.

### Banco de dados

- MySQL ou MariaDB.
- Modelo relacional.
- Chaves primárias e estrangeiras.
- Restrições de integridade.
- Consultas preparadas.
- Índices para consultas frequentes.

### Geolocalização e mapas

- Leaflet.js.
- OpenStreetMap.
- API de geolocalização do navegador, mediante autorização.
- Seleção manual de localização no mapa.
- Nominatim, opcionalmente, para consultas de endereços, respeitando suas políticas de utilização.

### Gráficos

- Chart.js.

### Ambiente de desenvolvimento

- Visual Studio Code.
- XAMPP.
- Apache.
- Git.
- GitHub.
- phpMyAdmin.
- Draw.io para diagramas e modelagem.
- Postman ou Bruno para testes de endpoints.
- PHPUnit, opcionalmente, para testes automatizados.

### Restrições tecnológicas

- Não utilizar React, Angular ou Vue.
- Não utilizar Laravel ou frameworks complexos.
- Não utilizar microsserviços.
- Não exigir serviços externos pagos.
- Não exigir Docker para execução local.
- Não utilizar inteligência artificial sem uma necessidade concreta.
- Priorizar tecnologias e conceitos compatíveis com a disciplina de Programação Web I.

## 6. Arquitetura do software

Utilizar uma arquitetura monolítica modular, seguindo o padrão MVC simplificado.

Responsabilidades:

- Model: acesso e manipulação dos dados.
- View: apresentação das informações ao usuário.
- Controller: recebimento de requisições e coordenação das operações.
- Service: regras de negócio que necessitem de maior organização.
- Middleware: autenticação, autorização e verificações de segurança.

Evitar colocar consultas SQL diretamente nas páginas HTML.

Estrutura sugerida:

```text
cidadecolaborativa/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   ├── Services/
│   ├── Middleware/
│   └── Helpers/
├── config/
│   ├── app.php
│   └── database.php
├── database/
│   ├── schema.sql
│   └── seed.sql
├── public/
│   ├── index.php
│   ├── .htaccess
│   └── assets/
│       ├── css/
│       ├── js/
│       └── images/
├── storage/
│   ├── uploads/
│   └── logs/
├── routes/
│   └── web.php
├── tests/
├── docs/
│   ├── requisitos/
│   ├── diagramas/
│   ├── prototipos/
│   └── testes/
├── .env.example
├── .gitignore
├── composer.json
└── README.md
```

A pasta public deverá ser utilizada como raiz pública da aplicação. Arquivos de configuração, credenciais, logs e dados privados não deverão ficar diretamente acessíveis pelo navegador.

## 7. Atores do sistema

### 7.1. Visitante

Usuário não autenticado.

Permissões:

- Consultar ocorrências públicas.
- Visualizar o mapa.
- Filtrar ocorrências.
- Consultar estatísticas.
- Consultar informações sobre enchentes.
- Realizar cadastro.

### 7.2. Cidadão

Usuário autenticado.

Permissões:

- Registrar ocorrências.
- Anexar fotografias.
- Consultar suas ocorrências.
- Editar ocorrências pendentes ou devolvidas para correção.
- Confirmar ocorrências de outros cidadãos.
- Denunciar conteúdos inadequados.
- Sugerir que uma ocorrência foi resolvida.
- Acompanhar mudanças de situação.
- Gerenciar seu perfil.

### 7.3. Administrador

Responsável pela moderação e gerenciamento da plataforma.

Permissões:

- Consultar todas as ocorrências.
- Aprovar ou rejeitar ocorrências.
- Solicitar correções.
- Identificar ocorrências duplicadas.
- Alterar situações.
- Gerenciar categorias.
- Gerenciar bairros.
- Gerenciar usuários.
- Gerenciar denúncias.
- Publicar avisos informativos.
- Gerenciar informações sobre enchentes.
- Consultar indicadores.
- Consultar registros de auditoria.

## 8. Categorias de ocorrências

Cadastrar inicialmente as seguintes categorias e subcategorias:

### Infraestrutura urbana

- Buracos nas ruas.
- Calçadas danificadas.
- Pavimentação irregular.
- Pontes danificadas.
- Problemas em vias públicas.

### Iluminação pública

- Poste apagado.
- Iluminação defeituosa.
- Estrutura danificada.

### Saneamento

- Vazamento de água.
- Esgoto a céu aberto.
- Bueiros entupidos.
- Problemas de drenagem.

### Meio ambiente

- Descarte irregular de lixo.
- Poluição.
- Acúmulo de resíduos.
- Problemas em áreas públicas.

### Trânsito

- Semáforo defeituoso.
- Sinalização danificada.
- Via obstruída.
- Problemas de acessibilidade.

### Defesa Civil e enchentes

- Alagamento.
- Rua inundada.
- Deslizamento.
- Queda de árvore.
- Interdição de via.
- Problemas relacionados às cheias.

### Outros

- Ocorrências que não se enquadram nas categorias anteriores.

As categorias deverão ser cadastradas no banco de dados e gerenciáveis pelo administrador, não devendo ficar fixas no código.

## 9. Requisitos funcionais

### RF01 — Cadastrar cidadão

Permitir o cadastro utilizando nome, e-mail, senha e confirmação de senha.

### RF02 — Autenticar usuário

Permitir login e logout, identificando o perfil e as permissões do usuário.

### RF03 — Registrar ocorrência

Permitir ao cidadão informar título, descrição, categoria, subcategoria, bairro, localização e fotografias.

### RF04 — Anexar fotografias

Permitir o envio de até três imagens por ocorrência, respeitando as regras de segurança e tamanho.

### RF05 — Selecionar localização

Permitir selecionar um ponto no mapa ou utilizar a localização atual do dispositivo, mediante autorização.

### RF06 — Consultar ocorrências

Disponibilizar uma listagem pública das ocorrências aprovadas.

### RF07 — Visualizar mapa de ocorrências

Apresentar as ocorrências públicas por meio de marcadores geográficos.

### RF08 — Filtrar ocorrências

Permitir filtros por categoria, bairro, situação, período e pesquisa textual.

### RF09 — Visualizar detalhes

Apresentar descrição, fotografias, localização pública, categoria, situação, data de publicação e histórico público.

### RF10 — Confirmar ocorrência

Permitir que cidadãos autenticados confirmem a existência de uma ocorrência registrada por outro usuário.

### RF11 — Denunciar ocorrência

Permitir denúncias relacionadas a informações falsas, conteúdo inadequado, exposição de dados pessoais ou duplicidade.

### RF12 — Acompanhar ocorrências pessoais

Disponibilizar uma área contendo as ocorrências registradas pelo cidadão.

### RF13 — Editar ocorrência

Permitir alterações enquanto a ocorrência estiver pendente ou devolvida para correção.

### RF14 — Moderar ocorrência

Permitir que administradores aprovem, rejeitem ou solicitem correções.

### RF15 — Atualizar situação

Permitir que administradores alterem a situação de uma ocorrência publicada.

### RF16 — Gerenciar categorias

Permitir cadastrar, editar e desativar categorias e subcategorias.

### RF17 — Gerenciar usuários

Permitir consultar, ativar, suspender e gerenciar permissões de usuários autorizados.

### RF18 — Gerenciar bairros

Permitir cadastrar, editar e desativar bairros atendidos pela plataforma.

### RF19 — Consultar indicadores

Apresentar indicadores sobre ocorrências, categorias, bairros, situações e evolução temporal.

### RF20 — Consultar histórico

Manter histórico das alterações de situação das ocorrências.

### RF21 — Publicar avisos

Permitir que administradores publiquem avisos informativos de interesse da comunidade.

### RF22 — Consultar informações sobre enchentes

Disponibilizar informações sobre níveis dos rios, alertas oficiais e orientações de prevenção.

### RF23 — Simular cotas de inundação

Permitir que o usuário informe um nível hipotético do rio e consulte ruas potencialmente atingidas, quando houver cotas oficiais disponíveis.

### RF24 — Consultar histórico do rio

Apresentar registros históricos dos níveis do rio, identificando fonte e data de atualização.

### RF25 — Gerenciar denúncias

Permitir análise, resolução e arquivamento das denúncias recebidas.

### RF26 — Identificar ocorrências semelhantes

Apresentar possíveis ocorrências duplicadas durante o registro ou a moderação.

### RF27 — Solicitar resolução

Permitir que cidadãos sugiram que determinada ocorrência foi resolvida, encaminhando a solicitação para análise administrativa.

### RF28 — Gerenciar perfil

Permitir que o cidadão atualize seus dados pessoais e sua senha.

### RF29 — Notificar usuários

Exibir notificações internas relacionadas às mudanças de situação de suas ocorrências.

### RF30 — Registrar auditoria

Registrar ações administrativas relevantes para permitir rastreabilidade.

## 10. Requisitos não funcionais

### RNF01 — Responsividade

O sistema deverá funcionar em computadores, tablets e dispositivos móveis.

### RNF02 — Usabilidade

A interface deverá ser intuitiva, organizada e adequada a usuários com pouca familiaridade tecnológica.

### RNF03 — Acessibilidade

Utilizar contraste adequado, textos legíveis, navegação por teclado, rótulos nos formulários e textos alternativos nas imagens.

### RNF04 — Segurança

Utilizar consultas preparadas, validação de entradas, proteção contra XSS e CSRF e controle de permissões no servidor.

### RNF05 — Privacidade

Respeitar os princípios da LGPD, coletando somente informações necessárias para o funcionamento da plataforma.

### RNF06 — Desempenho

Utilizar paginação, índices no banco de dados, carregamento otimizado de imagens e consultas limitadas à região visualizada no mapa quando necessário.

### RNF07 — Manutenibilidade

Organizar o código em módulos com responsabilidades bem definidas.

### RNF08 — Compatibilidade

Garantir funcionamento nos principais navegadores modernos.

### RNF09 — Integridade

Utilizar restrições relacionais e transações nas operações que envolvam múltiplas alterações.

### RNF10 — Rastreabilidade

Registrar alterações administrativas e mudanças de situação.

### RNF11 — Implantação

Permitir instalação local por meio do XAMPP, acompanhada de documentação.

### RNF12 — Versionamento

Todo o código-fonte deverá ser versionado utilizando Git e GitHub.

### RNF13 — Localização

O sistema deverá utilizar português brasileiro, datas no padrão brasileiro e fuso horário America/Sao_Paulo.

### RNF14 — Confiabilidade das informações

Informações comunitárias, oficiais e simuladas deverão ser visualmente diferenciadas.

### RNF15 — Tratamento de erros

O sistema deverá apresentar mensagens compreensíveis, sem expor consultas SQL, credenciais ou detalhes internos.

## 11. Regras de negócio

### RN01

Cada conta deverá possuir um endereço de e-mail único.

### RN02

Somente usuários autenticados poderão registrar ocorrências.

### RN03

Toda ocorrência deverá possuir título, descrição, categoria, bairro e localização válida.

### RN04

Toda ocorrência registrada por um cidadão deverá iniciar com a situação PENDENTE.

### RN05

Ocorrências pendentes, rejeitadas ou devolvidas para correção não deverão ser publicamente exibidas.

### RN06

Somente administradores poderão aprovar a publicação de ocorrências.

### RN07

Uma ocorrência rejeitada deverá possuir uma justificativa administrativa.

### RN08

Uma solicitação de correção deverá possuir uma justificativa.

### RN09

Ocorrências pendentes ou devolvidas para correção poderão ser editadas exclusivamente pelo responsável pelo registro.

### RN10

Ocorrências publicadas não poderão ser diretamente editadas pelo cidadão sem nova análise administrativa.

### RN11

Cada cidadão poderá confirmar determinada ocorrência somente uma vez.

### RN12

O responsável pelo registro não poderá confirmar sua própria ocorrência.

### RN13

Confirmações comunitárias não representam validação oficial por órgãos públicos.

### RN14

Somente administradores poderão alterar oficialmente a situação de uma ocorrência na plataforma.

### RN15

Toda alteração de situação deverá gerar um registro de histórico.

### RN16

Ocorrências resolvidas deverão permanecer disponíveis para consultas históricas, salvo necessidade de remoção por privacidade ou moderação.

### RN17

Ocorrências duplicadas poderão ser vinculadas a uma ocorrência principal.

### RN18

Ocorrências duplicadas não deverão ser contabilizadas como problemas independentes nos indicadores.

### RN19

Ocorrências próximas, pertencentes à mesma categoria e registradas em períodos semelhantes deverão ser apresentadas como possíveis duplicidades.

### RN20

A identificação automática de possíveis duplicidades não deverá excluir ou rejeitar registros automaticamente.

### RN21

Cada ocorrência poderá possuir até três fotografias.

### RN22

As fotografias deverão possuir tamanho máximo de 5 MB por arquivo e formatos JPEG, PNG ou WebP válidos.

### RN23

Não será permitido publicar fotografias ou descrições que exponham indevidamente dados pessoais, placas, documentos ou informações privadas de terceiros.

### RN24

Usuários suspensos não poderão registrar, confirmar ou denunciar ocorrências.

### RN25

Categorias e bairros associados a registros históricos deverão ser desativados em vez de fisicamente excluídos.

### RN26

Toda denúncia deverá possuir motivo e identificação interna do responsável.

### RN27

Uma denúncia não deverá remover automaticamente uma ocorrência pública.

### RN28

A resolução de uma ocorrência deverá possuir data, justificativa e identificação do administrador responsável.

### RN29

Cidadãos poderão sugerir a resolução, mas a confirmação dependerá de análise administrativa.

### RN30

A localização pública deverá representar o problema urbano, e não necessariamente a residência ou posição pessoal do cidadão.

### RN31

E-mails, senhas, endereços privados e outros dados pessoais não deverão ser exibidos publicamente.

### RN32

Dados hidrológicos deverão possuir fonte, data e horário de referência.

### RN33

Simulações de inundação deverão ser identificadas como estimativas, não como previsões oficiais.

### RN34

O sistema não deverá inventar níveis de rios, cotas, alertas ou informações de barragens.

### RN35

Na indisponibilidade de informações atualizadas, o sistema deverá apresentar o horário do último registro e informar a indisponibilidade.

### RN36

A plataforma deverá orientar o cidadão a procurar os canais oficiais em situações de emergência.

### RN37

O registro de uma ocorrência não representa abertura automática de chamado perante a prefeitura.

### RN38

Os administradores deverão possuir permissões atribuídas de forma controlada, sem possibilidade de autoatribuição por cidadãos.

### RN39

Toda situação de ocorrência deverá respeitar as transições previstas na máquina de estados.

### RN40

Operações administrativas relevantes deverão ser registradas em auditoria.

### RN41

O sistema deverá permitir solicitações de exclusão de conta e dados pessoais, aplicando anonimização ou remoção quando apropriado.

### RN42

Cadastros e registros deverão possuir mecanismos básicos de prevenção contra spam e abuso.

## 12. Máquina de estados das ocorrências

Implementar as seguintes situações:

- PENDENTE.
- CORRECAO_SOLICITADA.
- PUBLICADA.
- EM_ACOMPANHAMENTO.
- RESOLVIDA.
- REJEITADA.
- DUPLICADA.
- ARQUIVADA.

Transições permitidas:

- PENDENTE → PUBLICADA.
- PENDENTE → REJEITADA.
- PENDENTE → CORRECAO_SOLICITADA.
- PENDENTE → DUPLICADA.
- CORRECAO_SOLICITADA → PENDENTE.
- PUBLICADA → EM_ACOMPANHAMENTO.
- PUBLICADA → RESOLVIDA.
- PUBLICADA → ARQUIVADA.
- PUBLICADA → DUPLICADA.
- EM_ACOMPANHAMENTO → RESOLVIDA.
- EM_ACOMPANHAMENTO → PUBLICADA.
- EM_ACOMPANHAMENTO → ARQUIVADA.
- RESOLVIDA → EM_ACOMPANHAMENTO.

A reabertura de uma ocorrência resolvida deverá possuir justificativa.

As transições deverão ser validadas no backend, independentemente das opções exibidas na interface.

A situação EM_ACOMPANHAMENTO indica acompanhamento pela plataforma, não necessariamente atendimento pela prefeitura.

## 13. Modelagem do banco de dados

Criar um modelo relacional normalizado, preferencialmente até a terceira forma normal.

### Tabela usuarios

- id
- nome
- email
- senha_hash
- perfil
- status
- criado_em
- atualizado_em

### Tabela bairros

- id
- nome
- municipio
- ativo

### Tabela categorias

- id
- nome
- descricao
- icone
- cor
- ativo

### Tabela subcategorias

- id
- categoria_id
- nome
- ativo

### Tabela ocorrencias

- id
- usuario_id
- categoria_id
- subcategoria_id
- bairro_id
- titulo
- descricao
- endereco_referencia
- latitude
- longitude
- status
- ocorrencia_principal_id
- criado_em
- atualizado_em
- publicado_em
- resolvido_em

### Tabela ocorrencia_imagens

- id
- ocorrencia_id
- caminho_arquivo
- tipo_mime
- tamanho_bytes
- criado_em

### Tabela confirmacoes

- id
- ocorrencia_id
- usuario_id
- criado_em

Criar restrição UNIQUE para ocorrencia_id e usuario_id.

### Tabela denuncias

- id
- ocorrencia_id
- usuario_id
- motivo
- descricao
- status
- administrador_id
- parecer
- criado_em
- analisado_em

### Tabela historico_ocorrencias

- id
- ocorrencia_id
- usuario_id
- status_anterior
- status_novo
- justificativa
- criado_em

### Tabela solicitacoes_resolucao

- id
- ocorrencia_id
- usuario_id
- descricao
- status
- administrador_id
- parecer
- criado_em
- analisado_em

### Tabela notificacoes

- id
- usuario_id
- ocorrencia_id
- titulo
- mensagem
- lida_em
- criado_em

### Tabela avisos

- id
- administrador_id
- titulo
- descricao
- fonte
- url_fonte
- status
- publicado_em
- expira_em

### Tabela niveis_rio

- id
- estacao
- nivel_metros
- fonte
- url_fonte
- registrado_em
- coletado_em

### Tabela cotas_inundacao

- id
- bairro_id
- rua
- trecho
- cota_metros
- estacao_referencia
- fonte
- url_fonte
- atualizado_em

### Tabela auditoria

- id
- usuario_id
- acao
- entidade
- entidade_id
- detalhes
- criado_em

### Diretrizes do banco

- Utilizar chaves estrangeiras.
- Criar índices para filtros de categoria, bairro, situação e datas.
- Utilizar DECIMAL para coordenadas e níveis dos rios.
- Impedir confirmações duplicadas.
- Garantir consistência entre categorias e subcategorias.
- Utilizar UTF8MB4.
- Não armazenar imagens diretamente como BLOB.
- Armazenar somente o caminho das imagens.
- Utilizar transações nas operações críticas.
- Evitar exclusões em cascata que eliminem registros históricos indevidamente.
- Manter mecanismos de anonimização e remoção de dados pessoais.

Gerar schema.sql e seed.sql contendo a estrutura completa e dados demonstrativos.

Os dados fictícios deverão ser claramente identificados como demonstração.

## 14. Interfaces do sistema

### Interface pública

Desenvolver as seguintes páginas:

1. Página inicial.
2. Mapa interativo.
3. Listagem de ocorrências.
4. Detalhes da ocorrência.
5. Indicadores públicos.
6. Monitoramento de enchentes.
7. Simulador de inundação.
8. Avisos à comunidade.
9. Sobre o projeto.
10. Termos de uso e privacidade.
11. Login.
12. Cadastro.

### Interface do cidadão

13. Painel pessoal.
14. Nova ocorrência.
15. Minhas ocorrências.
16. Edição de ocorrência.
17. Notificações.
18. Meu perfil.

### Interface administrativa

19. Dashboard administrativo.
20. Fila de moderação.
21. Análise de ocorrência.
22. Gerenciamento de ocorrências.
23. Gerenciamento de usuários.
24. Gerenciamento de categorias.
25. Gerenciamento de bairros.
26. Gerenciamento de denúncias.
27. Gerenciamento de solicitações de resolução.
28. Gerenciamento de avisos.
29. Gerenciamento de níveis e cotas de inundação.
30. Consulta de auditoria.

Páginas poderão compartilhar componentes, formulários e estruturas para evitar duplicação de código.

## 15. Design e experiência do usuário

Criar uma identidade visual institucional, moderna e minimalista.

Utilizar:

- Tema claro.
- Fundo branco ou cinza muito claro.
- Azul como cor institucional.
- Verde para ocorrências resolvidas.
- Amarelo ou laranja para ocorrências em acompanhamento.
- Vermelho para situações críticas ou avisos importantes.
- Cinza para informações secundárias.
- Ícones consistentes.
- Tipografia legível.
- Layout responsivo.
- Navegação simplificada.

Não depender exclusivamente das cores para comunicar situações. Utilizar textos e ícones associados.

Evitar:

- Excesso de animações.
- Gradientes desnecessários.
- Informações duplicadas.
- Interfaces sobrecarregadas.
- Componentes decorativos sem funcionalidade.
- Aparência de dashboard genérico.

### Página inicial

Apresentar:

- Identidade visual do CidadeColaborativa.
- Breve explicação do projeto.
- Botão Registrar ocorrência.
- Botão Consultar mapa.
- Quantidade de ocorrências públicas.
- Quantidade de ocorrências resolvidas.
- Ocorrências recentes.
- Categorias principais.
- Avisos importantes.
- Acesso ao monitoramento de enchentes.

### Mapa interativo

O mapa deverá ser a principal funcionalidade da plataforma.

Implementar:

- Marcadores por categoria.
- Popups com resumo da ocorrência.
- Filtros por bairro, categoria e situação.
- Agrupamento de marcadores, se necessário.
- Alternância entre mapa e listagem.
- Geolocalização opcional.
- Ajuste da área visualizada.
- Link para detalhes da ocorrência.
- Atribuição obrigatória ao OpenStreetMap e demais provedores utilizados.

Centralizar inicialmente o mapa na área de Rio do Sul.

Não solicitar geolocalização automaticamente ao acessar o site.

### Formulário de ocorrência

O formulário deverá possuir:

- Título.
- Categoria.
- Subcategoria.
- Descrição.
- Bairro.
- Referência de endereço.
- Localização no mapa.
- Fotografias.
- Pré-visualização das imagens.
- Confirmação de responsabilidade pelas informações.

Antes de concluir, apresentar uma revisão dos dados informados.

## 16. Dashboard e indicadores

### Indicadores públicos

Apresentar:

- Total de ocorrências públicas.
- Total de ocorrências resolvidas.
- Ocorrências em acompanhamento.
- Ocorrências por categoria.
- Ocorrências por bairro.
- Evolução mensal de registros.
- Percentual de resolução das ocorrências publicadas.
- Regiões com maior concentração de ocorrências.

### Indicadores administrativos

Apresentar adicionalmente:

- Ocorrências pendentes.
- Ocorrências rejeitadas.
- Denúncias pendentes.
- Solicitações de resolução.
- Tempo médio de moderação.
- Tempo médio entre publicação e resolução.
- Volume de registros por período.

Implementar gráficos por meio do Chart.js.

Os indicadores deverão ser calculados a partir dos registros do banco de dados.

Não apresentar quantidade de registros como medida direta da gravidade dos problemas de um bairro, pois a participação comunitária pode variar entre regiões.

## 17. Módulo de enchentes

Desenvolver este módulo como funcionalidade complementar e independente do registro de problemas urbanos.

### Monitoramento

Permitir:

- Consultar o nível registrado do rio.
- Consultar histórico de níveis.
- Visualizar gráficos temporais.
- Consultar data e horário da última atualização.
- Consultar informações oficiais sobre barragens, quando disponíveis.
- Acessar os canais oficiais da Defesa Civil.

### Simulação

Permitir que o usuário informe um nível hipotético do rio.

O sistema deverá comparar o valor informado com as cotas cadastradas e apresentar ruas ou trechos que potencialmente seriam atingidos.

Regras:

- Utilizar somente cotas oficiais ou dados claramente identificados como demonstrativos.
- Comparar cotas e níveis que utilizem a mesma estação e referência.
- Não interpretar uma cota como probabilidade estatística.
- Não afirmar que uma rua está segura exclusivamente por estar abaixo de determinada cota.
- Identificar claramente as limitações da simulação.
- Não recomendar rotas de evacuação com base apenas nessas informações.

### Integração externa

Investigar posteriormente a disponibilidade de dados públicos da Defesa Civil, ANA, Epagri e outras instituições relevantes.

Criar uma interface de serviço que permita futuras integrações sem alterar os demais módulos.

Caso não exista uma API pública apropriada, utilizar registros administrativos e importação de CSV.

Não realizar scraping sem verificar autorização, disponibilidade e condições de utilização.

### Evolução futura

Preparar a arquitetura para eventual inclusão de:

- Previsão hidrológica.
- Probabilidade de inundação.
- Tendência de elevação.
- Integração com estações meteorológicas.
- Monitoramento de barragens.

Esses recursos não deverão impedir a conclusão do projeto principal e não deverão gerar previsões sem dados e validação adequados.

## 18. Segurança

Implementar obrigatoriamente:

- password_hash() e password_verify().
- PDO com prepared statements.
- Validação de campos no backend.
- Escape de conteúdo HTML.
- Tokens CSRF em operações de alteração.
- Regeneração do identificador de sessão após login.
- Verificação de permissões em todas as rotas protegidas.
- Controle de acesso aos registros pertencentes a outros usuários.
- Cookies de sessão HttpOnly e SameSite.
- Cookies Secure em ambientes HTTPS.
- Limitação básica de tentativas de login.
- Limitação de frequência de registros e denúncias.
- Validação do MIME real das imagens.
- Nomes aleatórios para arquivos enviados.
- Remoção de metadados de imagens, quando tecnicamente possível.
- Bloqueio de execução de arquivos enviados.
- Proteção contra enumeração indevida de usuários.
- Tratamento centralizado de erros.
- Logs administrativos.
- Credenciais fora do repositório.

Nenhuma senha deverá ser armazenada em texto puro.

Não confiar em permissões ou validações realizadas exclusivamente pelo JavaScript.

## 19. Engenharia de software

Criar uma documentação completa dentro da pasta docs.

### 19.1. Documentação inicial

- Visão do produto.
- Contextualização.
- Problema identificado.
- Justificativa.
- Objetivo geral.
- Objetivos específicos.
- Público-alvo.
- Escopo.
- Limitações.
- Partes interessadas.

### 19.2. Levantamento de requisitos

Preparar roteiros de entrevistas para moradores, representantes comunitários e possíveis representantes da Defesa Civil ou prefeitura.

Investigar:

- Como os problemas urbanos são comunicados atualmente?
- Quais dificuldades existem?
- Quais informações são necessárias em uma ocorrência?
- Quais categorias são mais relevantes?
- Como a população acompanharia os registros?
- Quais cuidados de privacidade são necessários?
- Quais informações seriam úteis durante enchentes?

Não inventar entrevistas, respostas, participantes ou evidências.

Manter os registros das entrevistas como documentos pendentes até a realização efetiva pelo grupo.

### 19.3. Especificação de requisitos

Documentar:

- Requisitos funcionais.
- Requisitos não funcionais.
- Regras de negócio.
- Prioridades.
- Critérios de aceitação.
- Matriz de rastreabilidade.

A matriz deverá relacionar requisitos, regras de negócio, casos de uso, componentes implementados e casos de teste.

### 19.4. Casos de uso

Criar especificações detalhadas para os principais casos de uso:

- CSU01 — Cadastrar cidadão.
- CSU02 — Autenticar usuário.
- CSU03 — Registrar ocorrência.
- CSU04 — Consultar ocorrências.
- CSU05 — Confirmar ocorrência.
- CSU06 — Denunciar ocorrência.
- CSU07 — Moderar ocorrência.
- CSU08 — Atualizar situação.
- CSU09 — Solicitar resolução.
- CSU10 — Consultar indicadores.
- CSU11 — Consultar informações sobre enchentes.
- CSU12 — Simular inundação.

Cada caso de uso deverá conter:

- Identificador.
- Nome.
- Descrição.
- Atores.
- Pré-condições.
- Pós-condições.
- Fluxo principal.
- Fluxos alternativos.
- Fluxos de exceção.
- Regras de negócio relacionadas.
- Requisitos relacionados.

### 19.5. Diagramas

Produzir:

- Diagrama de casos de uso.
- Diagrama de classes.
- Diagrama de entidade-relacionamento.
- Diagrama de componentes.
- Diagrama de implantação.
- Diagramas de atividade dos fluxos principais.
- Diagramas de sequência dos fluxos principais.
- Diagrama de máquina de estados das ocorrências.

Priorizar os fluxos de registro, moderação, confirmação e resolução.

Gerar os diagramas em formato editável compatível com Draw.io, utilizando XML ou arquivos .drawio.

Manter os diagramas organizados, com espaçamento adequado, identificação de atores e componentes, evitando cruzamentos e sobreposições desnecessárias.

### 19.6. Protótipos

Criar protótipos das principais interfaces antes de consolidar o frontend.

Priorizar:

- Página inicial.
- Mapa.
- Cadastro de ocorrência.
- Detalhes da ocorrência.
- Painel do cidadão.
- Fila de moderação.
- Dashboard administrativo.
- Monitoramento de enchentes.

### 19.7. Documentação técnica

Documentar:

- Arquitetura.
- Estrutura de pastas.
- Banco de dados.
- Rotas.
- Dependências.
- Configuração do ambiente.
- Instalação.
- Execução.
- Segurança.
- Limitações conhecidas.

## 20. Casos de teste

Elaborar testes manuais e, quando viável, automatizados.

Validar obrigatoriamente:

- Cadastro válido.
- Cadastro com e-mail duplicado.
- Login válido e inválido.
- Acesso não autorizado ao painel administrativo.
- Registro de ocorrência.
- Registro sem campos obrigatórios.
- Localização inválida.
- Upload de imagens válidas.
- Rejeição de arquivos executáveis.
- Rejeição de imagens acima do limite.
- Aprovação de ocorrência.
- Rejeição sem justificativa.
- Solicitação de correção.
- Alteração de situação inválida.
- Confirmação duplicada.
- Confirmação da própria ocorrência.
- Denúncia de ocorrência.
- Filtros do mapa.
- Solicitação de resolução.
- Atualização do histórico.
- Bloqueio de acesso indevido a registros.
- Tentativas de SQL Injection.
- Tentativas de XSS.
- Requisições sem token CSRF.
- Responsividade.
- Indisponibilidade de dados hidrológicos.
- Simulação com cotas incompatíveis.
- Integridade dos indicadores.

Cada caso de teste deverá conter:

- Identificador.
- Requisito relacionado.
- Pré-condições.
- Dados de entrada.
- Procedimento.
- Resultado esperado.
- Resultado obtido.
- Situação.

Não marcar testes como aprovados sem executá-los.

## 21. Priorização e desenvolvimento

Utilizar a técnica MoSCoW para organização do escopo.

### Must have — Obrigatório para o MVP

- Cadastro e autenticação.
- Registro de ocorrências.
- Upload seguro de fotografias.
- Banco de dados.
- Mapa interativo.
- Listagem e filtros.
- Moderação administrativa.
- Gerenciamento básico de categorias e bairros.
- Alteração de situação.
- Histórico.
- Segurança.
- Documentação essencial.

### Should have — Importante

- Confirmações comunitárias.
- Denúncias.
- Indicadores.
- Solicitações de resolução.
- Notificações internas.
- Identificação de duplicidades.
- Auditoria administrativa.

### Could have — Complementar

- Monitoramento dos níveis do rio.
- Simulador de inundação.
- Importação de dados hidrológicos.
- Avisos à comunidade.
- Indicadores avançados.

### Won't have — Fora do escopo inicial

- Previsão probabilística de enchentes.
- Previsão de rompimento de barragens.
- Aplicativos nativos.
- Integração obrigatória com sistemas municipais.
- Inteligência artificial.
- Notificações push.
- Microsserviços.

## 22. Cronograma acadêmico

Considerar a carga horária prevista pelo professor.

### Planejamento, análise e apresentação — 10 horas

- Levantamento de requisitos: 3 horas.
- Documentação e modelagem: 3 horas.
- Protótipos: 2 horas.
- Apresentação e gravação do vídeo: 2 horas.

### Desenvolvimento — 30 horas

- Configuração e banco de dados: 3 horas.
- Autenticação e segurança: 4 horas.
- Registro e consulta de ocorrências: 6 horas.
- Mapa interativo: 4 horas.
- Painel administrativo: 5 horas.
- Histórico, indicadores e funcionalidades complementares: 3 horas.
- Testes e correções: 3 horas.
- Revisão e documentação técnica: 2 horas.

Caso o tempo seja insuficiente, priorizar o MVP e documentar as funcionalidades complementares não implementadas.

## 23. Git e GitHub

Utilizar Git desde o início do desenvolvimento.

Manter:

- Branch main estável.
- Branches para funcionalidades, quando necessário.
- Commits pequenos e descritivos.
- README.md atualizado.
- .gitignore apropriado.
- Histórico real de desenvolvimento.

Exemplos de commits:

- feat: implementar cadastro de ocorrencias
- feat: adicionar mapa interativo
- fix: corrigir validacao de imagens
- docs: adicionar requisitos do sistema
- test: validar transicoes de ocorrencias

Não versionar credenciais, arquivos .env, dados pessoais reais ou fotografias privadas.

## 24. Critérios de aceitação do MVP

O projeto será considerado funcional quando:

- Um cidadão conseguir criar uma conta.
- O cidadão conseguir realizar login.
- O cidadão conseguir registrar uma ocorrência com localização e fotografia.
- A ocorrência permanecer invisível publicamente até aprovação.
- O administrador conseguir analisar e aprovar a ocorrência.
- A ocorrência aprovada aparecer no mapa e na listagem pública.
- Os filtros retornarem informações coerentes.
- O administrador conseguir alterar a situação da ocorrência.
- O cidadão conseguir acompanhar suas ocorrências.
- O histórico registrar corretamente as alterações.
- As permissões impedirem acessos indevidos.
- A aplicação funcionar em dispositivos móveis.
- O projeto puder ser instalado localmente seguindo o README.

## 25. Entregáveis acadêmicos

Entregar:

- Código-fonte funcional.
- Repositório GitHub.
- Script SQL de criação do banco.
- Dados demonstrativos.
- Documentação de planejamento.
- Levantamento de requisitos.
- Requisitos funcionais e não funcionais.
- Regras de negócio.
- Casos de uso.
- Diagramas editáveis.
- Protótipos.
- Matriz de rastreabilidade.
- Documentação de testes.
- Manual de instalação.
- Manual básico do usuário.
- Material para apresentação final.
- Roteiro para vídeo demonstrativo de até três minutos.

## 26. Instruções de execução para o agente de desenvolvimento

Antes de começar, verificar o conteúdo do diretório atual e identificar se existe um projeto previamente configurado.

Não sobrescrever arquivos existentes sem analisar seu conteúdo.

Executar o desenvolvimento seguindo estas etapas:

1. Analisar o escopo e identificar dependências.
2. Criar a documentação inicial.
3. Organizar o backlog de desenvolvimento.
4. Criar a estrutura MVC.
5. Implementar o banco de dados.
6. Implementar autenticação e autorização.
7. Desenvolver os módulos de ocorrências.
8. Implementar o mapa interativo.
9. Desenvolver o painel administrativo.
10. Implementar moderação e histórico.
11. Executar os testes do MVP.
12. Corrigir falhas encontradas.
13. Implementar funcionalidades complementares conforme disponibilidade.
14. Atualizar a documentação.
15. Validar a instalação completa.
16. Preparar os materiais de entrega.

Não gerar somente exemplos, pseudocódigo ou interfaces estáticas.

Implementar funcionalidades reais e integradas ao banco de dados.

Não utilizar botões fictícios ou componentes que aparentem funcionar sem possuir implementação.

Sempre que uma funcionalidade depender de dados externos indisponíveis, informar essa limitação explicitamente.

Priorizar um sistema funcional, seguro, simples e compreensível por estudantes de Sistemas de Informação.

Ao finalizar, apresentar:

- Funcionalidades implementadas.
- Estrutura do projeto.
- Instruções de execução.
- Dependências utilizadas.
- Testes executados e seus resultados.
- Pendências.
- Limitações.
- Sugestões de melhorias.

Iniciar o desenvolvimento do projeto CidadeColaborativa seguindo integralmente essas especificações, priorizando o MVP antes das funcionalidades complementares.
