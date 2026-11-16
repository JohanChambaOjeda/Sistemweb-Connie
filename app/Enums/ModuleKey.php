<?php

namespace App\Enums;

/**
 * Modules a company can buy. Routes and menu entries are protected with the
 * module:<key> middleware.
 */
enum ModuleKey: string
{
    case Sales = 'sales';
    case Postharvest = 'postharvest';
    case Packing = 'packing';
    case Billing = 'billing';
    case Portal = 'portal';
    case Warehouse = 'warehouse';
    case Accounting = 'accounting';
    case Payroll = 'payroll';
    case Crop = 'crop';
    case ThirdPartyFlower = 'third_party_flower';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    public function label(): string
    {
        return __('modules.'.$this->value);
    }
}
