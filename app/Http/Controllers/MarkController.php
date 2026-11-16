<?php

namespace App\Http\Controllers;

use App\Actions\Marks\CreateMark;
use App\Actions\Marks\DeleteMark;
use App\Actions\Marks\UpdateMark;
use App\Http\Requests\MarkRequest;
use App\Models\CargoAgency;
use App\Models\Customer;
use App\Models\Mark;
use App\Support\TableQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MarkController extends Controller
{
    /** @var list<string> */
    private const SEARCHABLE = ['code', 'consignee', 'destination_city', 'customer.name'];

    /** @var list<string> */
    private const SORTABLE = ['code', 'consignee', 'destination_country', 'created_at'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Mark::class);

        return Inertia::render('marks/index', [
            'marks' => TableQuery::paginate(
                Mark::query()->with(['customer:id,name', 'cargoAgency:id,name']),
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
        $this->authorize('create', Mark::class);

        return Inertia::render('marks/form', [
            'mark' => null,
            ...$this->formOptions(),
        ]);
    }

    public function store(MarkRequest $request, CreateMark $action): RedirectResponse
    {
        $this->authorize('create', Mark::class);

        $action->handle($request->validated());

        return to_route('marks.index')->with('success', __('marks.messages.created'));
    }

    public function edit(Mark $mark): Response
    {
        $this->authorize('update', $mark);

        return Inertia::render('marks/form', [
            'mark' => $mark,
            ...$this->formOptions(),
        ]);
    }

    public function update(MarkRequest $request, Mark $mark, UpdateMark $action): RedirectResponse
    {
        $this->authorize('update', $mark);

        $action->handle($mark, $request->validated());

        return to_route('marks.index')->with('success', __('marks.messages.updated'));
    }

    public function destroy(Mark $mark, DeleteMark $action): RedirectResponse
    {
        $this->authorize('delete', $mark);

        $action->handle($mark);

        return to_route('marks.index')->with('success', __('marks.messages.deleted'));
    }

    /**
     * Datos que necesitan los selectores del formulario.
     *
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
            'cargoAgencies' => CargoAgency::query()->orderBy('name')->get(['id', 'name']),
        ];
    }
}
