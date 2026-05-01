## Job Board — Task Distribution

### B1 — Backend Developer 1

#### Sprint 1 tasks

| ID | Task | Est. Hours | Acceptance Criterion |
| --- | --- | ---: | --- |
| B1-01 | Create `User` model with PHP Attributes and role enum | 4 | The `User` model uses Attribute-based metadata and persists candidate, employer, and admin roles required by auth flows. |
| B1-02 | Create `CandidateProfile` model and migration | 5 | Candidate profile data can be created and related to a user through a migrated table with the required ownership columns. |
| B1-03 | Create `EmployerProfile` model and migration | 5 | Employer profile data can be created and related to a user through a migrated table that supports company identity fields. |
| B1-04 | Create auth seeders for admin and sample role accounts | 4 | Seeder execution creates at least one admin account and representative candidate and employer users for local testing. |
| B1-05 | Implement `UserRepositoryInterface` and `EloquentUserRepository` | 4 | Auth and profile services can resolve user persistence through repository abstractions instead of direct controller queries. |
| B1-06 | Implement `AuthService` register, login, logout, and current-user flows | 6 | Auth business logic executes through the service layer and returns consistent user context for register, login, logout, and `me` endpoints. |
| B1-07 | Implement `RegisterRequest`, `LoginRequest`, `AuthController`, `UserResource`, `EmployerResource`, and `UserPolicy` | 6 | Registration and login endpoints validate input, return resources, and enforce role or ownership access through policy rules. |

#### Sprint 2 tasks

| ID | Task | Est. Hours | Acceptance Criterion |
| --- | --- | ---: | --- |
| B1-08 | Create `Category` model, migration, and seeder hooks | 4 | Job listings can reference seeded categories through a migrated relational table. |
| B1-09 | Create `Location` model, migration, and seeder hooks | 4 | Job listings can reference seeded locations through a migrated relational table. |
| B1-10 | Create `JobListing` model and migration with required business and moderation fields | 6 | The `job_listings` table stores all required listing attributes, moderation state, and publication timestamps. |
| B1-11 | Implement `JobListingRepositoryInterface` and `EloquentJobListingRepository` | 4 | Listing services and controllers can read and write employer-owned jobs through repository abstractions. |
| B1-12 | Implement `JobService` for create, update, deactivate, and moderation-reset rules | 6 | Job business rules execute in the service layer and consistently set listing state for create, edit, and deactivate flows. |
| B1-13 | Implement `StoreJobListingRequest` and `JobListingController@store` | 5 | An employer can submit a valid create payload and receive a persisted pending listing through the controller-service-repository path. |
| B1-14 | Implement `UpdateJobListingRequest`, `JobListingController@update`, `destroy`, and employer-owned `show` | 6 | Listing owners can view, edit, and remove their own jobs while non-owners are blocked by policy checks. |

#### Sprint 3 tasks

| ID | Task | Est. Hours | Acceptance Criterion |
| --- | --- | ---: | --- |
| B1-15 | Create `Resume` model and migration | 5 | Resume records can store candidate ownership plus durable file metadata required for later application use. |
| B1-16 | Implement `CandidateProfileService`, `UpdateCandidateProfileRequest`, and `CandidateProfileController` | 6 | Candidates can retrieve and update profile summary, contact details, skills, and default resume through a service-backed endpoint. |
| B1-17 | Implement `ResumeRepositoryInterface`, `EloquentResumeRepository`, and `ResumeResource` | 4 | Resume listing and upload flows can resolve resume data through repositories and stable API resources. |
| B1-18 | Create `Application` model and migration | 6 | Application records can persist job ownership, candidate ownership, resume links, contact snapshots, and status timestamps. |
| B1-19 | Implement `ApplicationRepositoryInterface`, `EloquentApplicationRepository`, `ApplicationResource`, and `ApplicationService` for submit or cancel | 6 | Application submission and cancellation run through a repository-backed service layer and return consistent serialized data. |
| B1-20 | Implement `FileStorageService`, `ResumeController`, and `ApplicationController` submit, history, and cancel endpoints | 7 | Candidates can upload resumes, submit applications, and retrieve or cancel owned applications through production-ready endpoints. |

#### Sprint 4 tasks

| ID | Task | Est. Hours | Acceptance Criterion |
| --- | --- | ---: | --- |
| B1-21 | Implement employer application review list and detail endpoints in `ApplicationController` | 5 | Employers can retrieve applications only for their own listings and inspect candidate submission details safely. |
| B1-22 | Extend `ApplicationService` with accept and reject transitions | 6 | Authorized employer decisions persist the new application status and status timestamp through the service layer. |
| B1-23 | Add `UpdateApplicationStatusRequest` and `ApplicationPolicy` coverage for employer decisions | 5 | Accept and reject endpoints validate payloads and block unauthorized status changes across role and ownership boundaries. |
| B1-24 | Implement `EmployerProfileController` company-name and logo-reference update flow `⚠️ depends on [B1-20]` | 5 | Employers can update branding data and associate the current logo reference without bypassing service-layer validation. |
| B1-25 | Add feature tests for auth, candidate profile, application submission, and employer decision flows | 6 | Automated tests cover positive and negative paths across B1-owned endpoints and ownership rules. |
| B1-26 | Fix QA defects and finalize B1-owned API contract examples | 4 | Blocking bugs found during integration are resolved and B1-owned request or response examples match the implemented API. |

**B1 Total: 26 tasks / 134 hours**

### B2 — Backend Developer 2

#### Sprint 1 tasks

| ID | Task | Est. Hours | Acceptance Criterion |
| --- | --- | ---: | --- |
| B2-01 | Organize `/api/v1` route files and middleware groups | 4 | Business routes are grouped by public, candidate, employer, and admin domains under the versioned API prefix. |
| B2-02 | Register repository bindings and `Queue::route()` wiring in `AppServiceProvider` | 4 | Repository interfaces resolve cleanly from the container and queue-related boot wiring is centralized in the provider. |
| B2-03 | Register `PreventRequestForgery`, Sanctum SPA middleware, and auth boot plumbing | 4 | Stateful SPA requests can pass CSRF protection and protected routes use the agreed authentication stack. |
| B2-04 | Create shared factories for `User`, `CandidateProfile`, and `EmployerProfile` | 5 | Auth-related models can be generated consistently in tests and seed flows through reusable factories. |
| B2-05 | Run a PHP Attribute audit baseline across backend models | 4 | Existing and newly added models follow the Attribute-based metadata convention without property-array regressions. |
| B2-06 | Build shared API feature-test utilities and response assertions | 4 | Backend tests can reuse helpers for auth setup, JSON:API pagination assertions, and role-scoped request setup. |

#### Sprint 2 tasks

| ID | Task | Est. Hours | Acceptance Criterion |
| --- | --- | ---: | --- |
| B2-07 | Create `Category`, `Location`, and `JobListing` factories plus seeders `⚠️ depends on [B1-08], [B1-09], [B1-10]` | 5 | Public-search and moderation tests can seed realistic listing data with categories and locations. |
| B2-08 | Implement public `JobListingController@index` with JSON:API pagination `⚠️ depends on [B1-10], [B1-11]` | 6 | The public jobs endpoint returns approved listings with stable pagination links and meta fields. |
| B2-09 | Implement public `JobListingController@show` with approved-only visibility `⚠️ depends on [B1-10]` | 5 | Public job detail requests return approved listings only and reject non-public records. |
| B2-10 | Build `SearchService` keyword, location, and category filters `⚠️ depends on [B1-10]` | 6 | Search requests can combine keyword, location, and category criteria without bypassing approval-state filtering. |
| B2-11 | Extend `SearchService` for salary, date, work-type, and experience filters `⚠️ depends on [B1-10]` | 6 | The public jobs endpoint can filter approved listings by all remaining required criteria with consistent pagination. |
| B2-12 | Implement public `CategoryController@index` and `LocationController@index` `⚠️ depends on [B1-08], [B1-09]` | 4 | The frontend can retrieve lookup datasets for category and location filter controls from versioned endpoints. |
| B2-13 | Implement listing cache layer and `Cache::touch()` hooks for listing responses `⚠️ depends on [B1-12]` | 5 | Public listing caches can be refreshed or marked stale when relevant listing state changes occur. |

#### Sprint 3 tasks

| ID | Task | Est. Hours | Acceptance Criterion |
| --- | --- | ---: | --- |
| B2-14 | Build `ProcessResumeUpload` queue job `⚠️ depends on [B1-17], [B1-20]` | 5 | Resume uploads are finalized through queued work rather than blocking the interactive upload request. |
| B2-15 | Build `ProcessCompanyLogoUpload` queue job `⚠️ depends on [B1-20]` | 5 | Employer logo upload side effects can be processed asynchronously with durable file metadata updates. |
| B2-16 | Build `NotifyEmployerOfApplication` database notification job `⚠️ depends on [B1-20]` | 5 | A new application dispatches a queued employer-facing notification or status record after persistence succeeds. |
| B2-17 | Implement `NotificationRepositoryInterface` and database notification persistence for application status events `⚠️ depends on [B1-18]` | 5 | Status changes can be stored and queried through a repository abstraction backed by the notifications table. |
| B2-18 | Create `Resume` and `Application` factories plus seeders `⚠️ depends on [B1-15], [B1-18]` | 4 | Candidate profile and application flows can be exercised with seeded files and application histories. |
| B2-19 | Add integration tests for queue-driven file processing and employer application notifications `⚠️ depends on [B2-14], [B2-16], [B2-17]` | 6 | Automated tests verify that queued upload and notification side effects complete with correct persisted outcomes. |

#### Sprint 4 tasks

| ID | Task | Est. Hours | Acceptance Criterion |
| --- | --- | ---: | --- |
| B2-20 | Implement `AdminJobService` approve or reject flow `⚠️ depends on [B1-10], [B1-12]` | 6 | Admin moderation transitions update job status, moderation metadata, and public visibility through the service layer. |
| B2-21 | Implement `AdminJobModerationController` pending and all-jobs endpoints `⚠️ depends on [B2-20]` | 6 | Admin endpoints return pending and historical moderation data with role-restricted access. |
| B2-22 | Implement `PlatformActivityQueryService` and `AdminActivityController` | 6 | Admin users can retrieve totals and recent records for users, jobs, and applications from a dedicated activity endpoint. |
| B2-23 | Implement `ApplicationStatusChanged` event and `BroadcastApplicationStatusChanged` listener `⚠️ depends on [B1-22], [B2-17]` | 5 | Application status transitions dispatch a broadcast-ready event payload after the database state is committed. |
| B2-24 | Configure Reverb private channels and broadcasting auth policy tests `⚠️ depends on [B2-23]` | 5 | Authorized candidate and employer users can subscribe only to their allowed private application-status channels. |
| B2-25 | Integrate cache invalidation for approval, rejection, and public listing visibility `⚠️ depends on [B2-13], [B2-20]` | 4 | Moderation state changes refresh or invalidate cached public listing data so public results stay correct. |
| B2-26 | Finalize route-contract docs and backend regression fixes for B2-owned domains | 4 | Public search, admin moderation, cache behavior, and real-time contracts are documented and stable for final QA. |

**B2 Total: 26 tasks / 128 hours**

### F1 — Frontend Developer 1

#### Sprint 1 tasks

| ID | Task | Est. Hours | Acceptance Criterion |
| --- | --- | ---: | --- |
| F1-01 | Scaffold the Vue `3.5.33` + TypeScript + Vite `8` application | 5 | The frontend boots locally with the agreed Vue, TypeScript, and Vite versions and a clean project structure. |
| F1-02 | Configure Vue Router `5.0.6` via `vue-router/vite` with generated file-based routes | 4 | Route generation works from the file system and nested layouts resolve through `_parent.vue` files. |
| F1-03 | Configure Pinia `3` and base app bootstrapping | 4 | The app mounts with Pinia installed and store registration ready for setup-store modules only. |
| F1-04 | Implement `http.ts` Axios instance with `401` and `422` interceptors | 5 | API requests share one configured client that logs out on unauthorized responses and exposes validation errors predictably. |
| F1-05 | Implement `useAuthStore` using setup-store syntax only | 6 | Auth state, current user context, and session-loading flags are managed through a Pinia setup store. |
| F1-06 | Implement `useAuth` composable and session bootstrap flow | 4 | Components can call a composable to register, log in, log out, and restore current-user context from the API. |
| F1-07 | Build `AuthLayout.vue`, `pages/auth/login.vue`, and `pages/auth/register.vue` | 8 | Guests can complete login and registration flows in the SPA with proper validation feedback and layout separation. |

#### Sprint 2 tasks

| ID | Task | Est. Hours | Acceptance Criterion |
| --- | --- | ---: | --- |
| F1-08 | Build `AppLayout.vue` and public navigation shell | 5 | Public pages share one application shell with route outlet, navigation, and consistent loading boundaries. |
| F1-09 | Build shared `AppButton.vue` | 3 | Buttons expose a reusable API for variant, disabled, and loading states used across the public UI. |
| F1-10 | Build shared `AppInput.vue` | 3 | Form inputs expose labeled, error-aware behavior that auth, search, and upload screens can reuse consistently. |
| F1-11 | Build shared `AppModal.vue` | 3 | Modal interactions support keyboard dismissal, focus management, and any DOM refs use `useTemplateRef()`. |
| F1-12 | Build shared `Pagination.vue` | 4 | Pagination consumes API links and meta data and renders stable page-navigation controls without layout shift. |
| F1-13 | Implement `useJobStore` `⚠️ depends on [B2-08], [B2-09]` | 6 | Public listing and detail data are fetched and cached in a typed Pinia setup store that matches the published job contract. |
| F1-14 | Implement `useJobSearch` `⚠️ depends on [B2-10], [B2-11]` | 6 | Search state maps cleanly to the public jobs query string and supports all required filter parameters. |
| F1-15 | Build `JobCard.vue` | 3 | Each listing summary renders the agreed public fields in a reusable card component that fits the list view. |
| F1-16 | Build `JobFilters.vue` `⚠️ depends on [B2-12]` | 4 | Users can edit keyword, category, location, salary, date, experience, and work-type filters through one reusable panel. |
| F1-17 | Build `JobDetail.vue` | 3 | The job detail component renders all public listing fields and employer branding data required by the public detail page. |
| F1-18 | Implement `pages/jobs/index.vue` `⚠️ depends on [F1-13], [F1-14], [B2-08]` | 8 | The public jobs page loads paginated results, applies filters, and keeps UI state synchronized with the API contract. |
| F1-19 | Implement `pages/jobs/[id].vue` `⚠️ depends on [B2-09], [F1-17]` | 5 | The public job detail page loads one approved listing and handles missing or unauthorized records gracefully. |

#### Sprint 3 tasks

| ID | Task | Est. Hours | Acceptance Criterion |
| --- | --- | ---: | --- |
| F1-20 | Integrate application-submit modal on job detail `⚠️ depends on [B1-20]` | 7 | A candidate can start an application from the public detail page and submit against the finalized backend contract. |
| F1-21 | Add client-side validation and API error mapping for auth, jobs, and application flows | 4 | Validation and business-rule errors from the API are surfaced consistently across public-facing forms. |
| F1-22 | Add public loading, empty, and retry states across jobs routes | 4 | Public jobs pages handle slow, empty, and failed requests without trapping the user in inconsistent UI states. |

#### Sprint 4 tasks

| ID | Task | Est. Hours | Acceptance Criterion |
| --- | --- | ---: | --- |
| F1-23 | Integrate Reverb auth bootstrap in the app shell `⚠️ depends on [B2-24]` | 4 | The SPA can initialize real-time auth prerequisites without breaking existing public or auth routes. |
| F1-24 | Apply accessibility polish to public and auth screens | 5 | Public and auth routes satisfy keyboard flow, visible focus, labeling, and basic contrast requirements. |
| F1-25 | Apply responsive layout polish to public and auth screens | 5 | Public and auth pages remain usable and visually stable across mobile and desktop breakpoints. |
| F1-26 | Add regression tests for auth and public job-discovery flows | 7 | Automated tests cover login, registration, public listing search, filter application, and job detail navigation. |

**F1 Total: 26 tasks / 122 hours**

### F2 — Frontend Developer 2

#### Sprint 1 tasks

| ID | Task | Est. Hours | Acceptance Criterion |
| --- | --- | ---: | --- |
| F2-01 | Create `pages/candidate/_parent.vue` layout shell | 4 | Candidate routes render inside a protected layout shell ready for role-specific navigation and page slots. |
| F2-02 | Create `pages/employer/_parent.vue` layout shell | 4 | Employer routes render inside a protected layout shell ready for dashboard navigation and page slots. |
| F2-03 | Create `pages/admin/_parent.vue` layout shell | 4 | Admin routes render inside a protected layout shell that can enforce admin-only navigation. |
| F2-04 | Implement `useToast` composable | 3 | Feature screens can push transient success and error messages through one shared composable API. |
| F2-05 | Build toast host component and app-level integration | 4 | Toast messages render consistently in the application shell and clean themselves up without manual page logic. |
| F2-06 | Implement shared `usePagination` composable | 3 | Dashboard pages can consume paginated collection responses through one reusable typed composable. |

#### Sprint 2 tasks

| ID | Task | Est. Hours | Acceptance Criterion |
| --- | --- | ---: | --- |
| F2-07 | Implement `useEmployerJobsStore` `⚠️ depends on [B1-13], [B1-14]` | 5 | Employer listing data and dashboard actions are managed through a typed Pinia setup store. |
| F2-08 | Implement `useEmployerJobForm` composable | 4 | Employer create and edit pages share form-state normalization, submission, and reset behavior. |
| F2-09 | Build `pages/employer/dashboard.vue` `⚠️ depends on [F2-07]` | 6 | Employers can see owned listings and their moderation status from a dedicated dashboard page. |
| F2-10 | Build `pages/employer/jobs/create.vue` `⚠️ depends on [B1-13]` | 5 | Employers can create a listing through a role-protected page wired to the finalized create endpoint. |
| F2-11 | Build `pages/employer/jobs/[id]/edit.vue` `⚠️ depends on [B1-14]` | 5 | Listing owners can edit an existing job through a role-protected page that respects the update contract. |
| F2-12 | Build shared employer `JobForm.vue` component | 5 | Create and edit routes share one reusable job form component with normalized field bindings. |
| F2-13 | Build `LogoUpload.vue` using shared input patterns `⚠️ depends on [B1-20]` | 4 | Employers can pick a logo file through a reusable upload component that matches the backend payload contract. |

#### Sprint 3 tasks

| ID | Task | Est. Hours | Acceptance Criterion |
| --- | --- | ---: | --- |
| F2-14 | Implement `useCandidateProfileStore` `⚠️ depends on [B1-16], [B1-17]` | 5 | Candidate profile data, default resume references, and save status are managed through a typed Pinia setup store. |
| F2-15 | Implement `useCandidateApplicationsStore` `⚠️ depends on [B1-20]` | 5 | Candidate application-history data and cancel actions are managed through a typed Pinia setup store. |
| F2-16 | Implement candidate profile and application composables | 4 | Candidate screens can reuse composables for loading, saving, and updating profile or application-history state. |
| F2-17 | Build `ResumeUpload.vue` using shared input patterns `⚠️ depends on [B1-20]` | 4 | Candidates can select and upload a resume file through a component that matches the backend upload contract. |
| F2-18 | Build `pages/candidate/profile.vue` `⚠️ depends on [F2-14], [F2-17]` | 6 | Candidates can edit profile data, manage the default resume, and save changes from a protected profile page. |
| F2-19 | Build `pages/candidate/applications.vue` `⚠️ depends on [F2-15]` | 6 | Candidates can view application history, inspect current statuses, and cancel eligible applications from one page. |
| F2-20 | Build employer application-review panel and `ApplicationStatusBadge.vue` `⚠️ depends on [B1-21], [B1-22]` | 6 | Employers can review applications and see clear status indicators that match backend status values. |

#### Sprint 4 tasks

| ID | Task | Est. Hours | Acceptance Criterion |
| --- | --- | ---: | --- |
| F2-21 | Implement `useAdminJobsStore` `⚠️ depends on [B2-21]` | 5 | Pending and moderated job data are managed through a typed Pinia setup store for admin routes. |
| F2-22 | Implement admin moderation composable | 4 | Admin screens can reuse composable logic for loading queues and sending approve or reject actions. |
| F2-23 | Build `pages/admin/jobs/index.vue` `⚠️ depends on [F2-21], [B2-22]` | 6 | Admin users can review pending jobs, browse moderated jobs, and access platform activity summaries from one page. |
| F2-24 | Implement `useEcho` composable and Reverb subscription lifecycle `⚠️ depends on [B2-24]` | 5 | Candidate and employer screens can open and close authorized real-time subscriptions without leaking listeners. |
| F2-25 | Wire live application-status updates into candidate and employer views `⚠️ depends on [F2-24], [B2-23]` | 6 | Relevant dashboard views update application-status state live when Reverb broadcasts a status-change event. |
| F2-26 | Add regression tests for employer, candidate, and admin dashboard flows | 7 | Automated tests cover employer listing management, candidate profile and application history, and admin moderation flows. |

**F2 Total: 26 tasks / 125 hours**

### Shared Milestones & Integration Points

| Checkpoint | Sprint | BE Owner | FE Owner | What must be ready |
| --- | --- | --- | --- | --- |
| Auth contract handoff | 1 | B1 | F1 | `register`, `login`, `logout`, and `me` endpoints with final request and response shapes plus seeded admin credentials. |
| Protected layout and role-context handoff | 1 | B1 | F2 | Authenticated role payload and route-guard expectations needed by candidate, employer, and admin layouts. |
| Public jobs contract handoff | 2 | B2 | F1 | Public list, detail, category, and location endpoints with pagination and filter query parameters. |
| Employer job CRUD contract handoff | 2 | B1 | F2 | Employer list, create, show, update, and delete job endpoints with final validation and moderation fields. |
| Candidate profile and upload contract handoff | 3 | B1 | F2 | Candidate profile, resume upload, default resume, and employer logo-reference endpoints with stable payloads. |
| Application submit contract handoff | 3 | B1 | F1 | Public job-application submission endpoint with accepted submission modes and error semantics. |
| Employer application review contract handoff | 3 | B1 | F2 | Employer application list, detail, and candidate cancel side effects reflected in stable response resources. |
| Admin moderation contract handoff | 4 | B2 | F2 | Pending jobs, moderated jobs, approve, reject, and platform activity endpoints with final payload shapes. |
| Real-time contract handoff | 4 | B2 | F2 | Reverb channel names, auth flow, and `ApplicationStatusChanged` payload schema for dashboard integration. |

### API Contract Freeze Schedule

| Domain | Frozen After Sprint | BE Owner |
| --- | --- | --- |
| Authentication | Sprint 1 | B1 |
| Public Jobs & Search | Sprint 2 | B2 |
| Employer Job Management | Sprint 2 | B1 |
| Candidate Profile | Sprint 3 | B1 |
| File Uploads | Sprint 3 | B1 |
| Applications | Sprint 3 | B1 |
| Admin Moderation | Sprint 4 | B2 |
| Platform Activity | Sprint 4 | B2 |
| Real-time Channels | Sprint 4 | B2 |
