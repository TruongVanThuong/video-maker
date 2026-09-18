# Development Rules

## Core Development Workflow

Every non-trivial feature should follow:

1. Understand
2. Explore
3. Plan
4. Approval
5. Implement
6. Test
7. Review
8. Fix
9. Document
10. Commit

---

## 1. Understand

Before modifying code:

- Understand the requirement.
- Identify the expected behavior.
- Identify constraints.
- Identify affected areas.

Do not immediately start coding if the requirement is ambiguous.

---

## 2. Explore

Inspect:

- existing implementation
- related models
- controllers
- services
- jobs
- routes
- React components
- tests
- configuration

Find similar features before creating new patterns.

---

## 3. Plan

For non-trivial tasks, create a plan containing:

- files to create
- files to modify
- database changes
- backend changes
- frontend changes
- tests
- risks

Do not implement major architectural changes without approval.

---

## 4. Implementation

During implementation:

- Follow project rules.
- Reuse existing patterns.
- Keep changes focused.
- Avoid unrelated refactoring.
- Avoid unnecessary dependencies.

---

## 5. Testing

After implementation:

- Run relevant tests.
- Check validation.
- Check authorization.
- Check database behavior.
- Check API behavior.
- Check frontend behavior where applicable.

Never hide or bypass failing tests.

---

## 6. Review

Review implementation for:

- correctness
- security
- performance
- duplicated logic
- unnecessary abstraction
- naming
- maintainability
- edge cases
- tests

---

## 7. Unexpected Decisions

If implementation requires a major decision that was not included in the approved plan:

STOP.

Explain:

1. The problem.
2. Available options.
3. Recommended approach based on existing architecture.

Wait for approval before proceeding.

---

## 8. Destructive Operations

Never perform destructive operations without explicit approval.

Examples:

- DROP DATABASE
- DELETE production data
- destructive migrations
- deleting large groups of files
- force push
- production deployment
- deleting S3 data
- deleting media permanently

---

## 9. Final Report

After completing a task, report:

- What changed
- Files changed
- Tests executed
- Test results
- Important decisions
- Remaining risks