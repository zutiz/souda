<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Controllers;

use App\Modules\Product\DTOs\CategoryDTO;
use App\Modules\Product\Http\Requests\StoreCategoryRequest;
use App\Modules\Product\Models\Category;
use App\Modules\Product\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController
{
    public function __construct(
        protected CategoryService $categoryService,
    ) {}

    public function index(): Response
    {
        $categories = $this->categoryService->getCategoryTree();

        return Inertia::render('Product/Category/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $dto = CategoryDTO::fromRequest($request->validated());
        $this->categoryService->createCategory($dto);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(Category $category): Response
    {
        $category->loadMissing(['parent', 'children', 'products']);

        return Inertia::render('Product/Category/Show', [
            'category' => $category,
        ]);
    }

    public function update(StoreCategoryRequest $request, Category $category): RedirectResponse
    {
        $dto = CategoryDTO::fromRequest($request->validated());
        $this->categoryService->updateCategory($category, $dto);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->categoryService->deleteCategory($category);

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:categories,id'],
        ]);

        $this->categoryService->reorderCategories($request->input('order'));

        return redirect()->route('categories.index')
            ->with('success', 'Categories reordered successfully.');
    }
}
