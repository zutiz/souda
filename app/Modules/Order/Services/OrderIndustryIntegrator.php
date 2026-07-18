<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use App\Modules\BusinessType\ValueObjects\TenantConfig;

class OrderIndustryIntegrator
{
    public function __construct(
        protected TenantConfig $config,
    ) {}

    public function shouldSendToKitchen(): bool
    {
        return $this->config->hasFeature('kitchen_display')
            || $this->config->hasModule('kitchen');
    }

    public function requiresPrescriptionCheck(): bool
    {
        return $this->config->hasFeature('prescription_management')
            || $this->config->hasFeature('prescription_check');
    }

    public function supportsDelivery(): bool
    {
        return $this->config->hasFeature('delivery')
            || $this->config->hasFeature('courier_integration');
    }

    public function supportsPickup(): bool
    {
        return $this->config->hasFeature('takeaway')
            || in_array($this->config->businessType, ['restaurant', 'cafe', 'bakery', 'pharmacy'], true);
    }

    public function requiresTableAssignment(): bool
    {
        return $this->config->hasFeature('table_management');
    }

    public function requiresStaffAssignment(): bool
    {
        return $this->config->hasFeature('staff_scheduling');
    }

    public function supportsInstallment(): bool
    {
        return $this->config->hasFeature('installment')
            || $this->config->businessType === 'electronics';
    }

    public function orderTypes(): array
    {
        $types = ['in_store'];

        if ($this->supportsDelivery()) {
            $types[] = 'delivery';
        }

        if ($this->supportsPickup()) {
            $types[] = 'takeaway';
        }

        if ($this->requiresTableAssignment()) {
            $types[] = 'dine_in';
        }

        if ($this->config->businessType === 'wholesale' || $this->config->businessType === 'distribution') {
            $types[] = 'wholesale';
        }

        return $types;
    }

    public function orderConfig(): array
    {
        return [
            'should_send_to_kitchen' => $this->shouldSendToKitchen(),
            'requires_prescription' => $this->requiresPrescriptionCheck(),
            'supports_delivery' => $this->supportsDelivery(),
            'supports_pickup' => $this->supportsPickup(),
            'requires_table' => $this->requiresTableAssignment(),
            'requires_staff' => $this->requiresStaffAssignment(),
            'supports_installment' => $this->supportsInstallment(),
            'order_types' => $this->orderTypes(),
            'business_type' => $this->config->businessType,
        ];
    }
}
