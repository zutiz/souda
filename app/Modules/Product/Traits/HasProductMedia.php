<?php

declare(strict_types=1);

namespace App\Modules\Product\Traits;

use App\Modules\Product\Models\ProductMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** @mixin Model */
trait HasProductMedia
{
    public function primaryMedia(): HasOne
    {
        return $this->hasOne(ProductMedia::class)->where('is_primary', true);
    }

    public function getPrimaryMedia(): ?ProductMedia
    {
        return $this->primaryMedia()->first();
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->getPrimaryMedia()?->file_path;
    }

    public function hasMedia(): bool
    {
        return $this->media()->exists();
    }
}
