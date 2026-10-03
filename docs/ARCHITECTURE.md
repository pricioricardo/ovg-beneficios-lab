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

Registre futuras decisões materiais com status, contexto, decisão e consequências. Não reescreva uma migration consolidada para expressar uma mudança posterior de schema; adicione uma nova migration.
