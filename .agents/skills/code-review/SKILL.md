---
name: code-review
description: Reviews AI Video Maker code changes for correctness, security, performance, architecture, maintainability, and unnecessary complexity. Use when reviewing completed work or a diff.
---

# Code Review Skill

## Review Order

Review in this order:

1. Correctness
2. Security
3. Data integrity
4. Performance
5. Architecture
6. Maintainability
7. Tests

---

## Correctness

Check:

- requirements
- edge cases
- error handling
- state transitions

---

## Security

Check:

- authorization
- validation
- mass assignment
- SQL injection
- file upload handling
- exposed secrets
- unsafe external input

---

## Database

Check:

- N+1 queries
- missing indexes
- incorrect relationships
- transaction boundaries
- duplicate records

---

## AI / Media

Check:

- retry behavior
- idempotency
- external API failure
- job state
- storage consistency
- webhook validation

---

## Architecture

Check:

- unnecessary abstractions
- duplicated logic
- controller complexity
- inappropriate responsibilities
- coupling

---

## Frontend

Check:

- component responsibility
- duplicated state
- unnecessary effects
- API handling
- error states
- loading states

---

## Output

Report findings by severity:

### Critical

Must fix before continuing.

### High

Should fix before merge.

### Medium

Recommended improvement.

### Low

Optional improvement.

Do not modify code during the first review unless explicitly requested.