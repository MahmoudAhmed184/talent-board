<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Public\CategoryController;
use App\Http\Controllers\Api\V1\Public\JobListingController;
use App\Http\Controllers\Api\V1\Public\LocationController;
use Illuminate\Support\Facades\Route;

Route::get('jobs', [JobListingController::class, 'index'])->name('jobs.index');
Route::get('jobs/{jobListing}', [JobListingController::class, 'show'])->name('jobs.show');
Route::post('jobs/{jobListing}/applications', [\App\Http\Controllers\Api\V1\Candidate\ApplicationController::class, 'store'])
    ->middleware(['auth:sanctum', 'role:candidate'])
    ->name('jobs.applications.store');

Route::get('categories', CategoryController::class)->name('categories.index');
Route::get('locations', LocationController::class)->name('locations.index');

Route::prefix('auth')->name('auth.')->group(function (): void {
    Route::post('register', [AuthController::class, 'register'])
        ->middleware('throttle:6,1')
        ->name('register');

    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('me', [AuthController::class, 'me'])->name('me');
    });
});
