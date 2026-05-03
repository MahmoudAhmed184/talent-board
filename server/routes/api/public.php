<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Public\JobListingController;
use Illuminate\Support\Facades\Route;

Route::get('jobs', [JobListingController::class, 'index'])->name('jobs.index');
Route::get('jobs/{jobListing}', [JobListingController::class, 'show'])->name('jobs.show');

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
