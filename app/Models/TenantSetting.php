<?php

namespace App\Models;

use App\Tenancy\Models\Concerns\HasTenantScope;
use App\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Model;

class TenantSetting extends Model
{
    use HasTenantScope;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'notification_preferences' => 'array',
            'feature_toggles' => 'array',
            'extra' => 'array',
        ];
    }

    public function getBrandLogoUrlAttribute(): ?string
    {
        return $this->brand_logo_url ?? null;
    }

    public function getPrimaryColorAttribute(): ?string
    {
        return $this->brand_primary_color ?? null;
    }

    public function getAccentColorAttribute(): ?string
    {
        return $this->brand_accent_color ?? null;
    }

    public function getConnectionName(): ?string
    {
        $manager = app(TenantManager::class);

        if ($manager->initialized() && $manager->isShared()) {
            return 'shared';
        }

        return null;
    }

    public static function getDefaults(): array
    {
        return [
            'timezone' => 'UTC',
            'locale' => 'en',
            'currency' => config('billing.currency', 'USD'),
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'company_name' => null,
            'company_address' => null,
            'company_email' => null,
            'company_phone' => null,
            'default_language' => 'en',
            'notification_preferences' => [
                'email_notifications' => true,
                'order_confirmation' => true,
                'low_stock_alerts' => true,
                'new_customer_alerts' => false,
            ],
            'feature_toggles' => [],
            'extra' => [],
        ];
    }
}
