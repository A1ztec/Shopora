<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'identifier' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !$this->isPhonenumber($value)) {
                        $fail('The ' . $attribute . ' must be a valid email address or phone number.');
                    }
                }
            ],
            'password' => ['required', 'string'],
            'remember_me' => ['boolean']
        ];
    }

    private function isPhonenumber($value): bool
    {

        return preg_match('/^\+[1-9]\d{1,14}$/', $value);
    }

    public function messages(): array
    {
        return [
            'identifier.required' => __('The identifier field is required.'),
            'identifier.string' => __('The identifier must be a string.'),
            'password.required' => __('The password field is required.'),
            'password.string' => __('The password must be a string.'),
            'remember_me.boolean' => __('The remember me field must be true or false.'),
        ];
    }
}
