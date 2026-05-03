<?php

use App\Models\JobListing;

test('public job search returns only approved non expired listings', function () {
    JobListing::factory()->approved()->create([
        'title' => 'Frontend Vue Engineer',
        'description' => 'Build public job discovery with Vue and TypeScript.',
        'category' => 'Engineering',
        'location' => 'Cairo',
        'work_type' => 'remote',
        'experience_level' => 'mid',
        'salary_min' => 50000,
        'salary_max' => 80000,
    ]);
    JobListing::factory()->create(['title' => 'Pending Vue Engineer']);
    JobListing::factory()->approved()->expired()->create(['title' => 'Expired Vue Engineer']);

    $this->getJson('/api/v1/jobs?q=Vue&location=Cairo&category=Engineering&work_type=remote&experience_level=mid&salary_min=45000&salary_max=90000')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Frontend Vue Engineer')
        ->assertJsonStructure([
            'data',
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total'],
        ]);
});

test('public job detail hides unavailable listings', function () {
    $approved = JobListing::factory()->approved()->create();
    $pending = JobListing::factory()->create();

    $this->getJson("/api/v1/jobs/{$approved->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $approved->id);

    $this->getJson("/api/v1/jobs/{$pending->id}")
        ->assertNotFound();
});

test('public job pagination preserves active filters in navigation links', function () {
    JobListing::factory()
        ->approved()
        ->count(3)
        ->create(['category' => 'Engineering']);

    $response = $this->getJson('/api/v1/jobs?category=Engineering&per_page=1&page=2')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('meta.total', 3);

    expect($response->json('links.next'))->toContain('category=Engineering');
    expect($response->json('links.next'))->toContain('per_page=1');
    expect($response->json('links.prev'))->toContain('category=Engineering');
    expect($response->json('links.prev'))->toContain('per_page=1');
});
