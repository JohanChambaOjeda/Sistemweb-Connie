<?php

namespace App\Http\Requests;

use App\Models\FlowerVariety;
use App\Models\Product;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
        /** @var array{min: int, max: int} $range */
        $range = config('floriculture.stem_length_range');

        return [
            'flower_variety_id' => [
                'required',
                Rule::exists((new FlowerVariety)->getTable(), 'id')->whereNull('deleted_at'),
            ],
            'stem_length_cm' => ['required', 'integer', 'min:'.$range['min'], 'max:'.$range['max']],
            'stems_per_bunch' => ['required', 'integer', 'min:1', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * A company cannot have the same variety, length and stems twice.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $duplicate = Product::query()
                    ->where('flower_variety_id', $this->input('flower_variety_id'))
                    ->where('stem_length_cm', $this->input('stem_length_cm'))
                    ->where('stems_per_bunch', $this->input('stems_per_bunch'))
                    ->when(
                        $this->route('product') !== null,
                        fn ($query) => $query->whereKeyNot($this->route('product')),
                    )
                    ->exists();

                if ($duplicate) {
                    $validator->errors()->add('stem_length_cm', __('products.validation.duplicate'));
                }
            },
        ];
    }
}
