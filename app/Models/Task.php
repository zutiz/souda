<?php

namespace App\Models;

use App\Tenancy\Models\Concerns\HasTenantScope;
use App\Tenancy\TenantManager;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory, HasTenantScope;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'is_completed',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
        ];
    }

    public function getConnectionName(): ?string
    {
        $manager = app(TenantManager::class);

        if ($manager->initialized() && $manager->isShared()) {
            return 'shared';
        }

        return null;
    }
}
