<?php

namespace App\Http\Requests;

use App\Models\CargoAgency;
use App\Models\Customer;
use App\Support\CompanyContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarkRequest extends FormRequest
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
            'customer_id' => [
                'required',
                Rule::exists((new Customer)->getTable(), 'id')->whereNull('deleted_at'),
            ],
            'cargo_agency_id' => [
                'nullable',
                Rule::exists((new CargoAgency)->getTable(), 'id')->whereNull('deleted_at'),
            ],
            'code' => [
                'required', 'string', 'max:50',
                Rule::unique('marks', 'code')
                    ->where('company_id', CompanyContext::id())
                    ->where('customer_id', $this->input('customer_id'))
                    ->ignore($this->route('mark'))
                    ->withoutTrashed(),
            ],
            'consignee' => ['nullable', 'string', 'max:255'],
            'destination_country' => ['nullable', 'string', 'max:255'],
            'destination_city' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
