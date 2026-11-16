<?php

namespace App\Enums;

/**
 * Roles of the system. `importer` lives in the portal guard, which is built in
 * a later session, so it is only declared here.
 */
enum RoleName: string
{
    case Admin = 'admin';
    case Sales = 'sales';
    case Billing = 'billing';
    case PostharvestLead = 'postharvest_lead';
    case WarehouseLead = 'warehouse_lead';
    case Importer = 'importer';

    /**
     * Roles that can log into the back office.
     *
     * @return list<self>
     */
    public static function backOffice(): array
    {
        return [
            self::Admin,
            self::Sales,
            self::Billing,
            self::PostharvestLead,
            self::WarehouseLead,
        ];
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    public function label(): string
    {
        return __('roles.'.$this->value);
    }
}
