## Software Requirements Specification — Job Board SPA

### 1. Introduction

#### 1.1 Purpose
This Software Requirements Specification (SRS) defines the functional, interface, and quality requirements for the Job Board single-page application. It is intended for backend developers, frontend developers, QA engineers, and project stakeholders who need a production-ready specification for implementation of the MVP described in the project requirements.

#### 1.2 Scope
The product is a job board where employers register and publish job listings, admins approve those listings, and candidates search and apply for jobs either with an uploaded resume or by forwarding contact details. The MVP includes authentication, role-based access, employer job management, public job discovery, candidate profile management, application submission and status tracking, admin moderation, S3-compatible file uploads, and Reverb-based real-time status updates.

#### 1.3 Definitions, Acronyms, Abbreviations
- `SPA`: Single-Page Application.
- `REST`: Representational State Transfer API style over HTTP.
- `API`: Application Programming Interface.
- `S3`: Amazon S3-compatible object storage interface.
- `DB`: Relational database used by the Laravel application.
- `JSON:API`: Response envelope style used for paginated collections.
- `JsonResource`: Laravel response resource used for single-item payloads.
- `Sanctum`: Laravel package used for SPA authentication and protected API access.
- `Reverb`: Laravel WebSocket broadcasting server used for real-time updates.
- `WCAG 2.1 AA`: Web Content Accessibility Guidelines, conformance level AA.
- `MVP`: Minimum Viable Product.

#### 1.4 References
1. `docs/project_description.md`
2. `docs/requirements.md`
3. Laravel 13 documentation
4. Vue 3.5 documentation

#### 1.5 Overview
Section 2 describes the product context, users, constraints, and assumptions. Section 3 specifies each system feature with API contracts and implementation components. Section 4 describes external interfaces. Section 5 defines non-functional requirements. Section 6 isolates future work items. Section 7 provides appendices for route inventory, entity design, and queue jobs.

### 2. Overall Description

#### 2.1 Product Perspective
The system is a decoupled web application composed of:

- A Vue `3.5.33` SPA for all user-facing interfaces.
- A Laravel `13.7.0` REST API under `/api/v1`.
- Laravel Sanctum for authenticated SPA access to protected routes.
- S3-compatible object storage for resume and logo uploads.
- Laravel Reverb for real-time application status broadcasts.
- Queue workers for file-processing, notification, and cache-related side effects.
- A relational database for business data, status persistence, notifications, and framework state.

The frontend consumes the backend exclusively through HTTP JSON APIs and Reverb WebSocket channels. Public users interact with approved job content only, while candidate, employer, and admin experiences are separated by authenticated routes and Laravel authorization policies.

#### 2.2 Product Functions
At a high level, the MVP provides these functions:

- Public browsing of approved job listings with search and filters.
- Candidate and employer registration and login.
- Candidate profile management, including contact details, skills, and default resume reference.
- Employer creation, update, and lifecycle management of job listings.
- Admin approval or rejection of employer-submitted job listings.
- Candidate application submission to approved jobs with resume or contact details.
- Employer review of applications and status decisions.
- Database-persisted application status tracking plus real-time updates to authorized SPA clients.

#### 2.3 User Characteristics

##### Guest
Guests are unauthenticated visitors who browse approved listings, inspect job details, and access authentication pages. They require a simple, responsive public UI and should never see non-public listings or protected dashboard data.

##### Candidate
Candidates are job seekers who register, maintain a profile, upload resumes, search the catalog, apply for jobs, and track application statuses. They require a straightforward application workflow, clear ownership boundaries around their data, and visibility into historical application outcomes.

##### Employer
Employers are hiring users who register, manage company identity data, create job listings, respond to moderation state changes, review candidate applications, and accept or reject applications. They require dashboard workflows centered on listing ownership and applicant review.

##### Admin
Admins are operational users who moderate pending job listings and inspect aggregate platform activity. They require high-trust access, clear moderation actions, and visibility into jobs, users, and applications without receiving public self-registration access.

#### 2.4 Constraints
- Backend runtime is fixed at PHP `8.5.5`.
- Backend framework is fixed at Laravel `13.7.0`.
- Frontend framework is fixed at Vue `3.5.33` stable only, not Vue `3.6` beta.
- State management is fixed at Pinia `3` using setup-store syntax only.
- Routing is fixed at Vue Router `5.0.6` using `import VueRouter from 'vue-router/vite'`.
- Build tooling is fixed at Vite `8`.
- Frontend source is fixed to TypeScript.
- Eloquent models must use PHP Attribute style such as `#[Table]`, `#[Fillable]`, and `#[Hidden]`; property-array metadata styles are out of scope.
- Backend controllers and FormRequests must remain thin and delegate business logic to Services and persistence access to Repositories.
- Any cloning examples or implementation guidance must use `clone($model, ['key' => $value])`; the `clone $model with {...}` form is not permitted.
- DOM element references in Vue must use `useTemplateRef()` rather than `ref(null)`.
- Vue props access must use direct destructuring such as `const { jobId } = defineProps<{ jobId: string }>()`.
- `useRoute()` is assumed to be auto-typed with no route-path argument.
- Paginated collection endpoints must use JSON:API-style envelopes, while single-item endpoints must use Laravel `JsonResource` responses.

#### 2.5 Assumptions and Dependencies
- Admin accounts are provisioned through seeders or controlled back-office setup.
- Each authenticated user has one primary role at a time: candidate, employer, or admin.
- Public search exposes approved and non-expired listings only.
- Editing public job content after approval is assumed to return the listing to `pending` moderation.
- The MVP uses S3-compatible storage in all environments.
- Reverb and queue workers are available in every environment where real-time updates are expected.
- Development environments are assumed to use Reverb without Redis, relying on database-backed supporting services where needed.
- Salary search operates on one configured currency represented as numeric minimum and maximum values.
- Contact-based applications forward the candidate contact snapshot stored at submission time.
- Application status states are assumed to include `submitted`, `cancelled`, `accepted`, and `rejected`.

### 3. System Features

#### 3.1 Authentication
**Description:** Provides registration, login, logout, current-user context loading, and role-scoped access control for guest, candidate, employer, and admin flows.

**Priority:** High

**Stimulus / Response:** A guest submits a registration or login form, or an authenticated user opens the SPA with an active session; the system validates the request, establishes or terminates Sanctum authentication state, and returns user context or structured errors.

**Functional Requirements**
1. `FR-AU-01`, `FR-AU-03`, `FR-AU-04`: Candidate and employer users can register, log in, and log out through authenticated SPA flows.
2. `FR-AU-02`: Admin users can sign in with pre-provisioned credentials.
3. `FR-AU-05`, `FR-AU-06`: Protected routes are enforced through Sanctum authentication and Laravel policy or gate checks.
4. `FR-AU-07`: The frontend can load the authenticated user record and active role context after login or page refresh.
5. `FR-AU-08`, `FR-AU-09`: Passwords remain protected and invalid auth requests return structured JSON validation errors.

**API Endpoints**

| Method | Route | Auth | Request Shape | Response Shape |
| --- | --- | --- | --- | --- |
| GET | `/sanctum/csrf-cookie` | Public | No body | `204 No Content` and CSRF cookie for first-party SPA requests |
| POST | `/api/v1/auth/register` | Public | JSON `{ role, name, email, password, password_confirmation, company_name? }` | `201 JsonResource { data: { user, role, abilities } }` |
| POST | `/api/v1/auth/login` | Public | JSON `{ email, password }` | `200 JsonResource { data: { user, role, abilities } }` |
| POST | `/api/v1/auth/logout` | `auth:sanctum` | No body | `204 No Content` |
| GET | `/api/v1/auth/me` | `auth:sanctum` | No body | `200 JsonResource { data: { user, role, profile } }` |

**Relevant Laravel Components**
- Controller: `AuthController`
- FormRequest: `RegisterRequest`, `LoginRequest`
- Resource: `UserResource`, `EmployerResource`, `AuthenticatedUserResource`
- Policy: `UserPolicy`
- Service: `AuthService`
- Repository: `UserRepositoryInterface`, `EloquentUserRepository`

**Relevant Vue Components**
- Pages: `src/pages/auth/login.vue`, `src/pages/auth/register.vue`
- Layouts: `src/layouts/AuthLayout.vue`
- Feature store: `src/features/auth/stores/useAuthStore.ts`
- Composable: `src/features/auth/composables/useAuth.ts`

#### 3.2 Job Listing Management
**Description:** Enables employers to create, update, view, and remove their own job listings while routing public publication through admin approval.

**Priority:** High

**Stimulus / Response:** An authenticated employer submits a create, update, or delete action on a job listing; the system validates ownership and payload rules, persists the listing state, and records the resulting moderation status for later admin review.

**Functional Requirements**
1. `FR-E-03`, `FR-JL-01`, `FR-JL-02`: Employers can create job listings with all required business fields.
2. `FR-E-04`, `FR-JL-03`, `FR-JL-04`: Employers can update or deactivate only their own listings.
3. `FR-JL-05`, `FR-JL-06`: Each listing carries a moderation state and requires admin approval before public visibility.
4. `FR-E-08`, `FR-JL-08`: Employers can retrieve all owned listings with moderation status.
5. `FR-E-07`, `FR-JL-09`, `FR-JL-10`, `FR-PM-06`: Branding metadata and application-deadline enforcement are included in listing publication behavior.

**API Endpoints**

| Method | Route | Auth | Request Shape | Response Shape |
| --- | --- | --- | --- | --- |
| GET | `/api/v1/employer/jobs` | `auth:sanctum` + employer policy | Query params `{ page?, status? }` | `200 JSON:API { data: JobListing[], links, meta }` |
| POST | `/api/v1/employer/jobs` | `auth:sanctum` + employer policy | JSON `{ title, description, responsibilities, required_skills[], qualifications, salary_min, salary_max, benefits, category_id, location_id, work_type, technologies[], experience_level, application_deadline }` | `201 JsonResource { data: JobListing }` |
| GET | `/api/v1/employer/jobs/{job}` | `auth:sanctum` + ownership policy | No body | `200 JsonResource { data: JobListing }` |
| PATCH | `/api/v1/employer/jobs/{job}` | `auth:sanctum` + ownership policy | Same as create payload, partial allowed | `200 JsonResource { data: JobListing }` |
| DELETE | `/api/v1/employer/jobs/{job}` | `auth:sanctum` + ownership policy | No body | `204 No Content` |
| POST | `/api/v1/admin/jobs/{job}/approve` | `auth:sanctum` + admin policy | JSON `{ note? }` | `200 JsonResource { data: JobListing }` |
| POST | `/api/v1/admin/jobs/{job}/reject` | `auth:sanctum` + admin policy | JSON `{ reason }` | `200 JsonResource { data: JobListing }` |

**Relevant Laravel Components**
- Controller: `JobListingController`, `AdminJobModerationController`
- FormRequest: `StoreJobListingRequest`, `UpdateJobListingRequest`, `RejectJobListingRequest`
- Resource: `JobListingResource`
- Policy: `JobListingPolicy`
- Service: `JobService`, `AdminJobService`
- Repository: `JobListingRepositoryInterface`, `EloquentJobListingRepository`

**Relevant Vue Components**
- Pages: `src/pages/employer/dashboard.vue`, `src/pages/employer/jobs/create.vue`, `src/pages/employer/jobs/[id]/edit.vue`
- Feature store: `src/features/employer/stores/useEmployerJobsStore.ts`
- Composable: `src/features/employer/composables/useEmployerJobForm.ts`

#### 3.3 Job Search & Filtering
**Description:** Exposes public search and filter capabilities over approved job listings for guests and candidates.

**Priority:** High

**Stimulus / Response:** A guest or candidate enters search text or filter values on the jobs screen; the system executes a filtered query over approved listings and returns paginated results plus paging metadata.

**Functional Requirements**
1. `FR-G-01`, `FR-G-02`, `FR-G-03`: Guests can list, filter, and inspect approved job listings.
2. `FR-C-04`, `FR-C-05`: Candidates have the same public discovery capabilities when authenticated.
3. `FR-SF-01` through `FR-SF-08`: The system supports keyword, location, category, experience level, salary range, date posted, work type, and pagination filters.
4. `FR-JL-07`: Search results expose approved, non-expired listings only.

**API Endpoints**

| Method | Route | Auth | Request Shape | Response Shape |
| --- | --- | --- | --- | --- |
| GET | `/api/v1/jobs` | Public | Query `{ q?, location_id?, category_id?, experience_level?, salary_min?, salary_max?, posted_after?, posted_before?, work_type?, page? }` | `200 JSON:API { data: JobListingSummary[], links, meta }` |
| GET | `/api/v1/jobs/{job}` | Public | No body | `200 JsonResource { data: JobListingDetail }` |
| GET | `/api/v1/categories` | Public | Query `{ page? }` | `200 JSON:API { data: Category[], links, meta }` |
| GET | `/api/v1/locations` | Public | Query `{ page? }` | `200 JSON:API { data: Location[], links, meta }` |

**Relevant Laravel Components**
- Controller: `JobListingController`
- FormRequest: `IndexJobListingsRequest`
- Resource: `JobListingCollection`, `JobListingResource`, `CategoryResource`, `LocationResource`
- Policy: public visibility handled in query scope, no ownership policy required
- Service: `SearchService`
- Repository: `JobListingRepositoryInterface`, `CategoryRepositoryInterface`, `LocationRepositoryInterface`

**Relevant Vue Components**
- Pages: `src/pages/jobs/index.vue`, `src/pages/jobs/[id].vue`
- Feature store: `src/features/jobs/stores/useJobStore.ts`
- Composable: `src/features/jobs/composables/useJobSearch.ts`
- Components: `src/features/jobs/components/JobCard.vue`, `JobFilters.vue`, `JobDetail.vue`

#### 3.4 Application Process
**Description:** Allows candidates to submit job applications using either an uploaded resume or forwarded contact data and allows employers to review the submitted candidate package.

**Priority:** High

**Stimulus / Response:** A candidate submits an application from a job detail page, or an employer opens an applicant list; the system validates eligibility, persists the application snapshot, and returns the application record for later status handling.

**Functional Requirements**
1. `FR-C-06`, `FR-AP-01`, `FR-AP-02`, `FR-AP-03`: Candidates can apply to approved, open jobs with a resume reference or forwarded contact details.
2. `FR-AP-04`: Application records preserve the contact snapshot used at submission time.
3. `FR-C-07`, `FR-C-08`, `FR-AP-05`, `FR-AP-06`: Candidates can review their history and cancel eligible self-owned applications.
4. `FR-E-05`, `FR-AP-07`, `FR-AP-08`: Employers can review applications for their own jobs and inspect candidate submission data.
5. `FR-AP-11`: Unauthorized users cannot access unrelated applications.

**API Endpoints**

| Method | Route | Auth | Request Shape | Response Shape |
| --- | --- | --- | --- | --- |
| POST | `/api/v1/jobs/{job}/applications` | `auth:sanctum` + candidate policy | JSON `{ submission_mode, resume_id?, use_profile_contact }` | `201 JsonResource { data: Application }` |
| GET | `/api/v1/candidate/applications` | `auth:sanctum` + candidate policy | Query `{ page?, status? }` | `200 JSON:API { data: Application[], links, meta }` |
| DELETE | `/api/v1/candidate/applications/{application}` | `auth:sanctum` + ownership policy | No body | `200 JsonResource { data: Application }` |
| GET | `/api/v1/employer/jobs/{job}/applications` | `auth:sanctum` + ownership policy | Query `{ page?, status? }` | `200 JSON:API { data: Application[], links, meta }` |
| GET | `/api/v1/employer/applications/{application}` | `auth:sanctum` + ownership policy | No body | `200 JsonResource { data: Application }` |

**Relevant Laravel Components**
- Controller: `ApplicationController`
- FormRequest: `StoreApplicationRequest`
- Resource: `ApplicationResource`
- Policy: `ApplicationPolicy`
- Service: `ApplicationService`
- Repository: `ApplicationRepositoryInterface`, `EloquentApplicationRepository`

**Relevant Vue Components**
- Pages: `src/pages/jobs/[id].vue`, `src/pages/candidate/applications.vue`, `src/pages/employer/dashboard.vue`
- Feature store: `src/features/candidate/stores/useCandidateApplicationsStore.ts`, `src/features/employer/stores/useEmployerApplicationsStore.ts`
- Composable: `src/features/candidate/composables/useApplicationSubmit.ts`

#### 3.5 Candidate Profile Management
**Description:** Provides candidate-owned profile editing for personal information, contact details, skills, default resume reference, and application history entry points.

**Priority:** High

**Stimulus / Response:** An authenticated candidate opens the profile screen or submits profile edits; the system validates the profile payload, persists the changes, and returns the updated candidate profile record.

**Functional Requirements**
1. `FR-C-03`, `FR-PM-01`, `FR-PM-02`, `FR-PM-03`: Candidates can manage profile summary, contact information, skills, and location data.
2. `FR-PM-04`: Candidates can assign a default resume reference for later applications.
3. `FR-PM-05`: The candidate profile area provides access to owned application history.
4. `FR-FU-01`, `FR-FU-05`: Resume uploads integrate with the candidate profile without breaking historical application links.

**API Endpoints**

| Method | Route | Auth | Request Shape | Response Shape |
| --- | --- | --- | --- | --- |
| GET | `/api/v1/candidate/profile` | `auth:sanctum` + candidate policy | No body | `200 JsonResource { data: CandidateProfile }` |
| PATCH | `/api/v1/candidate/profile` | `auth:sanctum` + candidate policy | JSON `{ summary, location_text, phone, skills[], default_resume_id? }` | `200 JsonResource { data: CandidateProfile }` |
| GET | `/api/v1/candidate/resumes` | `auth:sanctum` + candidate policy | Query `{ page? }` | `200 JSON:API { data: Resume[], links, meta }` |

**Relevant Laravel Components**
- Controller: `CandidateProfileController`
- FormRequest: `UpdateCandidateProfileRequest`
- Resource: `CandidateProfileResource`, `ResumeResource`
- Policy: `CandidateProfilePolicy`
- Service: `CandidateProfileService`
- Repository: `CandidateProfileRepositoryInterface`, `ResumeRepositoryInterface`

**Relevant Vue Components**
- Pages: `src/pages/candidate/profile.vue`, `src/pages/candidate/applications.vue`
- Feature store: `src/features/candidate/stores/useCandidateProfileStore.ts`, `useCandidateApplicationsStore.ts`
- Composable: `src/features/candidate/composables/useCandidateProfile.ts`

#### 3.6 Admin Moderation Panel
**Description:** Gives admins access to pending-job moderation queues, all moderated job listings, and high-level platform activity summaries.

**Priority:** High

**Stimulus / Response:** An admin opens the moderation panel or submits an approve or reject action; the system validates admin access, executes the moderation transition, and refreshes the moderation and activity data sources.

**Functional Requirements**
1. `FR-A-02`, `FR-AM-01`: Admins can retrieve pending job listings awaiting moderation.
2. `FR-A-03`, `FR-AM-02`: Admins can approve pending job listings for public publication.
3. `FR-A-04`, `FR-AM-03`: Admins can reject pending job listings and keep them private.
4. `FR-A-05`, `FR-AM-04`: Admins can view platform activity summaries across users, jobs, and applications.
5. `FR-A-01`, `FR-AM-05`: Admin-only panel routes reject non-admin access.

**API Endpoints**

| Method | Route | Auth | Request Shape | Response Shape |
| --- | --- | --- | --- | --- |
| GET | `/api/v1/admin/jobs/pending` | `auth:sanctum` + admin policy | Query `{ page? }` | `200 JSON:API { data: JobListing[], links, meta }` |
| GET | `/api/v1/admin/jobs` | `auth:sanctum` + admin policy | Query `{ page?, status? }` | `200 JSON:API { data: JobListing[], links, meta }` |
| POST | `/api/v1/admin/jobs/{job}/approve` | `auth:sanctum` + admin policy | JSON `{ note? }` | `200 JsonResource { data: JobListing }` |
| POST | `/api/v1/admin/jobs/{job}/reject` | `auth:sanctum` + admin policy | JSON `{ reason }` | `200 JsonResource { data: JobListing }` |
| GET | `/api/v1/admin/activity` | `auth:sanctum` + admin policy | No body | `200 JsonResource { data: { totals, recent_jobs, recent_applications, recent_users } }` |

**Relevant Laravel Components**
- Controller: `AdminJobModerationController`, `AdminActivityController`
- FormRequest: `RejectJobListingRequest`
- Resource: `JobListingResource`, `AdminActivityResource`
- Policy: `AdminPolicy`, `JobListingPolicy`
- Service: `AdminJobService`, `PlatformActivityQueryService`
- Repository: `JobListingRepositoryInterface`, `ApplicationRepositoryInterface`, `UserRepositoryInterface`

**Relevant Vue Components**
- Pages: `src/pages/admin/_parent.vue`, `src/pages/admin/jobs/index.vue`
- Feature store: `src/features/admin/stores/useAdminJobsStore.ts`
- Composable: `src/features/admin/composables/useAdminModeration.ts`

#### 3.7 Employer Dashboard
**Description:** Aggregates employer-owned listing management, moderation-state visibility, and application review into a single authenticated workflow.

**Priority:** High

**Stimulus / Response:** An employer opens the dashboard, selects a listing, or changes an application status; the system returns owned listings and applications, validates ownership, and persists the action outcome.

**Functional Requirements**
1. `FR-E-03`, `FR-E-04`, `FR-JL-08`: Employers can view and manage their own listings from a role-specific dashboard.
2. `FR-E-05`, `FR-AP-07`, `FR-AP-08`: Employers can list and inspect applications for owned jobs.
3. `FR-E-06`, `FR-AP-09`, `FR-AP-10`: Employers can accept or reject applications.
4. `FR-NO-03`, `FR-NO-04`: Employer-facing views expose current status and receive real-time updates when application states change.

**API Endpoints**

| Method | Route | Auth | Request Shape | Response Shape |
| --- | --- | --- | --- | --- |
| GET | `/api/v1/employer/jobs` | `auth:sanctum` + employer policy | Query `{ page?, status? }` | `200 JSON:API { data: JobListing[], links, meta }` |
| GET | `/api/v1/employer/jobs/{job}/applications` | `auth:sanctum` + ownership policy | Query `{ page?, status? }` | `200 JSON:API { data: Application[], links, meta }` |
| GET | `/api/v1/employer/applications/{application}` | `auth:sanctum` + ownership policy | No body | `200 JsonResource { data: Application }` |
| POST | `/api/v1/employer/applications/{application}/accept` | `auth:sanctum` + ownership policy | JSON `{ note? }` | `200 JsonResource { data: Application }` |
| POST | `/api/v1/employer/applications/{application}/reject` | `auth:sanctum` + ownership policy | JSON `{ reason? }` | `200 JsonResource { data: Application }` |

**Relevant Laravel Components**
- Controller: `JobListingController`, `ApplicationController`
- FormRequest: `UpdateApplicationStatusRequest`
- Resource: `JobListingResource`, `ApplicationResource`
- Policy: `JobListingPolicy`, `ApplicationPolicy`
- Service: `JobService`, `ApplicationService`
- Repository: `JobListingRepositoryInterface`, `ApplicationRepositoryInterface`

**Relevant Vue Components**
- Pages: `src/pages/employer/dashboard.vue`, `src/pages/employer/jobs/create.vue`, `src/pages/employer/jobs/[id]/edit.vue`
- Feature store: `src/features/employer/stores/useEmployerJobsStore.ts`, `useEmployerApplicationsStore.ts`
- Composable: `src/features/employer/composables/useEmployerDashboard.ts`

#### 3.8 File Upload
**Description:** Supports candidate resume uploads and employer company logo uploads using S3-compatible object storage and persisted file metadata.

**Priority:** High

**Stimulus / Response:** A candidate or employer selects a file in the SPA; the system validates the upload, stores the file through the S3-compatible disk, persists metadata, and returns a reference that other features can consume.

**Functional Requirements**
1. `FR-E-07`, `FR-FU-02`, `FR-PM-06`: Employers can upload a company logo that appears with their listing identity data.
2. `FR-FU-01`, `FR-PM-04`: Candidates can upload resumes and select one as the default resume reference.
3. `FR-FU-03`, `FR-FU-04`: All uploads are validated before storage and persisted with durable metadata.
4. `FR-FU-05`: Replacing an active file reference does not break historical application records.

**API Endpoints**

| Method | Route | Auth | Request Shape | Response Shape |
| --- | --- | --- | --- | --- |
| POST | `/api/v1/candidate/resumes` | `auth:sanctum` + candidate policy | `multipart/form-data` with `file` | `201 JsonResource { data: Resume }` |
| GET | `/api/v1/candidate/resumes` | `auth:sanctum` + candidate policy | Query `{ page? }` | `200 JSON:API { data: Resume[], links, meta }` |
| DELETE | `/api/v1/candidate/resumes/{resume}` | `auth:sanctum` + ownership policy | No body | `204 No Content` |
| PATCH | `/api/v1/employer/profile` | `auth:sanctum` + employer policy | `multipart/form-data` or JSON with `company_name`, `logo_file?` | `200 JsonResource { data: EmployerProfile }` |
| DELETE | `/api/v1/employer/company-logo` | `auth:sanctum` + employer policy | No body | `204 No Content` |

**Relevant Laravel Components**
- Controller: `ResumeController`, `EmployerProfileController`
- FormRequest: `StoreResumeRequest`, `UpdateEmployerProfileRequest`
- Resource: `ResumeResource`, `EmployerProfileResource`
- Policy: `ResumePolicy`, `EmployerProfilePolicy`
- Service: `FileStorageService`, `CandidateProfileService`
- Repository: `ResumeRepositoryInterface`, `EmployerProfileRepositoryInterface`

**Relevant Vue Components**
- Pages: `src/pages/candidate/profile.vue`, `src/pages/employer/jobs/create.vue`, `src/pages/employer/jobs/[id]/edit.vue`
- Feature store: `src/features/candidate/stores/useCandidateProfileStore.ts`, `src/features/employer/stores/useEmployerJobsStore.ts`
- Composable: `src/features/candidate/composables/useResumeUpload.ts`, `src/features/employer/composables/useLogoUpload.ts`
- Components: `src/features/candidate/components/ResumeUpload.vue`, `src/features/employer/components/LogoUpload.vue`

#### 3.9 Real-time Updates
**Description:** Delivers application status changes to authorized connected SPA clients through Reverb without polling.

**Priority:** Medium

**Stimulus / Response:** An application is accepted, rejected, or cancelled; the system persists the new status, records the database notification state, and broadcasts a scoped Reverb event to the candidate and employer clients authorized to see that application.

**Functional Requirements**
1. `FR-NO-01`: Application status changes are persisted in the database with actor and timestamp data.
2. `FR-NO-02`, `FR-NO-03`: Candidate and employer interfaces expose the latest persisted status.
3. `FR-NO-04`: Relevant connected clients receive real-time status-change payloads through Reverb.

**API Endpoints**

| Method | Route | Auth | Request Shape | Response Shape |
| --- | --- | --- | --- | --- |
| POST | `/api/v1/broadcasting/auth` | `auth:sanctum` | JSON `{ channel_name, socket_id }` | `200 JSON { auth, channel_data? }` |
| WS SUBSCRIBE | `private-users.{userId}.applications` | Reverb private channel | Event subscription payload | Event `ApplicationStatusChanged { application_id, job_listing_id, status, status_changed_at }` |

**Relevant Laravel Components**
- Controller: framework broadcasting auth endpoint plus `ApplicationController` status actions
- FormRequest: `UpdateApplicationStatusRequest`
- Resource: `ApplicationResource`
- Policy: `BroadcastChannelPolicy`, `ApplicationPolicy`
- Service: `ApplicationService`, `NotificationService`
- Repository: `ApplicationRepositoryInterface`, `NotificationRepositoryInterface`
- Event/Listener: `ApplicationStatusChanged`, `BroadcastApplicationStatusChanged`

**Relevant Vue Components**
- Pages: `src/pages/candidate/applications.vue`, `src/pages/employer/dashboard.vue`
- Feature store: `src/features/candidate/stores/useCandidateApplicationsStore.ts`, `src/features/employer/stores/useEmployerApplicationsStore.ts`
- Composable: `src/composables/useEcho.ts`
- Components: `src/features/shared/components/ApplicationStatusBadge.vue`

### 4. External Interface Requirements

#### 4.1 User Interfaces
The system exposes a responsive SPA optimized for desktop and mobile browsers. Core routes are grouped by role, and role-protected screens are separated through nested layouts.

Vue `3.5.33` component tree overview:

```text
App.vue
├── layouts/AuthLayout.vue
│   ├── pages/auth/login.vue
│   └── pages/auth/register.vue
├── layouts/AppLayout.vue
│   ├── pages/jobs/index.vue
│   └── pages/jobs/[id].vue
├── pages/candidate/_parent.vue
│   ├── pages/candidate/profile.vue
│   └── pages/candidate/applications.vue
├── pages/employer/_parent.vue
│   ├── pages/employer/dashboard.vue
│   ├── pages/employer/jobs/create.vue
│   └── pages/employer/jobs/[id]/edit.vue
└── pages/admin/_parent.vue
    └── pages/admin/jobs/index.vue
```

Shared UI requirements:

- Public listing pages must present search, filters, pagination, and job details without requiring login.
- Candidate profile pages must support resume management and application-history review.
- Employer dashboard pages must support listing CRUD and application review in one authenticated workspace.
- Admin pages must show pending moderation queues and platform activity summaries.
- All interactive controls must satisfy WCAG 2.1 AA requirements for labels, focus handling, and keyboard operation.

#### 4.2 API Interface
The backend exposes a REST JSON API under `/api/v1`. Collections use JSON:API-style pagination envelopes, while single items use Laravel `JsonResource` payloads. Standard error responses use Laravel validation and authorization semantics.

Collection response pattern:

```json
{
  "data": [],
  "links": {
    "first": "https://example.test/api/v1/jobs?page=1",
    "last": "https://example.test/api/v1/jobs?page=4",
    "prev": null,
    "next": "https://example.test/api/v1/jobs?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 4,
    "path": "https://example.test/api/v1/jobs",
    "per_page": 15,
    "to": 15,
    "total": 53
  }
}
```

Single-item response pattern:

```json
{
  "data": {
    "id": "job_123",
    "title": "Backend Developer"
  }
}
```

Validation error pattern:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}
```

Authentication and transport conventions:

- Protected routes use Sanctum-authenticated requests from the first-party SPA.
- State-changing SPA requests must include CSRF protection when applicable through `PreventRequestForgery`.
- File uploads use `multipart/form-data`.
- Real-time subscriptions use Reverb private channels authorized by the backend.

#### 4.3 Hardware Interfaces
Not applicable. The product targets commodity web clients and standard application hosting infrastructure with no dedicated hardware integration.

#### 4.4 Software Interfaces
- **Laravel Sanctum:** authenticates SPA requests and protects private API routes.
- **S3-compatible storage:** stores candidate resumes and employer logos.
- **Laravel Reverb:** broadcasts application status updates to authorized frontend clients.
- **Queue workers:** process file-related and notification-related side effects asynchronously.
- **Database-backed framework services:** persist queue state, notifications, and related support records in environments without Redis.

### 5. Non-Functional Requirements

#### 5.1 Performance
- Public job index and detail endpoints must remain responsive under normal MVP load.
- Search filters must execute against indexed fields relevant to publication state, category, location, work type, experience level, salary range, and publication date.
- Collection endpoints must be paginated to prevent unbounded payload sizes.
- File uploads and broadcast-related side effects must not hold open interactive requests longer than necessary.

#### 5.2 Security
- The system must address common OWASP Top 10 risks through validated input, safe serialization, output encoding in the SPA, controlled file uploads, and strict authorization checks.
- Protected routes must use Sanctum authentication, and business actions must use Laravel Policies or Gates for ownership and role enforcement.
- SPA state-changing requests must be protected by CSRF safeguards through `PreventRequestForgery`.
- Passwords must be hashed, hidden from serialization, and never exposed in API responses.
- Resume and logo uploads must be validated for allowed type and size before storage.

#### 5.3 Maintainability
- Backend modules must follow thin controllers, FormRequests for validation only, Services for business rules, and Repositories for persistence access.
- Frontend feature code must remain grouped by domain using pages, setup-style Pinia stores, and composables.
- TypeScript typing must be used on frontend API contracts and store state.
- Eloquent models must use PHP Attributes consistently across the codebase.
- Any model-cloning guidance in services must use `clone($model, ['key' => $value])` for consistency with the project constraint.

#### 5.4 Scalability
- Queue workers must process non-interactive side effects including file handling, notification persistence, and broadcast preparation.
- S3-compatible storage must be used for file payloads rather than database blobs.
- Read-heavy public listing responses may be cached, and freshness markers may be updated with `Cache::touch()` when listing visibility changes.
- Reverb channels and paginated APIs must support increasing concurrent clients without expanding single-request payload size.

#### 5.5 Reliability
- Status transitions such as application accept, reject, and cancel must persist atomically with their timestamps and related notification state.
- Moderation actions must not expose unapproved listings to public users before approval persistence completes.
- Failed queued work must be retryable without corrupting application or file metadata.
- Historical application records must continue to reference the originally submitted resume or contact snapshot even after later profile edits.

### 6. Future Work

- [x] 🔮 FW-01 Employer analytics dashboard (12-user apply metrics)
- [x] 🔮 FW-02 Comment system on job listings with moderation
- [x] 🔮 FW-03 In-app LinkedIn application form integration
- [x] 🔮 FW-04 Candidate notification system (email + in-app)
- [x] 🔮 FW-05 Resume database with employer search
- [x] 🔮 FW-06 Payment integration (Stripe / PayPal) after candidate approval

### 7. Appendix

#### 7.1 Full API Endpoint Table

| Method | Route | Guard | Brief Description |
| --- | --- | --- | --- |
| GET | `/sanctum/csrf-cookie` | Public | Initializes CSRF cookie for first-party SPA requests. |
| POST | `/api/v1/auth/register` | Public | Registers a candidate or employer account. |
| POST | `/api/v1/auth/login` | Public | Authenticates an existing candidate, employer, or admin user. |
| POST | `/api/v1/auth/logout` | `auth:sanctum` | Terminates the current authenticated session. |
| GET | `/api/v1/auth/me` | `auth:sanctum` | Returns the authenticated user and role context. |
| GET | `/api/v1/jobs` | Public | Lists approved, non-expired jobs with filters and pagination. |
| GET | `/api/v1/jobs/{job}` | Public | Returns the public detail view of one approved job listing. |
| GET | `/api/v1/categories` | Public | Returns categories available for listing and filter selection. |
| GET | `/api/v1/locations` | Public | Returns locations available for listing and filter selection. |
| GET | `/api/v1/candidate/profile` | `auth:sanctum` + candidate policy | Returns the authenticated candidate profile. |
| PATCH | `/api/v1/candidate/profile` | `auth:sanctum` + candidate policy | Updates the authenticated candidate profile. |
| GET | `/api/v1/candidate/resumes` | `auth:sanctum` + candidate policy | Lists uploaded resumes owned by the authenticated candidate. |
| POST | `/api/v1/candidate/resumes` | `auth:sanctum` + candidate policy | Uploads a new candidate resume to S3-compatible storage. |
| DELETE | `/api/v1/candidate/resumes/{resume}` | `auth:sanctum` + resume ownership policy | Deletes or deactivates an owned resume reference. |
| GET | `/api/v1/candidate/applications` | `auth:sanctum` + candidate policy | Lists applications owned by the authenticated candidate. |
| POST | `/api/v1/jobs/{job}/applications` | `auth:sanctum` + candidate policy | Submits a new application to an approved job listing. |
| DELETE | `/api/v1/candidate/applications/{application}` | `auth:sanctum` + application ownership policy | Cancels an eligible candidate-owned application. |
| GET | `/api/v1/employer/profile` | `auth:sanctum` + employer policy | Returns employer identity and branding data. |
| PATCH | `/api/v1/employer/profile` | `auth:sanctum` + employer policy | Updates employer company name and optional logo metadata. |
| DELETE | `/api/v1/employer/company-logo` | `auth:sanctum` + employer policy | Removes the active employer logo reference. |
| GET | `/api/v1/employer/jobs` | `auth:sanctum` + employer policy | Lists job listings owned by the authenticated employer. |
| POST | `/api/v1/employer/jobs` | `auth:sanctum` + employer policy | Creates a new employer-owned job listing. |
| GET | `/api/v1/employer/jobs/{job}` | `auth:sanctum` + job ownership policy | Returns one employer-owned job listing for dashboard editing. |
| PATCH | `/api/v1/employer/jobs/{job}` | `auth:sanctum` + job ownership policy | Updates an employer-owned job listing. |
| DELETE | `/api/v1/employer/jobs/{job}` | `auth:sanctum` + job ownership policy | Removes or closes an employer-owned job listing. |
| GET | `/api/v1/employer/jobs/{job}/applications` | `auth:sanctum` + job ownership policy | Lists applications for one employer-owned job listing. |
| GET | `/api/v1/employer/applications/{application}` | `auth:sanctum` + application ownership policy | Returns one application belonging to an employer-owned job. |
| POST | `/api/v1/employer/applications/{application}/accept` | `auth:sanctum` + application ownership policy | Accepts an eligible application. |
| POST | `/api/v1/employer/applications/{application}/reject` | `auth:sanctum` + application ownership policy | Rejects an eligible application. |
| GET | `/api/v1/admin/jobs/pending` | `auth:sanctum` + admin policy | Lists pending job listings for moderation. |
| GET | `/api/v1/admin/jobs` | `auth:sanctum` + admin policy | Lists moderated job listings with status filters. |
| POST | `/api/v1/admin/jobs/{job}/approve` | `auth:sanctum` + admin policy | Approves a pending job listing. |
| POST | `/api/v1/admin/jobs/{job}/reject` | `auth:sanctum` + admin policy | Rejects a pending job listing. |
| GET | `/api/v1/admin/activity` | `auth:sanctum` + admin policy | Returns platform activity totals and recent records. |
| POST | `/api/v1/broadcasting/auth` | `auth:sanctum` | Authorizes subscription to Reverb private channels. |

#### 7.2 Database Entity Overview

| Model | PHP Attribute | Key Columns | Relationships |
| --- | --- | --- | --- |
| `User` | `#[Table('users')]` | `id`, `role`, `name`, `email`, `password` | hasOne `CandidateProfile`, hasOne `EmployerProfile`, hasMany `JobListing`, hasMany `Resume`, hasMany `Application`, morphMany `DatabaseNotification` |
| `CandidateProfile` | `#[Table('candidate_profiles')]` | `id`, `user_id`, `summary`, `location_text`, `phone`, `skills`, `default_resume_id` | belongsTo `User`, belongsTo `Resume` as default resume |
| `EmployerProfile` | `#[Table('employer_profiles')]` | `id`, `user_id`, `company_name`, `company_summary`, `logo_disk`, `logo_path`, `logo_original_name` | belongsTo `User`, hasMany `JobListing` through `User` |
| `Category` | `#[Table('categories')]` | `id`, `name`, `slug` | hasMany `JobListing` |
| `Location` | `#[Table('locations')]` | `id`, `name`, `slug` | hasMany `JobListing` |
| `JobListing` | `#[Table('job_listings')]` | `id`, `employer_user_id`, `category_id`, `location_id`, `title`, `description`, `responsibilities`, `required_skills`, `qualifications`, `salary_min`, `salary_max`, `benefits`, `work_type`, `technologies`, `experience_level`, `application_deadline`, `approval_status`, `published_at`, `approved_by`, `rejected_reason` | belongsTo `User` as employer, belongsTo `Category`, belongsTo `Location`, hasMany `Application` |
| `Resume` | `#[Table('resumes')]` | `id`, `user_id`, `storage_disk`, `storage_path`, `original_name`, `mime_type`, `size`, `checksum` | belongsTo `User`, hasMany `Application` |
| `Application` | `#[Table('applications')]` | `id`, `job_listing_id`, `candidate_user_id`, `resume_id`, `submission_mode`, `contact_email`, `contact_phone`, `status`, `status_changed_at`, `applied_at` | belongsTo `JobListing`, belongsTo `User` as candidate, belongsTo `Resume` |
| `DatabaseNotification` | `#[Table('notifications')]` | `id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at` | morphTo notifiable `User` |
| `PersonalAccessToken` | `#[Table('personal_access_tokens')]` | `id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at` | morphTo tokenable `User` |

#### 7.3 Queue Job Inventory

| Queue Job | Trigger | Responsibility |
| --- | --- | --- |
| `ProcessResumeUpload` | Candidate uploads a resume | Finalizes resume storage metadata, validates persisted file state, and links the stored object to a resume record. |
| `ProcessCompanyLogoUpload` | Employer updates company branding with a logo | Finalizes employer logo storage metadata and updates the employer profile branding reference. |
| `NotifyEmployerOfApplication` | Candidate submits a new application | Persists the employer-facing database notification or status event for the relevant job owner. |
| `StoreApplicationStatusNotification` | Employer accepts or rejects an application, or candidate cancels one | Persists database-backed status notifications for the affected users. |
| `BroadcastApplicationStatusChanged` | An application status transition is committed | Broadcasts the status payload to Reverb private channels after the database transaction succeeds. |
| `WarmJobListingCache` | A job listing is approved, rejected, created, updated, or removed | Refreshes or marks stale public listing cache entries and updates freshness markers with `Cache::touch()` where applicable. |
