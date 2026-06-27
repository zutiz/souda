<?php

declare(strict_types=1);

namespace App\Modules\Store\Http\Middleware;

use App\Modules\Store\Models\Store;
use App\Modules\Store\Services\StoreContextManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Database\Models\Domain;
use Symfony\Component\HttpFoundation\Response;

class InitializeStoreContext
{
    public function __construct(
        private readonly StoreContextManager $storeContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $store = $this->resolveFromRoute($request)
            ?? $this->resolveFromDomain($request)
            ?? $this->resolveFromSubdomain($request)
            ?? $this->resolveFromSession($request);

        if (! $store) {
            $store = Store::query()->default()->first();
        }

        if (! $store) {
            return redirect()->route('stores.create');
        }

        if (! $store->isActive()) {
            return redirect()->route('stores.index')
                ->with('error', 'This store is not active.');
        }

        $this->storeContext->initialize($store);

        $request->session()->put('current_store_id', $store->id);

        return $next($request);
    }

    private function resolveFromRoute(Request $request): ?Store
    {
        $storeId = $request->route('store');

        if (! $storeId) {
            return null;
        }

        if ($storeId instanceof Store) {
            return $storeId;
        }

        return Store::query()->find($storeId);
    }

    private function resolveFromDomain(Request $request): ?Store
    {
        $host = $request->getHost();

        if ($host === '127.0.0.1' || $host === 'localhost') {
            return null;
        }

        try {
            $domain = Domain::query()->where('domain', $host)->first();

            if ($domain && $domain->store_id) {
                return Store::query()->find($domain->store_id);
            }
        } catch (\Throwable $e) {
            Log::debug('Domain resolution failed', ['host' => $host, 'error' => $e->getMessage()]);
        }

        return null;
    }

    private function resolveFromSubdomain(Request $request): ?Store
    {
        $host = $request->getHost();
        $centralDomains = config('tenancy.central_domains', []);

        foreach ($centralDomains as $centralDomain) {
            if (str_ends_with($host, ".{$centralDomain}")) {
                $subdomain = str_replace(".{$centralDomain}", '', $host);

                return Store::query()->where('slug', $subdomain)->first();
            }
        }

        return null;
    }

    private function resolveFromSession(Request $request): ?Store
    {
        $storeId = $request->session()->get('current_store_id');

        if (! $storeId) {
            return null;
        }

        return Store::query()->find($storeId);
    }
}
