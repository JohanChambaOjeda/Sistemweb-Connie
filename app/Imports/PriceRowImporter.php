<?php

namespace App\Imports;

use App\Enums\Season;
use App\Models\Customer;
use App\Models\FlowerVariety;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class PriceRowImporter extends RowImporter
{
    public function type(): string
    {
        return 'prices';
    }

    /**
     * @return list<ImportField>
     */
    public function fields(): array
    {
        return [
            new ImportField('variety', __('products.fields.variety'), true, ['variedad', 'variety'], 'Explorer'),
            new ImportField('stem_length_cm', __('products.fields.stem_length_cm'), true, ['largo', 'length', 'cm'], '60'),
            new ImportField('stems_per_bunch', __('products.fields.stems_per_bunch'), false, ['tallos', 'stems'], '25'),
            new ImportField('customer', __('prices.fields.customer'), false, ['cliente', 'customer'], ''),
            new ImportField('season', __('prices.fields.season'), false, ['temporada', 'season'], 'valentines'),
            new ImportField('price_per_stem', __('prices.fields.price_per_stem'), true, ['precio', 'price', 'precio por tallo'], '0.45'),
            new ImportField('valid_from', __('prices.fields.valid_from'), true, ['desde', 'vigente desde', 'valid from'], '2026-01-01'),
            new ImportField('valid_to', __('prices.fields.valid_to'), false, ['hasta', 'vigente hasta', 'valid to'], ''),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'variety' => ['required', 'string', 'max:255'],
            'stem_length_cm' => ['required', 'integer', 'min:1'],
            'stems_per_bunch' => ['nullable', 'integer', 'min:1', 'max:100'],
            'customer' => ['nullable', 'string', 'max:255'],
            'season' => ['nullable', Rule::in(Season::values())],
            'price_per_stem' => ['required', 'numeric', 'min:0.0001', 'max:999.9999'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
        ];
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array<string, mixed>
     */
    public function prepare(array $row): array
    {
        $row = parent::prepare($row);

        if (isset($row['stem_length_cm'])) {
            $row['stem_length_cm'] = (string) preg_replace('/[^0-9]/', '', (string) $row['stem_length_cm']);
        }

        if (isset($row['price_per_stem'])) {
            $row['price_per_stem'] = CustomerRowImporter::toNumber(
                str_replace('$', '', (string) $row['price_per_stem']),
            );
        }

        if (isset($row['season'])) {
            $season = ImportField::normalize((string) $row['season']);
            $row['season'] = match (true) {
                str_contains($season, 'valent') => Season::Valentines->value,
                str_contains($season, 'madre'), str_contains($season, 'mother') => Season::MothersDay->value,
                default => $row['season'],
            };
        }

        return $row;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function import(array $row): void
    {
        $variety = FlowerVariety::query()->firstOrCreate(
            ['name' => $row['variety']],
            ['is_active' => true],
        );

        $product = Product::query()->firstOrCreate(
            [
                'flower_variety_id' => $variety->getKey(),
                'stem_length_cm' => (int) $row['stem_length_cm'],
                'stems_per_bunch' => (int) ($row['stems_per_bunch'] ?? config('floriculture.default_stems_per_bunch')),
            ],
            ['is_active' => true],
        );

        $customerName = $row['customer'] ?? null;
        $customer = $customerName === null
            ? null
            : Customer::query()->where('name', $customerName)->first();

        ProductPrice::query()->updateOrCreate(
            [
                'product_id' => $product->getKey(),
                'customer_id' => $customer?->getKey(),
                'season' => $row['season'] ?? null,
                'valid_from' => Carbon::parse((string) $row['valid_from'])->toDateString(),
            ],
            [
                'price_per_stem' => $row['price_per_stem'],
                'valid_to' => isset($row['valid_to']) ? Carbon::parse((string) $row['valid_to'])->toDateString() : null,
                'is_active' => true,
            ],
        );
    }

    /**
     * El cliente nombrado en el archivo tiene que existir; si no, la fila se
     * rechaza con un motivo claro en lugar de crear un cliente a medias.
     *
     * @param  array<string, mixed>  $row
     * @return list<string>
     */
    public function errorsFor(array $row): array
    {
        $errors = parent::errorsFor($row);

        $customerName = $row['customer'] ?? null;

        if (is_string($customerName) && $customerName !== ''
            && ! Customer::query()->where('name', $customerName)->exists()) {
            $errors[] = __('imports.errors.unknown_customer', ['name' => $customerName]);
        }

        return $errors;
    }
}
