# OVG Benefícios Lab

Experimento fictício para avaliar desenvolvimento assistido por agentes de IA. Usa Laravel, Filament, MySQL, Docker, Git e testes automatizados. Todo dado deve ser sintético; o projeto não reproduz sistemas internos da OVG e terá no máximo 10 telas.

## Bootstrap local

Requisitos: Docker Desktop ou Docker Engine com Docker Compose v2, Git e `curl`.

```sh
./scripts/bootstrap.sh
```

O script cria `.env` local se necessário, constrói a imagem e inicia Laravel + MySQL. Para iniciar diretamente depois da primeira configuração:

```sh
docker compose up -d
```

Laravel fica em `http://localhost:8080`; MySQL fica na porta `3306`. As portas e credenciais locais podem ser ajustadas em `.env`. Os valores padrão são apenas placeholders públicos para desenvolvimento com dados sintéticos. Não os reutilize fora do ambiente local.

Para parar os serviços: `docker compose down`. Para remover também os dados locais do MySQL: `docker compose down --volumes`.

## Verificação

```sh
./scripts/verify.sh
```

Este é o ponto único de entrada para validação. Ele confirma a configuração Compose, sobe os serviços se necessário, executa os testes Laravel e verifica o endpoint de saúde HTTP. Lint, análise estática e verificações de segurança serão agregados quando forem adotados.

## Instruções e escopo

Leia [`AGENTS.md`](AGENTS.md), [`policies/`](policies/), [`docs/PROJECT.md`](docs/PROJECT.md) e [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md). `AGENTS.md` é a fonte portátil principal; `CLAUDE.md` apenas aponta para as regras compartilhadas.

O estágio atual prepara engenharia e harness. Beneficiários, benefícios, requisitos, avaliações, regras de elegibilidade e telas de negócio estão fora do escopo.
