# Policy de arquitetura

- Mantenha este piloto como uma aplicação Laravel com Filament e MySQL.
- Mantenha a aplicação futura em 10 telas ou menos.
- Não adicione Redis, filas, Kubernetes, microsserviços ou infraestrutura sem relação com o projeto sem um requisito explícito e documentado.
- Coloque o comportamento de negócio no código de aplicação/domínio e mantenha Filament focado em apresentação e interação.
- Documente decisões arquiteturais materiais em `docs/ARCHITECTURE.md` antes da mudança ou junto dela.
- Mantenha as instruções compartilhadas independentes de provedor; pastas específicas de provedores podem conter somente integrações.
- Trate migrations consolidadas como imutáveis e use migrations aditivas para mudanças posteriores de schema.
