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
- **Decisão:** Representar regras como árvores limitadas de Condições tipadas e grupos `AND`/`OR`. Requisitos, tipos, operadores, fontes e parâmetros pertencem a catálogos fechados. Cada Condição publicada fixa a versão semântica do Requisito; mudança de resolvedor capaz de alterar resultado exige nova versão semântica e nova Versão de Regra. Proibir código, SQL, scripts, paths técnicos e expressões livres. Limitar cada versão a três níveis de grupos e 30 Condições.
- **Consequências:** Analistas podem ajustar valores e combinações dentro do vocabulário aprovado. Novos fatos ou semânticas exigem evolução explícita do produto, sem trocar silenciosamente resolvedores publicados. Validação completa ocorre antes da publicação, e cada nó produz resultado explicável.

### ADR-005: Versões publicadas de regras são imutáveis

- **Status:** Proposto para congelamento no Gate 2.
- **Contexto:** Avaliações antigas precisam continuar explicáveis quando critérios ou parâmetros mudarem.
- **Decisão:** Versões seguem `RASCUNHO`, `PUBLICADA`, `SUBSTITUIDA` ou `INATIVA`. A publicação imediata congela conteúdo e significado, mas permite atualizar estado, fim de vigência e metadados de encerramento somente pelas transições autorizadas. A troca para uma sucessora encerra a anterior e inicia a nova atomicamente no mesmo instante `T`, em vigências `[início, fim)` não sobrepostas. Versões futuras permanecem rascunho. Parâmetros globais seguem a mesma disciplina; a Regra guarda seu código lógico e a Avaliação resolve a versão vigente.
- **Consequências:** A Avaliação mantém regra e parâmetros capturados no início mesmo durante uma troca. Correções não reescrevem histórico. A publicação precisará de transação e restrições, sem optimistic locking geral no MVP.

### ADR-006: Snapshot histórico híbrido das avaliações

- **Status:** Proposto para congelamento no Gate 2.
- **Contexto:** Dados cadastrais, idade, documentos, regras e parâmetros podem mudar depois de uma Avaliação.
- **Decisão:** O servidor captura uma vez o instante corrente em UTC no início da Avaliação, sem escolha retroativa ou futura pelo usuário. Cálculos de calendário usam `America/Sao_Paulo`; o mesmo instante seleciona regra, parâmetros e validade documental. Manter referências relacionais às entidades e versões, um snapshot JSON controlado e versionado das entradas/derivações e Resultados de Avaliação relacionais para cada nó da árvore. Valores monetários decisórios são exatos em centavos, sem `float` ou arredondamento de exibição.
- **Consequências:** Avaliações concluídas ficam imutáveis e explicáveis sem consultar o estado atual do Beneficiário. Snapshot, resultados por nó e conclusão precisam ser persistidos consistentemente. O JSON não substitui identidade e integridade relacionais. A implementação deverá versionar e validar seu schema.

Registre futuras decisões materiais com status, contexto, decisão e consequências. Não reescreva uma migration consolidada para expressar uma mudança posterior de schema; adicione uma nova migration.
