<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

enum MovementTypeEnum: string
{
    case PurchaseReceipt = 'purchase_receipt';
    case SaleDeduction = 'sale_deduction';
    case ReturnRestock = 'return_restock';
    case TransferOut = 'transfer_out';
    case TransferIn = 'transfer_in';
    case AdjustmentIncrease = 'adjustment_increase';
    case AdjustmentDecrease = 'adjustment_decrease';
    case ProductionOutput = 'production_output';
    case RecipeConsumption = 'recipe_consumption';
    case ReservationDeduction = 'reservation_deduction';
    case ReservationRelease = 'reservation_release';
    case Reversal = 'reversal';
    case InitialStock = 'initial_stock';

    public function label(): string
    {
        return match ($this) {
            self::PurchaseReceipt => 'Purchase Receipt',
            self::SaleDeduction => 'Sale Deduction',
            self::ReturnRestock => 'Return Restock',
            self::TransferOut => 'Transfer Out',
            self::TransferIn => 'Transfer In',
            self::AdjustmentIncrease => 'Adjustment Increase',
            self::AdjustmentDecrease => 'Adjustment Decrease',
            self::ProductionOutput => 'Production Output',
            self::RecipeConsumption => 'Recipe Consumption',
            self::ReservationDeduction => 'Reservation Deduction',
            self::ReservationRelease => 'Reservation Release',
            self::Reversal => 'Reversal',
            self::InitialStock => 'Initial Stock',
        };
    }

    public function isInbound(): bool
    {
        return in_array($this, [
            self::PurchaseReceipt,
            self::ReturnRestock,
            self::TransferIn,
            self::AdjustmentIncrease,
            self::ProductionOutput,
            self::ReservationRelease,
            self::Reversal,
            self::InitialStock,
        ], true);
    }

    public function isOutbound(): bool
    {
        return in_array($this, [
            self::SaleDeduction,
            self::TransferOut,
            self::AdjustmentDecrease,
            self::RecipeConsumption,
            self::ReservationDeduction,
        ], true);
    }

    public function referencePrefix(): string
    {
        return config("inventory.reference_prefixes.{$this->value}", 'MOV');
    }
}
