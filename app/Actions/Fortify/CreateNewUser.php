<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BillingEmailService;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    public function __construct(
        protected BillingEmailService $billingEmailService,
    ) {}

    protected function createTenantWithDefaults(string $name): Tenant
    {
        // In multi-DB mode, creating a Tenant automatically triggers database
        // creation and migration via the TenantCreated event listener.
        return Tenant::create([
            'name' => $name,
        ]);
    }

    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'business_name' => ['sometimes', 'string', 'max:255'],
            'business_type_slug' => ['sometimes', 'string', 'exists:business_types,slug'],
        ])->validate();

        // Note: No DB::transaction() wrapper here because Tenant creation
        // triggers DDL (CREATE DATABASE) in multi-DB mode, which auto-commits
        // any open MySQL transaction, making the outer transaction ineffective.
        $tenantName = $input['business_name'] ?? "{$input['name']}'s Account";
        $tenant = $this->createTenantWithDefaults($tenantName);

        $user = new User([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        $user->tenant_id = $tenant->id;
        $user->save();

        $user->tenants()->attach($tenant->id, [
            'role' => 'owner',
            'is_default' => true,
        ]);

        $tenant->update(['owner_id' => $user->id]);

        // Store business type selection for post-registration onboarding
        if (isset($input['business_type_slug'])) {
            session()->put('onboarding.business_type', $input['business_type_slug']);
        }

        $this->billingEmailService->sendWelcomeRegistered($user->fresh(['tenant']));

        return $user;
    }
}
