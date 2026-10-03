# Security policy

- Use synthetic data exclusively. Never use real beneficiary data, even for local testing.
- Never store secrets in Git, documentation, logs, screenshots, or agent instructions. Use ignored local configuration and secure environment settings.
- Any example database credentials in Compose are public, local-only placeholders and must not be reused outside synthetic development environments.
- Never make changes directly in production or assume access to OVG internal systems.
- Validate and authorize inputs at the application boundary; avoid exposing personal or secret values in logs.
- Keep dependencies on supported versions and preserve TLS, package-signature, and checksum verification during installation.
- Report security-relevant failures and unresolved risks instead of bypassing controls.
