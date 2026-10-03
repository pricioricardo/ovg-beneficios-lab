# Database policy

- MySQL is the development database and must be started through Docker Compose.
- Use synthetic data only. Never import, copy, log, or persist real beneficiary information.
- Keep schema changes in Laravel migrations and review them with the relevant tests.
- Once a migration has been consolidated, do not edit it; add a new migration for subsequent changes.
- Keep credentials and local connection overrides in ignored environment files. Never commit secrets.
- Do not add schema or seed data for beneficiaries, benefits, requirements, or eligibility evaluations during the bootstrap stage.
