<?php

declare(strict_types=1);

namespace App\Modules\Product\Traits;

use App\Modules\Product\Models\ProductMedia;
use Illuminate\Database\Eloquent\Model;

/** @mixin Model */
trait HasProductMedia
{
    public function getPrimaryMedia(): ?ProductMedia
    {
        return $this->media()->where('is_primary', true)->first();
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
