<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_plans', function (Blueprint $table) {
            $table->unsignedSmallInteger('default_stores')
                ->default(1)
                ->after('max_seats');

            $table->unsignedInteger('store_price')
                ->default(0)
                ->after('default_stores');
        });
    }

    public function down(): void
    {
        Schema::table('billing_plans', function (Blueprint $table) {
            $table->dropColumn(['default_stores', 'store_price']);
        });
    }
};
