<?php

namespace App\Console\Commands;

use App\Modules\Billing\Enums\SubscriptionStatus;
use App\Modules\Billing\Events\SubscriptionExpired;
use App\Modules\Billing\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscription:expire-expired
        {--dry-run : Preview expirations without making changes}';

    protected $description = 'Expire subscriptions that have passed their end date';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $now = now();
        $gracePeriodDays = config('billing.grace_period_days', 3);

        $expired = 0;
        $movedToGrace = 0;

        // Step 1: Move expired active/trial subscriptions to grace period
        $toGrace = Subscription::whereIn('status', [
            SubscriptionStatus::Active,
            SubscriptionStatus::Trial,
        ])
            ->where('expires_at', '<=', $now)
            ->get();

        foreach ($toGrace as $subscription) {
            if ($dryRun) {
                $this->line("  [DRY-RUN] Would move subscription #{$subscription->id} to grace");
                $movedToGrace++;

                continue;
            }

            $subscription->update([
                'status' => SubscriptionStatus::Grace,
            ]);

            Log::info('Subscription moved to grace period', [
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
            ]);

            $movedToGrace++;
        }

        if (! $dryRun) {
            $this->info("Moved {$movedToGrace} subscriptions to grace period.");
        }

        // Step 2: Expire grace subscriptions past the grace period
        $graceCutoff = $now->copy()->subDays($gracePeriodDays);

        $toExpire = Subscription::where('status', SubscriptionStatus::Grace)
            ->where('expires_at', '<=', $graceCutoff)
            ->get();

        foreach ($toExpire as $subscription) {
            if ($dryRun) {
                $this->line("  [DRY-RUN] Would expire subscription #{$subscription->id}");
                $expired++;

                continue;
            }

            $subscription->update([
                'status' => SubscriptionStatus::Expired,
            ]);

            SubscriptionExpired::dispatch($subscription);

            Log::info('Subscription expired', [
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
            ]);

            $expired++;
        }

        if (! $dryRun) {
            $this->info("Expired {$expired} subscriptions past grace period.");
        }

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Moved to grace', $movedToGrace],
                ['Expired', $expired],
                ['Mode', $dryRun ? 'Dry Run' : 'Live'],
            ]
        );

        return Command::SUCCESS;
    }
}
