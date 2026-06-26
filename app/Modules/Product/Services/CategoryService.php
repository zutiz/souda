<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\DTOs\CategoryDTO;
use App\Modules\Product\Exceptions\CircularCategoryException;
use App\Modules\Product\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function createCategory(CategoryDTO $dto): Category
    {
        if ($dto->parentId !== null) {
            $this->validateParent($dto->parentId);
        }

        return Category::query()->create([
            'parent_id' => $dto->parentId,
            'name' => $dto->name,
            'slug' => $dto->slug,
            'description' => $dto->description,
            'image_path' => $dto->imagePath,
            'is_active' => $dto->isActive,
            'sort_order' => $dto->sortOrder,
            'meta_title' => $dto->metaTitle,
            'meta_description' => $dto->metaDescription,
        ]);
    }

    public function updateCategory(Category $category, CategoryDTO $dto): Category
    {
        if ($dto->parentId !== null) {
            $this->validateParent($dto->parentId, $category->id);
        }

        $category->update([
            'parent_id' => $dto->parentId,
            'name' => $dto->name,
            'slug' => $dto->slug,
            'description' => $dto->description,
            'image_path' => $dto->imagePath,
            'is_active' => $dto->isActive,
            'sort_order' => $dto->sortOrder,
            'meta_title' => $dto->metaTitle,
            'meta_description' => $dto->metaDescription,
        ]);

        return $category;
    }

    public function deleteCategory(Category $category): bool
    {
        $category->delete();

        return true;
    }

    public function getCategoryTree(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();
    }

    public function reorderCategories(array $order): void
    {
        foreach ($order as $index => $id) {
            Category::query()->where('id', $id)->update(['sort_order' => $index]);
        }
    }

    protected function validateParent(int $parentId, ?int $currentId = null): void
    {
        if ($currentId !== null && $parentId === $currentId) {
            throw new CircularCategoryException;
        }

        $parent = Category::query()->find($parentId);

        if ($parent === null) {
            return;
        }

        if ($currentId !== null) {
            $descendantIds = $parent->getDescendantIds();

            if (in_array($currentId, $descendantIds, true)) {
                throw new CircularCategoryException;
            }
        }
    }
}
