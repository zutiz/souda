<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_task_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index();
            $table->string('command');
            $table->string('status', 20)->default('running');
            $table->integer('duration_ms')->nullable();
            $table->text('output')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index('command');
            $table->index('status');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_task_logs');
    }
};
