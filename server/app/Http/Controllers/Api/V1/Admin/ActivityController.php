<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\PlatformActivityQueryService;
use Illuminate\Http\JsonResponse;

class ActivityController extends Controller
{
    public function __construct(
        private readonly PlatformActivityQueryService $activity,
    ) {}

    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => $this->activity->summary(),
        ]);
    }
}
