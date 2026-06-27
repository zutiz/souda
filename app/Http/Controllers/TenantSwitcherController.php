<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Modules\BusinessType\Models\BusinessType;
use App\Modules\Onboarding\Services\ProvisioningPipeline;
use App\Modules\Onboarding\Services\TenantTemplateRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantSwitcherController extends Controller
{
    public function __construct(
        private readonly ProvisioningPipeline $pipeline,
        private readonly TenantTemplateRegistry $templateRegistry,
    ) {}

    public function switch(Request $request): RedirectResponse
    {
        $request->validate([
            'tenant_id' => ['required', 'string'],
        ]);

        $user = $request->user();

        /** @var Tenant|null $tenant */
        $tenant = $user->tenants()->where('tenant_id', $request->tenant_id)->first();

        if (! $tenant) {
            return redirect()->back()->with('error', 'Tenant not found or access denied.');
        }

        $request->session()->put('active_tenant_id', $tenant->id);

        return redirect()->route('dashboard');
    }

    public function create(): Response
    {
        $businessTypes = BusinessType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'slug', 'name', 'description', 'icon']);

        return Inertia::render('Tenant/Create', [
            'businessTypes' => $businessTypes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_type_slug' => ['required', 'string', 'exists:business_types,slug'],
        ]);

        $user = $request->user();

        if (! $this->templateRegistry->has($request->business_type_slug)) {
            return redirect()->back()->with('error', 'Invalid business type.');
        }

        $tenant = Tenant::create([
            'name' => $request->name,
        ]);

        $user->tenants()->attach($tenant->id, [
            'role' => 'owner',
            'is_default' => false,
        ]);

        $tenant->update(['owner_id' => $user->id]);

        session()->put('onboarding.business_type', $request->business_type_slug);

        $this->pipeline->run($tenant, $request->business_type_slug);

        $request->session()->put('active_tenant_id', $tenant->id);

        return redirect()->route('stores.create')
            ->with('success', 'New business created successfully. Let\'s set up your first store.');
    }
}
