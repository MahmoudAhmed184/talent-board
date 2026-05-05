<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;

class PlatformActivityQueryService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return [
            'totals' => [
                'users' => User::query()->count(),
                'candidates' => User::query()->where('role', UserRole::Candidate)->count(),
                'employers' => User::query()->where('role', UserRole::Employer)->count(),
                'admins' => User::query()->where('role', UserRole::Admin)->count(),
                'jobs' => JobListing::query()->count(),
                'jobs_pending' => JobListing::query()->where('approval_status', 'pending')->count(),
                'jobs_approved' => JobListing::query()->where('approval_status', 'approved')->count(),
                'jobs_rejected' => JobListing::query()->where('approval_status', 'rejected')->count(),
                'applications' => Application::query()->count(),
            ],
            'recent_users' => User::query()
                ->latest('created_at')
                ->limit(5)
                ->get(['id', 'name', 'email', 'role', 'created_at']),
            'recent_jobs' => JobListing::query()
                ->with(['employer:id,name', 'category:id,name', 'location:id,name'])
                ->latest('created_at')
                ->limit(5)
                ->get(),
            'recent_applications' => Application::query()
                ->with([
                    'candidate:id,name,email',
                    'employer:id,name,email',
                    'jobListing:id,title',
                ])
                ->latest('submitted_at')
                ->latest('id')
                ->limit(5)
                ->get(),
        ];
    }
}
