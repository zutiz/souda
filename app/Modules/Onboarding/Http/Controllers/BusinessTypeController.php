<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Http\Controllers;

use App\Modules\BusinessType\Models\BusinessType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BusinessTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $types = BusinessType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'slug', 'name', 'description', 'icon']);

        return response()->json(['data' => $types]);
    }
}
