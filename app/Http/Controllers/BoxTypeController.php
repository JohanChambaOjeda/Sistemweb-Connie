<?php

namespace App\Http\Controllers;

use App\Actions\BoxTypes\CreateBoxType;
use App\Actions\BoxTypes\DeleteBoxType;
use App\Actions\BoxTypes\UpdateBoxType;
use App\Http\Requests\BoxTypeRequest;
use App\Models\BoxType;
use App\Support\TableQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BoxTypeController extends Controller
{
    /** @var list<string> */
    private const SEARCHABLE = ['code', 'name'];

    /** @var list<string> */
    private const SORTABLE = ['code', 'name', 'full_box_factor', 'created_at'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', BoxType::class);

        return Inertia::render('box-types/index', [
            'boxTypes' => TableQuery::paginate(
                BoxType::query(),
                $request,
                self::SEARCHABLE,
                self::SORTABLE,
                'code',
            ),
            'table' => TableQuery::state($request, self::SORTABLE, 'code'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', BoxType::class);

        return Inertia::render('box-types/form', [
            'boxType' => null,
            ...$this->formOptions(),
        ]);
    }

    public function store(BoxTypeRequest $request, CreateBoxType $action): RedirectResponse
    {
        $this->authorize('create', BoxType::class);

        $action->handle($request->validated());

        return to_route('box-types.index')->with('success', __('box_types.messages.created'));
    }

    public function edit(BoxType $boxType): Response
    {
        $this->authorize('update', $boxType);

        return Inertia::render('box-types/form', [
            'boxType' => $boxType,
            ...$this->formOptions(),
        ]);
    }

    public function update(BoxTypeRequest $request, BoxType $boxType, UpdateBoxType $action): RedirectResponse
    {
        $this->authorize('update', $boxType);

        $action->handle($boxType, $request->validated());

        return to_route('box-types.index')->with('success', __('box_types.messages.updated'));
    }

    public function destroy(BoxType $boxType, DeleteBoxType $action): RedirectResponse
    {
        $this->authorize('delete', $boxType);

        $action->handle($boxType);

        return to_route('box-types.index')->with('success', __('box_types.messages.deleted'));
    }

    /**
     * Datos que necesitan los selectores del formulario.
     *
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [];
    }
}
