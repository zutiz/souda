<?php

declare(strict_types=1);

namespace App\Modules\Product\Rules;

use App\Modules\Product\Models\Category;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DifferentParent implements ValidationRule
{
    public function __construct(
        protected ?int $categoryId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $parentId = (int) $value;

        if ($this->categoryId !== null && $parentId === $this->categoryId) {
            $fail('A category cannot be its own parent.');

            return;
        }

        $parent = Category::query()->find($parentId);

        if ($parent !== null && $this->categoryId !== null) {
            $descendantIds = $parent->getDescendantIds();

            if (in_array($this->categoryId, $descendantIds, true)) {
                $fail('Circular category reference detected.');
            }
        }
    }
}
