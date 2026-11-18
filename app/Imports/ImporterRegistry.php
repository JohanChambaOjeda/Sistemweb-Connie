<?php

namespace App\Imports;

use App\Models\Company;
use InvalidArgumentException;

/**
 * Importadores disponibles. Añadir uno nuevo es registrarlo aquí.
 *
 * Cada importador pertenece a un módulo del plan, así que una finca solo ve
 * los importadores de lo que tiene contratado.
 */
class ImporterRegistry
{
    /** @var array<string, class-string<RowImporter>> */
    private const IMPORTERS = [
        'customers' => CustomerRowImporter::class,
        'varieties' => VarietyRowImporter::class,
        'products' => ProductRowImporter::class,
        'prices' => PriceRowImporter::class,
    ];

    /**
     * Todas las claves registradas, sin mirar módulos.
     *
     * @return list<string>
     */
    public static function types(): array
    {
        return array_keys(self::IMPORTERS);
    }

    public static function make(string $type): RowImporter
    {
        $class = self::IMPORTERS[$type] ?? null;

        if ($class === null) {
            throw new InvalidArgumentException("No hay un importador para [{$type}].");
        }

        return app($class);
    }

    /**
     * @return list<RowImporter>
     */
    public static function all(): array
    {
        return array_map(fn (string $type) => self::make($type), self::types());
    }

    /**
     * Importadores que la empresa puede usar según su plan.
     *
     * @return list<RowImporter>
     */
    public static function availableFor(?Company $company): array
    {
        return array_values(array_filter(
            self::all(),
            fn (RowImporter $importer) => self::isAvailable($importer->type(), $company),
        ));
    }

    /**
     * @return list<string>
     */
    public static function availableTypes(?Company $company): array
    {
        return array_map(
            fn (RowImporter $importer) => $importer->type(),
            self::availableFor($company),
        );
    }

    public static function isAvailable(string $type, ?Company $company): bool
    {
        if (! array_key_exists($type, self::IMPORTERS)) {
            return false;
        }

        $module = self::make($type)->module();

        if ($module === null) {
            return true;
        }

        return $company instanceof Company && $company->hasModule($module);
    }
}
