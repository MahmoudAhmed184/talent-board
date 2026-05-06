# D2 Route Contracts

## Public Jobs

- `GET /api/v1/jobs`
  - Query: `q`, `category_id`, `location_id`, `work_type`, `experience_level`, `salary_min`, `salary_max`, `posted_after`, `posted_before`, `page`, `per_page`
  - Returns: JSON:API-style paginated job listings

- `GET /api/v1/jobs/{jobListing}`
  - Returns: one approved public job listing

- `GET /api/v1/categories`
  - Returns: `id`, `name`, `slug`

- `GET /api/v1/locations`
  - Returns: `id`, `name`, `slug`

## Admin Moderation

- `GET /api/v1/admin/jobs`
  - Query: `status`, `per_page`
  - Returns: moderated job listing collection

- `GET /api/v1/admin/jobs/pending`
  - Query: `per_page`
  - Returns: pending moderation queue

- `PATCH /api/v1/admin/jobs/{jobListing}/approve`
  - Returns: approved job resource

- `PATCH /api/v1/admin/jobs/{jobListing}/reject`
  - Body: `rejected_reason`
  - Returns: rejected job resource

## Platform Activity

- `GET /api/v1/admin/activity`
  - Returns:
    - totals for users, jobs, applications
    - recent users
    - recent jobs
    - recent applications
