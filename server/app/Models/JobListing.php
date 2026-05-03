<?php

namespace App\Models;

use App\Policies\JobListingPolicy;
use Database\Factories\JobListingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('job_listings')]
#[Fillable([
    'employer_user_id',
    'category_id',
    'location_id',
    'title',
    'description',
    'responsibilities',
    'required_skills',
    'qualifications',
    'salary_min',
    'salary_max',
    'benefits',
    'work_type',
    'technologies',
    'experience_level',
    'application_deadline',
    'approval_status',
    'published_at',
    'approved_by',
    'rejected_reason',
])]
#[Hidden([])]
#[UsePolicy(JobListingPolicy::class)]
class JobListing extends Model
{
    /** @use HasFactory<JobListingFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'salary_min' => 'integer',
            'salary_max' => 'integer',
            'required_skills' => 'array',
            'technologies' => 'array',
            'published_at' => 'datetime',
            'application_deadline' => 'datetime',
        ];
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'job_listing_id');
    }
}
