# ExecPlans (planos de execução)

Use um ExecPlan para trabalhos que envolvam várias etapas ou arquivos, alterem arquitetura ou formato de dados, tragam risco material ou não possam ser concluídos e verificados em uma única etapa focada. Mudanças pequenas e locais não precisam de plano.

Armazene os planos ativos em `.agent/` com um nome descritivo, por exemplo `PLAN-filament-auth.md`. Um plano deve incluir:

1. **Objetivo e escopo** — resultado esperado, exclusões explícitas e critérios de aceite.
2. **Estado atual** — código, decisões, restrições e dependências relevantes.
3. **Etapas** — ações em ordem, indicando arquivos ou componentes.
4. **Validação** — comandos exatos e resultados observáveis, incluindo serviços necessários.
5. **Riscos e recuperação** — preocupações com dados ou compatibilidade e um caminho seguro de recuperação.
6. **Progresso** — trabalho concluído, próxima ação e blockers, atualizados durante a execução.

Mantenha o plano útil para outro agente sem o histórico do chat. Aponte para as policies e decisões de arquitetura relevantes em vez de copiá-las. Atualize `.agent/STATE.md` quando o plano ativo ou a etapa do projeto mudar; arquive ou remova planos concluídos quando deixarem de ajudar trabalhos futuros.
