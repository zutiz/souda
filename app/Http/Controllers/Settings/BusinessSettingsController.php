<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\TenantSetting;
use App\Modules\BusinessType\Models\BusinessType;
use App\Modules\BusinessType\Services\BusinessTypeEngine;
use App\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BusinessSettingsController extends Controller
{
    public function __construct(
        protected BusinessTypeEngine $businessTypeEngine,
        protected TenantManager $tenantManager,
    ) {}

    public function update(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->current();

        if ($tenant === null) {
            return redirect()->back()->with('error', 'No active tenant.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_type_slug' => ['sometimes', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! BusinessType::where('slug', $value)->exists()) {
                    $fail('The selected business type is invalid.');
                }
            }],
            'logo' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['sometimes', 'string'],
        ]);

        $tenant->name = $validated['name'];

        if ($request->boolean('remove_logo')) {
            if ($tenant->logo) {
                Storage::delete($tenant->logo);
            }

            $tenant->logo = null;
        }

        if ($request->hasFile('logo')) {
            if ($tenant->logo) {
                Storage::delete($tenant->logo);
            }

            $tenant->logo = $request->file('logo')->store('tenant-logos');
        }

        $tenant->save();

        if (isset($validated['business_type_slug']) && $validated['business_type_slug'] !== $tenant->businessType?->slug) {
            $this->businessTypeEngine->assignBusinessType($tenant, $validated['business_type_slug']);
        }

        return redirect()->back()->with('success', 'Business settings updated.');
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        $tenant = $this->tenantManager->current();

        if ($tenant === null) {
            return redirect()->back()->with('error', 'No active tenant.');
        }

        $validated = $request->validate([
            'brand_primary_color' => ['sometimes', 'nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_accent_color' => ['sometimes', 'nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_logo_url' => ['sometimes', 'nullable', 'url', 'max:500'],
            'reset_colors' => ['sometimes', 'boolean'],
            'reset_logo' => ['sometimes', 'boolean'],
        ]);

        $tenantSetting = TenantSetting::firstOrCreate(
            ['tenant_id' => $tenant->id],
            ['tenant_id' => $tenant->id]
        );

        if ($request->boolean('reset_colors')) {
            $tenantSetting->brand_primary_color = null;
            $tenantSetting->brand_accent_color = null;
        } else {
            if (isset($validated['brand_primary_color'])) {
                $tenantSetting->brand_primary_color = $validated['brand_primary_color'];
            }

            if (isset($validated['brand_accent_color'])) {
                $tenantSetting->brand_accent_color = $validated['brand_accent_color'];
            }
        }

        if ($request->boolean('reset_logo')) {
            $tenantSetting->brand_logo_url = null;
        } elseif (isset($validated['brand_logo_url'])) {
            $tenantSetting->brand_logo_url = $validated['brand_logo_url'];
        }

        $tenantSetting->save();

        // Invalidate tenant config cache so new branding takes effect
        $this->businessTypeEngine->invalidateConfig($tenant);

        return redirect()->back()->with('success', 'Branding updated.');
    }
}
