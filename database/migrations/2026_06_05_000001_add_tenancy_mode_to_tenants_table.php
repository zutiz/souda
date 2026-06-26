<?php

use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('tenancy_mode', 20)->default('shared')->after('trial_used');
            $table->string('database_name', 255)->nullable()->after('tenancy_mode');

            $table->index('tenancy_mode');
        });

        Tenant::query()->where('tenancy_mode', 'shared')->each(function (Tenant $tenant) {
            $hasDedicatedDb = false;

            try {
                $manager = $tenant->database()->manager();
                $hasDedicatedDb = $manager->databaseExists($tenant->database()->getName());
            } catch (Throwable) {
                $hasDedicatedDb = true;
            }

            if ($hasDedicatedDb) {
                $tenant->timestamps = false;
                $tenant->updateQuietly(['tenancy_mode' => 'dedicated']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex(['tenancy_mode']);
            $table->dropColumn(['tenancy_mode', 'database_name']);
        });
    }
};
