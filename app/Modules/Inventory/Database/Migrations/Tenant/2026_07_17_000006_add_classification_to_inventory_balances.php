<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_balances', function (Blueprint $table) {
            $table->string('abc_class')->nullable()->after('last_movement_at');
            $table->string('velocity_class')->nullable()->after('abc_class');

            $table->index('abc_class');
            $table->index('velocity_class');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_balances', function (Blueprint $table) {
            $table->dropColumn(['abc_class', 'velocity_class']);
        });
    }
};
