<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
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
}
