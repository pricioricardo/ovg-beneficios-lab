# Versionamento, histórico e explicabilidade

## Ciclo de vida da Versão de Regra

Uma Versão de Regra usa os estados:

- `RASCUNHO`: editável, não utilizável em Avaliações;
- `PUBLICADA`: validada, imutável e utilizável durante sua vigência;
- `SUBSTITUIDA`: foi publicada e permanece imutável para explicar o histórico, mas uma versão posterior assumiu a vigência;
- `INATIVA`: publicada, encerrada sem substituição e indisponível para novas Avaliações.

### Criação e publicação

1. Uma nova versão nasce em `RASCUNHO`, copiando opcionalmente uma versão anterior.
2. Enquanto rascunho, sua árvore e metadados podem ser alterados ou excluídos.
3. A publicação executa toda a validação do contrato, atribui número sequencial, autor/data de publicação e início de vigência.
4. Publicação torna estrutura, textos explicativos, referências e início de vigência imutáveis.
5. No instante da publicação não pode existir conflito de vigência com outra versão do mesmo Benefício.

### Substituição e encerramento

- Mudança de critério cria novo `RASCUNHO`; nunca edita a versão publicada.
- Ao publicar a sucessora, a versão anterior recebe uma única data de encerramento e estado `SUBSTITUIDA` de forma transacional. Esse fechamento de ciclo não altera seu conteúdo nem o intervalo usado por Avaliações anteriores.
- Uma versão pode ser encerrada como `INATIVA` sem sucessora, preservando todas as Avaliações.
- Correção de erro histórico não reescreve Avaliação nem regra; cria nova versão e, se necessário, uma anotação de auditoria futura.

Uma Avaliação iniciada deve referenciar a versão que estava `PUBLICADA` e vigente em seu instante de referência. A versão pode aparecer depois como `SUBSTITUIDA` ou `INATIVA`, sem afetar o resultado preservado.

## Parâmetros versionados

Valores de Parâmetro seguem o mesmo princípio temporal: código lógico estável, versões com vigência não sobreposta e imutabilidade após uso. Uma alteração em `SALARIO_MINIMO` cria novo valor vigente; não modifica o valor usado anteriormente.

## Snapshot histórico escolhido

O MVP usará uma **estratégia híbrida**:

1. relações normalizadas apontam para Beneficiário, Benefício, Versão de Regra e versões de Parâmetro;
2. um snapshot JSON controlado e versionado preserva as entradas efetivamente observadas e derivadas;
3. Resultados de Avaliação relacionais preservam cada nó avaliado, seu desfecho e explicação.

O JSON não substitui a modelagem relacional. Ele é um envelope histórico com schema conhecido e validado, apropriado para fatos heterogêneos de uma execução. Campos essenciais para identidade, consulta e integridade continuam relacionais.

### Conteúdo mínimo da Avaliação

- identificadores do Beneficiário e Benefício;
- identificador imutável e número da Versão de Regra;
- instante de referência e timestamps de início/conclusão;
- versão do schema do snapshot;
- snapshot dos valores persistidos relevantes;
- valores derivados usados, incluindo fórmula/versão do resolvedor quando aplicável;
- versões e valores dos Parâmetros resolvidos;
- estados efetivos das Comprovações consultadas;
- resultado automático consolidado;
- estado de processamento: `INICIADA`, `CONCLUIDA` ou `FALHA_TECNICA`.

Uma `FALHA_TECNICA` não é `INELEGIVEL` nem `REQUER_ANALISE_HUMANA`; indica que a execução não produziu resultado válido e pode ser repetida como nova tentativa auditada.

### Conteúdo mínimo de cada Resultado de Avaliação

- identificador e caminho estável do nó na árvore;
- tipo do nó: Condição, `AND` ou `OR`;
- código e rótulo do Requisito, quando Condição;
- tipo e operador;
- valor observado;
- valor esperado literal ou referência, versão e valor do Parâmetro;
- desfecho intermediário;
- texto explicativo determinístico;
- ordem de apresentação.

Os resultados de grupos são preservados junto aos resultados de Condições para reconstruir a árvore e explicar por que a precedência chegou ao resultado final.

## Reprodutibilidade

Uma Avaliação concluída é explicada usando sua Versão de Regra, snapshot e Resultados de Avaliação, sem reler os valores atuais do Beneficiário. Assim:

- alteração de renda não muda a renda usada no passado;
- aniversário posterior não muda a idade calculada;
- vencimento posterior de documento não altera sua presença na data avaliada;
- nova versão de regra não substitui a anterior;
- novo valor de Parâmetro não altera o valor resolvido.

Reexecutar hoje os dados atuais cria uma nova Avaliação. Não recalcula nem sobrescreve a antiga.

## Explicabilidade

A resposta a “por que este Beneficiário recebeu este resultado?” é montada a partir da árvore e dos resultados preservados. Deve apresentar:

- Benefício e versão aplicada;
- data de referência;
- critérios atendidos, não atendidos, pendentes e indeterminados;
- valores observados e limites esperados em linguagem clara;
- Parâmetros e vigências usados;
- resultado automático e aviso de que ele não é decisão administrativa.

Exemplo de formato:

```text
CADEIRA DE RODAS — regra v1
✓ Vulnerabilidade social: informada
✓ Limitação de mobilidade: informada
⚠ Relatório profissional: ausente
Resultado automático: PENDENTE_DOCUMENTACAO
```

## Invariantes

1. Uma Avaliação referencia exatamente um Beneficiário, um Benefício e uma Versão de Regra.
2. Ao iniciar, a Versão de Regra deve estar publicada e vigente e o Benefício deve estar ativo.
3. Versões publicadas ou usadas por Avaliação são imutáveis; alterações geram nova versão.
4. Vigências publicadas do mesmo Benefício não se sobrepõem.
5. Requisito, operador, literal e Parâmetro devem ter tipos compatíveis antes da publicação.
6. Uma Avaliação concluída e seus Resultados de Avaliação são imutáveis.
7. Toda entrada, valor derivado, Comprovação e Parâmetro que influencie um resultado deve ser rastreável no snapshot.
8. Grupos respeitam profundidade máxima de três níveis e no máximo 30 Condições.
9. Regra alguma pode conter ou invocar código, SQL, script, expressão livre ou identificador técnico arbitrário.
10. Resultado automático e futura Decisão Final humana nunca compartilham o mesmo campo nem sobrescrevem um ao outro.

## Auditoria e exclusão

- Rascunhos não publicados podem ser excluídos.
- Versões publicadas, versões de Parâmetro utilizadas e Avaliações concluídas são preservadas.
- Benefícios, Requisitos, Parâmetros e Beneficiários com histórico são inativados.
- Mudanças de estado registram data e, quando disponível, ator.
- O MVP não adota trilha genérica de eventos nem event sourcing; registros imutáveis e metadados explícitos atendem ao experimento.

## Concorrência

Optimistic locking não será adotado neste MVP. A publicação futura deverá usar transação, verificação do estado esperado e restrições de unicidade/vigência. Isso protege a decisão crítica sem espalhar complexidade de concorrência por cadastros de baixo volume.
