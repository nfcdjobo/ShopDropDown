<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'first_name' => ['required', 'string', 'min:2', 'max:60'],
            'last_name'  => ['required', 'string', 'min:2', 'max:60'],
            'phone'      => ['required', 'string', 'regex:/^[\d\s\-\+\(\)]{8,20}$/'],
            'email'      => [
                'required',
                'string',
                'email:rfc,dns',
                'max:150',
                Rule::unique('users', 'email')->ignore($userId),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'prénom',
            'last_name'  => 'nom',
            'phone'      => 'téléphone',
            'email'      => 'adresse email',
        ];
    }
}
