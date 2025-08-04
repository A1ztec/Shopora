<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class SocialAuthRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', 'in:facebook,google'],
            'access_token' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'provider.required' => __('The provider field is required.'),
            'provider.string' => __('The provider must be a string.'),
            'provider.in' => __('The selected provider is invalid.'),
            'access_token.required' => __('The access token field is required.'),
            'access_token.string' => __('The access token must be a string.'),
        ];
    }
}
