# ExecPlans

Use an ExecPlan for work that spans multiple files or steps, changes architecture or data shape, has material risk, or cannot be completed and verified in one focused pass. Small, local changes do not need a plan.

Store active plans under `.agent/` using a descriptive name such as `PLAN-filament-auth.md`. A plan should include:

1. **Goal and scope** — user outcome, explicit exclusions, and acceptance criteria.
2. **Current state** — relevant code, decisions, constraints, and dependencies.
3. **Steps** — ordered, actionable changes with files or components named.
4. **Validation** — exact commands and observable outcomes, including required services.
5. **Risks and recovery** — data or compatibility concerns and a safe recovery path.
6. **Progress** — completed work, next action, and blockers, updated as work proceeds.

Keep the plan usable by another agent with no chat history. Link relevant policy and architecture decisions instead of copying them. Update `.agent/STATE.md` when the active plan or project stage changes; archive or remove completed plans when they no longer help future work.
