# OVG Benefícios Lab

Experimento fictício para avaliar desenvolvimento assistido por agentes de IA. Usa Laravel, Filament, MySQL, Docker, Git e testes automatizados. Todo dado deve ser sintético; o projeto não reproduz sistemas internos da OVG e terá no máximo 10 telas.

## Bootstrap local

Requisitos: Docker Desktop ou Docker Engine com Docker Compose v2, Git e `curl`.

```sh
./scripts/bootstrap.sh
```

O script cria `.env` local se necessário, constrói a imagem PHP, instala as dependências Composer fixadas em `composer.lock`, inicia Laravel e MySQL, aplica as migrations, cria os dados sintéticos idempotentes do Gate 3 e aguarda o health check da aplicação. Para iniciar diretamente depois da primeira configuração:

```sh
docker compose up -d
```

Laravel fica em `http://localhost:8080`; MySQL fica na porta `3306`. As portas e credenciais locais podem ser ajustadas em `.env`. Os valores padrão são placeholders públicos exclusivos para desenvolvimento com dados sintéticos. Não os reutilize fora desse ambiente.

O painel do laboratório abre em `/admin` sem login e permite cadastrar Beneficiários sintéticos, registrar o estado do Relatório Profissional e avaliar o benefício fictício Cadeira de Rodas. Essa decisão de acesso vale somente para o laboratório.

Para parar os serviços: `docker compose down`. Para também remover os dados locais do MySQL: `docker compose down --volumes`.

## Verificação

```sh
./scripts/verify.sh
```

Este é o ponto único de entrada para validação. Ele confere a configuração do Compose, a sintaxe dos scripts, o manifesto Composer, os containers e o health check do MySQL, as versões do runtime, o estado das migrations, a suíte de testes em um banco MySQL isolado e as rotas `/up` e `/admin`.

Para reconstruir explicitamente **somente** o banco descartável de testes e repetir o cenário sintético, use `./scripts/reset-test-db.sh`. O script confere a conexão efetiva antes de `migrate:fresh --seed` e recusa configuração incompatível.

## Instruções e escopo

Leia [`AGENTS.md`](AGENTS.md), [`policies/`](policies/), [`docs/PROJECT.md`](docs/PROJECT.md), [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) e [`docs/ROADMAP.md`](docs/ROADMAP.md). `AGENTS.md` é a fonte portátil principal; `CLAUDE.md` é somente um adaptador fino para Claude Code.

O runtime foi certificado no Gate 1. O Gate 1.1 consolidou o harness em pt-BR. O Gate 2 congelou o contrato de domínio. O Gate 3 implementa somente o fluxo de Cadeira de Rodas e está pronto para nova revisão independente; consulte [a descrição do slice](docs/implementation/GATE_003.md).
