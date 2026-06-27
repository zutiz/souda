<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use App\Modules\BusinessType\Models\BusinessType;
use App\Modules\BusinessType\ValueObjects\TenantConfig;
use App\Modules\Store\Models\Store;
use App\Modules\Store\Services\StoreContextManager;
use App\Tenancy\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $settings = AppSetting::getMany(['app_name', 'logo', 'favicon']);
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => $settings['app_name'] ?? config('app.name'),
            'logo' => $settings['logo'] ? Storage::url($settings['logo']) : null,
            'favicon' => $settings['favicon'] ? Storage::url($settings['favicon']) : null,
            'auth' => [
                'user' => $user,
                'is_admin' => $user?->hasRole('admin') ?? false,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'tenant_config' => fn () => $this->resolveTenantConfig($request),
            'currentTenant' => fn () => $this->resolveCurrentTenant($request),
            'tenants' => fn () => $this->resolveTenants($request),
            'businessTypes' => fn () => $this->resolveBusinessTypes(),
            'currentStore' => fn () => $this->resolveCurrentStore($request),
            'stores' => fn () => $this->resolveStores($request),
        ];
    }

    protected function resolveCurrentStore(Request $request): ?array
    {
        /** @var StoreContextManager $context */
        $context = app(StoreContextManager::class);

        if ($context->initialized()) {
            $store = $context->current();

            if ($store !== null) {
                return $this->storeToArray($store);
            }
        }

        // Fallback: resolve default store directly when middleware hasn't run
        try {
            $store = Store::query()->default()->first();

            if ($store !== null) {
                $context->initialize($store);

                return $this->storeToArray($store);
            }
        } catch (\Throwable) {
            // Tenancy not initialized yet or no stores exist
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function storeToArray(Store $store): array
    {
        return [
            'id' => $store->id,
            'name' => $store->name,
            'slug' => $store->slug,
            'code' => $store->code,
            'currency' => $store->currency,
            'timezone' => $store->timezone,
            'status' => $store->status,
            'is_default' => $store->is_default,
        ];
    }

    protected function resolveStores(Request $request): array
    {
        $user = $request->user();

        if ($user === null || $user->tenant === null) {
            return [];
        }

        try {
            return Store::query()
                ->ordered()
                ->get()
                ->map(fn (Store $store) => [
                    'id' => $store->id,
                    'name' => $store->name,
                    'slug' => $store->slug,
                    'code' => $store->code,
                    'currency' => $store->currency,
                    'timezone' => $store->timezone,
                    'status' => $store->status,
                    'is_default' => $store->is_default,
                ])
                ->toArray();
        } catch (\Throwable $e) {
            Log::warning('Failed to resolve stores list', [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant->id,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    protected function resolveCurrentTenant(Request $request): ?array
    {
        $manager = app(TenantManager::class);

        if ($manager->initialized()) {
            $tenant = $manager->current();
        } else {
            $user = $request->user();

            if ($user === null) {
                return null;
            }

            try {
                $tenant = $user->tenants()->with('businessType')->first();
            } catch (\Throwable) {
                $tenant = null;
            }

            // Fallback: legacy direct tenant_id relationship
            if ($tenant === null && $user->relationLoaded('tenant') ? $user->tenant !== null : $user->tenant()->exists()) {
                $tenant = $user->tenant()->with('businessType')->first();
            }
        }

        if ($tenant === null) {
            return null;
        }

        return [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'business_type' => $tenant->businessType?->slug,
            'business_type_id' => $tenant->business_type_id,
            'logo' => $tenant->logo ? Storage::url($tenant->logo) : null,
        ];
    }

    protected function resolveTenants(Request $request): array
    {
        $user = $request->user();

        if ($user === null) {
            return [];
        }

        try {
            return $user->tenants()
                ->with('businessType')
                ->get()
                ->map(fn ($tenant) => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'business_type' => $tenant->businessType?->slug,
                ])
                ->toArray();
        } catch (\Throwable $e) {
            Log::warning('Failed to resolve tenants list', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    protected function resolveBusinessTypes(): array
    {
        try {
            return BusinessType::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'slug', 'name', 'description', 'icon'])
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    protected function resolveTenantConfig(Request $request): ?array
    {
        $user = $request->user();

        if ($user === null || $user->tenant === null) {
            return null;
        }

        try {
            $config = app(TenantConfig::class);

            return [
                'business_type' => $config->businessType,
                'modules' => $config->enabledModules,
            ];
        } catch (\Throwable $e) {
            Log::warning('Failed to resolve tenant config', [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
