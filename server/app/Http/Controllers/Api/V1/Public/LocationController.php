<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => Location::query()->orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }
}
