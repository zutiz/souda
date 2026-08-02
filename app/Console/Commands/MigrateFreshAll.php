<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateFreshAll extends Command
{
    protected $signature = 'migrate:fresh:all {--seed : Also run seeders}';

    protected $description = 'Drop all tables and remigrate all databases (central, shared, and module tables)';

    public function handle(Migrator $migrator): int
    {
        $centralDb = config('database.connections.mysql.database');

        $this->info('Dropping all tables in central database ('.$centralDb.')...');
        $this->dropTables(DB::connection()->getName());
        $this->info('Central database tables dropped.');

        $this->info('Dropping all tables in shared database (souda_shared)...');
        $this->dropTables('shared');
        $this->info('Shared database tables dropped.');

        // Run only central database migrations on default connection
        $this->info('Running central migrations on default database...');
        $this->call('migrate', [
            '--path' => 'database/migrations',
            '--force' => true,
        ]);

        // Run shared migrations first (creates products, brands, etc.)
        $this->info('Running shared migrations...');
        $this->call('migrate', [
            '--database' => 'shared',
            '--path' => 'database/migrations/shared',
            '--force' => true,
        ]);

        // Run module migrations on shared database
        $this->info('Running module migrations on shared database...');
        $this->call('migrate', [
            '--database' => 'shared',
            '--force' => true,
        ]);

        if ($this->option('seed')) {
            $this->call('db:seed', ['--force' => true]);
        }

        $this->info('All databases migrated successfully!');

        return self::SUCCESS;
    }

    private function dropTables(string $connection): void
    {
        $connection = DB::connection($connection);
        $database = $connection->getDatabaseName();

        $connection->statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = $connection->select('SHOW TABLES');
        foreach ($tables as $table) {
            $tableName = $table->{'Tables_in_'.$database};
            Schema::connection($connection->getName())->dropIfExists($tableName);
        }

        $connection->statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
