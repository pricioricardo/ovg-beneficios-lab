# ExecPlan (plano de execução): Gate 1.1 — harness em pt-BR

## Objetivo e escopo

Padronizar em português do Brasil o harness e a documentação, consolidar `docs/ROADMAP.md` e preservar sem alteração o runtime certificado no Gate 1. Não implementar domínio, não alterar versões da stack e não fazer merge.

## Estado inicial

- `origin/work` e `HEAD` estavam em `f5b03e2ea64a925c9609e717a4cc72d6cf467946`, com o Gate 1 integrado.
- O branch de trabalho é `chore/gate-001b-harness-ptbr`, criado a partir desse commit.
- A working tree tinha apenas a anotação de continuidade Cloud solicitada anteriormente em `.agent/STATE.md`. Ela foi preservada para inclusão na tradução do estado.
- A baseline certificada é PHP 8.4.26, Laravel 13.34.0, Filament 5.9.0, Livewire 4.4.7, MySQL 8.4.11, Composer 2.8.12 e Docker Compose v2.40.3.
- Nenhuma alteração funcional está autorizada.

## Etapas

1. Traduzir instruções, estado, ExecPlans, documentação, policies, skills, evals e mensagens humanas dos scripts, preservando comandos, identificadores e significado.
2. Adicionar a regra permanente de idioma pt-BR a `AGENTS.md` e manter `CLAUDE.md` como adaptador fino.
3. Criar o roadmap canônico com Gates 0, 1, 1.1, 2 e 3, etapas futuras não autorizadas, Model Routing futuro e observações do runtime.
4. Atualizar `.agent/STATE.md` com o resultado real, preservando o histórico da exceção de continuidade Cloud.
5. Comparar o diff com os originais para confirmar equivalência semântica e ausência de alterações ao runtime.
6. Executar `./scripts/verify.sh` e `git diff --check`; criar um commit e publicar somente o branch deste Gate, sem merge.

## Validação

- `git diff` revisado para conteúdo perdido, guardrails enfraquecidos, nomes técnicos ou comandos alterados e mudanças fora do escopo.
- `./scripts/verify.sh` passa integralmente com os serviços Docker existentes.
- `git diff --check` passa.
- `composer.json`, `composer.lock`, `Dockerfile` e `compose.yaml` permanecem sem alteração.
- O diff não introduz entidades, regras, telas ou dados de domínio.

## Riscos e recuperação

- Traduções podem mudar o sentido de um guardrail; conferir cada policy contra o texto de origem antes do commit.
- Uma alteração acidental ao runtime será removida do diff antes do commit; não atualizar dependências nem regenerar arquivos técnicos.
- A observação Cloud pré-existente será preservada; nenhuma configuração Cloud ou de proxy será alterada.

## Progresso

- [x] Confirmar a base, revisar o diff local preexistente e criar o branch do Gate 1.1.
- [x] Ler integralmente instruções, estado, planos, docs, policies, skills, evals e scripts exigidos.
- [x] Traduzir o harness e consolidar o roadmap.
- [x] Revisar equivalência semântica e confirmar que o runtime não mudou.
- [x] Executar `./scripts/verify.sh` (2 testes, 3 assertions) e `git diff --check`; registrar o resultado do Gate 1.1.
- [ ] Criar um commit único e publicar o branch sem merge.
