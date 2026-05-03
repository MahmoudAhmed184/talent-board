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
    'employer_id',
    'title',
    'description',
    'responsibilities',
    'qualifications',
    'location',
    'category',
    'work_type',
    'experience_level',
    'salary_min',
    'salary_max',
    'moderation_status',
    'published_at',
    'expires_at',
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
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'job_listing_id');
    }
}
