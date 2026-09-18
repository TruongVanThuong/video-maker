# AI Video Maker — Project Architecture Context

## 1. Product Overview

AI Video Maker là một **AI-powered Story-to-Video Platform** cho phép người dùng biến nội dung truyện tranh hoặc truyện chữ thành video hoàn chỉnh.

Hệ thống hỗ trợ hai workflow chính:

### Flow A — Comic to Video

```text
Comic / PDF
    ↓
Upload
    ↓
Page Extraction
    ↓
Panel Detection
    ↓
OCR
    ↓
Bubble / Dialogue Detection
    ↓
Optional AI Rewrite
    ↓
Character / Voice Mapping
    ↓
TTS
    ↓
Scene Composition
    ↓
Animation / Camera
    ↓
Timeline
    ↓
Video Rendering
    ↓
Final Video
```

### Flow B — Text Story to Video

```text
Text Story
    ↓
Story Analysis
    ↓
Character Detection
    ↓
Dialogue Detection
    ↓
Scene Detection
    ↓
User Character Images / References
    ↓
Voice Assignment
    ↓
Scene Generation
    ↓
Image Prompt Generation
    ↓
Camera / Animation Rules
    ↓
TTS
    ↓
Timeline
    ↓
Video Rendering
    ↓
Final Video
```

Hai workflow có input khác nhau nhưng cần hội tụ về một **common Scene Composition / Timeline model** trước khi rendering.

---

# 2. Architectural Principles

Các nguyên tắc chính:

1. **Project là trung tâm của domain**, nhưng không phải mọi business logic đều đặt trực tiếp trong Project.
2. **Frontend không chứa business rules**.
3. **Long-running operations phải chạy asynchronous** thông qua Laravel Queue/Jobs.
4. Pipeline phải hỗ trợ **parallel processing** và **sequential dependencies**.
5. Mỗi stage phải có trạng thái rõ ràng và có khả năng retry.
6. User có thể **edit / override intermediate results** trước khi render.
7. Input, intermediate assets và final media phải được quản lý riêng biệt.
8. Không để một Job thất bại làm toàn bộ Project phải chạy lại nếu không cần thiết.
9. Third-party AI providers phải được abstraction để có thể thay đổi provider.
10. Mọi AI processing quan trọng cần có logging để debug và theo dõi chi phí.
11. State transition phải được kiểm soát, tránh race condition giữa các Queue workers.
12. Ưu tiên thiết kế đơn giản và tránh premature optimization.

---

# 3. Technology Stack

## Backend

* Laravel
* Laravel Queue
* Laravel Bus / Batch / Chain
* MySQL
* Laravel Events / Notifications
* Webhook endpoints

## Frontend

```text
Laravel
    ↓
Inertia.js
    ↓
React
```

Frontend chịu trách nhiệm:

* UI
* User interaction
* Local UI state
* Timeline editor
* Asset preview
* Progress display

Backend chịu trách nhiệm:

* Domain logic
* Validation
* State transitions
* Queue orchestration
* AI provider integration
* Storage management
* Rendering orchestration

---

# 4. High-Level Architecture

```text
                         ┌──────────────────────┐
                         │        User          │
                         └──────────┬───────────┘
                                    │
                                    ▼
                         ┌──────────────────────┐
                         │   Laravel / Inertia  │
                         └──────────┬───────────┘
                                    │
                  ┌─────────────────┼─────────────────┐
                  │                 │                 │
                  ▼                 ▼                 ▼
             Project API       Studio API       Upload API
                  │                 │                 │
                  └─────────────────┼─────────────────┘
                                    ▼
                         ┌──────────────────────┐
                         │   Domain / Services  │
                         └──────────┬───────────┘
                                    │
                                    ▼
                         ┌──────────────────────┐
                         │   Queue Orchestrator │
                         └──────────┬───────────┘
                                    │
             ┌──────────────────────┼──────────────────────┐
             ▼                      ▼                      ▼
        Input Jobs             AI Jobs               Render Jobs
             │                      │                      │
             ▼                      ▼                      ▼
          OCR / PDF         LLM / TTS / Image       Creatomate /
          Processing          Generation APIs         Shotstack
             │                      │                      │
             └──────────────────────┼──────────────────────┘
                                    ▼
                              Media Storage
                                    │
                                    ▼
                              Final Video
```

---

# 5. Domain Model

## Project

`Project` là Aggregate Root của video generation workflow.

Project quản lý:

* Owner
* Source type
* Project configuration
* Overall processing state
* Scenes
* Characters
* Comic pages
* Project media
* Processing jobs
* Render jobs

### Project Source Types

```text
comic
story
```

### Project Lifecycle

```text
draft
  ↓
processing
  ↓
analyzed
  ↓
assets_ready
  ↓
rendering
  ↓
completed
```

Failure có thể xảy ra từ bất kỳ processing stage nào:

```text
processing ──→ failed
analyzed   ──→ failed
assets_ready ─→ failed
rendering  ──→ failed
```

`failed` không nhất thiết có nghĩa Project phải restart từ đầu. Project phải cho phép retry hoặc resume từ stage phù hợp.

---

# 6. Scene State Machine

Project state và Scene state phải được quản lý độc lập.

Ví dụ Scene lifecycle:

```text
draft
  ↓
analyzing
  ↓
ready
  ↓
generating_assets
  ↓
assets_ready
  ↓
rendering
  ↓
completed
```

Scene có thể chuyển sang:

```text
failed
```

tại các processing stages.

Ví dụ:

```text
Scene A → completed
Scene B → failed
Scene C → completed
```

Project không nên bị buộc phải reset toàn bộ chỉ vì Scene B thất bại.

Retry chỉ nên xử lý phần bị lỗi nếu dependency graph cho phép.

---

# 7. Core Entities

## User

Owner của Project.

```text
User
 └── hasMany Projects
```

---

## Project

Core aggregate.

Relationships:

```text
Project
 ├── Scenes
 ├── Characters
 ├── ComicPages
 ├── ProjectMedia
 ├── AIJobs
 └── RenderJobs
```

Project lưu:

* `source_type`
* `status`
* processing metadata
* configuration
* target duration
* queue/batch identifiers
* timestamps

---

## ComicPage

Chỉ sử dụng cho Flow A.

Lưu:

* Project reference
* Page number
* Original image/file
* Dimensions
* Processing status
* OCR status
* Storage location

Relationship:

```text
Project
 └── ComicPages
       └── ComicBubbles
```

---

## ComicBubble

Đại diện cho vùng thoại/text được phát hiện từ Comic Page.

Lưu:

* Bounding box
* OCR text
* Rewritten text
* Confidence
* Detection metadata
* Processing status

Bounding box:

```text
x
y
width
height
```

User phải có khả năng chỉnh sửa OCR hoặc rewritten text trước các bước downstream.

---

## Character

Đại diện cho nhân vật trong Story hoặc Comic-derived workflow.

Lưu:

* Name
* Description
* Reference image
* Voice profile
* Metadata
* User overrides

Relationship:

```text
Project
 └── Characters
```

Character có thể được sử dụng bởi nhiều Scene.

---

## VoiceProfile

Đại diện cho cấu hình voice của Character hoặc narrator.

Có thể chứa:

* Provider
* Provider voice ID
* Voice settings
* Language
* Stability / style parameters
* User overrides

Không nên hard-code provider-specific voice ID trực tiếp vào business logic.

---

## Scene

Scene là đơn vị chính để tạo Timeline.

Scene có thể chứa:

* Order
* Dialogue
* Narration
* Image prompt
* Camera rule
* Animation rule
* Duration
* Character references
* Generated image
* Generated audio
* Processing status

Scene là cầu nối giữa:

```text
Story / Comic
      ↓
Scene
      ↓
Timeline
      ↓
Render
```

---

## ProjectMedia

Quản lý media cấp Project.

Ví dụ:

* Final video
* Preview video
* Thumbnail
* Exported timeline

---

## AIJob

Theo dõi từng tác vụ AI hoặc external processing.

Ví dụ:

```text
OpenAI
Gemini
ElevenLabs
Fal.ai
Creatomate
Shotstack
```

AIJob cần lưu:

* Provider
* Operation
* Status
* Input metadata
* Output metadata
* External request ID
* Retry count
* Duration
* Error message
* API cost
* Timestamps

Mục tiêu:

```text
Observability
Debugging
Cost tracking
Retry management
```

---

## RenderJob

Theo dõi riêng video rendering lifecycle.

Có thể lưu:

* Provider
* External render ID
* Status
* Timeline payload/version
* Output URL
* Error
* Started/finished timestamps

RenderJob nên độc lập với AIJob vì rendering có lifecycle và webhook semantics khác.

---

# 8. Data Flow

## Flow A — Comic

```text
Upload
  ↓
Project
  ↓
ComicPage
  ↓
ComicBubble
  ↓
OCR
  ↓
Optional Rewrite
  ↓
User Review / Override
  ↓
Scene
  ↓
TTS + Media Generation
  ↓
Timeline
  ↓
Render
```

## Flow B — Story

```text
Story Input
  ↓
Story Analysis
  ↓
Characters
  ↓
Scenes
  ↓
User Review / Override
  ↓
Voice Assignment
  ↓
Image + TTS Generation
  ↓
Timeline
  ↓
Render
```

---

# 9. Queue Architecture

Long-running operations không được thực hiện trực tiếp trong HTTP request.

Laravel Queue được sử dụng cho:

* PDF processing
* Image preprocessing
* OCR
* AI analysis
* TTS
* Image generation
* Timeline construction
* Video rendering
* Cleanup

---

# 10. Pipeline Orchestration

Hệ thống sử dụng kết hợp:

```text
Bus::chain()
Bus::batch()
```

### Chain

Dùng khi stage B phụ thuộc hoàn toàn vào stage A.

Ví dụ:

```text
Analyze Story
    ↓
Create Scenes
    ↓
Build Timeline
```

### Batch

Dùng khi nhiều item có thể xử lý song song.

Ví dụ:

```text
ComicPage 1 ──┐
ComicPage 2 ──┤
ComicPage 3 ──┤──→ Fan-In
ComicPage 4 ──┤
ComicPage 5 ──┘
```

---

# 11. Stage Architecture

## Stage 1 — Input Processing

### Comic

```text
ProcessComicPdfJob
        ↓
Detect page count
        ↓
Bus::batch(
    ProcessComicPageJob[]
)
```

Mỗi page được xử lý độc lập.

Có thể bao gồm:

* Page extraction
* Image preprocessing
* Panel detection
* OCR
* Bubble detection

OCR sử dụng Tesseract hoặc provider tương đương.

---

### Story

```text
AnalyzeStoryJob
        ↓
Structured Story Analysis
        ↓
Characters
Scenes
Dialogues
Image Prompts
```

LLM output phải được validate bằng structured schema trước khi persist.

---

# 12. Stage 2 — User Review / Override

Intermediate results phải có thể chỉnh sửa trước khi tiếp tục pipeline.

User có thể chỉnh:

### Comic

* OCR text
* Bubble position
* Rewritten text
* Character mapping

### Story

* Character names
* Character descriptions
* Scene boundaries
* Dialogue
* Image prompts
* Voice assignments

Điều này là yêu cầu quan trọng để tránh việc AI output trở thành immutable pipeline data.

---

# 13. Stage 3 — Character & Voice Alignment

Character được gán:

```text
Character
    ↓
VoiceProfile
    ↓
Provider Voice ID
```

User có thể override automatic voice assignment.

Narrator cũng nên được hỗ trợ như một voice role riêng thay vì ép mọi audio phải thuộc Character.

---

# 14. Stage 4 — Parallel Asset Generation

Mỗi Scene có thể trigger nhiều independent jobs.

```text
                 ┌── TTS Generation
Scene ───────────┼── Image Generation
                 └── Other Asset Generation
```

Các task độc lập nên chạy parallel thông qua `Bus::batch()` hoặc orchestration tương đương.

### TTS

```text
Dialogue
   ↓
ElevenLabs
   ↓
MP3
   ↓
Storage
   ↓
Measure actual duration
   ↓
Scene.duration
```

Duration thực tế của audio là source of truth cho dialogue-driven scenes khi phù hợp.

### Image Generation

```text
Image Prompt
    ↓
Image Provider
    ↓
Generated Image
    ↓
Storage
    ↓
Scene Asset
```

Provider có thể thay đổi:

```text
Fal.ai
Other image generation providers
```

Provider integration không nên được hard-code vào Scene domain model.

---

# 15. Stage 5 — Timeline Construction

Sau khi required assets của Scene đã sẵn sàng:

```text
Scenes
 ├── Image
 ├── Audio
 ├── Duration
 ├── Camera Rule
 └── Animation Rule
          ↓
BuildVideoTimelineJob
          ↓
Provider-specific Timeline JSON
```

Timeline builder phải có abstraction khỏi rendering provider.

Ví dụ:

```text
Domain Timeline
      ↓
Provider Adapter
      ↓
Creatomate / Shotstack
```

Điều này cho phép thay đổi render provider mà không thay đổi domain Scene model.

---

# 16. Stage 6 — Rendering

Rendering là asynchronous.

```text
Build Timeline
    ↓
Create RenderJob
    ↓
Submit to Render Provider
    ↓
Store External Render ID
    ↓
Wait for Webhook
```

Không nên giữ HTTP request chờ render hoàn thành.

---

# 17. Webhook Architecture

Render provider callback:

```text
POST /api/webhooks/video-rendered
```

Webhook handler:

```text
Webhook
   ↓
Validate Signature
   ↓
Find RenderJob
   ↓
Idempotency Check
   ↓
Update RenderJob
   ↓
Store Final Media
   ↓
Update Project / Scene State
   ↓
Notify User
```

Webhook processing phải idempotent vì external provider có thể gửi duplicate callbacks.

---

# 18. Frontend Architecture

Frontend:

```text
Laravel
   ↓
Inertia
   ↓
React
```

Nguyên tắc:

> Dumb UI — Smart Backend

React không được duplicate:

* State machine rules
* AI business rules
* Pipeline orchestration
* Provider logic
* Permission rules

---

# 19. Studio UI

Primary workspace:

```text
/projects/{project}/studio
```

Có thể bao gồm:

### Timeline

Hiển thị:

```text
Scene 1 | Scene 2 | Scene 3 | Scene 4
```

### Scene Editor

Cho phép edit:

* Dialogue
* Narration
* Image prompt
* Camera rule
* Duration override
* Character assignment
* Voice assignment

### Asset Preview

* Image preview
* Audio playback
* Video preview

### Comic Editor

Flow A có thể hiển thị:

* Page image
* Panel regions
* Bubble bounding boxes
* OCR text

---

# 20. Frontend Processing Status

Frontend cần theo dõi backend processing state.

Có thể sử dụng:

```text
Polling
```

hoặc:

```text
WebSocket / Laravel broadcasting
```

### Initial implementation

Polling có thể được sử dụng cho MVP:

```text
GET /projects/{id}/status
```

Khoảng thời gian polling khoảng:

```text
1.5s – 2s
```

chỉ khi Project/Job đang processing.

Khi hệ thống cần realtime tốt hơn hoặc polling tạo tải đáng kể, có thể chuyển sang broadcasting/WebSocket.

---

# 21. Progress Tracking

Progress không nên chỉ dựa vào một con số giả lập.

Backend nên expose processing information dựa trên:

* Project status
* Scene statuses
* Queue batch progress
* Completed / total jobs
* Current stage

Ví dụ:

```text
Stage: Media Generation

Scenes:
8 / 12 completed

TTS:
12 / 12

Images:
7 / 12
```

Frontend chuyển dữ liệu này thành progress UI.

---

# 22. Storage Architecture

Storage được chia thành:

```text
Temporary Local Storage
        +
Persistent Object Storage
```

## Temporary Local

Dùng cho:

* Raw processing workspace
* PDF page extraction
* OCR temporary files
* Image conversion
* Intermediate processing artifacts

Các file này không nên tồn tại lâu hơn lifecycle cần thiết.

---

## Persistent Object Storage

S3-compatible storage được sử dụng khi phù hợp.

Ví dụ:

```text
raw/
assets/
    audio/
    images/
renders/
```

Conceptual structure:

```text
raw/{project_id}/...
assets/audio/{scene_id}.mp3
assets/images/{scene_id}.png
renders/{project_id}/...
```

Có thể sử dụng:

* AWS S3
* Cloudflare R2
* LocalStack trong local development

---

# 23. Public vs Private Storage

Không phải mọi media đều nên public.

### Private

Nên dùng cho:

* Original user uploads
* Raw comic/PDF
* Intermediate assets
* Sensitive project data

Truy cập thông qua:

```text
Signed URL
```

### Public / CDN

Có thể dùng cho:

* Final published video
* Public assets
* Thumbnails
* CDN-cacheable content

Quyết định public/private phải dựa trên quyền truy cập của Project và privacy requirements.

---

# 24. Garbage Collection

Temporary assets phải được cleanup.

Ví dụ:

```text
PDF
 ↓
Page extraction
 ↓
OCR
 ↓
Temporary page image
 ↓
Delete
```

Draft upload không được hoàn tất có thể có TTL.

Ví dụ:

```text
Unclaimed temporary files
        ↓
TTL
        ↓
Garbage Collection Job
```

TTL cụ thể cần được cấu hình theo product/storage requirements thay vì hard-code thành business rule.

---

# 25. Database Optimization

MySQL là primary relational database cho application metadata.

Các nguyên tắc:

### Bulk Insert

Khi OCR tạo nhiều bubbles:

```php
ComicBubble::insert($rows);
```

thay vì insert từng record.

### Eager Loading

Studio page phải tránh N+1:

```php
with([
    'scenes',
    'media',
    'characters',
])
```

### Indexing

Các bảng processing cần index cho:

* `project_id`
* `scene_id`
* `status`
* `batch_id`
* `external_id`
* `created_at`

Index cụ thể cần được xác nhận bằng query patterns thực tế.

---

# 26. Queue Strategy

Có thể phân chia queue:

```text
default
ocr
media
render
```

Ví dụ:

```text
ocr
 └── OCR / image processing

media
 ├── TTS
 └── Image generation

render
 └── Video rendering

default
 └── General application jobs
```

Worker concurrency phải được cấu hình dựa trên:

* CPU
* RAM
* Provider rate limits
* Job duration
* Deployment environment

Không nên giả định một concurrency cố định cho production.

---

# 27. Rate Limiting

Third-party providers có thể trả:

```text
429 Too Many Requests
```

Do đó cần:

* Rate limiting
* Backoff
* Retry
* Provider-specific concurrency limits

Provider API calls phải có retry strategy phù hợp.

Ví dụ:

```text
Attempt 1
   ↓
2 sec
   ↓
Attempt 2
   ↓
10 sec
   ↓
Attempt 3
   ↓
30 sec
```

Các giá trị cụ thể cần điều chỉnh theo provider API semantics.

---

# 28. Fault Tolerance

Mỗi processing job nên có:

* Retry policy
* Backoff
* Timeout
* Failure logging
* Idempotency strategy

Một Scene failure không nên tự động khiến toàn bộ Project chạy lại.

Ví dụ:

```text
Project
 ├── Scene A → completed
 ├── Scene B → failed
 ├── Scene C → completed
 └── Scene D → completed
```

User có thể retry Scene B.

---

# 29. Idempotency

Các external operations phải tránh duplicate processing.

Ví dụ:

```text
Generate TTS
Generate Image
Submit Render
Webhook
```

Mỗi operation nên có external/request identifier hoặc deterministic job key khi phù hợp.

Đặc biệt Webhook phải xử lý duplicate callback an toàn.

---

# 30. Provider Abstraction

AI providers không được trở thành domain dependency trực tiếp.

Conceptual architecture:

```text
Domain
  ↓
Application Service
  ↓
Provider Interface
  ↓
Provider Adapter
```

Ví dụ:

```text
TextToSpeechProvider
 ├── ElevenLabsAdapter
 └── OtherProviderAdapter

ImageGenerationProvider
 ├── FalAdapter
 └── OtherProviderAdapter

VideoRenderer
 ├── CreatomateAdapter
 └── ShotstackAdapter
```

Điều này cho phép thay provider mà không rewrite domain logic.

---

# 31. Observability

Hệ thống cần tracking:

### Processing

* Job status
* Duration
* Retry count
* Failure reason

### AI

* Provider
* Model
* Request ID
* Token/usage metadata nếu provider hỗ trợ
* API cost

### Rendering

* Render ID
* Render duration
* Provider status
* Output URL

Mục tiêu:

```text
Debugging
Monitoring
Cost Control
Operational Visibility
```

---

# 32. State Transition Rules

State transitions phải được centralized và validated.

Không nên để frontend hoặc arbitrary controller code tự sửa:

```text
project.status
scene.status
```

mà không đi qua transition logic.

Conceptual:

```text
ProjectStateMachine
SceneStateMachine
```

Mỗi transition cần kiểm tra:

* Current state
* Allowed next state
* Required dependencies
* Authorization
* Processing result

Điều này đặc biệt quan trọng trong môi trường Queue vì nhiều workers có thể cập nhật cùng một Project/Scene.

---

# 33. User Editing vs Generated Data

Generated data và user override phải được phân biệt về mặt domain.

Ví dụ:

```text
AI image prompt
User-edited image prompt
```

hoặc:

```text
AI dialogue
User-edited dialogue
```

Hệ thống phải đảm bảo một retry AI không vô tình overwrite dữ liệu mà User đã chỉnh sửa.

Đây là một trong những nguyên tắc quan trọng nhất của editor workflow.

---

# 34. Project Lifecycle

Conceptual lifecycle:

```text
                ┌───────────────┐
                │     draft     │
                └───────┬───────┘
                        ↓
                ┌───────────────┐
                │   processing  │
                └───────┬───────┘
                        ↓
                ┌───────────────┐
                │    analyzed   │
                └───────┬───────┘
                        ↓
                ┌───────────────┐
                │  assets_ready │
                └───────┬───────┘
                        ↓
                ┌───────────────┐
                │   rendering   │
                └───────┬───────┘
                        ↓
                ┌───────────────┐
                │   completed   │
                └───────────────┘

Any processing stage
        │
        └──────────→ failed
```

`failed` phải hỗ trợ recovery/resume thay vì mặc định restart toàn bộ workflow.

---

# 35. Current Architectural Direction

Architecture hiện tại được định hướng theo:

```text
Laravel Modular Monolith
        +
MySQL
        +
S3-compatible Object Storage
        +
Laravel Queue
        +
External AI Providers
        +
External Video Renderer
        +
Inertia + React
```

Chưa có yêu cầu bắt buộc phải tách thành microservices.

Ưu tiên hiện tại là:

```text
Clear Domain Boundaries
Reliable Async Processing
Editable Intermediate State
Provider Abstraction
Observability
Recoverability
```

Microservices chỉ nên được cân nhắc khi scale hoặc organizational requirements thực sự yêu cầu.

---

# 36. Non-Goals

Các vấn đề sau chưa được coi là requirement mặc định:

* Microservices architecture
* Kubernetes
* Distributed database
* Custom rendering engine
* Custom GPU inference infrastructure
* Real-time WebSocket bắt buộc ngay từ MVP
* Multi-region deployment
* Active-active infrastructure
* Unlimited provider abstraction
* Premature high-scale optimization

Các quyết định này phải được đánh giá lại khi có yêu cầu scale thực tế.

---

# 37. Important Architectural Constraints

Các constraint hiện tại:

1. Laravel là backend/domain owner.
2. React không duplicate business rules.
3. Long-running processing phải asynchronous.
4. Project và Scene cần state machine rõ ràng.
5. Intermediate AI results phải editable.
6. User overrides không được bị AI retry overwrite ngoài ý muốn.
7. Scene-level failure phải có khả năng retry độc lập khi dependency cho phép.
8. External provider operations phải có retry/idempotency.
9. Webhooks phải idempotent.
10. Media storage phải phân biệt temporary/private/public lifecycle.
11. Provider integrations phải được abstraction.
12. Rendering phải asynchronous.
13. Database chỉ lưu metadata/reference, không lưu large binary media.
14. Production concurrency và rate limits phải dựa trên giới hạn thực tế của infrastructure/provider.

---

# 38. Architecture Decision Priorities

Khi có conflict giữa các architectural choices, ưu tiên:

```text
Correctness
    ↓
Data Integrity
    ↓
Recoverability
    ↓
User Editability
    ↓
Observability
    ↓
Maintainability
    ↓
Scalability
    ↓
Performance Optimization
```

Không đánh đổi correctness hoặc data integrity chỉ để đạt performance sớm.

---

# 39. Expected Development Workflow

Feature mới nên đi qua:

```text
Requirement
    ↓
Context / Existing System Review
    ↓
Understanding
    ↓
Architecture / Design
    ↓
Decision Log
    ↓
Implementation Plan
    ↓
Implementation
    ↓
Testing
    ↓
Validation
```

Không nên bắt đầu implementation trước khi các state transitions, dependencies và user-editable boundaries liên quan đã được xác định.

---

# 40. Current Open Architectural Areas

Các vấn đề sau vẫn cần được quyết định khi feature tương ứng được triển khai:

* Exact database schema
* Exact state transition matrix
* Scene dependency graph
* Batch/Chain orchestration implementation
* Provider selection/fallback strategy
* WebSocket vs polling production strategy
* Exact S3/R2 bucket policy
* Media retention policy
* Authorization model
* Project versioning
* Timeline versioning
* Concurrent user editing
* Cost limits / quota system
* Provider timeout policies
* Render retry semantics
* Production infrastructure topology

Những vấn đề trên không nên tự động được giả định là đã quyết định chỉ dựa trên architecture context này.
