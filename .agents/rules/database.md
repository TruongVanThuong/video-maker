# Database Rules

## Database

The project uses MySQL.

---

## Primary Keys

Use the project's existing primary key convention.

Do not introduce a different ID strategy without reviewing existing models.

---

## Foreign Keys

Use foreign keys for relationships where appropriate.

Relationships should be explicit.

Example:

Project
→ Scenes
→ Media
→ Processing Jobs

---

## Status / Type

Do not use MySQL ENUM.

Prefer:

- integer status/type fields
- reference tables when the domain requires extensibility

Document integer meanings.

Example:

status:

1 = pending
2 = processing
3 = completed
4 = failed

Do not scatter undocumented numeric values throughout the codebase.

---

## Indexes

Add indexes based on actual query patterns.

Pay particular attention to:

- foreign keys
- project_id
- status
- type
- frequently queried timestamps

Do not blindly add indexes to every column.

---

## Relationships

Before creating a new relationship:

1. Inspect existing models.
2. Check database schema.
3. Identify ownership.
4. Identify cardinality.

Clearly define:

- hasOne
- hasMany
- belongsTo
- belongsToMany

---

## Migration

Every schema change should use migrations.

Do not manually modify production database structure.

---

## Data Integrity

Prefer database constraints where appropriate.

Examples:

- foreign keys
- unique constraints
- non-null constraints
- indexes

Business validation should remain in application-level validation where appropriate.