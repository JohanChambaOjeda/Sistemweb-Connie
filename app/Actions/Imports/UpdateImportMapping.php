<?php

namespace App\Actions\Imports;

use App\Enums\ImportStatus;
use App\Models\ImportRun;

class UpdateImportMapping
{
    /**
     * @param  array<string, int|null>  $columnMap
     */
    public function handle(ImportRun $run, array $columnMap, int $headerRow): ImportRun
    {
        $run->update([
            'column_map' => $columnMap,
            'header_row' => $headerRow,
            'status' => ImportStatus::Mapped->value,
        ]);

        return $run->refresh();
    }
}
