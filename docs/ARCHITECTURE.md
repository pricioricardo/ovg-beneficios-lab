# Arquitetura e decisões

## Estrutura inicial

Use uma aplicação Laravel com Filament como framework da interface administrativa e MySQL como banco de dados relacional. O Docker Compose executa somente a aplicação PHP/Apache e o MySQL. Mantenha o piloto pequeno e evite serviços externos até que um requisito concreto os justifique.

## Registro de decisões

### ADR-001: Aplicação Laravel 13 com Filament 5 e Livewire 4, usando MySQL 8.4

- **Status:** Aceito para o experimento.
- **Contexto:** A equipe está avaliando desenvolvimento assistido por agentes em uma aplicação pequena e fácil de revisar.
- **Decisão:** A baseline do Gate 1 é PHP 8.4, Laravel 13, Filament 5, Livewire 4, MySQL 8.4 LTS, Composer 2 e Docker Compose. As versões exatas resolvidas dos pacotes Composer estão em `composer.lock`.
- **Consequências:** Mantenha a lógica de negócio em Laravel e as responsabilidades de interface em Filament. Não adicione Redis, workers de fila, microsserviços ou plataformas de orquestração a este piloto. Limite a interface futura a 10 telas. Os resultados da certificação de runtime do Gate 1 estão registrados em `.agent/STATE.md`.

### ADR-002: Instruções de projeto independentes de provedor

- **Status:** Aceito.
- **Contexto:** O repositório deve funcionar com Codex e Claude Code, tanto na nuvem quanto em VS Code local.
- **Decisão:** Mantenha o comportamento compartilhado em `AGENTS.md`, `policies/`, `skills/`, `scripts/` e `docs/`; mantenha `CLAUDE.md` e futuras pastas de provedores como adaptadores finos.
- **Consequências:** Políticas e verificações essenciais não podem depender de um único fornecedor de IA.

### ADR-003: Compose para o runtime de desenvolvimento

- **Status:** Aceito.
- **Contexto:** Desenvolvedores precisam de um fluxo repetível com `docker compose up -d` e paridade entre ambiente local e Cloud.
- **Decisão:** Defina somente os serviços `app` e `mysql`, com um volume nomeado para os dados MySQL e um health check para ordenar a inicialização.
- **Consequências:** As dependências da aplicação são fixadas pelo Composer. O `.env` local e secrets ficam fora do Git. A verificação usa um script determinístico.

### ADR-004: Engine declarativa restrita para elegibilidade

- **Status:** Proposto para congelamento no Gate 2.
- **Contexto:** Critérios simples devem mudar sem alteração de PHP, mas uma engine arbitrária ampliaria risco, custo de manutenção e dificuldade de auditoria para um piloto de até dez telas.
- **Decisão:** Representar regras como árvores limitadas de Condições tipadas e grupos `AND`/`OR`. Requisitos, tipos, operadores, fontes e parâmetros pertencem a catálogos fechados. Proibir código, SQL, scripts, paths técnicos e expressões livres. Limitar cada versão a três níveis de grupos e 30 Condições.
- **Consequências:** Analistas podem ajustar valores e combinações dentro do vocabulário aprovado. Novos fatos ou semânticas exigem evolução explícita do produto. Validação completa ocorre antes da publicação, e cada nó produz resultado explicável.

### ADR-005: Versões publicadas de regras são imutáveis

- **Status:** Proposto para congelamento no Gate 2.
- **Contexto:** Avaliações antigas precisam continuar explicáveis quando critérios ou parâmetros mudarem.
- **Decisão:** Versões seguem `RASCUNHO`, `PUBLICADA`, `SUBSTITUIDA` ou `INATIVA`. A publicação congela a árvore e as referências; mudanças criam uma sucessora com vigência não sobreposta. Parâmetros globais também mantêm versões e vigências rastreáveis.
- **Consequências:** Uma Avaliação sempre aponta à versão vigente usada no instante de referência. Correções não reescrevem histórico. A publicação futura precisará de transação e restrições, mas não de optimistic locking geral no MVP.

### ADR-006: Snapshot histórico híbrido das avaliações

- **Status:** Proposto para congelamento no Gate 2.
- **Contexto:** Dados cadastrais, idade, documentos, regras e parâmetros podem mudar depois de uma Avaliação.
- **Decisão:** Manter referências relacionais às entidades e versões, um snapshot JSON controlado e versionado das entradas/derivações e Resultados de Avaliação relacionais para cada nó da árvore.
- **Consequências:** Avaliações concluídas ficam imutáveis e explicáveis sem consultar o estado atual do Beneficiário. O JSON não substitui identidade e integridade relacionais. A implementação deverá versionar e validar seu schema.

Registre futuras decisões materiais com status, contexto, decisão e consequências. Não reescreva uma migration consolidada para expressar uma mudança posterior de schema; adicione uma nova migration.
