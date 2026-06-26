<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('shared')->create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->index('tenant_id');
        });

        Schema::connection('shared')->create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('timezone')->default('UTC');
            $table->string('locale', 10)->default('en');
            $table->string('currency', 3)->default('USD');
            $table->string('date_format', 20)->default('Y-m-d');
            $table->string('time_format', 20)->default('H:i');
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('default_language', 10)->default('en');
            $table->json('notification_preferences')->nullable();
            $table->json('feature_toggles')->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::connection('shared')->dropIfExists('tenant_settings');
        Schema::connection('shared')->dropIfExists('tasks');
    }
};
