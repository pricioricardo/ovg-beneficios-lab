# Descrição do projeto

OVG Benefícios Lab é um experimento pequeno para avaliar desenvolvimento assistido por agentes de IA como possível alternativa futura à abordagem low-code atualmente usada pela equipe. O laboratório é fictício e inspirado em descrições públicas de programas sociais da OVG; não reproduz sistemas internos nem pressupõe acesso a eles.

## Limites do produto e da tecnologia

- Use somente dados sintéticos; nunca importe nem retenha informações reais de beneficiários.
- Limite a aplicação futura a, no máximo, 10 telas.
- Use Laravel, Filament, MySQL, Docker, Git e testes automatizados.
- Mantenha o fluxo de engenharia utilizável no Codex Cloud, em VS Code local com Docker, no Codex e no Claude Code.
- Mantenha as instruções principais independentes de provedor. `AGENTS.md` é a fonte principal; `CLAUDE.md` é somente um adaptador curto para Claude Code.
- Prefira scripts determinísticos para setup e verificação.
- Não adicione Redis, filas, Kubernetes, microsserviços ou infraestrutura além do necessário para este piloto.

## Marco atual

Padronizar o harness e a documentação em pt-BR e consolidar o roadmap do experimento, sem alterar o runtime certificado no Gate 1. Este marco não inclui beneficiários, entidades de benefício, requisitos de elegibilidade, avaliações, regras de negócio ou telas de negócio. Trabalho futuro de domínio exige uma tarefa explícita e os gates previstos.

## Entradas de desenvolvimento

- `./scripts/bootstrap.sh` valida os pré-requisitos e prepara a configuração local.
- `docker compose up -d` inicia a aplicação e MySQL.
- `./scripts/verify.sh` é o ponto único de entrada para as verificações do projeto.

Consulte [`ARCHITECTURE.md`](ARCHITECTURE.md) e [`../policies/`](../policies/) para decisões e guardrails compartilhados.
