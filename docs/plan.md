## Job Board — Detailed Project Plan

### Sprint 1 — Foundation & Auth
**Goal:** Establish the project skeleton, core data model, and working role-aware authentication across the API and SPA.

**Backend deliverables:**
- [ ] `[B1]` Create `User`, `CandidateProfile`, and `EmployerProfile` models, migrations, and seed data using PHP Attributes.
- [ ] `[B1]` Implement `UserRepository`, `AuthService`, `RegisterRequest`, `LoginRequest`, `AuthController`, and auth resources.
- [ ] `[B1]` Add `UserPolicy` and ownership or role authorization tests for auth entry points.
- [ ] `[B2]` Organize `/api/v1` routes, middleware groups, and `AppServiceProvider` repository bindings.
- [ ] `[B2]` Register `PreventRequestForgery`, Sanctum support plumbing, and shared factories for auth-related entities.

**Frontend deliverables:**
- [ ] `[F1]` Scaffold the Vue `3.5.33` + TypeScript + Vite `8` application with Vue Router `5.0.6` file-based routing and Pinia `3`.
- [ ] `[F1]` Build `http.ts`, `useAuthStore`, `useAuth`, `AuthLayout.vue`, and login or register pages.
- [ ] `[F1]` Add baseline shared UI primitives needed by auth forms.
- [ ] `[F2]` Create `_parent.vue` layout shells for candidate, employer, and admin route groups.
- [ ] `[F2]` Add shared toast and pagination infrastructure that later sprints can reuse.

**Definition of Done:**
- Auth-related migrations run cleanly on a fresh database.
- Candidate and employer users can register, log in, refresh the SPA, and log out.
- Pre-provisioned admin users can log in through the same auth flow.
- Protected API routes reject unauthenticated requests and unauthorized roles correctly.
- Frontend auth state survives reload through the sanctioned Sanctum flow.
- Unit or feature tests cover registration, login, logout, and auth-policy boundaries.

### Sprint 2 — Job Listings
**Goal:** Deliver employer job CRUD and public job discovery with filterable, paginated listing APIs and SPA screens.

**Backend deliverables:**
- [ ] `[B1]` Create `Category`, `Location`, and `JobListing` models, migrations, repositories, and service-layer business rules.
- [ ] `[B1]` Implement employer-facing job create, update, view, and remove endpoints with policy enforcement.
- [ ] `[B2]` Implement public job list and job detail endpoints using JSON:API pagination for collections and `JsonResource` for single records.
- [ ] `[B2]` Build the search service for keyword, location, category, experience level, salary, date, and work-type filters.
- [ ] `[B2]` Add listing cache freshness handling using `Cache::touch()` plus public lookup endpoints for categories and locations.

**Frontend deliverables:**
- [ ] `[F1]` Build the public jobs index page, job detail page, filter bar, pagination flow, and job feature store or composables.
- [ ] `[F1]` Add public loading, empty, validation, and fetch-error states for job discovery screens.
- [ ] `[F2]` Build the employer dashboard shell and the employer job create or edit pages.
- [ ] `[F2]` Implement employer job form state management and wire the job CRUD contract.
- [ ] `[F2]` Prepare company branding UI hooks so logo upload can be integrated without page rewrites in Sprint 3.

**Definition of Done:**
- Employers can create, edit, and remove their own job listings through the API and SPA.
- New or edited listings persist with the expected moderation status and are visible in the employer dashboard.
- Public users can browse approved job listings and filter them by every required criterion.
- Pagination metadata is consistent between backend and frontend implementations.
- Feature tests cover listing CRUD ownership rules and public filtering behavior.

### Sprint 3 — Applications, Profiles & File Uploads
**Goal:** Deliver the candidate profile workflow, resume and logo uploads, application submission, and employer review of candidate submissions.

**Backend deliverables:**
- [ ] `[B1]` Create `Resume` and `Application` models, migrations, repositories, resources, and service-layer status rules.
- [ ] `[B1]` Implement candidate profile endpoints and S3-compatible upload services for resumes and company logos.
- [ ] `[B1]` Implement application submission, candidate-owned history, and candidate cancel flows.
- [ ] `[B2]` Implement queued resume and logo processing plus employer-facing application notification persistence.
- [ ] `[B2]` Add factories, seeders, and integration tests for applications, resumes, notifications, and queue-driven file handling.

**Frontend deliverables:**
- [ ] `[F2]` Build candidate profile, resume upload, and candidate application-history pages.
- [ ] `[F2]` Build employer application-review views and shared application status display components.
- [ ] `[F1]` Integrate application submission from the public job detail page using the finalized application contract.
- [ ] `[F1]` Harden shared stores, layouts, and error handling for authenticated candidate and employer flows.
- [ ] `[F2]` Integrate logo upload and resume upload components into the relevant dashboard pages.

**Definition of Done:**
- Candidates can edit profile details, upload a resume, set a default resume, and retrieve application history.
- Candidates can apply to an approved, open job with either a resume reference or contact forwarding.
- Employers can review applications submitted to their own listings and inspect resume or contact data.
- Historical application records preserve the submitted contact snapshot and linked resume reference.
- Queue workers process file-related side effects without blocking interactive application requests.

### Sprint 4 — Admin Panel, Real-time & Polish
**Goal:** Finalize moderation workflows, real-time status updates, and end-to-end quality gates for the MVP release.

**Backend deliverables:**
- [ ] `[B2]` Implement admin moderation endpoints, platform activity queries, and approve or reject business logic.
- [ ] `[B2]` Configure `ApplicationStatusChanged` broadcasting, private channel authorization, and final cache invalidation hooks.
- [ ] `[B1]` Implement employer accept or reject application transitions with policy coverage and audit timestamps.
- [ ] `[B1]` Close QA findings across auth, listings, profiles, file uploads, and applications.
- [ ] `[B2]` Complete API contract verification and backend regression checks for all `/api/v1` domains.

**Frontend deliverables:**
- [ ] `[F2]` Build the admin jobs moderation page and connect it to pending and all-job endpoints.
- [ ] `[F2]` Add `useEcho`, role-scoped live status updates, and protected dashboard navigation.
- [ ] `[F1]` Finish responsive and accessibility polish on public or auth screens and verify WCAG 2.1 AA basics.
- [ ] `[F1]` Add regression tests for public job discovery and auth workflows.
- [ ] `[F2]` Add regression tests for employer, candidate, and admin flows after real-time integration.

**Definition of Done:**
- Admins can approve or reject pending job listings from the SPA, and approved jobs become publicly searchable.
- Employers can accept or reject applications, and status changes are persisted correctly.
- Candidate and employer dashboards receive Reverb updates for application status changes without polling.
- Accessibility, responsive layout, and protected-route checks are complete for all primary user journeys.
- Cross-role regression tests pass for auth, listings, applications, moderation, uploads, and real-time behavior.

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
| API contract drift between backend resources and frontend stores may slow Sprint 3 and Sprint 4 integration. | High | High | Freeze contracts by domain at sprint boundaries, publish example payloads, and review response shapes during integration checkpoints. |
| Public search performance may degrade once multiple filters are combined against real data. | Medium | Medium | Index filter columns early, keep collection endpoints paginated, and test worst-case filter combinations with seeded datasets in Sprint 2. |
| Authorization gaps may expose employer or candidate data across ownership boundaries. | Medium | High | Use Laravel Policies on every protected domain action and add negative-path feature tests for cross-role and cross-owner access. |
| Queue workers or Reverb may not be running in shared development environments, masking real-time failures until late QA. | Medium | High | Include environment checklists in Sprint 4, add observable health checks, and validate queue plus Reverb setup before polishing work starts. |
| Equal team progress may be disrupted if one backend contract lands late and blocks both frontend owners. | Medium | Medium | Keep API domains isolated by owner, prioritize contract-first reviews, and use lookup or mock payloads where short-term decoupling is possible. |
| Final accessibility and regression work may be compressed by late integration defects. | Medium | Medium | Reserve explicit Sprint 4 capacity for QA and a11y fixes rather than assuming they can be absorbed into feature-complete tasks. |
