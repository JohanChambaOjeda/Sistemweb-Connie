<?php

namespace App\Imports;

use App\Enums\Incoterm;
use App\Enums\ModuleKey;
use App\Models\Customer;
use Illuminate\Validation\Rule;

class CustomerRowImporter extends RowImporter
{
    public function type(): string
    {
        return 'customers';
    }

    public function module(): ?string
    {
        return ModuleKey::Sales->value;
    }

    /**
     * @return list<ImportField>
     */
    public function fields(): array
    {
        return [
            new ImportField('name', __('customers.fields.name'), true, ['cliente', 'customer', 'razon social', 'nombre'], 'Miami Roses'),
            new ImportField('country', __('customers.fields.country'), false, ['pais', 'country'], 'United States'),
            new ImportField('city', __('customers.fields.city'), false, ['ciudad', 'city'], 'Miami'),
            new ImportField('invoice_email', __('customers.fields.invoice_email'), false, ['email', 'correo', 'mail', 'e mail'], 'pagos@cliente.com'),
            new ImportField('phone', __('customers.fields.phone'), false, ['telefono', 'phone', 'tel'], '+1 305 555 1234'),
            new ImportField('whatsapp', __('customers.fields.whatsapp'), false, ['celular', 'movil', 'mobile'], ''),
            new ImportField('incoterm', __('customers.fields.incoterm'), false, ['terminos', 'terms', 'fob cif'], 'FOB'),
            new ImportField('credit_days', __('customers.fields.credit_days'), false, ['dias de credito', 'credito', 'credit days'], '30'),
            new ImportField('credit_limit', __('customers.fields.credit_limit'), false, ['limite de credito', 'credit limit', 'cupo'], '15000'),
            new ImportField('locale', __('customers.fields.locale'), false, ['idioma', 'language'], 'en'),
            new ImportField('is_active', __('customers.fields.is_active'), false, ['activo', 'active', 'estado'], 'si'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'invoice_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'incoterm' => ['nullable', Rule::in(Incoterm::values())],
            'credit_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'locale' => ['nullable', 'in:es,en'],
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

        if (isset($row['incoterm'])) {
            $row['incoterm'] = mb_strtoupper((string) $row['incoterm']);
        }

        if (isset($row['locale'])) {
            $row['locale'] = mb_strtolower((string) $row['locale']);
        }

        if (isset($row['credit_limit'])) {
            // Acepta "15.000,50" y "15,000.50".
            $row['credit_limit'] = self::toNumber((string) $row['credit_limit']);
        }

        return $row;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function import(array $row): void
    {
        Customer::query()->updateOrCreate(
            ['name' => $row['name']],
            [
                'country' => $row['country'] ?? null,
                'city' => $row['city'] ?? null,
                'invoice_email' => $row['invoice_email'] ?? null,
                'phone' => $row['phone'] ?? null,
                'whatsapp' => $row['whatsapp'] ?? null,
                'incoterm' => $row['incoterm'] ?? Incoterm::Fob->value,
                'credit_days' => (int) ($row['credit_days'] ?? 0),
                'credit_limit_cents' => (int) round(((float) ($row['credit_limit'] ?? 0)) * 100),
                'locale' => $row['locale'] ?? 'en',
                'is_active' => self::toBoolean($row['is_active'] ?? null),
            ],
        );
    }

    /**
     * Normaliza un número escrito a mano.
     *
     * Cuando aparecen los dos separadores, el último es el decimal: así
     * "15.000,50" y "15,000.50" dan lo mismo. Si solo hay uno, se toma como
     * decimal ("0,45" es cuarenta y cinco centavos). La vista previa muestra el
     * valor interpretado antes de guardar, así que un caso raro se ve a tiempo.
     */
    public static function toNumber(string $value): string
    {
        $value = str_replace([' ', "\u{a0}"], '', $value);

        $lastComma = strrpos($value, ',');
        $lastDot = strrpos($value, '.');

        if ($lastComma !== false && $lastDot !== false) {
            return $lastComma > $lastDot
                ? str_replace(',', '.', str_replace('.', '', $value))
                : str_replace(',', '', $value);
        }

        return str_replace(',', '.', $value);
    }
}
