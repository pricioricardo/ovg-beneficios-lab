# Policy de segurança

- Use exclusivamente dados sintéticos. Nunca use dados reais de beneficiários, nem mesmo em testes locais.
- Nunca armazene secrets no Git, na documentação, em logs, capturas de tela ou instruções de agentes. Use configuração local ignorada e configurações seguras do ambiente.
- Credenciais de exemplo do banco no Compose são placeholders públicos, exclusivos para uso local, e não devem ser reutilizados fora de ambientes de desenvolvimento sintéticos.
- Nunca faça alterações diretamente em produção nem presuma acesso a sistemas internos da OVG.
- Valide e autorize entradas na fronteira da aplicação; evite expor valores pessoais ou secretos em logs.
- Mantenha dependências em versões suportadas e preserve TLS, verificação de assinatura de pacotes e checksums durante a instalação.
- Relate falhas relevantes para segurança e riscos não resolvidos em vez de contornar controles.
