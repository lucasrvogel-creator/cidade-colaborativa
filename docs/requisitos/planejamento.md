# Planejamento — CidadeColaborativa

## Visão e justificativa
Plataforma acadêmica de participação comunitária para Rio do Sul, desenvolvida para Programação Web I / Sistemas de Informação / UNIDAVI. Centraliza relatos urbanos dispersos, permite consulta geográfica e acompanhamento com moderação. Público: moradores, representantes comunitários e equipe moderadora. Não representa prefeitura ou Defesa Civil e não abre chamados oficiais.

## Decisão de arquitetura
PHP 8.2+, orientação a objetos, MVC simplificado, PDO/MySQL e sessões. HTML renderizado no servidor, Bootstrap 5, JavaScript sem framework, Fetch, Leaflet/OpenStreetMap e Chart.js. A configuração React inicial é substituída; continua recuperável no commit anterior. Sem Docker ou serviços pagos obrigatórios.

## Backlog e prioridades
1. **Must:** configuração, esquema relacional e dados demonstrativos; autenticação e permissões; registro e edição com fotografias; listagem/mapa/filtros; moderação, estados e histórico; cadastros auxiliares; documentação e testes.
2. **Should:** confirmações, denúncias, resolução sugerida, notificações, perfil, auditoria, indicadores e possíveis duplicidades.
3. **Could:** avisos, registros hidrológicos e simulador de cotas compatíveis. Sem integração externa automática: informar ausência de dados e aceitar registros administrativos rastreáveis.
4. **Won't:** previsão de enchentes, previsão de barragens, IA, apps nativos, notificações push e integração obrigatória com prefeitura.

## Etapas de entrega
Cada etapa coerente terá commit descritivo, com verificações antes do registro. Estimativa acadêmica do enunciado: 10 horas de planejamento/apresentação e 30 horas de desenvolvimento; não constitui registro de horas efetivamente realizadas. Priorizar fluxo ponta a ponta do MVP. Funcionalidades e verificações ainda não concluídas devem constar como pendentes.

## Critério principal
Cadastrar cidadão → autenticar → registrar ocorrência com imagem e localização → manter privada → moderar como administrador → publicar no mapa → atualizar situação → acompanhar histórico. O banco é a fonte dos dados; não simular sucesso em botões.

## Levantamento com interessados — pendente
Não foram realizadas entrevistas. O grupo deve coletar consentimento e entrevistar moradores, lideranças e possíveis representantes públicos. Registrar data, participante autorizado, método e evidência, sem publicar dados pessoais desnecessários.

Roteiro: como comunica problemas hoje? Quais dificuldades? Quais categorias e informações são necessárias? Como acompanhar respostas? Quais riscos de exposição de pessoas? Quais fontes oficiais usa em enchentes? Como avaliar uma interface acessível? Que limitações devem ser explicadas?

## Limitações iniciais
Sem acordo institucional, entrevistas comprovadas ou fonte hidrológica integrada. Fotos e descrições exigem moderação humana. Mapa base depende de acesso à internet e política do provedor. Dados de demonstração não representam situação real da cidade.
