# Contrato do motor de elegibilidade

## Alternativas analisadas

| Alternativa | Vantagens | Riscos e limitações |
| --- | --- | --- |
| Regras hardcoded em PHP por Benefício | Implementação inicial direta, IDE e testes convencionais | Cada ajuste simples exige código e deploy; manutenção por analistas é inviável; versionamento histórico e explicação tendem a ficar dispersos. |
| Engine genérica de expressões arbitrárias | Flexibilidade alta e pouca limitação prévia | Amplia superfície de ataque e complexidade; dificulta validação, auditoria, testes e explicação; aproxima o piloto de uma linguagem de programação. |
| Engine declarativa restrita | Permite ajustar valores e combinações sem alterar PHP; valida antes de publicar; é segura, versionável, testável e explicável | Exige catálogo fechado e interface de edição cuidadosa; casos fora do vocabulário precisam de evolução explícita do produto. |

## Estratégia escolhida

O MVP usará **engine declarativa restrita com Requisitos tipados, operadores fechados e grupos `AND`/`OR`**. É a alternativa compatível com manutenção controlada por analistas e com o limite de dez telas, sem introduzir uma linguagem arbitrária.

São explicitamente proibidos:

- PHP ou qualquer código executável armazenado no banco;
- `eval()`;
- SQL arbitrário armazenado no banco;
- expressões livres interpretadas dinamicamente;
- scripts fornecidos por usuário;
- chamadas dinâmicas a classes, métodos ou paths informados na regra;
- uma linguagem Turing-complete criada para o piloto.

O código da aplicação implementará resolvedores previamente registrados para cada Requisito e avaliará somente a estrutura declarativa validada.

## Forma declarativa

Uma Regra de Elegibilidade é exatamente um destes nós:

1. **Condição:** `requisito + operador + operando`;
2. **Grupo:** operador lógico `AND` ou `OR` e uma lista ordenada de dois ou mais nós filhos.

O operando de uma Condição é um literal tipado ou uma referência controlada a Parâmetro. Ele nunca é uma expressão. Cada nó recebe um identificador estável dentro da Versão de Regra para que resultados históricos possam apontar ao mesmo nó.

Restrições do MVP:

- no máximo três níveis de grupos, contando o grupo raiz;
- no máximo 30 Condições por Versão de Regra;
- grupos vazios ou com apenas um filho são inválidos;
- ciclos e referências entre árvores são inválidos;
- todos os nós devem ser alcançáveis a partir de uma única raiz.

Três níveis acomodam os casos do laboratório, inclusive `A OR (B AND C)`, sem permitir árvores difíceis de revisar. Alterar esses limites exige decisão arquitetural posterior.

## Tipos de dados fechados

| Tipo | Uso |
| --- | --- |
| `BOOLEAN` | Fatos `true`/`false`, como deficiência ou vulnerabilidade. |
| `INTEGER` | Idade, integrantes, dias e meses derivados. |
| `DECIMAL` | Renda e parâmetros monetários. |
| `DATE` | Datas quando uma comparação cronológica direta for necessária. |
| `ENUM` | Valores de um conjunto fechado, como UF ou estado categórico. |
| `DOCUMENT_PRESENCE` | Presença efetiva de uma Comprovação na data da avaliação. |

`null` não é um tipo nem equivale a `false`. Fato obrigatório ausente ou inconsistente produz necessidade de análise; ausência documental tem semântica própria.

## Operadores e compatibilidade

| Tipo | Operadores permitidos |
| --- | --- |
| `BOOLEAN` | `EQ`, `NEQ` |
| `INTEGER` | `EQ`, `NEQ`, `GT`, `GTE`, `LT`, `LTE` |
| `DECIMAL` | `EQ`, `NEQ`, `GT`, `GTE`, `LT`, `LTE` |
| `DATE` | `EQ`, `NEQ`, `GT`, `GTE`, `LT`, `LTE`, todos com comparação cronológica explícita |
| `ENUM` | `EQ`, `NEQ` |
| `DOCUMENT_PRESENCE` | `PRESENT`, `NOT_PRESENT` |

`IN` fica fora do MVP: os cinco exemplos não o exigem e alternativas podem ser expressas por um grupo `OR` pequeno e visível. Não há coerção implícita entre tipos. Valor, requisito, operador e parâmetro devem ser compatíveis para uma versão ser publicada.

## Catálogo mínimo de Requisitos

| Código | Tipo | Fonte controlada |
| --- | --- | --- |
| `IDADE_ANOS` | `INTEGER` | Derivado de data de nascimento e instante da avaliação. |
| `RENDA_PER_CAPITA` | `DECIMAL` | Derivado de renda familiar e integrantes. |
| `VULNERABILIDADE_SOCIAL` | `BOOLEAN` | Beneficiário. |
| `DEFICIENCIA` | `BOOLEAN` | Beneficiário. |
| `LIMITACAO_MOBILIDADE` | `BOOLEAN` | Beneficiário. |
| `GESTANTE` | `BOOLEAN` | Beneficiário. |
| `MES_GESTACIONAL` | `INTEGER` | Derivado da data provável do parto pela convenção do laboratório. |
| `DIAS_APOS_NASCIMENTO_BEBE` | `INTEGER` | Derivado da data de nascimento do bebê. |
| `AUTONOMIA_FUNCIONAL` | `BOOLEAN` | Beneficiário. |
| `LAUDO_MEDICO` | `DOCUMENT_PRESENCE` | Comprovação. |
| `RELATORIO_PROFISSIONAL` | `DOCUMENT_PRESENCE` | Comprovação. |
| `CARTAO_GESTANTE` | `DOCUMENT_PRESENCE` | Comprovação. |
| `ULTRASSONOGRAFIA` | `DOCUMENT_PRESENCE` | Comprovação. |

Outros tipos de Comprovação podem existir no cadastro sem participar das cinco regras iniciais. Adicionar Requisito exige resolvedor conhecido, tipo, rótulo e testes em gate de implementação; não autoriza consulta arbitrária.

## Parâmetros de Referência

Parâmetros entram no MVP para fatos globais versionáveis. A primeira necessidade é `SALARIO_MINIMO`, do tipo `DECIMAL`.

Cada valor de Parâmetro possui:

- código estável;
- tipo;
- valor tipado;
- início e fim opcional de vigência;
- estado e histórico de publicação;
- identificação imutável da versão.

Parâmetros podem usar `BOOLEAN`, `INTEGER`, `DECIMAL`, `DATE` ou `ENUM`. `DOCUMENT_PRESENCE` não aceita Parâmetro, pois sua presença sempre vem de uma Comprovação do Beneficiário.

Ao iniciar uma Avaliação, o motor resolve o valor vigente no instante de referência. A versão e o valor resolvidos entram no snapshot. Não pode haver sobreposição de vigências publicadas para o mesmo código. Ausência ou ambiguidade de parâmetro impede concluir automaticamente e gera erro controlado de configuração, sem executar a regra com valor presumido.

## Validação antes da publicação

Uma Versão de Regra só pode ser publicada quando:

- árvore, profundidade e quantidade de Condições forem válidas;
- cada Requisito estiver ativo e tiver resolvedor conhecido;
- operador e operandos forem compatíveis com o tipo;
- cada referência de Parâmetro existir, tiver tipo compatível e puder ser resolvida para a vigência planejada;
- todos os rótulos necessários à explicação estiverem presentes;
- a vigência não conflitar com outra versão publicada do mesmo Benefício;
- não houver código, SQL ou expressão livre em nenhum campo.

## Resultados de Condições

Cada Condição produz um dos desfechos intermediários:

- `ATENDIDA`: a comparação é verdadeira;
- `NAO_ATENDIDA`: a comparação é falsa;
- `DOCUMENTACAO_PENDENTE`: uma Comprovação exigida não está efetivamente presente;
- `INDETERMINADA`: fato não documental ausente, inconsistente ou não resolvível.

O resultado registra o valor observado, o operando esperado, eventual Parâmetro resolvido e uma mensagem baseada em template conhecido. A mensagem não é gerada por IA.

Para `DOCUMENT_PRESENCE`, `PRESENT` com estado efetivo ausente ou vencido produz `DOCUMENTACAO_PENDENTE`; com presença válida, produz `ATENDIDA`. `NOT_PRESENT` inverte a comparação: ausência efetiva produz `ATENDIDA` e presença válida produz `NAO_ATENDIDA`. Estado documental inconsistente ou desconhecido produz `INDETERMINADA`.

## Semântica de grupos e precedência

Os grupos usam lógica determinística com estados intermediários:

### Grupo `AND`

1. se algum filho for `NAO_ATENDIDA`, o grupo é `NAO_ATENDIDA`;
2. caso contrário, se algum filho for `INDETERMINADA`, o grupo é `INDETERMINADA`;
3. caso contrário, se algum filho for `DOCUMENTACAO_PENDENTE`, o grupo é `DOCUMENTACAO_PENDENTE`;
4. caso contrário, todos são `ATENDIDA` e o grupo é `ATENDIDA`.

### Grupo `OR`

1. se algum filho for `ATENDIDA`, o grupo é `ATENDIDA`;
2. caso contrário, se algum filho for `INDETERMINADA`, o grupo é `INDETERMINADA`;
3. caso contrário, se algum filho for `DOCUMENTACAO_PENDENTE`, o grupo é `DOCUMENTACAO_PENDENTE`;
4. caso contrário, todos são `NAO_ATENDIDA` e o grupo é `NAO_ATENDIDA`.

Essa ordem considera primeiro um resultado logicamente decisivo. Por exemplo, um documento ausente não torna pendente um `AND` que já falhou em outro critério; em um `OR`, um ramo atendido torna o grupo atendido mesmo que outro ramo esteja pendente.

## Resultado automático consolidado

O desfecho da raiz é mapeado assim:

| Desfecho da raiz | Resultado automático |
| --- | --- |
| `ATENDIDA` | `ELEGIVEL` |
| `NAO_ATENDIDA` | `INELEGIVEL` |
| `DOCUMENTACAO_PENDENTE` | `PENDENTE_DOCUMENTACAO` |
| `INDETERMINADA` | `REQUER_ANALISE_HUMANA` |

`NAO_APLICAVEL` não entra no MVP. Benefício inativo, ausência de versão vigente ou entrada estruturalmente inválida impedem criar/concluir uma Avaliação; não são resultados de elegibilidade. Critérios de aplicabilidade pertencem à própria regra e, quando não atendidos, levam a `INELEGIVEL`.

O resultado automático é uma análise técnica do laboratório. Ele nunca representa concessão, negativa oficial ou autoridade final. Uma futura Decisão Final humana será preservada separadamente.
