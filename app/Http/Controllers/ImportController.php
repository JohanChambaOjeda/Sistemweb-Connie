<?php

namespace App\Http\Controllers;

use App\Actions\Imports\CommitImportRun;
use App\Actions\Imports\PreviewImportRun;
use App\Actions\Imports\StartImportRun;
use App\Actions\Imports\UpdateImportMapping;
use App\Imports\ImporterRegistry;
use App\Imports\ImportRunReader;
use App\Models\ImportRun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Importación de datos desde Excel: subir archivo, revisar el mapeo de
 * columnas, ver la vista previa y guardar solo las filas válidas.
 */
class ImportController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('imports.run');

        return Inertia::render('imports/index', [
            'types' => array_map(fn ($importer) => [
                'type' => $importer->type(),
                'label' => $importer->label(),
                'fields' => array_map(fn ($field) => [
                    'key' => $field->key,
                    'label' => $field->label,
                    'required' => $field->required,
                    'example' => $field->example,
                ], $importer->fields()),
            ], ImporterRegistry::all()),
            'runs' => ImportRun::query()
                ->with('user:id,name')
                ->latest('id')
                ->limit(20)
                ->get(),
        ]);
    }

    public function store(Request $request, StartImportRun $action): RedirectResponse
    {
        Gate::authorize('imports.run');

        $validated = $request->validate([
            'type' => ['required', Rule::in(ImporterRegistry::types())],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
            'header_row' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $file = $request->file('file');

        $run = $action->handle(
            $file,
            $validated['type'],
            $request->user(),
            (int) ($validated['header_row'] ?? 1),
        );

        return to_route('imports.show', $run);
    }

    public function show(ImportRun $import, ImportRunReader $reader, PreviewImportRun $preview): Response
    {
        Gate::authorize('imports.run');

        $importer = ImporterRegistry::make($import->type);

        return Inertia::render('imports/show', [
            'run' => $import,
            'headers' => $reader->headers($import),
            'fields' => array_map(fn ($field) => [
                'key' => $field->key,
                'label' => $field->label,
                'required' => $field->required,
                'example' => $field->example,
            ], $importer->fields()),
            'preview' => $preview->handle($import),
            'typeLabel' => $importer->label(),
        ]);
    }

    public function update(Request $request, ImportRun $import, UpdateImportMapping $action): RedirectResponse
    {
        Gate::authorize('imports.run');

        $importer = ImporterRegistry::make($import->type);
        $keys = array_map(fn ($field) => $field->key, $importer->fields());

        $validated = $request->validate([
            'header_row' => ['required', 'integer', 'min:1', 'max:20'],
            'column_map' => ['required', 'array'],
            'column_map.*' => ['nullable', 'integer', 'min:0'],
        ]);

        /** @var array<string, int|null> $map */
        $map = [];

        foreach ($keys as $key) {
            $value = $validated['column_map'][$key] ?? null;
            $map[$key] = $value === null ? null : (int) $value;
        }

        $action->handle($import, $map, (int) $validated['header_row']);

        return to_route('imports.show', $import)->with('success', __('imports.messages.mapping_saved'));
    }

    public function commit(ImportRun $import, CommitImportRun $action): RedirectResponse
    {
        Gate::authorize('imports.run');

        $run = $action->handle($import);

        return to_route('imports.show', $run)->with('success', __('imports.messages.committed', [
            'imported' => $run->committed_rows,
            'rejected' => $run->invalid_rows,
        ]));
    }

    public function report(ImportRun $import): StreamedResponse
    {
        Gate::authorize('imports.run');

        abort_if($import->error_report_path === null, 404);

        return Storage::disk('local')->download(
            $import->error_report_path,
            'rechazos-'.$import->type.'-'.$import->getKey().'.xlsx',
        );
    }

    public function destroy(ImportRun $import): RedirectResponse
    {
        Gate::authorize('imports.run');

        Storage::disk('local')->delete(array_filter([$import->stored_path, $import->error_report_path]));

        $import->delete();

        return to_route('imports.index')->with('success', __('imports.messages.deleted'));
    }
}
