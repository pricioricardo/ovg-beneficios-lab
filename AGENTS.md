# OVG Benefícios Lab

## Objetivo e escopo

Este repositório é uma aplicação experimental pequena, feita com Laravel e Filament, para avaliar engenharia assistida por agentes. Use somente dados sintéticos. O projeto se inspira em descrições públicas de programas de benefícios sociais, mas não reproduz sistemas internos da OVG nem pressupõe acesso a eles. Mantenha a aplicação futura em, no máximo, 10 telas.

A etapa atual trata somente da engenharia e do harness. Não adicione beneficiários, entidades de benefícios, requisitos de elegibilidade, avaliações, regras de negócio ou telas de negócio, a menos que uma tarefa futura autorize explicitamente esse trabalho.

## Idioma oficial do projeto

- Português do Brasil (pt-BR) é o idioma padrão do harness, da documentação e das instruções humanas deste projeto.
- Escreva documentação, policies, planos, estado e instruções humanas de Skills novas em pt-BR.
- Relatórios de agentes devem preferir pt-BR.
- Preserve identificadores técnicos necessários para interoperabilidade, incluindo comandos, nomes de classes, namespaces, métodos, propriedades, variáveis, pacotes, chaves de configuração, paths, arquivos, branches e nomes oficiais de tecnologias.
- Uma tradução nunca pode mudar o sentido de guardrails, critérios de aceite, comandos ou contratos.

## Regras de trabalho portáteis

- Este arquivo e `policies/`, `skills/`, `scripts/` e `docs/` são a fonte compartilhada para Codex, Claude Code e desenvolvimento local.
- Antes de uma alteração substancial, leia os arquivos aplicáveis em `policies/`. Siga `docs/ARCHITECTURE.md` e registre nele decisões materiais.
- Use somente dados sintéticos. Nunca inclua secrets no Git. Nunca presuma acesso a sistemas internos da OVG nem faça alterações diretamente em produção.
- Não faça push diretamente para `main`. Trabalhe em um branch e use revisão antes da integração.
- Adicione ou atualize testes automatizados relevantes para mudanças de comportamento. Não declare o trabalho concluído até `scripts/verify.sh` passar; informe com precisão qualquer verificação indisponível.
- Trate migrations consolidadas como imutáveis. Para uma mudança de schema posterior, adicione uma nova migration.
- Prefira scripts determinísticos e configurações suportadas pelo repositório a instruções repetidas em prosa.
- Para mudanças complexas, siga `.agent/PLANS.md` e mantenha `.agent/STATE.md` conciso e atualizado.
- Mantenha o comportamento específico de cada provedor em adaptadores finos, como `CLAUDE.md`; não mova políticas compartilhadas para `.claude/` ou `.codex/`.
- Use o checkout existente nas tarefas Cloud. Não crie um Git worktree a menos que o usuário solicite.

## Trabalho neste repositório

- Prepare ou atualize os pré-requisitos locais com `./scripts/bootstrap.sh`.
- Em um checkout novo, inicie a aplicação e MySQL com `docker compose up -d --build`; depois que a imagem estiver construída, use `docker compose up -d`.
- Execute as verificações canônicas com `./scripts/verify.sh`.
- Consulte `docker compose ps` e os logs da aplicação quando a inicialização ou as verificações falharem. Quando apropriado, pare somente os serviços iniciados para a tarefa.

## Relatório de conclusão

Informe a mudança, as verificações realmente executadas e seus resultados, além de qualquer blocker restante. Mantenha `.agent/STATE.md` alinhado ao estado real do repositório. Não implemente comportamento de domínio fora do escopo como atalho para preparar o bootstrap.
