<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

enum RuleActionTypeEnum: string
{
    case CreateAlert = 'create_alert';
    case SendNotification = 'send_notification';
    case GenerateSuggestion = 'generate_suggestion';

    public function label(): string
    {
        return match ($this) {
            self::CreateAlert => 'Create Alert',
            self::SendNotification => 'Send Notification',
            self::GenerateSuggestion => 'Generate Purchase Suggestion',
        };
    }
}
