# Gate 3 — primeiro fluxo vertical

**Situação:** READY FOR RE-REVIEW após correção dos três blockers da revisão independente. `scripts/verify.sh` passou com 39 testes e 113 assertions; o PASS depende de nova revisão.

## Escopo implementado

Somente o Benefício fictício `CADEIRA_RODAS`, com a Versão de Regra v1 publicada: `VULNERABILIDADE_SOCIAL EQ true AND LIMITACAO_MOBILIDADE EQ true AND RELATORIO_PROFISSIONAL PRESENT`. O seeder idempotente cria três Beneficiários `LAB-...`: elegível, pendente de documentação e inelegível. Nenhum CPF, dado real ou arquivo é usado.

## Schema físico

Cinco migrations aditivas criam `beneficiarios`, `comprovacoes`, `beneficios`, `requisitos`, `versoes_regra`, `regras_elegibilidade`, `avaliacoes` e `resultados_avaliacao`. Beneficiários guardam fatos cadastrais sintéticos, renda em centavos e estado. Comprovações são únicas por Beneficiário/tipo e só persistem `AUSENTE` ou `APRESENTADA`; `VENCIDA` é derivada. Requisitos usam código/versão semântica; a árvore referencia cada Requisito e preserva a versão no resultado. FKs compostas ligam cada nó ao pai da mesma Versão e cada Avaliação ao Benefício da Versão selecionada. Avaliações guardam snapshot JSON versionado; resultados por nó são relacionais.

## Classes e avaliação

Os oito Models correspondem às tabelas de domínio. `ResolvedorRequisitos` usa catálogo fechado de três códigos `v1`; `ValidadorRegra` exige raiz única, grupos de dois ou mais filhos, profundidade de até três grupos, até 30 condições e operandos compatíveis. `MotorElegibilidade` avalia todos os nós, sem short circuit, e aplica a precedência contratada de `AND` e `OR`. `ExecutarAvaliacao` captura uma vez o instante UTC com precisão de segundos. A primeira transação exige MySQL `REPEATABLE READ` e lê Beneficiário, Benefício, Versão, árvore, Requisitos e Comprovações por leituras consistentes, sem misturar `lockForUpdate`. A primeira leitura de tabela captura o instante UTC do MySQL e estabelece a visão consistente simultaneamente; os valores capturados alimentam o motor, mesmo se outra conexão editar o cadastro depois. A transação grava a tentativa `INICIADA`. Uma segunda transação persiste os quatro resultados e a conclusão; falha nessa fase reverte resultados parciais e deixa a tentativa em `FALHA_TECNICA`, com resultado automático nulo. O erro é propagado e um log registra somente id da Avaliação e tipo de exceção.

Observers impedem editar conteúdo publicado, alterar semântica de Requisito e editar ou aumentar resultados de Avaliação concluída. A conclusão exige snapshot e um resultado por nó. O histórico é exibido pelos resultados e snapshot preservados; não consulta o cadastro atual para reconstruir o resultado anterior. O snapshot v1 contém identificador sintético, fatos consultados, estado e datas do relatório, versões semânticas, Versão de Regra, instante e valores resolvidos.

## Correções da revisão independente

`TestDatabaseGuard` recusa ambiente diferente de `testing`, driver diferente de MySQL, `DB_URL` ativa, nome configurado diferente do banco descartável e divergência com `SELECT DATABASE()` da conexão efetiva. `SafeRefreshDatabase` invoca a guarda antes do reset do trait. `scripts/verify.sh` faz uma pré-verificação; `scripts/reset-test-db.sh` é o caminho seguro para `migrate:fresh --seed` manual, com guarda repetida no mesmo processo do reset. O banco padrão do Compose não pode ter o nome do banco descartável. Config cache incompatível causa recusa antes do reset.

As regressões acrescentadas cobrem configuração conflitante, falha injetada na segunda gravação de Resultado e duas conexões MySQL intercaladas entre o início da Avaliação e a leitura do Relatório Profissional. O relatório ausente no início produz `PENDENTE_DOCUMENTACAO`; após a edição confirmada, uma nova Avaliação pode produzir `ELEGIVEL`.

`scripts/reset-test-db.sh` executou `migrate:fresh --seed` no banco descartável; uma segunda execução do seeder passou. Testes adversariais sem reset recusaram `DB_URL` conflitante e cache de configuração do banco normal. A falha sintética preservou uma Avaliação `FALHA_TECNICA` com resultado automático nulo e zero Resultados. `scripts/verify.sh` confirmou Compose, baseline, migrations, MySQL real, suíte, `/up`, `/admin` e `git diff --check`.

Permanece como melhoria futura tornar o seeder recuperável após interrupção no meio da criação do rascunho. Atualizações diretas por Query Builder continuam fora das garantias dos Observers.

## Interface

O recurso de Beneficiários lista, cria, edita e visualiza cadastro sintético. Na visualização, seções de Comprovações e Avaliações permitem registrar o Relatório Profissional, executar a avaliação e abrir a página do resultado. Essa página apresenta Cadeira de Rodas, regra v1, cada critério, resultado automático e aviso de que não há decisão administrativa. `/admin` abre sem login somente neste laboratório, conforme ADR-007.

## Limites e próximos incrementos

Não há editor/publicação de regras na UI, parâmetros monetários, demais benefícios, upload, IA, decisão humana, API, RBAC ou integração externa. A extensão a outros tipos (`INTEGER`, `DECIMAL`, `DATE`, `ENUM`) exige novos resolvedores, validadores e testes sem alterar o mecanismo de árvore. Uma revisão independente deve avaliar este gate antes de qualquer expansão.
