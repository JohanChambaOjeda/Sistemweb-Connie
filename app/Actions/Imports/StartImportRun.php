<?php

namespace App\Actions\Imports;

use App\Enums\ImportStatus;
use App\Imports\ImporterRegistry;
use App\Imports\ImportField;
use App\Imports\RowImporter;
use App\Models\ImportRun;
use App\Models\User;
use App\Support\SpreadsheetReader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Guarda el archivo subido y adivina el mapeo de columnas por el encabezado.
 */
class StartImportRun
{
    public function handle(UploadedFile $file, string $type, ?User $user = null, int $headerRow = 1): ImportRun
    {
        $importer = ImporterRegistry::make($type);

        $path = $file->store('imports', 'local');

        $rows = SpreadsheetReader::rows(Storage::disk('local')->path($path));
        $headers = $rows[max(0, $headerRow - 1)] ?? [];

        return ImportRun::create([
            'user_id' => $user?->getKey(),
            'type' => $type,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'header_row' => $headerRow,
            'column_map' => $this->guessMap($importer, $headers),
            'status' => ImportStatus::Uploaded->value,
            'total_rows' => max(0, count($rows) - $headerRow),
        ]);
    }

    /**
     * @param  list<string|null>  $headers
     * @return array<string, int|null>
     */
    public function guessMap(RowImporter $importer, array $headers): array
    {
        $map = [];

        foreach ($importer->fields() as $field) {
            $map[$field->key] = null;

            foreach ($headers as $index => $header) {
                if ($header !== null && $field->matches($header)) {
                    $map[$field->key] = $index;

                    break;
                }
            }
        }

        // Si el encabezado no coincide exacto, se intenta por parecido parcial.
        foreach ($importer->fields() as $field) {
            if ($map[$field->key] !== null) {
                continue;
            }

            $usedColumns = array_values(array_filter($map, fn (?int $column) => $column !== null));

            foreach ($headers as $index => $header) {
                if ($header === null || in_array($index, $usedColumns, true)) {
                    continue;
                }

                $normalized = ImportField::normalize($header);

                if ($normalized !== '' && str_contains(ImportField::normalize($field->label), $normalized)) {
                    $map[$field->key] = $index;

                    break;
                }
            }
        }

        return $map;
    }
}
