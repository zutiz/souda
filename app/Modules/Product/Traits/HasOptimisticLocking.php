<?php

declare(strict_types=1);

namespace App\Modules\Product\Traits;

use App\Modules\Product\Exceptions\StaleModelException;

trait HasOptimisticLocking
{
    public function initializeHasOptimisticLocking(): void
    {
        $this->fillable[] = 'lock_version';
    }

    public function lockVersion(): int
    {
        return (int) $this->getAttribute('lock_version');
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function updateWithLock(array $options = []): bool
    {
        $currentVersion = $this->lockVersion();

        $this->setAttribute('lock_version', $currentVersion + 1);

        $affected = static::query()
            ->whereKey($this->getKey())
            ->where('lock_version', $currentVersion)
            ->update(array_merge(
                $this->getDirty(),
                ['lock_version' => $currentVersion + 1],
            ));

        if ($affected === 0) {
            throw new StaleModelException(static::class, $this->getKey());
        }

        $this->syncChanges();

        return true;
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  array<string, mixed>  $options
     */
    public function updateOrFailWithLock(array $values = [], array $options = []): bool
    {
        return $this->updateWithLock($options);
    }
}
