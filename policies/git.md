# Policy de Git

- Não faça push diretamente para `main`; trabalhe em um branch e integre por revisão.
- Mantenha secrets, arquivos de ambiente locais, caches de dependências e saídas de runtime geradas fora do Git.
- Faça commit dos manifests e lockfiles de dependências em conjunto quando as dependências mudarem.
- Mantenha commits revisáveis e com escopo definido. Não misture formatação sem relação ou arquivos gerados a uma funcionalidade.
- Revise `git status` e o diff antes de informar a conclusão.
