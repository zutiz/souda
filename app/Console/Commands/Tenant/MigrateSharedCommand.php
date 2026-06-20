<?php

namespace App\Console\Commands\Tenant;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MigrateSharedCommand extends Command
{
    protected $signature = 'tenants:migrate-shared
        {--fresh : Drop all shared tables before running}
        {--seed : Seed after migration}';

    protected $description = 'Run tenant migrations against the shared database';

    public function handle(): int
    {
        $this->info('Running shared database migrations...');

        $params = [
            '--force' => true,
            '--path' => 'database/migrations/shared',
            '--database' => 'shared',
        ];

        if ($this->option('fresh')) {
            Artisan::call('migrate:fresh', [
                '--force' => true,
                '--path' => 'database/migrations/shared',
                '--database' => 'shared',
            ]);

            $this->info('Shared database refreshed.');
        } else {
            Artisan::call('migrate', $params);
            $this->info(Artisan::output());
        }

        if ($this->option('seed')) {
            $this->info('Seeding shared database...');
            // Extend with shared seeder when available
        }

        $this->info('Shared database migrations complete.');

        return self::SUCCESS;
    }
}
