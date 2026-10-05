# Roadmap do OVG Benefícios Lab

Este é o registro canônico dos gates do experimento. Atualize-o em gates futuros, preservando o histórico. Um gate planejado não autoriza antecipar sua implementação.

## GATE 0 — Bootstrap do Harness

**Status:** CONSOLIDADO

Criação inicial do harness portátil, da documentação, das policies, dos scripts, da estrutura de Skills e Evals e do runtime provisório.

## GATE 1 — Certificação do Runtime

**Status:** PASS

Baseline certificada: PHP 8.4, Laravel 13, Filament 5, Livewire 4, MySQL 8.4 LTS, Composer 2 e Docker Compose.

O bootstrap e `scripts/verify.sh` passaram. MySQL foi validado com conexão Laravel → MySQL e consulta real; Laravel, Filament e Livewire foram validados. Nenhuma funcionalidade de domínio foi criada. Consulte `docs/TECHNOLOGY_BASELINE.md` e `.agent/STATE.md` para as versões exatas e os resultados registrados.

## GATE 1.1 — Padronização pt-BR e consolidação do Harness

**Status:** PASS

Padronização das instruções e documentação em português do Brasil, inclusão da regra permanente de idioma no `AGENTS.md` e consolidação deste roadmap, sem alterar o runtime certificado. O Gate 1.1 foi aprovado após a verificação integral e a revisão do diff.

## GATE 2 — Contrato de Domínio

**Status:** PASS / FROZEN

O contrato conceitual está documentado em `docs/domain/`: Beneficiário, Benefício, Comprovação, Requisito, Versão de Regra, Regra de Elegibilidade, Parâmetro de Referência, Avaliação e Resultado de Avaliação. A revisão independente final aprovou o freeze; o contrato está congelado. Nenhuma implementação de domínio ocorreu. O Gate 3 é o próximo gate planejado e não foi iniciado nesta tarefa.

## GATE 3 — Primeira Implementação Funcional

**Status:** READY FOR RE-REVIEW

O primeiro fluxo vertical de Cadeira de Rodas está implementado conforme o contrato congelado no Gate 2. Os três blockers da revisão independente — segurança do banco de teste, preservação de `FALHA_TECNICA` e visão inicial consistente — foram corrigidos e cobertos por regressões. `scripts/verify.sh` passou com 39 testes e 113 assertions. Uma nova revisão independente decidirá o PASS. Consulte `docs/implementation/GATE_003.md`.

## Etapas futuras — não autorizadas

As etapas abaixo são intenções futuras e não estão autorizadas por este gate:

- evolução das Skills;
- suíte de Evals do agente para CRUD, mudanças de banco, regras de negócio, regressão, segurança e manutenção;
- Model Routing / Capability-Cost Routing;
- agentes especializados e agentes paralelos;
- integração híbrida Codex + Claude Code e abstração de provedores;
- hardening de rede;
- benchmark de modelos;
- benchmark Scriptcase x Laravel/Filament;
- ambiente local com VS Code + Docker;
- preparação da demonstração e apresentação executiva.

A futura demonstração apresentará **análise de elegibilidade**, com os rótulos “ELEGÍVEL”, “INELEGÍVEL”, “PENDENTE DE DOCUMENTAÇÃO” e “REQUER ANÁLISE HUMANA”. Não tratará esses resultados como aprovação ou reprovação administrativa; Decisão Final humana fica fora do MVP.

## Intenção futura: Model Routing / Capability-Cost Routing

O desenho futuro deverá:

- usar tiers portáveis, evitando espalhar nomes concretos de modelos pelo código, com classes equivalentes a `mechanical`, `standard` e `complex`;
- considerar complexidade, risco, custo, necessidade de raciocínio e impacto arquitetural;
- permitir provedores distintos, incluindo OpenAI e Anthropic;
- preferir roteamento determinístico nos casos evidentes;
- permitir escalation automática após falha de verificação, incerteza, repetição de erro, tarefa de alto risco ou mudança arquitetural importante;
- dar suporte a agentes paralelos;
- registrar futuramente tier, provedor, modelo, tempo, custo, tentativas, retries, escalonamentos, resultado e intervenção humana;
- permitir comparar “modelo forte sempre” com “roteamento inteligente”.

Esta intenção não autoriza criar `models.yaml`, router, SDKs ou lógica de roteamento neste gate.

## Observações do runtime certificado

- No Gate 1, o painel Filament usava autenticação padrão e `/admin` redirecionava ao login. No Gate 3, `/admin` abre diretamente o painel exclusivamente sintético do laboratório, conforme ADR-007.
- As imagens Docker usam linhas de versão como `php:8.4` e `mysql:8.4`, sem necessariamente fixar patch ou digest imutável. Antes do benchmark final, avaliar se versões ou image digests serão congelados para aumentar a reprodutibilidade.
- Duas novas tarefas Codex Cloud falharam antes de qualquer alteração por indisponibilidade do proxy interno na porta 8080. O Gate 1.1 ocorre excepcionalmente no workspace atual, cuja conectividade GitHub foi validada. Essa exceção não invalida o Git nem o harness persistido; a retomada em um novo workspace deverá ser revalidada em gate futuro após a normalização da conectividade Cloud.
