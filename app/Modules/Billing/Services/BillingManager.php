<?php

namespace App\Modules\Billing\Services;

use App\Modules\Billing\Contracts\BillingGatewayInterface;
use App\Modules\Billing\Enums\Gateway;
use App\Modules\Billing\Exceptions\InvalidGatewayException;

class BillingManager
{
    /** @var array<string, BillingGatewayInterface> */
    private array $drivers = [];

    /**
     * Register a custom gateway driver.
     */
    public function registerDriver(string $gateway, BillingGatewayInterface $driver): void
    {
        $this->drivers[$gateway] = $driver;
    }

    /**
     * Get the gateway driver for the given gateway name.
     *
     * @throws InvalidGatewayException
     */
    public function driver(?string $gateway = null): BillingGatewayInterface
    {
        $gateway = $gateway ?? config('billing.default_gateway', 'manual');

        if (isset($this->drivers[$gateway])) {
            return $this->drivers[$gateway];
        }

        $driver = $this->resolveDriver($gateway);

        if (! $driver) {
            throw new InvalidGatewayException($gateway);
        }

        $this->drivers[$gateway] = $driver;

        return $driver;
    }

    /**
     * Resolve a gateway driver from configuration.
     */
    private function resolveDriver(string $gateway): ?BillingGatewayInterface
    {
        $config = config("billing.gateways.{$gateway}");

        if (! $config || ! ($config['driver'] ?? false)) {
            return null;
        }

        $driverClass = $config['driver'];
        $driverConfig = $config['config'] ?? [];

        return app()->makeWith($driverClass, $driverConfig);
    }

    /**
     * Get all available gateway names.
     *
     * @return array<string>
     */
    public function availableGateways(): array
    {
        return array_keys(config('billing.gateways', []));
    }

    /**
     * Get the display label for a gateway.
     */
    public function gatewayLabel(string $gateway): string
    {
        return Gateway::tryFrom($gateway)?->label() ?? ucfirst($gateway);
    }
}
