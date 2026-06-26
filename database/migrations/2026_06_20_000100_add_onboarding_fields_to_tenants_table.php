<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('onboarding_status')
                ->default('pending')
                ->after('business_type_id');

            $table->json('onboarding_progress')
                ->nullable()
                ->after('onboarding_status');

            $table->timestamp('onboarded_at')
                ->nullable()
                ->after('onboarding_progress');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['onboarding_status', 'onboarding_progress', 'onboarded_at']);
        });
    }
};
