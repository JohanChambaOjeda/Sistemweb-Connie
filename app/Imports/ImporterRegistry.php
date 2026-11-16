<?php

namespace App\Imports;

use InvalidArgumentException;

/**
 * Importadores disponibles. Añadir uno nuevo es registrarlo aquí.
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
}
