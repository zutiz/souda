<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'shared';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('shared')->table('tenant_settings', function (Blueprint $table) {
            $table->string('brand_primary_color', 9)->nullable()->after('favicon_path');
            $table->string('brand_accent_color', 9)->nullable()->after('brand_primary_color');
            $table->string('brand_logo_url')->nullable()->after('brand_accent_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('shared')->table('tenant_settings', function (Blueprint $table) {
            $table->dropColumn(['brand_primary_color', 'brand_accent_color', 'brand_logo_url']);
        });
    }
};
