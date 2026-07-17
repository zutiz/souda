<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\ScheduledTaskLog;

class ScheduledTaskLogService
{
    public function recordStart(string $command): ScheduledTaskLog
    {
        return ScheduledTaskLog::create([
            'command' => $command,
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function recordSuccess(ScheduledTaskLog $log, ?string $output = null): void
    {
        $log->update([
            'status' => 'success',
            'duration_ms' => $this->calculateDuration($log),
            'output' => $output,
            'finished_at' => now(),
        ]);
    }

    public function recordFailure(ScheduledTaskLog $log, string $error): void
    {
        $log->update([
            'status' => 'failed',
            'duration_ms' => $this->calculateDuration($log),
            'output' => $error,
            'finished_at' => now(),
        ]);
    }

    public function getLatestPerCommand(): array
    {
        $logs = ScheduledTaskLog::query()
            ->selectRaw('command, MAX(id) as max_id')
            ->groupBy('command')
            ->pluck('max_id');

        if ($logs->isEmpty()) {
            return [];
        }

        return ScheduledTaskLog::whereIn('id', $logs)
            ->orderBy('command')
            ->get()
            ->keyBy('command')
            ->all();
    }

    public function getAll(): array
    {
        return ScheduledTaskLog::query()
            ->orderBy('started_at', 'desc')
            ->limit(100)
            ->get()
            ->all();
    }

    protected function calculateDuration(ScheduledTaskLog $log): int
    {
        if ($log->started_at === null) {
            return 0;
        }

        return (int) $log->started_at->diffInMilliseconds(now());
    }
}
