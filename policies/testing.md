# Policy de testes

- Mudanças que afetem comportamento ou arquitetura exigem testes automatizados relevantes no nível apropriado.
- Use somente fixtures sintéticas; os testes não podem depender de sistemas da OVG nem de dados reais de beneficiários.
- Execute `./scripts/verify.sh` antes de declarar o trabalho concluído e informe quais verificações passaram, falharam ou estavam indisponíveis.
- Mantenha os testes determinísticos e independentes de serviços externos, exceto quando o teste validar explicitamente essa integração.
- Depois que as dependências estiverem instaladas, as verificações de bootstrap devem exercitar a aplicação Laravel e sua suíte de testes; apenas validar a configuração não é suficiente.
