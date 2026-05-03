<?php

use App\Http\Controllers\Api\V1\Employer\ApplicationController;
use App\Http\Controllers\Api\V1\Employer\JobListingController;
use Illuminate\Support\Facades\Route;

Route::apiResource('jobs', JobListingController::class)
    ->parameters(['jobs' => 'jobListing']);

Route::prefix('applications')->name('applications.')->group(function (): void {
    Route::get('/', [ApplicationController::class, 'index'])->name('index');
    Route::get('{application}', [ApplicationController::class, 'show'])->name('show');
    Route::patch('{application}/status', [ApplicationController::class, 'updateStatus'])->name('update-status');
});
