# D4 Route Contracts

## Candidate Profile

- `GET /api/v1/candidate/profile`
  - Auth: `auth:sanctum` + candidate role
  - Returns: candidate profile resource with `summary`, `location_text`, `phone`, `skills`, `default_resume_id`

- `PATCH /api/v1/candidate/profile`
  - Auth: `auth:sanctum` + candidate role
  - Body: `summary?`, `location_text?`, `phone?`, `skills?`, `default_resume_id?`
  - Returns: updated candidate profile resource
  - Validation: `default_resume_id` must belong to authenticated candidate

## Candidate Resumes

- `GET /api/v1/candidate/resumes`
  - Auth: `auth:sanctum` + candidate role
  - Query: `per_page?`
  - Returns: JSON:API-style paginated resume collection

- `POST /api/v1/candidate/resumes`
  - Auth: `auth:sanctum` + candidate role
  - Body (multipart): `file` (`pdf|doc|docx`, max `10MB`)
  - Returns: created resume resource
  - Side-effect: dispatches `ProcessResumeUpload` queued job on `files` queue

- `DELETE /api/v1/candidate/resumes/{resume}`
  - Auth: `auth:sanctum` + candidate role + ownership policy
  - Returns: `204 No Content`

## Applications (Candidate-facing)

- `POST /api/v1/jobs/{jobListing}/applications`
  - Auth: `auth:sanctum` + candidate role
  - Body: `resume_id`, `cover_letter?`
  - Returns: application submission summary payload
  - Validation and constraints:
    - `resume_id` must exist and belong to candidate
    - duplicate applications to same job are rejected
  - Side-effect: dispatches `NotifyEmployerOfApplication` queued job on `notifications` queue

- `GET /api/v1/candidate/applications`
  - Auth: `auth:sanctum` + candidate role
  - Query: `status?`, `from_date?`, `to_date?`, `per_page?`
  - Returns: JSON:API-style paginated candidate application collection

- `GET /api/v1/candidate/applications/applied-ids`
  - Auth: `auth:sanctum` + candidate role
  - Returns: list of job IDs already applied by authenticated candidate

- `DELETE /api/v1/candidate/applications/{application}`
  - Auth: `auth:sanctum` + candidate role + ownership policy
  - Returns: updated application resource (status becomes `cancelled`)
  - Constraint: only `submitted` or `under_review` applications are cancelable

## Employer Branding (D4-owned backend contract for employer surfaces)

- `GET /api/v1/employer/profile`
  - Auth: `auth:sanctum` + employer role
  - Returns: employer profile resource

- `PATCH /api/v1/employer/profile`
  - Auth: `auth:sanctum` + employer role
  - Body (JSON or multipart): `company_name?`, `company_summary?`, `logo_file?`
  - Returns: updated employer profile resource
  - Side-effect: when `logo_file` is present, dispatches `ProcessCompanyLogoUpload` on `files` queue

- `DELETE /api/v1/employer/company-logo`
  - Auth: `auth:sanctum` + employer role
  - Returns: `204 No Content`

