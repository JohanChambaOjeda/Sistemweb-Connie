<?php

namespace App\Imports;

use App\Enums\VarietyType;
use App\Models\FlowerVariety;
use Illuminate\Validation\Rule;

class VarietyRowImporter extends RowImporter
{
    public function type(): string
    {
        return 'varieties';
    }

    /**
     * @return list<ImportField>
     */
    public function fields(): array
    {
        return [
            new ImportField('name', __('varieties.fields.name'), true, ['variedad', 'variety', 'nombre'], 'Explorer'),
            new ImportField('color', __('varieties.fields.color'), false, ['color', 'tono'], 'Rojo'),
            new ImportField('type', __('varieties.fields.type'), false, ['tipo', 'type'], 'standard'),
            new ImportField('is_active', __('varieties.fields.is_active'), false, ['activa', 'activo', 'active'], 'si'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', Rule::in(VarietyType::values())],
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

        if (isset($row['type'])) {
            $type = mb_strtolower((string) $row['type']);
            $row['type'] = in_array($type, ['garden', 'jardin', 'jardín'], true)
                ? VarietyType::Garden->value
                : VarietyType::Standard->value;
        }

        return $row;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function import(array $row): void
    {
        FlowerVariety::query()->updateOrCreate(
            ['name' => $row['name']],
            [
                'color' => $row['color'] ?? null,
                'type' => $row['type'] ?? VarietyType::Standard->value,
                'is_active' => self::toBoolean($row['is_active'] ?? null),
            ],
        );
    }
}
