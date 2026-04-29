<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in([
                'confirm', 'process', 'ship', 'deliver', 'cancel',
                'assign', 'notes',
            ])],
            'note'                => ['nullable', 'string', 'max:500'],
            'cancellation_reason' => ['nullable', 'string', 'max:255', 'required_if:action,cancel'],
            'assigned_to'         => ['nullable', 'string', 'exists:users,id'],
            'admin_notes'         => ['nullable', 'string', 'max:2000'],
        ];
    }
}





// cancelled
