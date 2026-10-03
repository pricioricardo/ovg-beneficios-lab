# Gate 3 — primeiro fluxo vertical

**Situação:** READY FOR REVIEW; `scripts/verify.sh` passou com 31 testes e 92 assertions. A revisão independente ainda não ocorreu.

## Escopo implementado

Somente o Benefício fictício `CADEIRA_RODAS`, com a Versão de Regra v1 publicada: `VULNERABILIDADE_SOCIAL EQ true AND LIMITACAO_MOBILIDADE EQ true AND RELATORIO_PROFISSIONAL PRESENT`. O seeder idempotente cria três Beneficiários `LAB-...`: elegível, pendente de documentação e inelegível. Nenhum CPF, dado real ou arquivo é usado.

## Schema físico

Cinco migrations aditivas criam `beneficiarios`, `comprovacoes`, `beneficios`, `requisitos`, `versoes_regra`, `regras_elegibilidade`, `avaliacoes` e `resultados_avaliacao`. Beneficiários guardam fatos cadastrais sintéticos, renda em centavos e estado. Comprovações são únicas por Beneficiário/tipo e só persistem `AUSENTE` ou `APRESENTADA`; `VENCIDA` é derivada. Requisitos usam código/versão semântica; a árvore referencia cada Requisito e preserva a versão no resultado. FKs compostas ligam cada nó ao pai da mesma Versão e cada Avaliação ao Benefício da Versão selecionada. Avaliações guardam snapshot JSON versionado; resultados por nó são relacionais.

## Classes e avaliação

Os oito Models correspondem às tabelas de domínio. `ResolvedorRequisitos` usa catálogo fechado de três códigos `v1`; `ValidadorRegra` exige raiz única, grupos de dois ou mais filhos, profundidade de até três grupos, até 30 condições e operandos compatíveis. `MotorElegibilidade` avalia todos os nós, sem short circuit, e aplica a precedência contratada de `AND` e `OR`. `ExecutarAvaliacao` captura uma vez o instante UTC com precisão de segundos e executa em transação: bloqueia Beneficiário, Benefício, Versão publicada vigente e Comprovações, resolve fatos, grava Avaliação, snapshot, quatro resultados por nó e conclusão. Exceções revertem a transação.

Observers impedem editar conteúdo publicado, alterar semântica de Requisito e editar ou aumentar resultados de Avaliação concluída. A conclusão exige snapshot e um resultado por nó. O histórico é exibido pelos resultados e snapshot preservados; não consulta o cadastro atual para reconstruir o resultado anterior. O snapshot v1 contém identificador sintético, fatos consultados, estado e datas do relatório, versões semânticas, Versão de Regra, instante e valores resolvidos.

## Interface

O recurso de Beneficiários lista, cria, edita e visualiza cadastro sintético. Na visualização, seções de Comprovações e Avaliações permitem registrar o Relatório Profissional, executar a avaliação e abrir a página do resultado. Essa página apresenta Cadeira de Rodas, regra v1, cada critério, resultado automático e aviso de que não há decisão administrativa. `/admin` abre sem login somente neste laboratório, conforme ADR-007.

## Limites e próximos incrementos

Não há editor/publicação de regras na UI, parâmetros monetários, demais benefícios, upload, IA, decisão humana, API, RBAC ou integração externa. A extensão a outros tipos (`INTEGER`, `DECIMAL`, `DATE`, `ENUM`) exige novos resolvedores, validadores e testes sem alterar o mecanismo de árvore. Uma revisão independente deve avaliar este gate antes de qualquer expansão.
