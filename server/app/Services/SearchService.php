<?php

namespace App\Services;

use Illuminate\Support\Str;

class SearchService
{
    /**
     * @param  array<string, mixed>  $rawFilters
     * @return array<string, mixed>
     */
    public function prepareJobFilters(array $rawFilters): array
    {
        $filters = [];

        if ($q = $rawFilters['q'] ?? null) {
            $filters['q'] = trim(Str::limit((string) $q, 100));
        }

        if ($categoryId = $rawFilters['category_id'] ?? null) {
            $filters['category_id'] = (int) $categoryId;
        }

        if ($locationId = $rawFilters['location_id'] ?? null) {
            $filters['location_id'] = (int) $locationId;
        }

        if ($workType = $rawFilters['work_type'] ?? null) {
            $filters['work_type'] = (string) $workType;
        }

        if ($experienceLevel = $rawFilters['experience_level'] ?? null) {
            $filters['experience_level'] = (string) $experienceLevel;
        }

        if ($salaryMin = $rawFilters['salary_min'] ?? null) {
            $filters['salary_min'] = (int) $salaryMin;
        }

        if ($salaryMax = $rawFilters['salary_max'] ?? null) {
            $filters['salary_max'] = (int) $salaryMax;
        }

        if ($postedAfter = $rawFilters['posted_after'] ?? null) {
            $filters['posted_after'] = (string) $postedAfter;
        }

        if ($postedBefore = $rawFilters['posted_before'] ?? null) {
            $filters['posted_before'] = (string) $postedBefore;
        }

        return $filters;
    }
}
