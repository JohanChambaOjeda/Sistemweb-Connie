<?php

namespace App\Http\Requests;

use App\Enums\VarietyType;
use App\Support\CompanyContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FlowerVarietyRequest extends FormRequest
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
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('flower_varieties', 'name')
                    ->where('company_id', CompanyContext::id())
                    ->ignore($this->route('variety'))
                    ->withoutTrashed(),
            ],
            'color' => ['nullable', 'string', 'max:50'],
            'type' => ['required', Rule::in(VarietyType::values())],
            'photo' => ['nullable', 'image', 'max:4096'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
