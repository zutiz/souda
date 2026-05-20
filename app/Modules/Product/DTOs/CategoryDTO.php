<?php

declare(strict_types=1);

namespace App\Modules\Product\DTOs;

use App\Modules\Product\Models\Category;

readonly class CategoryDTO
{
    public function __construct(
        public ?int $id,
        public ?int $parentId,
        public string $name,
        public string $slug,
        public ?string $description,
        public ?string $imagePath,
        public bool $isActive,
        public int $sortOrder,
        public ?string $metaTitle,
        public ?string $metaDescription,
        public ?array $children,
    ) {}

    public static function fromModel(Category $category): self
    {
        return new self(
            id: $category->id,
            parentId: $category->parent_id,
            name: $category->name,
            slug: $category->slug,
            description: $category->description,
            imagePath: $category->image_path,
            isActive: $category->is_active,
            sortOrder: $category->sort_order,
            metaTitle: $category->meta_title,
            metaDescription: $category->meta_description,
            children: $category->relationLoaded('children')
                ? $category->children->map(fn (Category $child) => self::fromModel($child))->toArray()
                : null,
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            id: null,
            parentId: $data['parent_id'] ?? null,
            name: $data['name'],
            slug: $data['slug'] ?? '',
            description: $data['description'] ?? null,
            imagePath: $data['image_path'] ?? null,
            isActive: $data['is_active'] ?? true,
            sortOrder: (int) ($data['sort_order'] ?? 0),
            metaTitle: $data['meta_title'] ?? null,
            metaDescription: $data['meta_description'] ?? null,
            children: null,
        );
    }
}
