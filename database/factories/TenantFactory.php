<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Modules\Billing\Enums\BillingCycle;
use App\Modules\Billing\Enums\SubscriptionStatus;
use App\Modules\Billing\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $accountName = $this->faker->company().' Account';

        return [
            'name' => $accountName,
            'tenancy_mode' => 'dedicated',
        ];
    }

    public function configure(): static
    {
        return $this;
    }

    public function shared(): static
    {
        return $this->state(fn (array $attributes) => [
            'tenancy_mode' => 'shared',
        ]);
    }

    public function subscribed(): static
    {
        return $this->afterCreating(function (Tenant $tenant) {
            $plan = Plan::factory()->createQuietly();

            $tenant->subscriptions()->create([
                'plan_id' => $plan->id,
                'gateway' => 'manual',
                'status' => SubscriptionStatus::Active,
                'billing_cycle' => BillingCycle::Monthly,
                'amount' => $plan->monthly_price,
                'currency' => $plan->currency,
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
                'next_billing_at' => now()->addMonth(),
            ]);

            if ($tenant->isDedicated()) {
                $this->provisionTenantDatabase($tenant);
            }
        });
    }

    public function cancelledSubscription(): static
    {
        return $this->afterCreating(function (Tenant $tenant) {
            $plan = Plan::factory()->createQuietly();

            $tenant->subscriptions()->create([
                'plan_id' => $plan->id,
                'gateway' => 'manual',
                'status' => SubscriptionStatus::Cancelled,
                'billing_cycle' => BillingCycle::Monthly,
                'amount' => $plan->monthly_price,
                'currency' => $plan->currency,
                'starts_at' => now()->subDays(30),
                'expires_at' => now()->subDay(),
                'cancelled_at' => now()->subDay(),
            ]);

            if ($tenant->isDedicated()) {
                $this->provisionTenantDatabase($tenant);
            }
        });
    }

    protected function provisionTenantDatabase(Tenant $tenant): void
    {
        $manager = $tenant->database()->manager();

        if (! $manager->databaseExists($tenant->database()->getName())) {
            $createJob = app(CreateDatabase::class, ['tenant' => $tenant]);
            $createJob->handle(app(DatabaseManager::class));
        }

        $migrateJob = app(MigrateDatabase::class, ['tenant' => $tenant]);
        $migrateJob->handle();
    }
}
