<?php

namespace App\Console\Commands\Tenant;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MigrateSharedCommand extends Command
{
    protected $signature = 'tenants:migrate-shared
        {--fresh : Drop all shared tables before running}
        {--seed : Seed after migration}';

    protected $description = 'Run tenant migrations against the shared database';

    public function handle(): int
    {
        $this->info('Running shared database migrations...');

        $database = config('database.connections.shared.database', 'souda_shared');

        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->info("Database [{$database}] created or already exists.");
        } catch (\Throwable $e) {
            $this->warn("Could not create database: {$e->getMessage()}");

            return self::FAILURE;
        }

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
