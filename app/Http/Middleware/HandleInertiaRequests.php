<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use App\Modules\BusinessType\ValueObjects\TenantConfig;
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
        ];
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
