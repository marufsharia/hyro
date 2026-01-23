<?php

namespace Marufsharia\Hyro\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rules\Password;

class UserCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userModel = Config::get('hyro.models.user');
        $table = (new $userModel)->getTable();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', "unique:{$table},email"],
            'password' => ['required', 'confirmed', Password::defaults()],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'max:255', 'exists:' . Config::get('hyro.database.tables.roles') . ',slug'],
        ];

        // Add password policy rules if enabled
        if (Config::get('hyro.security.password_policy.enabled', false)) {
            $passwordRules = ['required', 'confirmed'];

            if (Config::get('hyro.security.password_policy.requires_mixed_case', true)) {
                $passwordRules[] = 'regex:/[a-z]/';
                $passwordRules[] = 'regex:/[A-Z]/';
            }

            if (Config::get('hyro.security.password_policy.requires_numbers', true)) {
                $passwordRules[] = 'regex:/[0-9]/';
            }

            if (Config::get('hyro.security.password_policy.requires_symbols', true)) {
                $passwordRules[] = 'regex:/[^a-zA-Z0-9]/';
            }

            $rules['password'] = $passwordRules;
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered',
            'roles.*.exists' => 'One or more specified roles do not exist',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character',
        ];
    }
}
