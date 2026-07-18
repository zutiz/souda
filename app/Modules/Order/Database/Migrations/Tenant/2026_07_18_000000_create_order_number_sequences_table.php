<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 10);
            $table->string('date_part', 6);
            $table->unsignedInteger('last_sequence')->default(0);
            $table->timestamps();

            $table->unique(['prefix', 'date_part']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_number_sequences');
    }
};
