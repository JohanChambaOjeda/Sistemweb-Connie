<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Reporte descargable de las filas que no se pudieron importar, con el motivo.
 */
class RejectedRowsExport implements FromArray, ShouldAutoSize, WithHeadings
{
    /**
     * @param  list<string>  $fieldLabels
     * @param  list<array{number: int, values: array<string, mixed>, errors: list<string>}>  $rejected
     */
    public function __construct(
        private readonly array $fieldLabels,
        private readonly array $rejected,
    ) {}

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [__('imports.report.row'), ...$this->fieldLabels, __('imports.report.reason')];
    }

    /**
     * @return list<list<string>>
     */
    public function array(): array
    {
        return array_map(
            fn (array $row) => [
                (string) $row['number'],
                ...array_map(fn (mixed $value) => $value === null ? '' : (string) $value, array_values($row['values'])),
                implode(' | ', $row['errors']),
            ],
            $this->rejected,
        );
    }
}
