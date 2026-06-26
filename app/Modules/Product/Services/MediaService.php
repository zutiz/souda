<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\ProductMedia;
use App\Modules\Product\Models\Variant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaService
{
    public function uploadMedia(Product $product, UploadedFile $file, ?Variant $variant = null): ProductMedia
    {
        $path = $file->store("products/{$product->id}", 'public');

        return ProductMedia::query()->create([
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'file_path' => $path,
            'file_type' => $file->getMimeType() === 'image' ? 'image' : 'document',
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'alt_text' => $product->name,
        ]);
    }

    public function updateMedia(ProductMedia $media, array $data): ProductMedia
    {
        $media->update($data);

        return $media;
    }

    public function deleteMedia(ProductMedia $media): bool
    {
        Storage::disk('public')->delete($media->file_path);

        $media->delete();

        return true;
    }

    public function setPrimaryMedia(Product $product, ProductMedia $media): void
    {
        $product->media()->where('is_primary', true)->update(['is_primary' => false]);

        $media->update(['is_primary' => true]);
    }

    public function reorderMedia(Product $product, array $order): void
    {
        foreach ($order as $index => $mediaId) {
            ProductMedia::query()
                ->where('product_id', $product->id)
                ->where('id', $mediaId)
                ->update(['sort_order' => $index]);
        }
    }

    public function uploadBulk(Product $product, array $files): Collection
    {
        $media = new Collection;

        foreach ($files as $file) {
            $media->push($this->uploadMedia($product, $file));
        }

        return $media;
    }
}
