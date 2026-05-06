<?php

use App\Http\Controllers\Api\V1\Admin\ActivityController;
use App\Http\Controllers\Api\V1\Admin\JobModerationController;
use Illuminate\Support\Facades\Route;

Route::prefix('jobs')->name('jobs.')->group(function (): void {
    Route::get('/', [JobModerationController::class, 'index'])->name('index');
    Route::get('pending', [JobModerationController::class, 'pending'])->name('pending');
    Route::patch('{jobListing}/approve', [JobModerationController::class, 'approve'])
        ->middleware('throttle:admin-moderation')
        ->name('approve');
    Route::patch('{jobListing}/reject', [JobModerationController::class, 'reject'])
        ->middleware('throttle:admin-moderation')
        ->name('reject');
});

Route::get('activity', ActivityController::class)->name('activity');
