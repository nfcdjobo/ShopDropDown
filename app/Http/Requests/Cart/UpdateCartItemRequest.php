<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 0 = suppression de la ligne
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ];
    }
}
