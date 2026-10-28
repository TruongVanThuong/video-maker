---
name: testing
description: Tests Laravel, Inertia, React, AI processing, queue jobs, and media pipeline functionality. Use after implementing or modifying application behavior.
---

# Testing Skill

## Goal

Verify that the implementation works and remains compatible with existing behavior.

---

# Test Strategy

First identify the appropriate test level.

Use:

- Unit tests
- Feature tests
- Integration tests

according to the behavior being tested.

---

# Laravel

Check:

- validation
- authorization
- model relationships
- business logic
- database behavior
- API responses

---

# Jobs

For jobs verify:

- correct input
- correct state transition
- retry behavior
- failure handling
- duplicate execution concerns

---

# AI Pipeline

When external AI services are involved:

- avoid real API calls in normal automated tests
- mock external clients where appropriate
- verify request payload
- verify response handling
- verify failure handling

---

# Media Pipeline

Test:

- timeline creation
- render request
- external render ID handling
- webhook handling
- final media persistence
- failure state

---

# Test Execution

After tests:

Report:

- command executed
- number of tests
- failures
- warnings
- relevant observations

Never hide failing tests.

Never remove tests just to make the suite pass.