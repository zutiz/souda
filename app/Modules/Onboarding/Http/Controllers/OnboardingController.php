<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Http\Controllers;

use App\Models\Tenant;
use App\Modules\BusinessType\Models\BusinessType;
use App\Modules\Onboarding\Services\ProvisioningPipeline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function __construct(
        private ProvisioningPipeline $pipeline,
    ) {}

    public function start(): Response
    {
        $types = BusinessType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'slug', 'name', 'description', 'icon']);

        return Inertia::render('onboarding/business-type', [
            'businessTypes' => $types,
        ]);
    }

    public function selectType(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'business_type_slug' => ['required', 'string', 'exists:business_types,slug'],
        ]);

        $request->session()->put('onboarding.business_type', $validated['business_type_slug']);

        return response()->json([
            'redirect' => url('/onboarding/provision'),
        ]);
    }

    public function provision(Request $request): Response
    {
        $slug = $request->session()->get('onboarding.business_type');

        if ($slug === null) {
            return Inertia::render('onboarding/business-type', [
                'businessTypes' => BusinessType::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'slug', 'name', 'description', 'icon']),
            ]);
        }

        $type = BusinessType::query()->where('slug', $slug)->first();

        return Inertia::render('onboarding/provisioning', [
            'businessType' => $type,
            'tenantId' => tenant()?->id,
        ]);
    }

    public function run(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $tenant = $user->tenant;

        if ($tenant === null) {
            return response()->json(['error' => 'No tenant found.'], 400);
        }

        if ($tenant->onboarding_status === 'completed') {
            return response()->json(['status' => 'already_completed', 'redirect' => url('/dashboard')]);
        }

        $slug = $request->session()->get('onboarding.business_type');

        if ($slug === null) {
            return response()->json(['error' => 'No business type selected.'], 400);
        }

        // Run provisioning synchronously for now.
        // For long-running provisioning, dispatch a job instead.
        try {
            $this->pipeline->run($tenant, $slug);

            $request->session()->forget('onboarding.business_type');

            return response()->json(['status' => 'completed', 'redirect' => url('/dashboard')]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'failed',
                'error' => 'Provisioning failed. Please contact support.',
            ], 500);
        }
    }

    public function progress(Request $request, string $tenantId): JsonResponse
    {
        $tenant = Tenant::find($tenantId);

        if ($tenant === null) {
            return response()->json(['error' => 'Tenant not found.'], 404);
        }

        return response()->json([
            'status' => $tenant->onboarding_status,
            'progress' => json_decode($tenant->onboarding_progress ?? '[]', true),
        ]);
    }
}
