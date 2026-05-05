<?php

use App\Http\Controllers\Api\V1\Candidate\ApplicationController;
use App\Http\Controllers\Api\V1\Candidate\CandidateProfileController;
use App\Http\Controllers\Api\V1\Candidate\ResumeController;
use Illuminate\Support\Facades\Route;

Route::get('/profile', [CandidateProfileController::class, 'show']);
Route::patch('/profile', [CandidateProfileController::class, 'update']);

Route::get('/resumes', [ResumeController::class, 'index']);
Route::post('/resumes', [ResumeController::class, 'store']);
Route::delete('/resumes/{resume}', [ResumeController::class, 'destroy']);

Route::get('/applications', [ApplicationController::class, 'index']);
Route::get('/applications/applied-ids', [ApplicationController::class, 'appliedJobIds']);
Route::delete('/applications/{application}', [ApplicationController::class, 'cancel']);
