<?php

declare(strict_types=1);

namespace App\Modules\Store\Http\Controllers;

use App\Modules\Billing\Services\StoreBillingService;
use App\Modules\Store\DTOs\StoreDTO;
use App\Modules\Store\Exceptions\StoreLimitExceededException;
use App\Modules\Store\Http\Requests\StoreStoreRequest;
use App\Modules\Store\Http\Requests\UpdateStoreRequest;
use App\Modules\Store\Models\Store;
use App\Modules\Store\Services\StoreContextManager;
use App\Modules\Store\Services\StoreService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StoreController
{
    use AuthorizesRequests;

    public function __construct(
        protected StoreService $storeService,
        protected StoreContextManager $storeContext,
    ) {}

    public function index(): Response
    {
        $stores = Store::query()->ordered()->paginate(25);

        return Inertia::render('Store/Index', [
            'stores' => $stores,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Store/Create');
    }

    public function store(StoreStoreRequest $request, StoreBillingService $billing): RedirectResponse
    {
        $tenant = tenancy()->tenant;

        if ($tenant && ! $billing->canCreateStore($tenant)) {
            throw new StoreLimitExceededException;
        }

        $dto = StoreDTO::fromRequest($request->validated());
        $store = $this->storeService->createStore($dto);

        return redirect()->route('stores.index')
            ->with('success', 'Store created successfully.');
    }

    public function show(Store $store): Response
    {
        return Inertia::render('Store/Show', [
            'store' => StoreDTO::fromModel($store),
        ]);
    }

    public function edit(Store $store): Response
    {
        return Inertia::render('Store/Edit', [
            'store' => StoreDTO::fromModel($store),
        ]);
    }

    public function update(UpdateStoreRequest $request, Store $store): RedirectResponse
    {
        $dto = StoreDTO::fromRequest($request->validated());
        $this->storeService->updateStore($store, $dto);

        return redirect()->route('stores.index')
            ->with('success', 'Store updated successfully.');
    }

    public function destroy(Store $store): RedirectResponse
    {
        $this->storeService->deleteStore($store);

        return redirect()->route('stores.index')
            ->with('success', 'Store deleted successfully.');
    }

    public function switch(Store $store): RedirectResponse
    {
        $this->authorize('switch', $store);

        $this->storeContext->initialize($store);

        session()->put('current_store_id', $store->id);

        return redirect()->route('dashboard', ['store' => $store])
            ->with('success', "Switched to {$store->name}.");
    }

    public function setDefault(Store $store): RedirectResponse
    {
        $this->storeService->setDefaultStore($store);

        return redirect()->route('stores.index')
            ->with('success', 'Default store updated successfully.');
    }
}
