<?php

namespace App\Support;

use App\Enums\RoleName;

/**
 * Single source of truth for permission names and what each role may do.
 * Seeders, policies and tests all read from here.
 */
class PermissionCatalog
{
    /**
     * Resources with the standard view/create/update/delete actions.
     *
     * @var list<string>
     */
    public const RESOURCES = [
        'users',
        'customers',
        'customer_contacts',
        'cargo_agencies',
        'marks',
        'flower_varieties',
        'products',
        'box_types',
        'product_prices',
    ];

    /** @var list<string> */
    public const ACTIONS = ['view', 'create', 'update', 'delete'];

    /**
     * Permissions that do not follow the resource/action pattern.
     *
     * @var list<string>
     */
    public const EXTRA = [
        'activity_log.view',
        'imports.run',
        'companies.update',
    ];

    /**
     * Every permission the application knows about.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        $permissions = [];

        foreach (self::RESOURCES as $resource) {
            foreach (self::ACTIONS as $action) {
                $permissions[] = $resource.'.'.$action;
            }
        }

        return array_merge($permissions, self::EXTRA);
    }

    /**
     * Permissions granted to a role. The admin gets everything.
     *
     * @return list<string>
     */
    public static function forRole(RoleName $role): array
    {
        return match ($role) {
            RoleName::Admin => self::all(),
            RoleName::Sales => array_merge(
                self::crud('customers'),
                self::crud('customer_contacts'),
                self::crud('marks'),
                self::readOnly('cargo_agencies'),
                self::readOnly('flower_varieties'),
                self::readOnly('products'),
                self::readOnly('box_types'),
                self::readOnly('product_prices'),
                ['imports.run'],
            ),
            RoleName::Billing => array_merge(
                self::readOnly('customers'),
                self::readOnly('customer_contacts'),
                self::readOnly('marks'),
                self::readOnly('cargo_agencies'),
                self::readOnly('products'),
                self::readOnly('box_types'),
                self::crud('product_prices'),
                ['activity_log.view'],
            ),
            RoleName::PostharvestLead => array_merge(
                self::readOnly('customers'),
                self::readOnly('marks'),
                self::crud('flower_varieties'),
                self::crud('products'),
                self::readOnly('box_types'),
            ),
            RoleName::WarehouseLead => array_merge(
                self::readOnly('products'),
                self::readOnly('box_types'),
            ),
            RoleName::Importer => [],
        };
    }

    /**
     * @return list<string>
     */
    public static function crud(string $resource): array
    {
        return array_map(fn (string $action) => $resource.'.'.$action, self::ACTIONS);
    }

    /**
     * @return list<string>
     */
    public static function readOnly(string $resource): array
    {
        return [$resource.'.view'];
    }
}
