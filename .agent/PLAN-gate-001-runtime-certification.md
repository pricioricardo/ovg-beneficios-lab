# ExecPlan (plano de execução): certificação do runtime no Gate 1

## Objetivo e escopo

Certificar o runtime de desenvolvimento solicitado: PHP 8.4, Laravel 13, Filament 5, Livewire 4 e MySQL 8.4, a partir do commit do Gate 0 `a447700302dab51652db3853dae86792cb316f44`. Não adicionar entidades de domínio, regras, dados de seed ou telas. Não fazer merge deste branch.

O aceite exige a checklist completa do pedido do Gate 1, incluindo bootstrap bem-sucedido, conexão ao banco e migrations, smoke test HTTP do painel, testes automatizados e aprovação integral de `scripts/verify.sh`.

## Estado inicial

- A auditoria foi concluída no `origin/work` limpo, no commit do Gate 0.
- O scaffold existente era da geração Laravel 12.12.2; o lock resolvia framework 12.69.3, Filament 4.14.0 e Livewire 3.8.10.
- A configuração Docker existente usava PHP 8.3 e MySQL 8.4. Nenhum serviço da aplicação ou banco estava ativo na auditoria.
- O acesso HTTPS ao Packagist e ao GitHub codeload foi bem-sucedido; a resolução e instalação completas do Composer foram concluídas usando TLS.
- O branch de trabalho foi `chore/gate-001-runtime-certification`, criado a partir de `origin/work`.

## Estratégia

Atualizar o scaffold Laravel mínimo existente. Ele continha apenas código padrão do framework e nenhum domínio; ajustar as restrições do Composer, a imagem PHP e a configuração enxuta do painel trazia menos risco que recriar o repositório e perturbar o harness.

## Etapas

1. Registrar a decisão sobre a baseline e ajustar as restrições do Composer para PHP 8.4, Laravel 13, Filament 5 e Livewire 4; gerar e validar o lockfile.
2. Atualizar a imagem Docker PHP para 8.4, manter somente os serviços app e MySQL 8.4 e tornar o bootstrap responsável por instalar, migrar, iniciar e aguardar de forma determinística.
3. Instalar um painel Filament mínimo e vazio, sem recursos de domínio nem criação de usuário administrativo.
4. Adicionar testes somente de runtime para saúde HTTP, conectividade com MySQL e painel vazio.
5. Ampliar `scripts/verify.sh` para conferir versões das ferramentas e pacotes, containers e health check do Compose, migrations, testes, saúde HTTP e resposta do painel; executar bootstrap e verify completos.
6. Atualizar `.agent/STATE.md` com as versões medidas e os resultados exatos. Fazer commit e push somente deste branch, sem merge.

## Validação

- `composer validate --strict`
- `./scripts/bootstrap.sh`
- `./scripts/verify.sh`
- Confirmação direta de `php artisan --version`, MySQL `SELECT 1`, migrations, `/up` e `/admin`.
- `git diff --check` e estado final do Git.

## Riscos e recuperação

- O acesso externo do Composer ou Docker poderia falhar durante esta tarefa. Preservar TLS e checksums; identificar o host bloqueado e parar com `GATE 1 — BLOCKED` se fosse necessária uma mudança externa do ambiente.
- Restrições de versões principais poderiam expor incompatibilidades no scaffold. Corrigir a configuração de framework suportada e repetir as verificações afetadas; nunca alterar testes para mascarar comportamento quebrado.
- Se uma instalação parcial ou migration falhasse, manter os volumes locais sintéticos e diagnosticar; não remover dados de usuário não relacionados nem redefinir o histórico Git.

## Progresso

- [x] Ler instruções do projeto, policies, arquitetura, manifests, configuração Docker e estado do Git.
- [x] Confirmar que `origin/work` era o commit do Gate 0; criar o branch solicitado.
- [x] Atualizar e documentar a baseline de versões.
- [x] Instalar dependências e preparar o runtime Docker.
- [x] Configurar painel Filament mínimo e verificações de runtime.
- [x] Executar bootstrap completo e verificar a certificação.
- [x] Registrar o resultado, fazer commit e push sem merge.

## Nota de tradução

Este registro histórico foi traduzido durante o Gate 1.1. Os eventos, estados, critérios, comandos, commits e versões originais foram preservados.
