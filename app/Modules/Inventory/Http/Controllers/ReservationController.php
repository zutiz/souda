<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Models\StockReservation;
use App\Modules\Inventory\Services\ReservationEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController
{
    public function __construct(
        protected ReservationEngine $reservationEngine,
    ) {}

    public function index(Request $request): Response
    {
        $query = StockReservation::query()
            ->with(['product:id,name,sku', 'warehouse:id,name', 'variant:id,name'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $reservations = $query->paginate(25);

        return Inertia::render('Inventory/Reservations/Index', [
            'reservations' => $reservations,
            'filters' => $request->only(['status']),
        ]);
    }

    public function show(StockReservation $reservation): Response
    {
        $reservation->load([
            'product:id,name,sku',
            'warehouse:id,name',
            'variant:id,name',
        ]);

        return Inertia::render('Inventory/Reservations/Index', [
            'reservations' => [$reservation],
            'filters' => [],
        ]);
    }

    public function cancel(StockReservation $reservation): RedirectResponse
    {
        $this->reservationEngine->cancel($reservation->id);

        return redirect()->route('inventory.reservations.index')
            ->with('success', 'Reservation cancelled.');
    }
}
