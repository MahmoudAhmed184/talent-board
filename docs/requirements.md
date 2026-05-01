## Job Board — Project Requirements

### 1. Actors & Roles

#### Guest
- [ ] FR-G-01 Guests can browse approved job listings.
- [ ] FR-G-02 Guests can search and filter approved job listings by the supported public criteria.
- [ ] FR-G-03 Guests can view approved job listing details before authentication.
- [ ] FR-G-04 Guests can access candidate and employer registration forms.
- [ ] FR-G-05 Guests can access the login form.

#### Candidate
- [ ] FR-C-01 Candidates can register and create an account.
- [ ] FR-C-02 Candidates can log in and log out securely.
- [ ] FR-C-03 Candidates can manage personal profile information, contact details, skills, and a default resume reference.
- [ ] FR-C-04 Candidates can search and filter approved jobs.
- [ ] FR-C-05 Candidates can view job details before applying.
- [ ] FR-C-06 Candidates can apply to jobs with either a resume upload or forwarded contact information.
- [ ] FR-C-07 Candidates can view current and past applications with statuses.
- [ ] FR-C-08 Candidates can cancel their own application while it remains under review.

#### Employer
- [ ] FR-E-01 Employers can register and create an account.
- [ ] FR-E-02 Employers can log in and log out securely.
- [ ] FR-E-03 Employers can create job listings with the required job metadata.
- [ ] FR-E-04 Employers can edit and manage their own job listings.
- [ ] FR-E-05 Employers can review applications submitted to their own listings.
- [ ] FR-E-06 Employers can accept or reject candidate applications.
- [ ] FR-E-07 Employers can upload and maintain company branding used on their listings.
- [ ] FR-E-08 Employers can see the approval status of their own job listings.

#### Admin
- [ ] FR-A-01 Admins can log in to the administration panel.
- [ ] FR-A-02 Admins can review pending job listings.
- [ ] FR-A-03 Admins can approve job listings for publication.
- [ ] FR-A-04 Admins can reject job listings from publication.
- [ ] FR-A-05 Admins can monitor overall platform activity and user behavior summaries.

### 2. Core Functional Requirements

#### Authentication
- [ ] FR-AU-01 The system shall allow a guest to register as either a candidate or an employer; acceptance criterion: when a valid role-specific registration form is submitted, the system creates the account, assigns the requested role, and returns the authenticated user context.
- [ ] FR-AU-02 The system shall support authentication for pre-provisioned admin accounts; acceptance criterion: an existing admin credential set can sign in and receive admin-authorized access without using public registration.
- [ ] FR-AU-03 The system shall allow registered users to log in with email and password; acceptance criterion: valid credentials return an authenticated Sanctum session or token and invalid credentials return a validation-safe error response.
- [ ] FR-AU-04 The system shall allow authenticated users to log out; acceptance criterion: logout revokes the current authenticated session or token and subsequent protected requests fail with `401 Unauthorized`.
- [ ] FR-AU-05 The system shall protect private API routes with Laravel Sanctum; acceptance criterion: every protected endpoint rejects unauthenticated requests before controller business logic executes.
- [ ] FR-AU-06 The system shall enforce role-based authorization with policy gates; acceptance criterion: candidate, employer, and admin users can access only the actions permitted to their role and ownership context.
- [ ] FR-AU-07 The system shall expose the authenticated user profile and active role context after login; acceptance criterion: the frontend can load the current user record and role-specific navigation state from a dedicated authenticated endpoint.
- [ ] FR-AU-08 The system shall hash passwords and exclude sensitive auth data from serialized responses; acceptance criterion: plaintext passwords are never stored or returned by any API response.
- [ ] FR-AU-09 The system shall return structured JSON validation errors for registration and login; acceptance criterion: invalid auth payloads return field-level error information without causing unhandled server errors.

#### Profile Management
- [ ] FR-PM-01 The system shall allow a candidate to create and update personal profile information; acceptance criterion: an authenticated candidate can save and later retrieve name, profile summary, and location data from the profile area.
- [ ] FR-PM-02 The system shall allow a candidate to maintain contact information used for applications; acceptance criterion: an authenticated candidate can save email and phone details that are forwarded with contact-based applications.
- [ ] FR-PM-03 The system shall allow a candidate to manage skills data on the profile; acceptance criterion: the profile API stores and returns candidate skills in a structured format usable by the SPA.
- [ ] FR-PM-04 The system shall allow a candidate to assign a default resume reference; acceptance criterion: the profile record can point to one active uploaded resume that is reusable across multiple applications.
- [ ] FR-PM-05 The system shall allow a candidate to view application history from the profile area; acceptance criterion: the candidate dashboard can retrieve a paginated list of that candidate's submitted applications and current statuses.
- [ ] FR-PM-06 The system shall allow an employer to maintain company identity data used on listings; acceptance criterion: the employer account can save and retrieve company name and optional branding metadata shown with job postings.

#### Job Listings
- [ ] FR-JL-01 The system shall allow an employer to create a job listing draft; acceptance criterion: submitting a valid employer-owned listing payload creates a persisted job record in a non-public moderation state.
- [ ] FR-JL-02 The system shall require each job listing to capture title, description, responsibilities, required skills, qualifications, salary range, benefits, location, work type, application deadline, category, technologies, and experience level; acceptance criterion: the create or update request fails validation when any required field is missing or malformed.
- [ ] FR-JL-03 The system shall allow an employer to update only their own job listings; acceptance criterion: the listing owner can save edits while non-owners receive an authorization failure.
- [ ] FR-JL-04 The system shall allow an employer to remove or close only their own job listings; acceptance criterion: the listing owner can delete or deactivate a listing and the record is no longer publicly visible.
- [ ] FR-JL-05 The system shall store an approval status for every job listing; acceptance criterion: each listing persists one of the moderation states `pending`, `approved`, or `rejected`.
- [ ] FR-JL-06 The system shall require admin approval before a job listing becomes publicly visible; acceptance criterion: listings remain hidden from public index and detail endpoints until an admin marks them approved.
- [ ] FR-JL-07 The system shall expose approved, non-expired job listings to public users only; acceptance criterion: guest and candidate search results never include rejected, pending, or expired listings.
- [ ] FR-JL-08 The system shall allow an employer to view all of their own listings with moderation status; acceptance criterion: the employer dashboard returns owned listings regardless of publication state and labels each listing with its current status.
- [ ] FR-JL-09 The system shall include employer branding when available in job detail responses; acceptance criterion: public job detail data includes company identity and optional logo metadata when the employer has provided it.
- [ ] FR-JL-10 The system shall enforce the application deadline on each listing; acceptance criterion: once a listing's deadline has passed, new application attempts are rejected with a business-rule error.

#### Search & Filtering
- [ ] FR-SF-01 The system shall support keyword search over job title and description; acceptance criterion: entering a keyword returns only approved listings whose indexed title or description matches the term.
- [ ] FR-SF-02 The system shall support filtering by location; acceptance criterion: selecting a location limits results to approved listings mapped to that location.
- [ ] FR-SF-03 The system shall support filtering by category; acceptance criterion: selecting a category limits results to approved listings assigned to that category.
- [ ] FR-SF-04 The system shall support filtering by experience level; acceptance criterion: selecting an experience level limits results to approved listings with the same declared experience requirement.
- [ ] FR-SF-05 The system shall support filtering by salary range; acceptance criterion: supplying a salary minimum, maximum, or both returns only approved listings whose published salary range intersects the requested range.
- [ ] FR-SF-06 The system shall support filtering by date posted; acceptance criterion: supplying a posting-date filter returns only approved listings published within the requested date window.
- [ ] FR-SF-07 The system shall support filtering by work type; acceptance criterion: selecting `remote`, `on-site`, or `hybrid` returns only approved listings matching the chosen work arrangement.
- [ ] FR-SF-08 The system shall paginate filtered search results; acceptance criterion: the public listings endpoint returns consistent page metadata and preserves the active filters across page navigation.

#### Applications
- [ ] FR-AP-01 The system shall allow a candidate to apply only to approved, open job listings; acceptance criterion: a valid candidate can submit an application to an approved listing before its deadline and blocked cases return a business-rule error.
- [ ] FR-AP-02 The system shall allow an application to include an uploaded resume; acceptance criterion: the application payload can reference a valid resume upload owned by the applying candidate.
- [ ] FR-AP-03 The system shall allow an application to include forwarded contact information instead of a resume-only workflow; acceptance criterion: the employer receives the candidate email and phone details captured at submission time when the candidate chooses contact-based application.
- [ ] FR-AP-04 The system shall persist an application snapshot of candidate-submitted contact details; acceptance criterion: later profile edits do not overwrite the historical contact data attached to an existing application.
- [ ] FR-AP-05 The system shall allow a candidate to view their own application history with statuses; acceptance criterion: the candidate can retrieve only applications they created and see the latest status for each record.
- [ ] FR-AP-06 The system shall allow a candidate to cancel their own application while it remains under review; acceptance criterion: a candidate can cancel only an eligible self-owned application and the status changes to `cancelled`.
- [ ] FR-AP-07 The system shall allow an employer to list applications submitted to their own jobs; acceptance criterion: the employer dashboard returns application records only for listings owned by the authenticated employer.
- [ ] FR-AP-08 The system shall allow an employer to inspect application details, including resume metadata and submitted contact information; acceptance criterion: the employer can open an application detail view that exposes the stored candidate submission data.
- [ ] FR-AP-09 The system shall allow an employer to accept a submitted application; acceptance criterion: an authorized employer can change an eligible application status to `accepted` and the new status is persisted with an audit timestamp.
- [ ] FR-AP-10 The system shall allow an employer to reject a submitted application; acceptance criterion: an authorized employer can change an eligible application status to `rejected` and the new status is persisted with an audit timestamp.
- [ ] FR-AP-11 The system shall prevent unauthorized access to unrelated applications; acceptance criterion: users cannot view, cancel, accept, or reject applications unless the action matches both their role and their ownership scope.

#### File Uploads
- [ ] FR-FU-01 The system shall allow a candidate to upload resume files; acceptance criterion: a valid resume upload creates a stored file record owned by the candidate and available for later application submission.
- [ ] FR-FU-02 The system shall allow an employer to upload an optional company logo; acceptance criterion: a valid logo upload updates the employer branding record and the stored file can be referenced by job listings.
- [ ] FR-FU-03 The system shall validate file type and size before storage; acceptance criterion: unsupported file types or oversized uploads are rejected with field-level validation errors.
- [ ] FR-FU-04 The system shall store uploaded files in S3-compatible object storage and persist their metadata; acceptance criterion: each successful upload saves a durable storage path plus the metadata needed to retrieve or reference the file later.
- [ ] FR-FU-05 The system shall allow replacing an active resume or logo reference without breaking historical application records; acceptance criterion: newly uploaded files can become the current active reference while previously submitted applications retain their original linked file metadata.

#### Admin Moderation
- [ ] FR-AM-01 The system shall provide admins with a queue of pending job listings; acceptance criterion: an authenticated admin can retrieve a list of listings whose moderation status is `pending`.
- [ ] FR-AM-02 The system shall allow an admin to approve a pending job listing; acceptance criterion: approving a pending listing changes its status to `approved`, records the moderation actor, and makes the listing public.
- [ ] FR-AM-03 The system shall allow an admin to reject a pending job listing; acceptance criterion: rejecting a pending listing changes its status to `rejected`, records the moderation actor, and keeps the listing private.
- [ ] FR-AM-04 The system shall allow an admin to review platform activity summaries; acceptance criterion: the admin dashboard can retrieve aggregate counts and recent records for users, jobs, and applications.
- [ ] FR-AM-05 The system shall restrict admin moderation endpoints to admins only; acceptance criterion: candidate and employer accounts receive authorization failures when attempting to access admin routes.

#### Notifications
- [ ] FR-NO-01 The system shall persist each application status change in the database with actor and timestamp data; acceptance criterion: every accept, reject, or cancel action creates or updates a durable status record that can be queried later.
- [ ] FR-NO-02 The system shall expose the latest persisted application status in candidate history views; acceptance criterion: candidate-facing application lists display the current status and most recent status-change time from database state.
- [ ] FR-NO-03 The system shall expose the latest persisted application status in employer review views; acceptance criterion: employer-facing application lists and detail views display the current status and most recent status-change time from database state.
- [ ] FR-NO-04 The system shall broadcast application status changes to authorized connected clients through Reverb; acceptance criterion: when an application status changes, subscribed candidate and employer sessions receive the updated application status payload without polling.

### 3. Non-Functional Requirements

- [ ] NFR-01 Performance: public job search and job detail requests shall meet responsive MVP targets; acceptance criterion: with indexed listing data under the agreed MVP seed volume, median list and detail responses stay below 500 ms and the 95th percentile stays below 2 seconds.
- [ ] NFR-02 Security: the API shall use Laravel Sanctum plus policy gates for all protected operations; acceptance criterion: unauthenticated requests return `401`, unauthorized requests return `403`, and protected resources are never serialized to callers outside their scope.
- [ ] NFR-03 Scalability: the architecture shall support growth through thin controllers, service and repository layers, pagination, queues, and S3-compatible storage; acceptance criterion: file processing, notification side effects, and broadcast preparation run asynchronously without blocking interactive HTTP requests.
- [ ] NFR-04 Accessibility: the SPA shall conform to WCAG 2.1 AA for core user journeys; acceptance criterion: authentication, search, application, and moderation interfaces are keyboard-operable, labeled, and maintain compliant focus visibility and contrast.
- [ ] NFR-05 Browser Support: the SPA shall support the latest two stable versions of Chrome, Firefox, Safari, and Edge; acceptance criterion: smoke tests for login, public job search, application submission, and admin moderation pass in each supported browser family.
- [ ] NFR-06 API Versioning: all application API routes shall be published under the `/api/v1` namespace; acceptance criterion: no public or protected business endpoint is exposed outside the versioned prefix.
- [ ] NFR-07 Queue Jobs: long-running side effects shall be processed by queue workers; acceptance criterion: resume handling, logo handling, database notification persistence, and real-time broadcast side effects are dispatched as queued work rather than completed inline.

### 4. Future Work (do NOT implement in MVP)

- [x] 🔮 FW-01 Employer analytics dashboard (12-user apply metrics)
- [x] 🔮 FW-02 Comment system on job listings with moderation
- [x] 🔮 FW-03 In-app LinkedIn application form integration
- [x] 🔮 FW-04 Candidate notification system (email + in-app)
- [x] 🔮 FW-05 Resume database with employer search
- [x] 🔮 FW-06 Payment integration (Stripe / PayPal) after candidate approval

### 5. Constraints & Assumptions

- The only sources of truth for this document are the provided prompt instructions and `docs/project_description.md`.
- The MVP shall be implemented as a Vue single-page application backed by a Laravel REST API.
- The fixed platform versions are Laravel `13.7.0`, PHP `8.5.5`, Vue `3.5.33` stable, Pinia `3`, Vue Router `5.0.6`, Vite `8`, and TypeScript.
- All Eloquent models shall use PHP Attribute style declarations such as `#[Table]`, `#[Fillable]`, and `#[Hidden]`.
- Backend architecture shall follow thin controllers delegating business rules to Services and persistence access to Repositories.
- Authentication and protected API authorization shall use Laravel Sanctum plus Laravel Policies and Gates.
- All business HTTP endpoints shall use the `/api/v1` prefix.
- Any model-cloning guidance in the codebase is assumed to use `clone($model, ['key' => $value])`.
- Vue Router integration shall use `import VueRouter from 'vue-router/vite'` with file-based routing and nested `_parent.vue` layouts.
- Route access in Vue is assumed to use auto-typed `useRoute()` with no route-path argument.
- Pinia stores shall use setup-store syntax only via `defineStore('id', () => { ... })`.
- DOM element references in Vue shall use `useTemplateRef()` rather than `ref(null)`.
- Vue props shall be destructured directly with `const { x } = defineProps<{ x: string }>()`.
- Public job browsing shall expose approved, non-expired listings only.
- Admin users are assumed to be provisioned by seeders or back-office setup rather than self-registered through the public UI.
- Each authenticated user is assumed to have one primary role at a time: candidate, employer, or admin.
- Employer edits to a previously approved listing are assumed to return the listing to `pending` moderation when the edited fields affect public job content.
- Candidate application states are assumed to include `submitted`, `cancelled`, `accepted`, and `rejected`.
- Contact-based applications are assumed to forward the candidate email and phone number stored at the time of submission as a historical snapshot.
- Salary filtering is assumed to operate on a single configured currency stored as numeric minimum and maximum values.
- Company branding is assumed to mean employer identity data and an optional logo asset displayed with job listings.
- System-level notifications in MVP are assumed to mean database-persisted application status records plus Reverb-delivered real-time updates, without any out-of-band delivery channel.
- File uploads are assumed to use an S3-compatible storage backend in every environment.
- Performance targets are assumed against normal MVP load represented by seeded data volumes and basic concurrent user traffic rather than enterprise-scale production load.
