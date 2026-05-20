<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Controllers;

use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\ProductMedia;
use App\Modules\Product\Models\Variant;
use App\Modules\Product\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class MediaController
{
    public function __construct(
        protected MediaService $mediaService,
    ) {}

    public function store(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:jpeg,png,jpg,gif,svg,webp,mp4,pdf', 'max:10240'],
            'variant_id' => ['nullable', 'exists:variants,id'],
        ]);

        $file = $request->file('file');
        $variantId = $request->input('variant_id');
        $variant = $variantId ? Variant::query()->find($variantId) : null;

        $this->mediaService->uploadMedia($product, $file, $variant);

        return Redirect::back()->with('success', 'Media uploaded successfully.');
    }

    public function update(Request $request, ProductMedia $media): RedirectResponse
    {
        $request->validate([
            'alt_text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $this->mediaService->updateMedia($media, $request->only(['alt_text', 'sort_order']));

        return Redirect::back()->with('success', 'Media updated successfully.');
    }

    public function destroy(ProductMedia $media): RedirectResponse
    {
        $this->mediaService->deleteMedia($media);

        return Redirect::back()->with('success', 'Media deleted successfully.');
    }

    public function setPrimary(Product $product, ProductMedia $media): RedirectResponse
    {
        $this->mediaService->setPrimaryMedia($product, $media);

        return Redirect::back()->with('success', 'Primary media set successfully.');
    }

    public function reorder(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:product_media,id'],
        ]);

        $this->mediaService->reorderMedia($product, $request->input('order'));

        return Redirect::back()->with('success', 'Media reordered successfully.');
    }
}
