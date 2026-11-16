<?php

namespace App\Http\Controllers;

use App\Actions\Products\CreateProduct;
use App\Actions\Products\DeleteProduct;
use App\Actions\Products\UpdateProduct;
use App\Http\Requests\ProductRequest;
use App\Models\FlowerVariety;
use App\Models\Product;
use App\Support\TableQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /** @var list<string> */
    private const SEARCHABLE = ['flowerVariety.name'];

    /** @var list<string> */
    private const SORTABLE = ['stem_length_cm', 'stems_per_bunch', 'created_at'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Product::class);

        return Inertia::render('products/index', [
            'products' => TableQuery::paginate(
                Product::query()->with('flowerVariety:id,name,color'),
                $request,
                self::SEARCHABLE,
                self::SORTABLE,
                'stem_length_cm',
            ),
            'table' => TableQuery::state($request, self::SORTABLE, 'stem_length_cm'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Product::class);

        return Inertia::render('products/form', [
            'product' => null,
            ...$this->formOptions(),
        ]);
    }

    public function store(ProductRequest $request, CreateProduct $action): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $action->handle($request->validated());

        return to_route('products.index')->with('success', __('products.messages.created'));
    }

    public function edit(Product $product): Response
    {
        $this->authorize('update', $product);

        return Inertia::render('products/form', [
            'product' => $product,
            ...$this->formOptions(),
        ]);
    }

    public function update(ProductRequest $request, Product $product, UpdateProduct $action): RedirectResponse
    {
        $this->authorize('update', $product);

        $action->handle($product, $request->validated());

        return to_route('products.index')->with('success', __('products.messages.updated'));
    }

    public function destroy(Product $product, DeleteProduct $action): RedirectResponse
    {
        $this->authorize('delete', $product);

        $action->handle($product);

        return to_route('products.index')->with('success', __('products.messages.deleted'));
    }

    /**
     * Datos que necesitan los selectores del formulario.
     *
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'varieties' => FlowerVariety::query()->where('is_active', true)
                ->orderBy('name')->get(['id', 'name']),
            'defaultStemsPerBunch' => (int) config('floriculture.default_stems_per_bunch'),
            'lengthRange' => config('floriculture.stem_length_range'),
        ];
    }
}
