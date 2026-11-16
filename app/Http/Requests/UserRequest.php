<?php

namespace App\Http\Requests;

use App\Enums\RoleName;
use App\Support\Translations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
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
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user),
            ],
            'password' => [
                $user === null ? 'required' : 'nullable',
                'confirmed',
                Password::min(8),
            ],
            'locale' => ['required', Rule::in(Translations::available())],
            'is_active' => ['required', 'boolean'],
            'role' => [
                'required',
                Rule::in(array_map(fn (RoleName $role) => $role->value, RoleName::backOffice())),
            ],
            'company_ids' => ['required', 'array', 'min:1'],
            'company_ids.*' => ['integer', Rule::exists('companies', 'id')->whereNull('deleted_at')],
        ];
    }
}
