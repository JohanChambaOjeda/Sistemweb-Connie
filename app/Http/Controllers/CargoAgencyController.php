<?php

namespace App\Http\Controllers;

use App\Actions\CargoAgencies\CreateCargoAgency;
use App\Actions\CargoAgencies\DeleteCargoAgency;
use App\Actions\CargoAgencies\UpdateCargoAgency;
use App\Http\Requests\CargoAgencyRequest;
use App\Models\CargoAgency;
use App\Support\TableQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CargoAgencyController extends Controller
{
    /** @var list<string> */
    private const SEARCHABLE = ['name', 'tax_id', 'contact_name', 'email'];

    /** @var list<string> */
    private const SORTABLE = ['name', 'tax_id', 'cutoff_time', 'created_at'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CargoAgency::class);

        return Inertia::render('cargo-agencies/index', [
            'agencies' => TableQuery::paginate(
                CargoAgency::query(),
                $request,
                self::SEARCHABLE,
                self::SORTABLE,
                'name',
            ),
            'table' => TableQuery::state($request, self::SORTABLE, 'name'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', CargoAgency::class);

        return Inertia::render('cargo-agencies/form', [
            'agency' => null,
            ...$this->formOptions(),
        ]);
    }

    public function store(CargoAgencyRequest $request, CreateCargoAgency $action): RedirectResponse
    {
        $this->authorize('create', CargoAgency::class);

        $action->handle($request->validated());

        return to_route('cargo-agencies.index')->with('success', __('agencies.messages.created'));
    }

    public function edit(CargoAgency $cargoAgency): Response
    {
        $this->authorize('update', $cargoAgency);

        return Inertia::render('cargo-agencies/form', [
            'agency' => $cargoAgency,
            ...$this->formOptions(),
        ]);
    }

    public function update(CargoAgencyRequest $request, CargoAgency $cargoAgency, UpdateCargoAgency $action): RedirectResponse
    {
        $this->authorize('update', $cargoAgency);

        $action->handle($cargoAgency, $request->validated());

        return to_route('cargo-agencies.index')->with('success', __('agencies.messages.updated'));
    }

    public function destroy(CargoAgency $cargoAgency, DeleteCargoAgency $action): RedirectResponse
    {
        $this->authorize('delete', $cargoAgency);

        $action->handle($cargoAgency);

        return to_route('cargo-agencies.index')->with('success', __('agencies.messages.deleted'));
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
