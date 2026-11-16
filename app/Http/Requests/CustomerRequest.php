<?php

namespace App\Http\Requests;

use App\Enums\Incoterm;
use App\Support\CompanyContext;
use App\Support\Translations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    /**
     * Authorization lives in the policy called from the controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The form sends the credit limit in dollars; the database keeps cents.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('credit_limit')) {
            $this->merge([
                'credit_limit_cents' => (int) round(((float) $this->input('credit_limit')) * 100),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('customers', 'name')
                    ->where('company_id', CompanyContext::id())
                    ->ignore($this->route('customer'))
                    ->withoutTrashed(),
            ],
            'country' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'locale' => ['required', Rule::in(Translations::available())],
            'invoice_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'incoterm' => ['required', Rule::in(Incoterm::values())],
            'credit_days' => ['required', 'integer', 'min:0', 'max:365'],
            'credit_limit_cents' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'credit_limit_cents' => __('customers.fields.credit_limit'),
        ];
    }
}
