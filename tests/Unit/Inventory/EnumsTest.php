<?php

declare(strict_types=1);

use App\Modules\Inventory\Enums\AbcClassEnum;
use App\Modules\Inventory\Enums\AlertSeverityEnum;
use App\Modules\Inventory\Enums\BatchStatusEnum;
use App\Modules\Inventory\Enums\CostingMethodEnum;
use App\Modules\Inventory\Enums\CountItemStatusEnum;
use App\Modules\Inventory\Enums\CountStatusEnum;
use App\Modules\Inventory\Enums\ForecastModelEnum;
use App\Modules\Inventory\Enums\MovementTypeEnum;
use App\Modules\Inventory\Enums\ReservationStatusEnum;
use App\Modules\Inventory\Enums\RuleActionTypeEnum;
use App\Modules\Inventory\Enums\RuleConditionTypeEnum;
use App\Modules\Inventory\Enums\SerialStatusEnum;
use App\Modules\Inventory\Enums\TransferStatusEnum;
use App\Modules\Inventory\Enums\VelocityClassEnum;

describe('TransferStatusEnum', function () {
    test('labels are human-readable', function () {
        expect(TransferStatusEnum::Draft->label())->toBe('Draft');
        expect(TransferStatusEnum::InTransit->label())->toBe('In Transit');
        expect(TransferStatusEnum::Completed->label())->toBe('Completed');
        expect(TransferStatusEnum::Cancelled->label())->toBe('Cancelled');
    });

    test('only draft status is editable', function () {
        expect(TransferStatusEnum::Draft->isEditable())->toBeTrue();
        expect(TransferStatusEnum::InTransit->isEditable())->toBeFalse();
        expect(TransferStatusEnum::Completed->isEditable())->toBeFalse();
        expect(TransferStatusEnum::Cancelled->isEditable())->toBeFalse();
    });

    test('completed and cancelled are terminal states', function () {
        expect(TransferStatusEnum::Draft->isTerminal())->toBeFalse();
        expect(TransferStatusEnum::InTransit->isTerminal())->toBeFalse();
        expect(TransferStatusEnum::Completed->isTerminal())->toBeTrue();
        expect(TransferStatusEnum::Cancelled->isTerminal())->toBeTrue();
    });
});

describe('MovementTypeEnum', function () {
    test('labels are human-readable', function () {
        expect(MovementTypeEnum::PurchaseReceipt->label())->toBe('Purchase Receipt');
        expect(MovementTypeEnum::SaleDeduction->label())->toBe('Sale Deduction');
        expect(MovementTypeEnum::TransferOut->label())->toBe('Transfer Out');
        expect(MovementTypeEnum::InitialStock->label())->toBe('Initial Stock');
    });

    test('inbound types are correctly classified', function () {
        expect(MovementTypeEnum::PurchaseReceipt->isInbound())->toBeTrue();
        expect(MovementTypeEnum::ReturnRestock->isInbound())->toBeTrue();
        expect(MovementTypeEnum::TransferIn->isInbound())->toBeTrue();
        expect(MovementTypeEnum::AdjustmentIncrease->isInbound())->toBeTrue();
        expect(MovementTypeEnum::ProductionOutput->isInbound())->toBeTrue();
        expect(MovementTypeEnum::ReservationRelease->isInbound())->toBeTrue();
        expect(MovementTypeEnum::Reversal->isInbound())->toBeTrue();
        expect(MovementTypeEnum::InitialStock->isInbound())->toBeTrue();
    });

    test('outbound types are correctly classified', function () {
        expect(MovementTypeEnum::SaleDeduction->isOutbound())->toBeTrue();
        expect(MovementTypeEnum::TransferOut->isOutbound())->toBeTrue();
        expect(MovementTypeEnum::AdjustmentDecrease->isOutbound())->toBeTrue();
        expect(MovementTypeEnum::RecipeConsumption->isOutbound())->toBeTrue();
        expect(MovementTypeEnum::ReservationDeduction->isOutbound())->toBeTrue();
    });

    test('inbound and outbound are mutually exclusive', function () {
        expect(MovementTypeEnum::Reversal->isInbound())->toBeTrue();
        expect(MovementTypeEnum::Reversal->isOutbound())->toBeFalse();
        expect(MovementTypeEnum::TransferOut->isOutbound())->toBeTrue();
        expect(MovementTypeEnum::TransferOut->isInbound())->toBeFalse();
    });
});

describe('ReservationStatusEnum', function () {
    test('values match expected strings', function () {
        expect(ReservationStatusEnum::Active->value)->toBe('active');
        expect(ReservationStatusEnum::Consumed->value)->toBe('consumed');
        expect(ReservationStatusEnum::Expired->value)->toBe('expired');
        expect(ReservationStatusEnum::Cancelled->value)->toBe('cancelled');
    });
});

describe('BatchStatusEnum', function () {
    test('values match expected strings', function () {
        expect(BatchStatusEnum::Active->value)->toBe('active');
        expect(BatchStatusEnum::Quarantined->value)->toBe('quarantined');
        expect(BatchStatusEnum::Depleted->value)->toBe('depleted');
        expect(BatchStatusEnum::Expired->value)->toBe('expired');
    });
});

describe('SerialStatusEnum', function () {
    test('values match expected strings', function () {
        expect(SerialStatusEnum::Available->value)->toBe('available');
        expect(SerialStatusEnum::Sold->value)->toBe('sold');
        expect(SerialStatusEnum::Returned->value)->toBe('returned');
        expect(SerialStatusEnum::Quarantined->value)->toBe('quarantined');
        expect(SerialStatusEnum::Disposed->value)->toBe('disposed');
    });
});

describe('CountStatusEnum', function () {
    test('values match expected strings', function () {
        expect(CountStatusEnum::Draft->value)->toBe('draft');
        expect(CountStatusEnum::InProgress->value)->toBe('in_progress');
        expect(CountStatusEnum::Verified->value)->toBe('verified');
        expect(CountStatusEnum::Adjusted->value)->toBe('adjusted');
        expect(CountStatusEnum::Completed->value)->toBe('completed');
        expect(CountStatusEnum::Cancelled->value)->toBe('cancelled');
    });
});

describe('CountItemStatusEnum', function () {
    test('values match expected strings', function () {
        expect(CountItemStatusEnum::Pending->value)->toBe('pending');
        expect(CountItemStatusEnum::Counted->value)->toBe('counted');
        expect(CountItemStatusEnum::Verified->value)->toBe('verified');
    });
});

describe('AbcClassEnum', function () {
    test('values match expected strings', function () {
        expect(AbcClassEnum::A->value)->toBe('a');
        expect(AbcClassEnum::B->value)->toBe('b');
        expect(AbcClassEnum::C->value)->toBe('c');
    });
});

describe('VelocityClassEnum', function () {
    test('values match expected strings', function () {
        expect(VelocityClassEnum::Fast->value)->toBe('fast');
        expect(VelocityClassEnum::Slow->value)->toBe('slow');
        expect(VelocityClassEnum::Dead->value)->toBe('dead');
        expect(VelocityClassEnum::New->value)->toBe('new');
    });
});

describe('AlertSeverityEnum', function () {
    test('values match expected strings', function () {
        expect(AlertSeverityEnum::Info->value)->toBe('info');
        expect(AlertSeverityEnum::Warning->value)->toBe('warning');
        expect(AlertSeverityEnum::Critical->value)->toBe('critical');
    });
});

describe('CostingMethodEnum', function () {
    test('values match expected strings', function () {
        expect(CostingMethodEnum::WeightedAverage->value)->toBe('weighted_average');
        expect(CostingMethodEnum::Fifo->value)->toBe('fifo');
    });
});

describe('ForecastModelEnum', function () {
    test('values match expected strings', function () {
        expect(ForecastModelEnum::MovingAverage->value)->toBe('moving_average');
        expect(ForecastModelEnum::LinearTrend->value)->toBe('linear_trend');
        expect(ForecastModelEnum::Seasonal->value)->toBe('seasonal');
    });
});

describe('RuleActionTypeEnum', function () {
    test('values match expected strings', function () {
        expect(RuleActionTypeEnum::CreateAlert->value)->toBe('create_alert');
        expect(RuleActionTypeEnum::SendNotification->value)->toBe('send_notification');
        expect(RuleActionTypeEnum::GenerateSuggestion->value)->toBe('generate_suggestion');
    });
});

describe('RuleConditionTypeEnum', function () {
    test('values match expected strings', function () {
        expect(RuleConditionTypeEnum::LowStock->value)->toBe('low_stock');
        expect(RuleConditionTypeEnum::DeadStock->value)->toBe('dead_stock');
        expect(RuleConditionTypeEnum::Overstock->value)->toBe('overstock');
        expect(RuleConditionTypeEnum::ExpiringBatch->value)->toBe('expiring_batch');
        expect(RuleConditionTypeEnum::SlowMoving->value)->toBe('slow_moving');
        expect(RuleConditionTypeEnum::FastMoving->value)->toBe('fast_moving');
    });
});
