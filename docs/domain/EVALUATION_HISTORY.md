# Versionamento, histórico e explicabilidade

## Instante de referência da Avaliação

No MVP, o usuário não escolhe a data. O servidor captura **uma única vez** o instante corrente ao iniciar cada Avaliação, persiste o timestamp em UTC e o mantém imutável até a conclusão. Não há Avaliações retroativas ou futuras. Cálculos de calendário do laboratório, como idade e dias após nascimento, interpretam esse instante em `America/Sao_Paulo`.

Esse mesmo instante seleciona a Versão de Regra e as versões dos Parâmetros vigentes, determina a validade das Comprovações e alimenta todos os fatos derivados dependentes de data. A resolução da regra, dos parâmetros e das entradas necessárias ocorre em uma visão consistente no início da execução, inclusive quando regra e parâmetro são publicados simultaneamente. Seus identificadores e valores ficam fixos para essa Avaliação mesmo se uma publicação ou edição cadastral ocorrer durante o processamento. Reavaliar cria outra Avaliação com outro instante; mudanças posteriores nunca alteram a anterior.

## Ciclo de vida da Versão de Regra

Uma Versão de Regra usa os estados:

- `RASCUNHO`: editável, não utilizável em Avaliações;
- `PUBLICADA`: validada, com conteúdo imutável e utilizável durante sua vigência;
- `SUBSTITUIDA`: foi publicada e mantém conteúdo imutável para explicar o histórico, mas uma versão posterior assumiu a vigência;
- `INATIVA`: publicada, encerrada sem substituição e indisponível para novas Avaliações.

### Criação e publicação

1. Uma nova versão nasce em `RASCUNHO`, copiando opcionalmente uma versão anterior.
2. Enquanto rascunho, sua árvore e metadados podem ser alterados ou excluídos.
3. A publicação executa toda a validação do contrato, atribui número sequencial e timestamp de publicação e inicia a vigência **imediatamente**. Não há agendamento de versões publicadas no MVP; uma versão destinada ao futuro permanece `RASCUNHO` até sua publicação. O ator é opcional até haver identidade confiável.
4. A publicação congela árvore, condições, operadores, operandos, versões semânticas dos Requisitos, textos explicativos relevantes, referências e início de vigência.
5. No instante da publicação não pode existir conflito de vigência com outra versão publicada do mesmo Benefício.

### Substituição e encerramento

- Mudança de critério cria novo `RASCUNHO`; nunca edita a versão publicada.
- Ao publicar a sucessora, o servidor captura um único instante `T`. A versão anterior termina em `T` e passa a `SUBSTITUIDA`; a sucessora começa em `T` e passa a `PUBLICADA`, tudo na mesma transação. Não há intervalo sem versão nem sobreposição.
- Uma versão pode ser encerrada imediatamente como `INATIVA` sem sucessora, preservando todas as Avaliações já iniciadas.
- Correção de erro histórico não reescreve Avaliação nem regra; cria nova versão e, se necessário, uma anotação de auditoria futura.

Vigências são intervalos **`[início, fim)`**: a versão é vigente se `início <= instante` e (`fim` é nulo ou `instante < fim`). O mesmo limite rege os Parâmetros. Uma Avaliação iniciada seleciona a versão `PUBLICADA` e vigente no seu instante de referência e mantém essa referência até concluir, mesmo que a versão passe depois a `SUBSTITUIDA` ou `INATIVA`.

**Imutabilidade significa imutabilidade do conteúdo e do significado.** Após a publicação, somente estado, fim de vigência e metadados de encerramento/substituição podem mudar, exclusivamente pelas transições acima. Essas mudanças de ciclo não alteram a regra aplicada nem os valores históricos já capturados.

## Parâmetros versionados

Valores de Parâmetro seguem o mesmo princípio temporal: código lógico estável, publicação imediata, versões com vigência `[início, fim)` não sobreposta e valor publicado imutável. Uma alteração em `SALARIO_MINIMO` encerra a versão anterior e inicia a sucessora atomicamente em um único instante `T`; não modifica o valor usado anteriormente. A Regra referencia o **código lógico** do Parâmetro. A Avaliação resolve e congela sua versão vigente e o valor no instante de referência.

## Snapshot histórico escolhido

O MVP usará uma **estratégia híbrida**:

1. relações normalizadas apontam para Beneficiário, Benefício, Versão de Regra e versões de Parâmetro;
2. um snapshot JSON controlado e versionado preserva as entradas efetivamente observadas e derivadas;
3. Resultados de Avaliação relacionais preservam cada nó avaliado, seu desfecho e explicação.

O JSON não substitui a modelagem relacional. Ele é um envelope histórico com schema conhecido e validado, apropriado para fatos heterogêneos de uma execução. Campos essenciais para identidade, consulta e integridade continuam relacionais.

O início da Avaliação captura de forma consistente o instante, a Versão de Regra, as versões de Parâmetros, as entradas cadastrais e documentais necessárias e os identificadores semânticos dos resolvedores. Snapshot, Resultados de Avaliação e conclusão são persistidos consistentemente: uma Avaliação `CONCLUIDA` só existe quando o conjunto completo de resultados e o snapshot correspondente estiverem gravados. Falha deixa `FALHA_TECNICA`, sem resultado automático válido; não se exibe um resultado parcial como concluído.

### Conteúdo mínimo da Avaliação

- identificadores do Beneficiário e Benefício;
- identificador imutável e número da Versão de Regra;
- instante de referência imutável em UTC e timestamps de início/conclusão;
- versão do schema do snapshot;
- snapshot dos valores persistidos relevantes;
- valores derivados usados, incluindo obrigatoriamente código lógico e versão semântica do Requisito/resolvedor;
- operandos monetários exatos em centavos e quantidade de integrantes usados na comparação, sem arredondamento decisório;
- versões e valores dos Parâmetros resolvidos;
- estados efetivos, apresentação e validade das Comprovações consultadas no instante de referência;
- resultado automático consolidado;
- estado de processamento: `INICIADA`, `CONCLUIDA` ou `FALHA_TECNICA`.

Uma `FALHA_TECNICA` não é `INELEGIVEL` nem `REQUER_ANALISE_HUMANA`; indica que a execução não produziu resultado válido e pode ser repetida como nova tentativa auditada.

### Conteúdo mínimo de cada Resultado de Avaliação

- identificador e caminho estável do nó na árvore;
- tipo do nó: Condição, `AND` ou `OR`;
- código, versão semântica e rótulo do Requisito, quando Condição;
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
2. Ao iniciar, a Versão de Regra deve estar publicada e vigente e o Benefício deve estar ativo; o instante de referência é capturado uma vez e não pode ser escolhido pelo usuário.
3. O conteúdo e o significado de versões publicadas ou usadas por Avaliação são imutáveis; apenas metadados controlados de ciclo podem mudar. Alterações de critério geram nova versão.
4. Vigências `[início, fim)` publicadas do mesmo Benefício ou código de Parâmetro não se sobrepõem.
5. Requisito, operador, literal e Parâmetro devem ter tipos compatíveis antes da publicação.
6. Uma Avaliação concluída e seus Resultados de Avaliação são imutáveis.
7. Toda entrada, valor derivado, Comprovação e Parâmetro que influencie um resultado deve ser rastreável no snapshot.
8. Grupos respeitam profundidade máxima de três níveis e no máximo 30 Condições.
9. Regra alguma pode conter ou invocar código, SQL, script, expressão livre ou identificador técnico arbitrário.
10. Resultado automático e futura Decisão Final humana nunca compartilham o mesmo campo nem sobrescrevem um ao outro.
11. Uma Avaliação concluída tem snapshot e todos os resultados por nó persistidos de modo consistente, sem depender do cadastro atual para explicar o passado.

## Auditoria e exclusão

- Rascunhos não publicados podem ser excluídos.
- Versões publicadas, versões de Parâmetro utilizadas e Avaliações concluídas são preservadas.
- Benefícios, Requisitos, Parâmetros e Beneficiários com histórico são inativados.
- Mudanças de estado registram obrigatoriamente timestamp. O ator é opcional enquanto não houver mecanismo de identidade confiável; não se atribui autoria humana autenticada sem esse mecanismo.
- O MVP não adota trilha genérica de eventos nem event sourcing; registros imutáveis e metadados explícitos atendem ao experimento.

## Concorrência

Optimistic locking não será adotado neste MVP. A publicação futura deverá usar transação, verificação do estado esperado e restrições de unicidade/vigência. Isso protege a decisão crítica sem espalhar complexidade de concorrência por cadastros de baixo volume.
