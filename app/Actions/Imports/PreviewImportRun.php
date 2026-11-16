<?php

namespace App\Actions\Imports;

use App\Imports\ImporterRegistry;
use App\Imports\ImportRunReader;
use App\Models\ImportRun;

/**
 * Revisa el archivo sin guardar nada: cuántas filas sirven, cuáles no y por qué.
 */
class PreviewImportRun
{
    public function __construct(private readonly ImportRunReader $reader) {}

    /**
     * @return array{
     *     total: int,
     *     valid: int,
     *     invalid: int,
     *     rows: list<array{number: int, values: array<string, mixed>, errors: list<string>}>,
     *     invalid_rows: list<array{number: int, values: array<string, mixed>, errors: list<string>}>
     * }
     */
    public function handle(ImportRun $run, int $limit = 50): array
    {
        $importer = ImporterRegistry::make($run->type);

        $total = 0;
        $valid = 0;
        $invalid = 0;
        $rows = [];
        $invalidRows = [];

        foreach ($this->reader->rows($run, $importer) as $row) {
            $errors = $importer->errorsFor($row['values']);
            $total++;

            if ($errors === []) {
                $valid++;
            } else {
                $invalid++;
            }

            if (count($rows) < $limit) {
                $rows[] = [...$row, 'errors' => $errors];
            }

            if ($errors !== [] && count($invalidRows) < $limit) {
                $invalidRows[] = [...$row, 'errors' => $errors];
            }
        }

        $run->update([
            'total_rows' => $total,
            'valid_rows' => $valid,
            'invalid_rows' => $invalid,
        ]);

        return [
            'total' => $total,
            'valid' => $valid,
            'invalid' => $invalid,
            'rows' => $rows,
            'invalid_rows' => $invalidRows,
        ];
    }
}
