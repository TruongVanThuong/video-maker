# AI Video Maker - Architecture Rules

## 1. Project Overview

This project is an AI-powered story-to-video application.

The application supports two main input flows:

1. Comic → Video
2. Text Story → Video

The system may contain:

- Project management
- Media management
- Story / scene management
- Metadata
- AI processing
- OCR
- TTS
- Video timeline
- Video rendering
- Webhook processing
- Final media storage

---

## 2. Technology Stack

Backend:

- Laravel
- PHP 8.3

Frontend:

- Inertia.js
- React

Database:

- MySQL

Infrastructure:

- Docker
- AWS / S3

Media:

- FFmpeg

AI:

- LLM
- OCR
- TTS

---

## 3. Architectural Principles

### Backend

Follow Laravel conventions.

Prefer:

- Controllers
- Form Requests
- Policies
- Services when business logic is complex
- Jobs for asynchronous processing
- Events / listeners when appropriate
- Dependency Injection

Controllers should remain thin.

Do not place complex business logic directly inside controllers.

---

## 4. Abstraction

Do not create abstractions simply because a pattern exists.

Before creating:

- Repository
- Factory
- Strategy
- Interface
- Service

first inspect the existing codebase.

Reuse an existing pattern when appropriate.

Prefer simple architecture over unnecessary abstraction.

---

## 5. Frontend

Use Inertia + React.

React components should be:

- focused
- reusable
- readable
- composable

Avoid creating large components containing unrelated responsibilities.

Separate:

- UI
- state
- API interaction
- complex business logic

when the complexity justifies the separation.

---

## 6. Existing Architecture First

Before implementing a new feature:

1. Inspect the existing project.
2. Find similar functionality.
3. Identify existing patterns.
4. Reuse those patterns where appropriate.

Do not introduce a completely new architecture without a clear reason.

---

## 7. File Scope

Only modify files that are necessary for the requested task.

Do not refactor unrelated code.

Do not rename existing structures without a reason.

Do not introduce dependencies unless required.

---

## 8. AI Pipeline

AI processing should be designed as explicit stages.

Example:

Input
→ Analyze
→ Process
→ Store result
→ Continue next stage

Long-running operations should not block normal HTTP requests.

Prefer Jobs / Queue-based processing for expensive operations.

---

## 9. Media Processing

Media operations may involve:

- image processing
- OCR
- TTS
- FFmpeg
- video rendering
- external video APIs

These operations should be isolated from normal CRUD logic.

---

## 10. Project ID

Project is the central entity of the video generation pipeline.

Related processing should be traceable to:

project_id

Whenever possible, processing records should maintain clear relationships back to the project.

---

## 11. Status and Type

Do not use MySQL ENUM for business statuses or types.

Prefer integer values or reference tables.

Status/type definitions should be documented clearly.

---

## 12. Maintainability

Prefer:

- readable code
- explicit naming
- small responsibilities
- reusable logic
- testable code

Avoid:

- duplicated business logic
- magic values
- hidden side effects
- unnecessary abstraction