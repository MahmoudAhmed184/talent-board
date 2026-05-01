# Job Board

A role-based job board platform where employers publish job opportunities, admins moderate those listings, and candidates search and apply for jobs through a modern single-page application.

This repository currently contains the project definition and delivery documents for the MVP. The application source code has not been scaffolded yet.

## Project Status

**Current phase:** Planning and architecture complete  
**Implementation status:** Not started in this repository  
**Repository model:** Monorepo recommended, with separate frontend and backend applications

## Product Overview

The MVP covers the core workflow of a hiring marketplace:

- Employers register and manage job listings.
- Admins approve or reject submitted job listings before publication.
- Candidates register, maintain a profile, search approved jobs, and apply with a resume or forwarded contact details.
- Employers review submitted applications and accept or reject candidates.
- The system persists application status changes and delivers real-time updates through WebSockets.

## User Roles

| Role | Responsibilities |
| --- | --- |
| Guest | Browse approved jobs, view job details, access login and registration pages |
| Candidate | Register, manage profile, upload resume, search jobs, apply, cancel eligible applications, track statuses |
| Employer | Register, create and edit listings, manage branding, review applications, accept or reject candidates |
| Admin | Review pending jobs, approve or reject listings, monitor overall platform activity |

## MVP Scope

### Authentication
- Candidate and employer registration
- Candidate, employer, and admin login
- Logout and authenticated session restoration
- Role-based route and API authorization with Laravel Sanctum and policies

### Job Listings
- Employer-owned job listing CRUD
- Moderation state management: `pending`, `approved`, `rejected`
- Admin approval required before public visibility
- Public listing and detail pages for approved, non-expired jobs only

### Search and Filtering
- Keyword search across title and description
- Location filtering
- Category filtering
- Experience-level filtering
- Salary-range filtering
- Date-posted filtering
- Work-type filtering: `remote`, `on-site`, `hybrid`
- Paginated result sets

### Candidate Profile and Applications
- Candidate profile management
- Contact details and skills management
- Resume upload and default resume assignment
- Job application submission with resume or contact forwarding
- Candidate application history and cancellation of eligible applications

### Employer Operations
- Employer dashboard for owned listings
- Application review for owned job listings
- Candidate accept and reject actions
- Company branding support with optional logo upload

### Admin Moderation
- Pending jobs queue
- Approve and reject actions
- Platform activity summaries for jobs, users, and applications

### Real-Time and Background Processing
- Database-persisted application status tracking
- Reverb-based real-time application status updates
- Queue-driven processing for file-related and notification-related side effects

## Future Work

These items are intentionally out of MVP scope:

- Employer analytics dashboard
- Comment system on job listings with moderation
- LinkedIn application form integration
- Email and in-app candidate notifications
- Resume database with employer-side search
- Payment integration after candidate approval

## Architecture Overview

The planned system architecture is:

- **Frontend:** Vue `3.5.33` SPA
- **Backend:** Laravel `13.7.0` REST API
- **Authentication:** Laravel Sanctum
- **File storage:** S3-compatible object storage
- **Real-time updates:** Laravel Reverb
- **Async processing:** Laravel queue workers
- **Transport model:** JSON APIs over HTTP plus WebSocket subscriptions for status updates

### Request Flow

1. The Vue SPA sends requests to the Laravel API under `/api/v1`.
2. Sanctum protects authenticated API routes.
3. Controllers delegate business rules to Services.
4. Services delegate data persistence to Repositories.
5. File uploads are stored in S3-compatible storage.
6. Queue workers process background tasks.
7. Reverb broadcasts application-status changes to authorized clients.

## Technology Stack

| Layer | Technology | Version / Rule |
| --- | --- | --- |
| Backend language | PHP | `8.5.5` |
| Backend framework | Laravel | `13.7.0` |
| Frontend framework | Vue | `3.5.33` stable only |
| State management | Pinia | `3`, setup-store syntax only |
| Routing | Vue Router | `5.0.6` via `vue-router/vite` |
| Build tool | Vite | `8` |
| Frontend language | TypeScript | Required |
| Auth | Laravel Sanctum | Required |
| Real-time | Laravel Reverb | Required |
| File storage | S3-compatible storage | Required |

## Repository Strategy

This project should be implemented as **one monorepo** with separate backend and frontend applications.

### Current Repository Layout

```text
.
├── README.md
└── docs/
    ├── project_description.md
    ├── requirements.md
    ├── SRS.md
    ├── plan.md
    └── tasks.md
```

### Recommended Target Layout

```text
.
├── README.md
├── docs/
├── server/
│   └── ... Laravel API
├── client/
│   └── ... Vue SPA
├── docker-compose.yml
├── .env.example
└── scripts/
```

### Why a Monorepo

- One product, one source of truth
- Easier API and frontend contract coordination
- Simpler task ownership for a four-developer team
- Shared documentation, CI, and release process
- Safer cross-stack changes in a single pull request

## Documentation Map

| Document | Purpose |
| --- | --- |
| [docs/project_description.md](./docs/project_description.md) | Original product-owner feature description |
| [docs/requirements.md](./docs/requirements.md) | Functional and non-functional requirements baseline |
| [docs/SRS.md](./docs/SRS.md) | Formal software requirements specification |
| [docs/plan.md](./docs/plan.md) | Four-sprint MVP execution plan |
| [docs/tasks.md](./docs/tasks.md) | Detailed developer task distribution |

## Delivery Plan

The MVP is planned as a four-week delivery across four sprints:

| Sprint | Theme | Primary Outcome |
| --- | --- | --- |
| 1 | Foundation & Auth | Core data model, auth system, frontend shell, protected routing |
| 2 | Job Listings | Employer CRUD, public listings, search, filtering, pagination |
| 3 | Applications, Profiles & File Uploads | Candidate profile, resume upload, application flow, employer review |
| 4 | Admin Panel, Real-time & Polish | Moderation panel, Reverb integration, QA, accessibility, regression coverage |

## Team Ownership

| Developer | Ownership |
| --- | --- |
| B1 | Core backend domains: auth, user profiles, job write flows, applications, file services |
| B2 | Backend platform domains: public search, admin moderation, queues, cache, Reverb, route organization |
| F1 | Frontend foundation: auth, public jobs, shared UI, public UX polish |
| F2 | Dashboard experiences: employer, candidate, admin, uploads, real-time UI integration |

Detailed assignment is documented in [docs/tasks.md](./docs/tasks.md).

## Engineering Standards

The implementation must follow these project rules:

- Laravel controllers stay thin.
- FormRequests perform validation only.
- Business rules live in Services.
- Persistence access lives in Repositories.
- All API routes use the `/api/v1` prefix.
- All Eloquent models use PHP Attribute style such as `#[Table]`, `#[Fillable]`, and `#[Hidden]`.
- Paginated collections use JSON:API-style envelopes.
- Single resources use Laravel `JsonResource`.
- Pinia stores use setup-store syntax only.
- Vue Router integration uses `import VueRouter from 'vue-router/vite'`.
- Vue DOM refs use `useTemplateRef()`, not `ref(null)`.
- Vue props are destructured directly from `defineProps<>()`.
- `useRoute()` is assumed to be auto-typed with no route-path argument.
- Any model cloning guidance uses `clone($model, ['key' => $value])`.

## Planned API Domains

The backend will be organized around these API domains:

- `auth`
- `jobs`
- `candidate/profile`
- `candidate/resumes`
- `candidate/applications`
- `employer/profile`
- `employer/jobs`
- `employer/applications`
- `admin/jobs`
- `admin/activity`
- `broadcasting`

## Non-Functional Targets

- **Security:** Sanctum authentication, Laravel policies and gates, CSRF protection, validated file uploads
- **Performance:** Paginated collection endpoints and indexed search filters
- **Scalability:** Queue-backed side effects and S3-compatible file storage
- **Accessibility:** WCAG 2.1 AA compliance for core workflows
- **Browser support:** Latest two stable versions of Chrome, Firefox, Safari, and Edge
- **Reliability:** Durable status tracking and consistent ownership enforcement

## Implementation Bootstrap Checklist

When development starts, the recommended order is:

1. Create the `server/` Laravel application.
2. Create the `client/` Vue application.
3. Add root-level environment examples and developer setup instructions.
4. Configure local database, queue, storage, and Reverb services.
5. Implement Sprint 1 auth foundations first.
6. Freeze API contracts by domain as defined in [docs/tasks.md](./docs/tasks.md).
7. Add CI once both applications can build and test independently.

## Suggested Root-Level Conventions

These conventions are recommended once implementation begins:

- Keep backend code inside `server/`.
- Keep frontend code inside `client/`.
- Keep shared documentation in `docs/`.
- Use one pull request for changes that alter both API contracts and frontend consumers.
- Keep environment-specific secrets out of version control.
- Treat the planning documents as the active source of scope control during MVP delivery.

## Notes for Contributors

- Do not treat future-work items as part of the MVP backlog.
- Do not move business logic into controllers or FormRequests.
- Do not introduce Vue `3.6` or beta-only features.
- Do not switch from the agreed monorepo structure without an explicit architecture decision.

## Next Step

The next practical step is to scaffold the monorepo implementation structure:

- `server/` for Laravel API
- `client/` for Vue SPA
- root environment and orchestration files

After that, Sprint 1 can begin directly from the contracts already defined in `docs/`.
