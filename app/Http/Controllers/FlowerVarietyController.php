<?php

namespace App\Http\Controllers;

use App\Actions\FlowerVarieties\CreateFlowerVariety;
use App\Actions\FlowerVarieties\DeleteFlowerVariety;
use App\Actions\FlowerVarieties\UpdateFlowerVariety;
use App\Enums\VarietyType;
use App\Http\Requests\FlowerVarietyRequest;
use App\Models\FlowerVariety;
use App\Support\TableQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class FlowerVarietyController extends Controller
{
    /** @var list<string> */
    private const SEARCHABLE = ['name', 'color'];

    /** @var list<string> */
    private const SORTABLE = ['name', 'color', 'type', 'created_at'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', FlowerVariety::class);

        $varieties = TableQuery::paginate(
            FlowerVariety::query()->withCount('products'),
            $request,
            self::SEARCHABLE,
            self::SORTABLE,
            'name',
        );

        $varieties->getCollection()->each(function (FlowerVariety $variety) {
            $variety->setAttribute(
                'photo_url',
                $variety->photo_path === null ? null : Storage::disk('public')->url($variety->photo_path),
            );
        });

        return Inertia::render('varieties/index', [
            'varieties' => $varieties,
            'table' => TableQuery::state($request, self::SORTABLE, 'name'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', FlowerVariety::class);

        return Inertia::render('varieties/form', [
            'variety' => null,
            'types' => VarietyType::values(),
        ]);
    }

    public function store(FlowerVarietyRequest $request, CreateFlowerVariety $action): RedirectResponse
    {
        $this->authorize('create', FlowerVariety::class);

        $action->handle($this->data($request), $this->photo($request));

        return to_route('varieties.index')->with('success', __('varieties.messages.created'));
    }

    public function edit(FlowerVariety $variety): Response
    {
        $this->authorize('update', $variety);

        $variety->setAttribute(
            'photo_url',
            $variety->photo_path === null ? null : Storage::disk('public')->url($variety->photo_path),
        );

        return Inertia::render('varieties/form', [
            'variety' => $variety,
            'types' => VarietyType::values(),
        ]);
    }

    public function update(
        FlowerVarietyRequest $request,
        FlowerVariety $variety,
        UpdateFlowerVariety $action,
    ): RedirectResponse {
        $this->authorize('update', $variety);

        $action->handle($variety, $this->data($request), $this->photo($request));

        return to_route('varieties.index')->with('success', __('varieties.messages.updated'));
    }

    public function destroy(FlowerVariety $variety, DeleteFlowerVariety $action): RedirectResponse
    {
        $this->authorize('delete', $variety);

        $action->handle($variety);

        return to_route('varieties.index')->with('success', __('varieties.messages.deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function data(FlowerVarietyRequest $request): array
    {
        $data = $request->validated();
        unset($data['photo']);

        return $data;
    }

    private function photo(FlowerVarietyRequest $request): ?UploadedFile
    {
        $photo = $request->file('photo');

        return $photo instanceof UploadedFile ? $photo : null;
    }
}
