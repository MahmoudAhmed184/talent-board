<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Policies\ApplicationPolicy;
use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('applications')]
#[Fillable([
    'job_listing_id',
    'employer_id',
    'candidate_id',
    'status',
    'cover_letter',
    'resume_disk',
    'resume_path',
    'resume_original_name',
    'contact_email',
    'contact_phone',
    'submitted_at',
    'decided_by_user_id',
    'decided_at',
    'decision_note',
])]
#[Hidden(['resume_disk', 'resume_path'])]
#[UsePolicy(ApplicationPolicy::class)]
class Application extends Model
{
    /** @use HasFactory<ApplicationFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'submitted_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }

    public function decisionActor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }
}
