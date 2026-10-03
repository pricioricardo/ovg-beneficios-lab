# Exemplos fictícios do MVP

Os exemplos abaixo validam o poder expressivo do contrato. São pseudoconfigurações conceituais, não arquivos executáveis nem reprodução de políticas oficiais da OVG. Todos os futuros registros usados para demonstrá-los serão sintéticos.

## Convenções dos exemplos

- `condition` contém somente Requisito, operador e operando tipados.
- `all` representa grupo `AND`; `any` representa grupo `OR`.
- `parameter` referencia um Parâmetro versionado, nunca uma variável livre.
- A Versão de Regra completa permanece sujeita à validação, publicação, vigência e imutabilidade.

Para este laboratório, `MES_GESTACIONAL` é derivado de data provável do parto e data de referência usando gestação convencional de 280 dias. Calcula-se `semanas = floor((280 - dias_ate_o_parto) / 7)` e então `mes = floor(semanas / 4) + 1`, limitado de 1 a 10. A fórmula, suas entradas e o valor entram no snapshot. Essa convenção é experimental e não orientação clínica.

## A. Fralda infantil

```yaml
any:
  - condition: { requirement: IDADE_ANOS, operator: LTE, value: 2 }
  - all:
      - condition: { requirement: DEFICIENCIA, operator: EQ, value: true }
      - condition: { requirement: LAUDO_MEDICO, operator: PRESENT }
```

- Criança com 2 anos resulta em `ELEGIVEL`, independentemente do segundo ramo.
- Pessoa acima desse limite, com deficiência informada e laudo ausente, resulta em `PENDENTE_DOCUMENTACAO`.
- Pessoa acima do limite sem deficiência resulta em `INELEGIVEL`, mesmo se o laudo estiver ausente, pois o ramo `AND` já é falso.

## B. Fralda geriátrica

```yaml
condition: { requirement: LAUDO_MEDICO, operator: PRESENT }
```

- Laudo efetivamente presente na data de referência resulta em `ELEGIVEL`.
- Laudo ausente ou vencido resulta em `PENDENTE_DOCUMENTACAO`.

Este exemplo segue deliberadamente o critério fornecido para o laboratório e não presume outros critérios reais.

## C. Cadeira de rodas

```yaml
all:
  - condition: { requirement: VULNERABILIDADE_SOCIAL, operator: EQ, value: true }
  - condition: { requirement: LIMITACAO_MOBILIDADE, operator: EQ, value: true }
  - condition: { requirement: RELATORIO_PROFISSIONAL, operator: PRESENT }
```

- Todos atendidos resultam em `ELEGIVEL`.
- Vulnerabilidade e limitação atendidas, com relatório ausente, resultam em `PENDENTE_DOCUMENTACAO`.
- Vulnerabilidade não atendida resulta em `INELEGIVEL`; eventual documento ausente não muda o fato de o `AND` já ser falso.

## D. Kit enxoval

```yaml
all:
  - any:
      - all:
          - condition: { requirement: GESTANTE, operator: EQ, value: true }
          - condition: { requirement: MES_GESTACIONAL, operator: GTE, value: 5 }
      - all:
          - condition: { requirement: DIAS_APOS_NASCIMENTO_BEBE, operator: GTE, value: 0 }
          - condition: { requirement: DIAS_APOS_NASCIMENTO_BEBE, operator: LTE, value: 30 }
  - any:
      - condition: { requirement: CARTAO_GESTANTE, operator: PRESENT }
      - condition: { requirement: ULTRASSONOGRAFIA, operator: PRESENT }
```

- O primeiro grupo aceita gestação a partir do 5º mês ou de 0 a 30 dias após o nascimento.
- O segundo grupo exige ao menos uma das duas Comprovações.
- Período atendido com ambos os documentos ausentes resulta em `PENDENTE_DOCUMENTACAO`.
- Uma das Comprovações presente satisfaz o grupo documental.

## E. Centro de convivência para idosos

```yaml
all:
  - condition: { requirement: IDADE_ANOS, operator: GTE, value: 60 }
  - condition: { requirement: AUTONOMIA_FUNCIONAL, operator: EQ, value: true }
  - condition:
      requirement: RENDA_PER_CAPITA
      operator: LTE
      parameter: SALARIO_MINIMO
```

- A Avaliação resolve a versão de `SALARIO_MINIMO` vigente na data de referência e registra versão e valor no snapshot.
- Autonomia funcional não informada resulta em `REQUER_ANALISE_HUMANA` se nenhum outro filho tornar o `AND` definitivamente falso.
- Idade ou renda acima do limite resulta em `INELEGIVEL`.

## Mudança simples sem código PHP

Alterar `IDADE_ANOS LTE 2` para `IDADE_ANOS LTE 3` consiste em copiar a versão publicada para um novo rascunho, alterar o literal tipado, validar e publicar a sucessora. A versão anterior e suas Avaliações permanecem imutáveis.

A capacidade de alterar configuração não elimina revisão e publicação. Novos tipos de fato ou semânticas de cálculo exigem evolução explícita do código e novo gate; não podem ser simulados por expressão livre.
