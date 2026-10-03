# Critérios de aceite do contrato

## Checklist do Gate 2

- [x] O domínio mínimo e as responsabilidades estão definidos.
- [x] Beneficiário e seus dados persistidos/derivados estão definidos.
- [x] Benefício e seu ciclo ativo/inativo estão definidos.
- [x] Comprovação sem upload está definida.
- [x] Requisito e sua diferença para Regra de Elegibilidade estão definidos.
- [x] Versão de Regra e Regra de Elegibilidade estão definidas.
- [x] Avaliação e Resultado de Avaliação estão definidos.
- [x] Resultado automático e futura Decisão Final humana estão separados.
- [x] Estados automáticos e sua precedência estão definidos.
- [x] Grupos `AND`/`OR`, profundidade e tamanho máximos estão definidos.
- [x] Tipos e operadores permitidos têm conjuntos fechados e compatibilidade explícita.
- [x] Código, SQL, scripts e expressões arbitrárias estão proibidos.
- [x] Parâmetro de Referência entra no MVP com vigência e histórico.
- [x] Versionamento imutável de regras está definido.
- [x] Snapshot histórico híbrido e explicabilidade estão definidos.
- [x] Invariantes, retenção e auditoria mínimas estão definidas.
- [x] Optimistic locking foi rejeitado para este MVP com justificativa.
- [x] Os cinco exemplos fictícios são representáveis pelo contrato.
- [x] O domínio cabe em nove telas futuras, abaixo do limite de dez.
- [x] Limites e exclusões do MVP estão explícitos.
- [x] Nenhuma implementação de domínio faz parte deste gate.
- [x] O servidor captura uma única vez o instante corrente da Avaliação em UTC, sem retroatividade ou agendamento; cálculos de calendário usam `America/Sao_Paulo` e reavaliação cria nova execução.
- [x] Regra, Parâmetros, entradas e validade documental são selecionados consistentemente pelo mesmo instante imutável.
- [x] Vigências usam `[início, fim)` e versões publicadas futuras não são agendadas.
- [x] Publicação/substituição imediata encerram a versão anterior e iniciam a sucessora atomicamente no mesmo `T`.
- [x] Conteúdo publicado imutável está distinguido de metadados controlados de ciclo de vida.
- [x] O cadastro representa um episódio gestacional coerente, com DPP válida e encerramento da gestação ao ocorrer nascimento.
- [x] `NASCIMENTO_BEBE_OCORRIDO` distingue `true`, `false` e desconhecido; a ausência legítima da data não produz análise humana por si só.
- [x] Comprovações têm apresentação não futura, validade não anterior à apresentação, limite inclusivo e `VENCIDA` derivado.
- [x] Valores monetários em centavos e comparação exata impedem arredondamento de apresentação de alterar elegibilidade.
- [x] Uma Versão de Regra fixa a versão semântica do Requisito/resolvedor; o snapshot a registra.
- [x] O identificador sintético local não usa formato de CPF potencialmente real.
- [x] A demonstração apresenta somente resultados de elegibilidade, sem chamar os estados de aprovação/reprovação.
- [x] Exemplos do Kit Enxoval e do centro de idosos refletem os fatos e limites corretos.
- [x] Relações condicionais de Condições, Grupos, Parâmetros e Resultados estão documentadas sem cardinalidades enganosas.

## Decisões propostas para congelamento

O Gate 2 está pronto para revisão e congelamento com estas decisões deliberadas:

1. engine declarativa restrita, sem linguagem de expressão genérica;
2. Requisitos em catálogo com resolvedores controlados;
3. `IN` fora do MVP;
4. no máximo três níveis de grupos e 30 Condições por versão;
5. `NAO_APLICAVEL` fora dos estados automáticos do MVP;
6. Parâmetro de Referência incluído, começando por `SALARIO_MINIMO`;
7. conteúdo de versões publicadas e Avaliações concluídas imutáveis, com metadados controlados de ciclo;
8. snapshot híbrido: relações + JSON controlado + resultados por nó;
9. Decisão Final humana fora do MVP e sempre separada do resultado automático;
10. optimistic locking fora do MVP; publicação protegida por transação e restrições;
11. convenção gestacional sintética e determinística descrita em `MVP_EXAMPLES.md`;
12. instante capturado pelo servidor em UTC, com calendário `America/Sao_Paulo` e publicação imediata;
13. identificador sintético local fora do formato CPF;
14. demonstração restrita a elegibilidade técnica, sem aprovação ou reprovação administrativa.

Esses pontos não bloqueiam a consistência do contrato, mas devem ser aceitos explicitamente ao congelar o Gate 2. Qualquer mudança posterior deve atualizar o contrato e as ADRs antes da implementação correspondente.

## Evidências documentais

| Tema | Documento principal |
| --- | --- |
| Entidades, dados, relações, exclusão e telas | [DOMAIN_MODEL.md](DOMAIN_MODEL.md) |
| Estratégia da engine, tipos, operadores, grupos, estados e parâmetros | [ELIGIBILITY_ENGINE.md](ELIGIBILITY_ENGINE.md) |
| Versões, snapshot, explicabilidade, invariantes e auditoria | [EVALUATION_HISTORY.md](EVALUATION_HISTORY.md) |
| Cinco regras fictícias e alteração versionada | [MVP_EXAMPLES.md](MVP_EXAMPLES.md) |

## Verificações obrigatórias antes do commit

- [x] `./scripts/verify.sh` passa integralmente.
- [x] `git diff --check` passa.
- [x] O diff não altera migrations, Models, Resources, Services, runtime ou baseline.
- [x] `docs/ROADMAP.md` e `.agent/STATE.md` registram o resultado real.
