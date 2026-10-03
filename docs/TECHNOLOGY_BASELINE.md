# Baseline tecnológica

## Baseline certificada no Gate 1

| Componente | Versão resolvida | Fonte |
| --- | --- | --- |
| PHP | 8.4.26 | Runtime do container |
| Laravel | 13.34.0 | `composer.lock` e `php artisan --version` |
| Filament | 5.9.0 | `composer.lock` |
| Livewire | 4.4.7 | `composer.lock` |
| MySQL | 8.4.11 | Consulta de versão do servidor e imagem do container |
| Composer | 2.8.12 | Runtime do container |
| Docker Compose | v2.40.3 | CLI Cloud |

`composer.lock` fixa as versões exatas dos pacotes PHP. As tags das imagens PHP e MySQL selecionam linhas de runtime compatíveis. Atualize este documento e os pins do lockfile/imagens em conjunto quando a equipe revisar deliberadamente a baseline.

## Migração do Gate 0

O scaffold do Gate 0 era provisório: resolvia Laravel 12.69.3, Filament 4.14.0 e Livewire 3.8.10 com PHP 8.3. O Gate 1 atualizou o scaffold Laravel mínimo existente porque ele não continha código de domínio a preservar ou separar. O harness independente de provedor e o histórico Git foram mantidos.

Nenhuma entidade, regra, dado ou tela de domínio foi adicionada no Gate 1. O painel administrativo vazio do Filament existe somente para comprovar a integração. Consulte `.agent/STATE.md` para os resultados de bootstrap e verify.
