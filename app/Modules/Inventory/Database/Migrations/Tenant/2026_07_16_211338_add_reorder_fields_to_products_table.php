<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('lead_time_days')->nullable()->after('low_stock_threshold');
            $table->integer('safety_stock')->nullable()->after('lead_time_days');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['lead_time_days', 'safety_stock']);
        });
    }
};
