<?php

namespace App\Http\Requests;

use App\Enums\Season;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                Rule::exists((new Product)->getTable(), 'id')->whereNull('deleted_at'),
            ],
            'customer_id' => [
                'nullable',
                Rule::exists((new Customer)->getTable(), 'id')->whereNull('deleted_at'),
            ],
            'season' => ['nullable', Rule::in(Season::values())],
            'price_per_stem' => ['required', 'numeric', 'min:0.0001', 'max:999.9999'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
