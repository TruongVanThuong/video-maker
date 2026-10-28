# AI and Media Pipeline Rules

## Purpose

This project generates videos from:

1. Comic input
2. Text story input

The system may use:

- OCR
- LLM
- TTS
- image processing
- FFmpeg
- external rendering services
- AWS S3

---

## Pipeline Principle

Do not implement the entire video pipeline as one large function.

Prefer explicit processing stages.

Example:

Input
→ Project
→ Scene
→ Media
→ AI Processing
→ Timeline
→ Rendering
→ Webhook
→ Final Media

---

## Long-running Tasks

Long-running operations should not run directly inside normal HTTP requests.

Examples:

- OCR
- LLM processing
- TTS
- image processing
- FFmpeg
- video rendering
- external API calls

Prefer Laravel Jobs / Queue.

---

## Idempotency

Pipeline jobs should consider retry behavior.

A job may execute more than once.

Avoid creating duplicate:

- media
- scenes
- render jobs
- external API requests

when the operation can be retried.

---

## External Services

External services should be isolated behind clear application boundaries.

Examples:

- AI client
- OCR client
- TTS client
- video rendering client
- S3 storage

Do not spread raw external API calls throughout controllers.

---

## Project Traceability

Processing should remain traceable to the project.

Prefer:

project_id
→ processing record
→ job
→ output

where appropriate.

---

## Failure Handling

AI/media operations may fail.

Represent failure explicitly.

Store enough information to diagnose:

- status
- error message
- external job ID
- retry information
- timestamps

---

## Media Storage

Large media files should not be stored directly in MySQL.

Use object storage such as S3 where appropriate.

Database should store metadata and references.

---

## Video Rendering

Rendering should be treated as an asynchronous process.

Example:

Project
→ Build timeline
→ Send render request
→ Receive render ID
→ Wait for webhook
→ Download/store result
→ Mark project completed

---

## Verification

Never assume an external media operation succeeded simply because an API request returned successfully.

Verify:

- response
- external job status
- downloaded file
- final stored media