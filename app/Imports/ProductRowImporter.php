<?php

namespace App\Imports;

use App\Models\FlowerVariety;
use App\Models\Product;

class ProductRowImporter extends RowImporter
{
    public function type(): string
    {
        return 'products';
    }

    /**
     * @return list<ImportField>
     */
    public function fields(): array
    {
        return [
            new ImportField('variety', __('products.fields.variety'), true, ['variedad', 'variety'], 'Explorer'),
            new ImportField('stem_length_cm', __('products.fields.stem_length_cm'), true, ['largo', 'length', 'cm', 'tallo'], '60'),
            new ImportField('stems_per_bunch', __('products.fields.stems_per_bunch'), false, ['tallos', 'tallos por bonche', 'stems'], '25'),
            new ImportField('is_active', __('products.fields.is_active'), false, ['activo', 'active'], 'si'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var array{min: int, max: int} $range */
        $range = config('floriculture.stem_length_range');

        return [
            'variety' => ['required', 'string', 'max:255'],
            'stem_length_cm' => ['required', 'integer', 'min:'.$range['min'], 'max:'.$range['max']],
            'stems_per_bunch' => ['nullable', 'integer', 'min:1', 'max:100'],
            'is_active' => ['nullable', 'string'],
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
            // Acepta "60", "60 cm" y "60cm".
            $row['stem_length_cm'] = (string) preg_replace('/[^0-9]/', '', (string) $row['stem_length_cm']);
        }

        return $row;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function import(array $row): void
    {
        // La variedad se crea si todavía no existe, así un solo archivo con
        // variedad y largo deja el catálogo completo.
        $variety = FlowerVariety::query()->firstOrCreate(
            ['name' => $row['variety']],
            ['is_active' => true],
        );

        Product::query()->updateOrCreate(
            [
                'flower_variety_id' => $variety->getKey(),
                'stem_length_cm' => (int) $row['stem_length_cm'],
                'stems_per_bunch' => (int) ($row['stems_per_bunch'] ?? config('floriculture.default_stems_per_bunch')),
            ],
            ['is_active' => self::toBoolean($row['is_active'] ?? null)],
        );
    }
}
