<?php

namespace Marufsharia\Hyro\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class TokenCreateRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
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
            'name.required' => 'Token name is required',
            'abilities.array' => 'Abilities must be an array',
            'expires_at.after' => 'Expiration date must be in the future',
        ];
    }
}
