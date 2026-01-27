<?php

namespace Marufsharia\Hyro\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the users is authorized to make this request.
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
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:' . Config::get('hyro.security.password_policy.min_length', 8)],
            'abilities' => ['sometimes', 'array'],
            'abilities.*' => ['string', 'max:255'],
            'expires_at' => ['sometimes', 'nullable', 'date', 'after:now'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'Please provide a valid email address',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least :min characters',
        ];
    }
}
