<?php

declare(strict_types=1);

namespace App\Modules\Order\Contracts;

use App\Modules\Order\DTOs\OrderDTO;

interface ReceiptRenderer
{
    public function render(OrderDTO $order, array $config = []): string;

    public function contentType(): string;
}
