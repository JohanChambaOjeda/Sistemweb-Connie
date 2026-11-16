<?php

namespace App\Enums;

/**
 * Sale terms agreed with the importer.
 */
enum Incoterm: string
{
    case Fob = 'FOB';
    case Cif = 'CIF';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
