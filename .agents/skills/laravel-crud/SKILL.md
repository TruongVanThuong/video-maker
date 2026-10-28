---
name: laravel-crud
description: Creates and modifies Laravel CRUD modules including migrations, models, Form Requests, policies, controllers, routes, and Inertia React pages. Use when implementing standard CRUD functionality.
---

# Laravel CRUD Skill

## Before Coding

Inspect at least two similar CRUD modules when available.

Identify:

- naming conventions
- controller structure
- validation pattern
- authorization pattern
- frontend structure
- test structure

Reuse the existing pattern.

---

# Database

Create migration.

Consider:

- primary key
- foreign keys
- indexes
- unique constraints
- nullable fields

Do not use MySQL ENUM.

---

# Model

Implement:

- fillable / guarded according to project convention
- casts
- relationships

Do not add unnecessary model logic.

---

# Validation

Use Form Requests for non-trivial validation.

Validation should be explicit.

---

# Authorization

Use the project's existing authorization pattern.

Use Policies where appropriate.

Do not rely only on frontend authorization.

---

# Controller

Controllers should remain thin.

Prefer:

Request
→ Controller
→ Domain/service logic
→ Response

Do not place complex business logic inside the controller.

---

# Frontend

Use the existing Inertia + React conventions.

Prefer focused components.

Avoid large monolithic pages.

---

# Tests

Add relevant tests for:

- create
- read
- update
- delete
- validation
- authorization

---

# Final Verification

Run tests.

Inspect changed files.

Check for unrelated modifications.