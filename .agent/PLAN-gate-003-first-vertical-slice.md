# ExecPlan — Gate 3: primeiro fluxo vertical

## Objetivo e escopo

Implementar o fluxo sintético de Cadeira de Rodas do contrato congelado no Gate 2: cadastro, comprovação, regra v1, avaliação, snapshot, resultados por nó e explicação no Filament. Excluir os demais benefícios, parâmetros, publicação pela UI, decisão administrativa, uploads, IA e integrações.

## Estado e fontes

Base `origin/work` em `16b563184fbbae94b419a072168d6ebf971c3624`; branch `feature/gate-003-first-vertical-slice`. Fontes normativas: `docs/domain/` e ADR-004 a ADR-006 em `docs/ARCHITECTURE.md`. Runtime: Laravel 13, Filament 5, Livewire 4 e MySQL 8.4.

## Schema e ordem das migrations

1. `beneficiarios`: identificador `LAB-...` único, fatos cadastrais do contrato, renda em centavos, integrantes positivos, estados controlados.
2. `comprovacoes`: FK de Beneficiário, tipo e estado controlados, apresentação/validade; unicidade por Beneficiário/tipo.
3. `beneficios` e `requisitos`: códigos estáveis; Requisito único por código/versão semântica, com tipo e fonte controlados.
4. `versoes_regra` e `regras_elegibilidade`: sequência única por Benefício, vigência UTC, árvore normalizada; FK composta mantém pai e filho na mesma versão.
5. `avaliacoes` e `resultados_avaliacao`: FK composta assegura que a Versão pertence ao Benefício; snapshot JSON versionado, resultado consolidado e uma linha por nó.

Checks e FKs cobrem invariantes simples. Validação de árvore, tipos, operadores, datas e transições fica no domínio. Migrations consolidadas anteriores permanecem intactas.

## Invariantes e estratégia transacional

Capturar um único `now()` em UTC; derivar a data civil em `America/Sao_Paulo`. Uma transação bloqueia Beneficiário, Benefício, Versão vigente e Comprovações consultadas, resolve fatos e grava Avaliação, snapshot, todos os resultados e conclusão. Exceção reverte tudo, sem Avaliação aparentemente concluída. Cada nó é avaliado, mesmo depois de um resultado decisivo. Versões publicadas, nós dessas versões e Avaliações concluídas são protegidos contra edição pelo fluxo de domínio. A engine aceita somente catálogo de requisitos e combinações tipadas conhecidos no código.

## Classes, telas e testes previstos

- Models: Beneficiario, Comprovacao, Beneficio, Requisito, VersaoRegra, RegraElegibilidade, Avaliacao e ResultadoAvaliacao.
- Enums pequenos para estados, tipos, operadores e desfechos; serviços para validação da árvore, resolução fechada dos fatos e avaliação.
- Filament: lista/criação/edição/visualização de Beneficiários, ação de avaliar, histórico e detalhe explicável; acesso direto a `/admin` exclusivo do laboratório.
- Testes em MySQL isolado: os três cenários, validade inclusiva/vencida, precedência AND/OR, bloqueios de estado, imutabilidade, snapshot/histórico, rejeição de combinações e payloads arbitrários, constraints e fluxo HTTP/Filament.

## Validação e aceite

Executar `migrate:fresh --seed` somente no banco de teste, repetir seed, `./scripts/verify.sh`, `git diff --check`, smoke HTTP `/up` e `/admin`. O gate pode chegar a `READY FOR REVIEW` apenas com testes, migração limpa, dados sintéticos, histórico completo e explicação visível; a revisão independente decidirá PASS.

## Riscos e recuperação

Separar explicitamente o banco de teste impede apagar o banco persistente de desenvolvimento. Falha de constraint, API do Filament ou migração exige corrigir no branch e repetir a validação. Não atualizar versões da baseline nem reabrir o contrato congelado. Descartar o banco de teste e reconstruí-lo com migrations/seeders é o caminho de recuperação.

## Progresso

- [x] Base e working tree confirmados; contrato e policies lidos; branch criada.
- [x] Schema, models e seeders; `migrate:fresh --seed` e segunda execução do seeder passaram no banco MySQL de teste.
- [x] Engine e fluxo transacional; histórico, imutabilidade e catálogo fechado cobertos por testes.
- [x] Interface Filament; `/admin`, cadastro, comprovação, ação de avaliar e explicação cobertos por testes.
- [x] Documentação e `./scripts/verify.sh`: 31 testes e 92 assertions passaram; Gate 3 ficou `READY FOR REVIEW`, sem PASS antecipado.
