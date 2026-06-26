<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Controllers;

use App\Modules\Product\DTOs\AttributeDTO;
use App\Modules\Product\Http\Requests\StoreAttributeRequest;
use App\Modules\Product\Models\Attribute;
use App\Modules\Product\Models\AttributeValue;
use App\Modules\Product\Services\AttributeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttributeController
{
    public function __construct(
        protected AttributeService $attributeService,
    ) {}

    public function index(): Response
    {
        $attributes = Attribute::query()->with('values')->orderBy('sort_order')->paginate(25);

        return Inertia::render('Product/Attribute/Index', [
            'attributes' => $attributes,
        ]);
    }

    public function store(StoreAttributeRequest $request): RedirectResponse
    {
        $dto = AttributeDTO::fromRequest($request->validated());
        $this->attributeService->createAttribute($dto);

        return redirect()->route('attributes.index')
            ->with('success', 'Attribute created successfully.');
    }

    public function update(StoreAttributeRequest $request, Attribute $attribute): RedirectResponse
    {
        $dto = AttributeDTO::fromRequest($request->validated());
        $this->attributeService->updateAttribute($attribute, $dto);

        return redirect()->route('attributes.index')
            ->with('success', 'Attribute updated successfully.');
    }

    public function destroy(Attribute $attribute): RedirectResponse
    {
        $this->attributeService->deleteAttribute($attribute);

        return redirect()->route('attributes.index')
            ->with('success', 'Attribute deleted successfully.');
    }

    public function storeValue(Request $request, Attribute $attribute): RedirectResponse
    {
        $request->validate([
            'value' => ['required', 'string', 'max:255'],
            'swatch_color' => ['nullable', 'string', 'max:7'],
        ]);

        $this->attributeService->addValue(
            $attribute,
            $request->input('value'),
            $request->input('swatch_color'),
        );

        return redirect()->route('attributes.index')
            ->with('success', 'Attribute value added successfully.');
    }

    public function updateValue(Request $request, AttributeValue $value): RedirectResponse
    {
        $request->validate([
            'value' => ['required', 'string', 'max:255'],
            'swatch_color' => ['nullable', 'string', 'max:7'],
        ]);

        $value->update($request->only(['value', 'swatch_color']));

        return redirect()->route('attributes.index')
            ->with('success', 'Attribute value updated successfully.');
    }

    public function destroyValue(AttributeValue $value): RedirectResponse
    {
        $this->attributeService->deleteValue($value);

        return redirect()->route('attributes.index')
            ->with('success', 'Attribute value deleted successfully.');
    }
}
