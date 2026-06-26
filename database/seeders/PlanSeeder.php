<?php

namespace Database\Seeders;

use App\Modules\Billing\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Perfect for getting started with basic features.',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'currency' => 'BDT',
                'features' => ['basic_dashboard', 'up_to_5_tasks'],
                'limits' => ['tasks' => 5, 'users' => 1],
                'is_active' => true,
                'display_order' => 1,
                'popular' => false,
                'trial_enabled' => false,
                'trial_days' => 0,
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'For small teams who need more power.',
                'monthly_price' => 999,
                'yearly_price' => 9990,
                'currency' => 'BDT',
                'features' => ['basic_dashboard', 'unlimited_tasks', 'basic_reports', 'email_support'],
                'limits' => ['tasks' => -1, 'users' => 5],
                'is_active' => true,
                'display_order' => 2,
                'popular' => false,
                'cta' => 'Start Free Trial',
                'trial_enabled' => true,
                'trial_days' => 14,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'For growing businesses with advanced needs.',
                'monthly_price' => 2999,
                'yearly_price' => 29990,
                'currency' => 'BDT',
                'features' => [
                    'basic_dashboard',
                    'unlimited_tasks',
                    'advanced_reports',
                    'priority_support',
                    'team_collaboration',
                    'api_access',
                ],
                'limits' => ['tasks' => -1, 'users' => 20],
                'is_active' => true,
                'display_order' => 3,
                'popular' => true,
                'cta' => 'Start Free Trial',
                'trial_enabled' => true,
                'trial_days' => 14,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For large organizations requiring custom solutions.',
                'monthly_price' => 9999,
                'yearly_price' => 99990,
                'currency' => 'BDT',
                'features' => [
                    'everything_in_professional',
                    'custom_integrations',
                    'dedicated_support',
                    'sla_guarantee',
                    'white_label',
                    'advanced_security',
                    'audit_logs',
                ],
                'limits' => ['tasks' => -1, 'users' => -1],
                'is_active' => true,
                'display_order' => 4,
                'popular' => false,
                'cta' => 'Contact Sales',
                'trial_enabled' => true,
                'trial_days' => 30,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan,
            );
        }
    }
}
