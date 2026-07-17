<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Model;

class ScheduledTaskLog extends Model
{
    use HasTenantScope;

    protected $table = 'scheduled_task_logs';

    protected $fillable = [
        'command',
        'status',
        'duration_ms',
        'output',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'duration_ms' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
