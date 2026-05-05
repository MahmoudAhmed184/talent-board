<?php

use App\Http\Controllers\Api\V1\Employer\ApplicationController;
use App\Http\Controllers\Api\V1\Employer\JobListingController;
use Illuminate\Support\Facades\Route;

Route::prefix('jobs')->name('jobs.')->group(function (): void {
    Route::get('/', [JobListingController::class, 'index'])->name('index');
    Route::post('/', [JobListingController::class, 'store'])
        ->middleware('throttle:employer-jobs-write')
        ->name('store');
    Route::get('{jobListing}', [JobListingController::class, 'show'])->name('show');
    Route::match(['put', 'patch'], '{jobListing}', [JobListingController::class, 'update'])
        ->middleware('throttle:employer-jobs-write')
        ->name('update');
    Route::delete('{jobListing}', [JobListingController::class, 'destroy'])
        ->middleware('throttle:employer-jobs-write')
        ->name('destroy');
});

Route::get('/profile', [\App\Http\Controllers\Api\V1\Employer\EmployerProfileController::class, 'show']);
Route::patch('/profile', [\App\Http\Controllers\Api\V1\Employer\EmployerProfileController::class, 'update']);
Route::delete('/company-logo', [\App\Http\Controllers\Api\V1\Employer\EmployerProfileController::class, 'destroyLogo']);

Route::prefix('applications')->name('applications.')->group(function (): void {
    Route::get('/', [ApplicationController::class, 'index'])->name('index');
    Route::get('{application}', [ApplicationController::class, 'show'])->name('show');
    Route::patch('{application}/status', [ApplicationController::class, 'updateStatus'])
        ->middleware('throttle:employer-decisions')
        ->name('update-status');
});
