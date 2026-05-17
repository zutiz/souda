<?php

namespace App\Modules\Billing\Enums;

enum Gateway: string
{
    case Stripe = 'stripe';
    case SSLCommerz = 'sslcommerz';
    case BKash = 'bkash';
    case Nagad = 'nagad';
    case PortWallet = 'portwallet';
    case Manual = 'manual';

    /**
     * Returns the display name for the gateway.
     */
    public function label(): string
    {
        return match ($this) {
            self::Stripe => 'Stripe',
            self::SSLCommerz => 'SSLCommerz',
            self::BKash => 'bKash',
            self::Nagad => 'Nagad',
            self::PortWallet => 'PortWallet',
            self::Manual => 'Manual',
        };
    }
}
