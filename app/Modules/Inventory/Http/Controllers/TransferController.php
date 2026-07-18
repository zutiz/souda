<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Http\Requests\StoreTransferRequest;
use App\Modules\Inventory\Models\InventoryTransfer;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\TransferEngine;
use App\Modules\Product\Models\Product;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TransferController
{
    public function __construct(
        protected TransferEngine $transferEngine,
    ) {}

    public function index(): Response
    {
        $transfers = InventoryTransfer::query()
            ->with(['fromWarehouse:id,name', 'toWarehouse:id,name'])
            ->latest()
            ->paginate(25);

        return Inertia::render('Inventory/Transfers/Index', [
            'transfers' => $transfers,
        ]);
    }

    public function create(): Response
    {
        $warehouses = Warehouse::active()->get(['id', 'name']);
        $products = Product::query()
            ->where('track_inventory', true)
            ->get(['id', 'name', 'sku']);

        return Inertia::render('Inventory/Transfers/Create', [
            'warehouses' => $warehouses,
            'products' => $products,
        ]);
    }

    public function store(StoreTransferRequest $request): RedirectResponse
    {
        $transfer = $this->transferEngine->initiate(
            fromWarehouseId: $request->integer('from_warehouse_id'),
            toWarehouseId: $request->integer('to_warehouse_id'),
            items: $request->input('items'),
            description: $request->input('description'),
        );

        return redirect()->route('inventory.transfers.show', $transfer)
            ->with('success', 'Transfer initiated successfully.');
    }

    public function show(InventoryTransfer $transfer): Response
    {
        $transfer->load([
            'fromWarehouse:id,name',
            'toWarehouse:id,name',
            'items',
        ]);

        return Inertia::render('Inventory/Transfers/Show', [
            'transfer' => $transfer,
        ]);
    }

    public function send(InventoryTransfer $transfer): RedirectResponse
    {
        $this->transferEngine->send($transfer->id);

        return redirect()->route('inventory.transfers.show', $transfer)
            ->with('success', 'Transfer marked as sent.');
    }

    public function receive(InventoryTransfer $transfer): RedirectResponse
    {
        $this->transferEngine->receive($transfer->id);

        return redirect()->route('inventory.transfers.show', $transfer)
            ->with('success', 'Transfer completed successfully.');
    }

    public function cancel(InventoryTransfer $transfer): RedirectResponse
    {
        $this->transferEngine->cancel($transfer->id, 'Cancelled by user.');

        return redirect()->route('inventory.transfers.show', $transfer)
            ->with('success', 'Transfer cancelled.');
    }
}
