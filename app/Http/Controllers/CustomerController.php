<?php

namespace App\Http\Controllers;

use App\Actions\Customers\CreateCustomer;
use App\Actions\Customers\DeleteCustomer;
use App\Actions\Customers\UpdateCustomer;
use App\Enums\Incoterm;
use App\Http\Requests\CustomerRequest;
use App\Models\CargoAgency;
use App\Models\Customer;
use App\Support\TableQuery;
use App\Support\Translations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    /** @var list<string> */
    private const SEARCHABLE = ['name', 'country', 'city', 'invoice_email'];

    /** @var list<string> */
    private const SORTABLE = ['name', 'country', 'city', 'credit_days', 'created_at'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Customer::class);

        return Inertia::render('customers/index', [
            'customers' => TableQuery::paginate(
                Customer::query()->withCount(['contacts', 'marks']),
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
        $this->authorize('create', Customer::class);

        return Inertia::render('customers/form', [
            'customer' => null,
            'incoterms' => Incoterm::values(),
            'locales' => Translations::available(),
        ]);
    }

    public function store(CustomerRequest $request, CreateCustomer $action): RedirectResponse
    {
        $this->authorize('create', Customer::class);

        $action->handle($request->validated());

        return to_route('customers.index')->with('success', __('customers.messages.created'));
    }

    public function show(Customer $customer): Response
    {
        $this->authorize('view', $customer);

        return Inertia::render('customers/show', [
            'customer' => $customer->load([
                'contacts' => fn ($query) => $query->orderBy('name'),
                'marks' => fn ($query) => $query->with('cargoAgency')->orderBy('code'),
            ]),
            'cargoAgencies' => CargoAgency::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function edit(Customer $customer): Response
    {
        $this->authorize('update', $customer);

        return Inertia::render('customers/form', [
            'customer' => $customer,
            'incoterms' => Incoterm::values(),
            'locales' => Translations::available(),
        ]);
    }

    public function update(CustomerRequest $request, Customer $customer, UpdateCustomer $action): RedirectResponse
    {
        $this->authorize('update', $customer);

        $action->handle($customer, $request->validated());

        return to_route('customers.index')->with('success', __('customers.messages.updated'));
    }

    public function destroy(Customer $customer, DeleteCustomer $action): RedirectResponse
    {
        $this->authorize('delete', $customer);

        $action->handle($customer);

        return to_route('customers.index')->with('success', __('customers.messages.deleted'));
    }
}
