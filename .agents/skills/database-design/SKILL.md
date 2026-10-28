---
name: database-design
description: Designs and reviews MySQL database schemas, relationships, indexes, constraints, statuses, and migrations for the AI Video Maker. Use when creating or changing database structure.
---

# Database Design Skill

## Goal

Design a maintainable MySQL schema that fits the existing AI Video Maker architecture.

---

## Step 1 — Understand Domain

Identify:

- entities
- ownership
- relationships
- lifecycle
- status
- type
- metadata

---

## Step 2 — Inspect Existing Schema

Before proposing tables:

- inspect migrations
- inspect models
- inspect relationships
- inspect existing naming conventions

Do not duplicate existing concepts.

---

## Step 3 — Design

Define:

- table names
- columns
- data types
- primary keys
- foreign keys
- indexes
- unique constraints
- nullable fields

---

## Status and Type

Do not use MySQL ENUM.

Prefer integer fields or reference tables.

Document the meaning of each value.

---

## Metadata

Prefer flexible metadata structures when the domain requires extensibility.

Potential metadata dimensions include:

- genre
- mood
- style
- audience
- visual style
- voice
- language

Do not create a new table for every metadata concept unless the domain requires it.

---

## Query Patterns

Indexes should follow actual query patterns.

Consider:

- project_id
- status
- type
- foreign keys
- sorting columns

---

## Migration

Generate Laravel migrations.

Do not manually modify the database.

---

## Output

Return:

1. Entity relationship explanation.
2. Table design.
3. Relationships.
4. Index strategy.
5. Migration plan.
6. Risks.