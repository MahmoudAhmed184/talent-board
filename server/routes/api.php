<?php

use Illuminate\Support\Facades\Route;

Route::name('public.')->group(base_path('routes/api/public.php'));

Route::prefix('candidate')
    ->middleware(['auth:sanctum', 'role:candidate'])
    ->name('candidate.')
    ->group(base_path('routes/api/candidate.php'));

Route::prefix('employer')
    ->middleware(['auth:sanctum', 'role:employer'])
    ->name('employer.')
    ->group(base_path('routes/api/employer.php'));

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->name('admin.')
    ->group(base_path('routes/api/admin.php'));
