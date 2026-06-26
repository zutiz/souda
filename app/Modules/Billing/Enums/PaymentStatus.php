<?php

namespace App\Modules\Billing\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case PartialRefunded = 'partial_refunded';
}
