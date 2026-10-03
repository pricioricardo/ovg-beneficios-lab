# ExecPlan (plano de execução): Gate 2 — contrato de domínio

## Objetivo e escopo

Definir e congelar para revisão o contrato conceitual do domínio e do motor de elegibilidade do MVP, sem criar código funcional, schema, telas ou integrações. O resultado deve permitir que o Gate 3 implemente um recorte aprovado sem rediscutir conceitos essenciais.

## Estado inicial

- A base obrigatória é `origin/work` em `93ccb95e280c54c91b35a62c57360d9149145cc3`.
- O Gate 1 e o Gate 1.1 estão integrados e aprovados.
- A baseline certificada permanece PHP 8.4.26, Laravel 13.34.0, Filament 5.9.0, Livewire 4.4.7, MySQL 8.4.11, Composer 2.8.12 e Docker Compose v2.40.3.
- O branch deste gate é `docs/gate-002-domain-contract`.
- O repositório não contém implementação de domínio.

## Etapas

1. Definir entidades, responsabilidades, dados persistidos e derivados, relações e limites do MVP.
2. Comparar estratégias de regras e especificar uma engine declarativa restrita, com tipos, operadores, grupos e parâmetros fechados.
3. Definir versionamento, snapshots, resultados individuais, explicabilidade, auditoria e invariantes.
4. Demonstrar os cinco casos fictícios com a notação conceitual do contrato e verificar o limite de telas.
5. Registrar as decisões materiais como ADRs e atualizar roadmap e estado.
6. Revisar o diff, executar as verificações canônicas, fazer um único commit e publicar somente este branch.

## Validação

- Todos os critérios em `docs/domain/ACCEPTANCE_CRITERIA.md` devem estar satisfeitos.
- `./scripts/verify.sh` e `git diff --check` devem passar.
- O diff não pode tocar `database/migrations/`, `app/Models/`, `app/Filament/` ou `app/Services/`.
- `composer.json`, `composer.lock`, `Dockerfile` e `compose.yaml` devem permanecer inalterados.

## Riscos e recuperação

- Ambiguidade excessiva tornaria o Gate 3 dependente de decisões implícitas; cada estado e precedência deve ser fechado no contrato.
- Flexibilidade excessiva criaria uma linguagem arbitrária; tipos, operadores, fontes e profundidade devem permanecer limitados.
- O contrato pode crescer além do piloto; entidades e telas são limitadas ao necessário para os cinco exemplos fictícios.
- Qualquer alteração funcional acidental será removida antes do commit, preservando o runtime certificado.

## Progresso

- [x] Ler instruções, policies, arquitetura, roadmap, baseline e estado.
- [x] Confirmar `origin/work`, árvore limpa e criar o branch.
- [x] Produzir o contrato e os exemplos.
- [x] Registrar ADRs, roadmap e estado.
- [x] Revisar o escopo e executar `./scripts/verify.sh` (2 testes, 3 assertions) e `git diff --check`.
- [x] Fazer commit único e publicar o branch sem merge.
