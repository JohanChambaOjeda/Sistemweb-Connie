<?php

namespace App\Http\Controllers;

use App\Actions\ProductPrices\CreateProductPrice;
use App\Actions\ProductPrices\DeleteProductPrice;
use App\Actions\ProductPrices\UpdateProductPrice;
use App\Enums\Season;
use App\Http\Requests\ProductPriceRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Support\TableQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductPriceController extends Controller
{
    /** @var list<string> */
    private const SEARCHABLE = ['customer.name', 'product.flowerVariety.name'];

    /** @var list<string> */
    private const SORTABLE = ['price_per_stem', 'valid_from', 'valid_to', 'created_at'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ProductPrice::class);

        return Inertia::render('prices/index', [
            'prices' => TableQuery::paginate(
                ProductPrice::query()->with(['product.flowerVariety:id,name', 'customer:id,name']),
                $request,
                self::SEARCHABLE,
                self::SORTABLE,
                'valid_from',
            ),
            'table' => TableQuery::state($request, self::SORTABLE, 'valid_from'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', ProductPrice::class);

        return Inertia::render('prices/form', [
            'price' => null,
            ...$this->formOptions(),
        ]);
    }

    public function store(ProductPriceRequest $request, CreateProductPrice $action): RedirectResponse
    {
        $this->authorize('create', ProductPrice::class);

        $action->handle($request->validated());

        return to_route('prices.index')->with('success', __('prices.messages.created'));
    }

    public function edit(ProductPrice $price): Response
    {
        $this->authorize('update', $price);

        return Inertia::render('prices/form', [
            'price' => $price,
            ...$this->formOptions(),
        ]);
    }

    public function update(ProductPriceRequest $request, ProductPrice $price, UpdateProductPrice $action): RedirectResponse
    {
        $this->authorize('update', $price);

        $action->handle($price, $request->validated());

        return to_route('prices.index')->with('success', __('prices.messages.updated'));
    }

    public function destroy(ProductPrice $price, DeleteProductPrice $action): RedirectResponse
    {
        $this->authorize('delete', $price);

        $action->handle($price);

        return to_route('prices.index')->with('success', __('prices.messages.deleted'));
    }

    /**
     * Datos que necesitan los selectores del formulario.
     *
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'products' => Product::query()->with('flowerVariety:id,name')
                ->where('is_active', true)->get()
                ->sortBy('label')
                ->map(fn (Product $product) => ['id' => $product->id, 'label' => $product->label])
                ->values(),
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name']),
            'seasons' => Season::values(),
        ];
    }
}
