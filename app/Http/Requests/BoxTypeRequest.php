<?php

namespace App\Http\Requests;

use App\Support\CompanyContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BoxTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge(['code' => strtoupper(trim((string) $this->input('code')))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required', 'string', 'max:10',
                Rule::unique('box_types', 'code')
                    ->where('company_id', CompanyContext::id())
                    ->ignore($this->route('box_type'))
                    ->withoutTrashed(),
            ],
            'name' => ['required', 'string', 'max:255'],
            'full_box_factor' => ['required', 'numeric', 'min:0.001', 'max:99.999'],
            'default_bunches_per_box' => ['required', 'integer', 'min:0', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
