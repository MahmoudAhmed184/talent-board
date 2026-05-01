## Job Board — Detailed Project Plan

This plan is organized around four vertical slices so each developer owns a coherent domain with minimal cross-slice blocking:

- `D1` — Identity and platform shell
- `D2` — Jobs discovery and governance
- `D3` — Employer operations and live status
- `D4` — Candidate lifecycle and file pipeline

Detailed task-level ownership lives in [docs/tasks.md](./tasks.md).

### Sprint 1 — Slice Foundations
**Goal:** Start all four slices in parallel with enough local ownership that no developer is blocked on another developer’s Sprint 1 work.

**Slice deliverables:**
- [ ] `[D1]` Create the auth data baseline, seed representative users, wire `/api/v1` routing and container bootstrapping, enable Sanctum or CSRF plumbing, and scaffold the Vue shell with candidate and employer layouts.
- [ ] `[D2]` Build `Category`, `Location`, and `JobListing` core models plus repositories, initial factories or seeders, and the first public job UI primitives.
- [ ] `[D3]` Create `EmployerProfile`, establish employer form abstractions, and add validation or accessibility groundwork on shared public and auth surfaces.
- [ ] `[D4]` Create `CandidateProfile`, `Resume`, and `Application` core models plus candidate or application composable groundwork and seed support.

**Key handoffs:**
- `D2` freezes the jobs core model, repository, and service contract needed by `D3` employer CRUD at the end of Sprint 1.

**Definition of Done:**
- Auth bootstrap, route grouping, and shared frontend shell foundations are in place.
- Job, employer, candidate, resume, and application core data models exist and migrate cleanly.
- All four developers can start Sprint 2 on their own slice without waiting on unfinished Sprint 1 ownership transfers.

### Sprint 2 — Contracts & Feature Surfaces
**Goal:** Finish the core API contracts and land the first full user-facing surfaces for auth, discovery, employer operations, and candidate profile infrastructure.

**Slice deliverables:**
- [ ] `[D1]` Finish auth services, controllers, policies, shared factories, public shell, auth pages, and the admin layout shell.
- [ ] `[D2]` Deliver public jobs list, detail, search, lookup, and cache contracts, plus the public job pages and search stores that consume them.
- [ ] `[D3]` Deliver employer job create, update, show, and delete endpoints, employer state management, dashboard UX, and create or edit pages.
- [ ] `[D4]` Deliver candidate profile service contracts, resume and application repositories, notification persistence, and the candidate profile store.

**Key handoffs:**
- `D1` freezes the authentication and shell contract after Sprint 2.
- `D2` freezes the public jobs and search contract after Sprint 2.
- `D3` freezes the employer job-management contract after Sprint 2.
- `D4` freezes the notification payload or schema needed for later live-status work at the end of Sprint 2.

**Definition of Done:**
- Auth flows are stable for every protected slice.
- Public jobs discovery is contract-stable and consumable end to end.
- Employer CRUD works against the D2 jobs core without ownership drift.
- Candidate profile and application persistence foundations are ready for Sprint 3 feature completion.

### Sprint 3 — Candidate Lifecycle & Decision Flows
**Goal:** Deliver the candidate workflow end to end while completing employer review, decision handling, and the shared UI infrastructure that later dashboards depend on.

**Slice deliverables:**
- [ ] `[D1]` Deliver shared UI primitives, toast infrastructure, and reusable pagination helpers for downstream screens.
- [ ] `[D2]` Start admin moderation service, controller, store, and composable work so Sprint 4 UI can land against stable moderation contracts.
- [ ] `[D3]` Deliver employer application review, accept or reject transitions, status badges, and public loading or responsive polish.
- [ ] `[D4]` Deliver file storage, resume or application endpoints, queue jobs, candidate profile or application pages, and the public apply modal.

**Key handoffs:**
- `D4` freezes the candidate profile and application contract after Sprint 3.
- `D3` freezes the employer decision and status contract after Sprint 3.
- `D2` publishes the admin moderation contract before Sprint 4 UI completion.

**Definition of Done:**
- Candidates can maintain profile data, upload resumes, apply from job detail, view history, and cancel eligible applications.
- Employers can review applications for owned jobs and change status through stable endpoints and UI.
- Shared UI primitives cover the common modal, form, toast, and pagination needs used by the remaining dashboards.

### Sprint 4 — Governance, Real-time & Release Hardening
**Goal:** Finish moderation, real-time status updates, branding upload integration, and final regression coverage for release readiness.

**Slice deliverables:**
- [ ] `[D1]` Run the PHP Attribute audit, add shared backend test helpers, and support final integration cleanup on shared foundations.
- [ ] `[D2]` Finish admin moderation surfaces, platform activity endpoints, cache invalidation, route-contract docs, and regression fixes for discovery or governance domains.
- [ ] `[D3]` Deliver Reverb events, channels, auth wiring, `useEcho`, live status UX, and auth or public-discovery regression coverage.
- [ ] `[D4]` Deliver employer branding-upload contracts, queue-backed logo processing, file or notification integration tests, the candidate applications page, and dashboard regression coverage.

**Key handoffs:**
- `D4` publishes branding-upload payloads before employer surface integration.
- `D3` publishes the real-time channel and payload contract before final QA.

**Definition of Done:**
- Admins can approve or reject job listings and inspect platform activity from the SPA.
- Candidate and employer dashboards receive real-time application status updates without polling.
- Branding upload, queue-driven file processing, and notification persistence are verified through integration tests.
- Accessibility, responsive layout, regression coverage, and route-contract documentation are complete for MVP signoff.

### Technology Decisions Log

- **PHP Attribute style on all Eloquent models:** Keeps schema and serialization metadata colocated on the model class and satisfies the hard project constraint against legacy property-array metadata.
- **`clone($model, [...])` syntax:** Establishes one approved cloning form for service-layer examples and avoids unsupported syntax variants.
- **Vue `3.5.33` stable only, not `3.6` beta:** Keeps the frontend on a stable runtime with predictable behavior and avoids beta-only API drift.
- **Reactive Props Destructuring via `const { x } = defineProps<>()`:** Aligns component code with the approved Vue `3.5` pattern and improves type-local readability.
- **`useTemplateRef()` instead of `ref()` for DOM refs:** Produces clearer DOM-ref intent and preserves the agreed project convention for template element access.
- **JSON:API for paginated collections and `JsonResource` for single items:** Gives the frontend stable pagination metadata while keeping single-resource payloads straightforward and idiomatic for Laravel.
- **Reverb with database driver and no Redis in development:** Reduces local infrastructure overhead while preserving the production architecture for real-time delivery.
- **Vue Router `5` file-based routing with `_parent.vue` for nested layouts:** Minimizes manual route maintenance and fits the role-separated dashboard structure.
- **Pinia `3` setup-store syntax only:** Keeps state modules composition-friendly, consistent, and aligned with the approved frontend coding style.

### Risk Register

| Risk | Likelihood | Impact | Mitigation |
| --- | --- | --- | --- |
| Re-approval rules for employer edits may be implemented inconsistently between backend and frontend states. | Medium | High | Treat moderation status as backend-owned, expose it explicitly in every employer listing payload, and cover edit-after-approval behavior with feature tests. |
| S3-compatible upload configuration may fail late in integration, blocking resume and logo workflows. | Medium | High | Validate the storage driver in Sprint 3 with real uploads, keep upload services isolated, and add fallback QA steps before UI completion. |
| API contract drift between slices may slow Sprint 3 and Sprint 4 integration. | High | High | Freeze contracts by domain at sprint boundaries, publish example payloads, and review response shapes during integration checkpoints. |
| Public search performance may degrade once multiple filters are combined against real data. | Medium | Medium | Index filter columns early, keep collection endpoints paginated, and test worst-case filter combinations with seeded datasets in Sprint 2. |
| Authorization gaps may expose employer or candidate data across ownership boundaries. | Medium | High | Use Laravel Policies on every protected domain action and add negative-path feature tests for cross-role and cross-owner access. |
| Queue workers or Reverb may not be running in shared development environments, masking real-time failures until late QA. | Medium | High | Include environment checklists in Sprint 4, add observable health checks, and validate queue plus Reverb setup before polishing work starts. |
| The notification-persistence to live-broadcast handoff may land late and block final real-time integration. | Medium | Medium | Freeze the notification schema at the end of Sprint 2, test the event payload contract in isolation, and schedule the D3 plus D4 integration check before Sprint 4 implementation. |
| Final accessibility and regression work may be compressed by late integration defects. | Medium | Medium | Reserve explicit Sprint 4 capacity for QA and a11y fixes rather than assuming they can be absorbed into feature-complete tasks. |
