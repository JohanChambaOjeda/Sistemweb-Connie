<?php

namespace App\Support;

use Maatwebsite\Excel\Facades\Excel;

/**
 * Lee un Excel o CSV como una matriz de textos, sin suponer encabezados: el
 * usuario decide en pantalla cuál es la fila de títulos.
 */
class SpreadsheetReader
{
    /**
     * @return list<list<string|null>>
     */
    public static function rows(string $absolutePath): array
    {
        /** @var array<int, array<int, array<int, mixed>>> $sheets */
        $sheets = Excel::toArray(new RawSheet, $absolutePath);

        /** @var array<int, array<int, mixed>> $sheet */
        $sheet = $sheets[0] ?? [];

        return array_values(array_map(
            fn (array $row) => array_values(array_map(
                fn (mixed $cell) => $cell === null ? null : trim((string) $cell),
                $row,
            )),
            $sheet,
        ));
    }
}
