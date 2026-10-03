# Technology baseline

## Gate 1 certified baseline

| Component | Resolved version | Source |
| --- | --- | --- |
| PHP | 8.4.26 | Container runtime |
| Laravel | 13.34.0 | `composer.lock` and `php artisan --version` |
| Filament | 5.9.0 | `composer.lock` |
| Livewire | 4.4.7 | `composer.lock` |
| MySQL | 8.4.11 | Server version query and container image |
| Composer | 2.8.12 | Container runtime |
| Docker Compose | v2.40.3 | Cloud CLI |

`composer.lock` pins exact PHP package releases. The PHP and MySQL image tags select compatible runtime lines. Update this file and the lock/image pins together when the team deliberately revises the baseline.

## Migration from Gate 0

The Gate 0 scaffold was provisional: it resolved Laravel 12.69.3, Filament 4.14.0, and Livewire 3.8.10 on PHP 8.3. Gate 1 upgrades the existing minimal Laravel scaffold in place because it contains no business-domain code to preserve or disentangle. The provider-neutral harness and Git history remain intact.

No business-domain entities, rules, data, or screens were added in Gate 1. The empty Filament admin panel exists only to prove integration. See `.agent/STATE.md` for the latest bootstrap and verification results.
