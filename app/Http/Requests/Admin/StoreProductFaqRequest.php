<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->is_active && $user->isAdmin();
    }

    public function rules(): array
    {
        return [
            'question'   => ['required', 'string', 'max:255'],
            'answer'     => ['required', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'La question est obligatoire.',
            'question.max'      => 'La question ne peut pas dépasser 255 caractères.',
            'answer.required'   => 'La réponse est obligatoire.',
            'answer.max'        => 'La réponse ne peut pas dépasser 2000 caractères.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            /** @var Product|null $product */
            $product = $this->route('product');

            if (!$product) {
                $v->errors()->add('product_id', 'Produit introuvable.');
                return;
            }

            if ($product->isItem()) {
                $v->errors()->add(
                    'product_id',
                    'Les FAQ ne peuvent être ajoutées que sur un produit simple ou un package.'
                );
                return;
            }

            if ($product->faqs()->count() >= Product::MAX_FAQS) {
                $v->errors()->add(
                    'product_id',
                    'Ce produit a déjà '.Product::MAX_FAQS.' questions. Supprimez-en une avant d\'en ajouter.'
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'question' => is_string($this->question) ? trim($this->question) : $this->question,
            'answer'   => is_string($this->answer) ? trim($this->answer) : $this->answer,
        ]);
    }
}
