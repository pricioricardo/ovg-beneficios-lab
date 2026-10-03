# Testing policy

- Changes that affect behavior or architecture require meaningful automated tests at the appropriate level.
- Use synthetic fixtures only; tests must not depend on OVG systems or real beneficiary data.
- Run `./scripts/verify.sh` before declaring work complete and report the checks that ran, failed, or were unavailable.
- Keep tests deterministic and independent of external services unless the test explicitly validates that integration.
- Bootstrap checks should exercise the Laravel application and its test suite once dependencies are installed; configuration parsing alone is insufficient.
