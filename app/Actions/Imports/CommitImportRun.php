<?php

namespace App\Actions\Imports;

use App\Enums\ImportStatus;
use App\Exports\RejectedRowsExport;
use App\Imports\ImporterRegistry;
use App\Imports\ImportRunReader;
use App\Models\ImportRun;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Importa solo las filas válidas y deja un reporte descargable con las que se
 * rechazaron y su motivo.
 */
class CommitImportRun
{
    public function __construct(private readonly ImportRunReader $reader) {}

    public function handle(ImportRun $run): ImportRun
    {
        $importer = ImporterRegistry::make($run->type);

        $committed = 0;
        $invalid = 0;
        $total = 0;
        $rejected = [];

        DB::transaction(function () use ($importer, $run, &$committed, &$invalid, &$total, &$rejected) {
            foreach ($this->reader->rows($run, $importer) as $row) {
                $total++;
                $errors = $importer->errorsFor($row['values']);

                if ($errors !== []) {
                    $invalid++;
                    $rejected[] = [...$row, 'errors' => $errors];

                    continue;
                }

                $importer->import($row['values']);
                $committed++;
            }
        });

        $reportPath = null;

        if ($rejected !== []) {
            $reportPath = 'imports/rechazos-'.$run->getKey().'.xlsx';

            $labels = array_map(fn ($field) => $field->label, $importer->fields());

            Excel::store(new RejectedRowsExport($labels, $rejected), $reportPath, 'local');
        }

        $run->update([
            'status' => ImportStatus::Committed->value,
            'total_rows' => $total,
            'valid_rows' => $committed,
            'invalid_rows' => $invalid,
            'committed_rows' => $committed,
            'error_report_path' => $reportPath,
        ]);

        return $run->refresh();
    }
}
