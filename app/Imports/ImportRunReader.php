<?php

namespace App\Imports;

use App\Models\ImportRun;
use App\Support\SpreadsheetReader;
use Generator;
use Illuminate\Support\Facades\Storage;

/**
 * Recorre el archivo de una corrida aplicando el mapeo de columnas elegido.
 */
class ImportRunReader
{
    /**
     * @return list<string|null>
     */
    public function headers(ImportRun $run): array
    {
        $rows = $this->sheet($run);

        return $rows[max(0, $run->header_row - 1)] ?? [];
    }

    /**
     * Filas del archivo ya mapeadas a los campos del importador.
     *
     * @return Generator<int, array{number: int, values: array<string, mixed>}>
     */
    public function rows(ImportRun $run, RowImporter $importer): Generator
    {
        $sheet = $this->sheet($run);
        /** @var array<string, int|null> $map */
        $map = $run->column_map ?? [];

        foreach ($sheet as $index => $row) {
            $number = $index + 1;

            if ($number <= $run->header_row) {
                continue;
            }

            $values = [];

            foreach ($importer->fields() as $field) {
                $column = $map[$field->key] ?? null;
                $values[$field->key] = $column === null ? null : ($row[$column] ?? null);
            }

            // Una fila totalmente vacía es el final útil del archivo.
            if (collect($values)->filter(fn (?string $value) => $value !== null && $value !== '')->isEmpty()) {
                continue;
            }

            yield ['number' => $number, 'values' => $importer->prepare($values)];
        }
    }

    /**
     * @return list<list<string|null>>
     */
    private function sheet(ImportRun $run): array
    {
        return SpreadsheetReader::rows(Storage::disk('local')->path($run->stored_path));
    }
}
