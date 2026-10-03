# Git policy

- Do not push directly to `main`; work on a branch and integrate through review.
- Keep secrets, local environment files, dependency caches, and generated runtime output out of Git.
- Commit dependency manifests and lockfiles together when dependencies change.
- Keep commits reviewable and scoped. Do not mix unrelated formatting or generated changes into a feature.
- Review `git status` and the diff before reporting completion.
