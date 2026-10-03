# Policy de banco de dados

- MySQL é o banco de desenvolvimento e deve ser iniciado pelo Docker Compose.
- Use somente dados sintéticos. Nunca importe, copie, registre em logs ou persista informações reais de beneficiários.
- Mantenha mudanças de schema em Laravel migrations e revise-as com os testes pertinentes.
- Depois que uma migration for consolidada, não a edite; adicione uma nova migration para mudanças subsequentes.
- Mantenha credenciais e configurações locais de conexão em arquivos de ambiente ignorados pelo Git. Nunca faça commit de secrets.
- Não adicione schema ou dados de seed para beneficiários, benefícios, requisitos ou avaliações de elegibilidade durante a etapa de bootstrap.
