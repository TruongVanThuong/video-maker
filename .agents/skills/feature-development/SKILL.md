---
name: feature-development
description: Implements a complete feature in the AI Video Maker using the project's Laravel, Inertia, React, MySQL, queue, AI, and media architecture. Use when implementing a non-trivial product feature.
---

# Feature Development Skill

## Goal

Implement a feature safely while following the project's architecture.

---

# Phase 1 — Understand

Before coding:

1. Read the requirement.
2. Identify expected behavior.
3. Identify constraints.
4. Identify affected domains.

---

# Phase 2 — Explore

Inspect:

- models
- migrations
- controllers
- services
- jobs
- routes
- React components
- tests
- configuration

Search for similar functionality.

Prefer existing patterns.

---

# Phase 3 — Plan

Create a plan containing:

## Backend

- models
- migrations
- requests
- policies
- services
- jobs
- controllers
- routes

## Frontend

- pages
- components
- state
- API interactions

## Database

- tables
- relationships
- indexes
- constraints

## Tests

- unit tests
- feature tests
- integration tests

---

# Phase 4 — Approval

For non-trivial features:

Do not implement until the plan is approved.

If the user explicitly asks to implement immediately, the plan may be created and executed in the same task.

---

# Phase 5 — Implementation

Implement in dependency order:

1. database
2. models
3. backend domain logic
4. jobs / processing
5. API / controller
6. frontend

Do not modify unrelated files.

---

# Phase 6 — Testing

Run relevant tests.

Verify:

- validation
- authorization
- relationships
- business logic
- API response
- frontend behavior

Fix failures rather than bypassing them.

---

# Phase 7 — Review

Review:

- correctness
- security
- performance
- maintainability
- duplicated code
- unnecessary abstraction
- edge cases

---

# Phase 8 — Final Report

Report:

- changed files
- implementation summary
- tests
- review findings
- remaining risks