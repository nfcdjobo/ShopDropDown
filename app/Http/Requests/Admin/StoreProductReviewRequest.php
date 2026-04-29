<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->is_active && $user->isAdmin();
    }

    public function rules(): array
    {
        return [
            'author_name'     => ['required', 'string', 'max:120'],
            'author_location' => ['nullable', 'string', 'max:120'],
            'rating'          => ['required', 'integer', 'between:1,5'],
            'title'           => ['nullable', 'string', 'max:160'],
            'comment'         => ['required', 'string', 'max:1000'],
            'reviewed_on'     => ['nullable', 'date', 'before_or_equal:today'],
            'sort_order'      => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            'author_name.required' => 'Le nom de l\'auteur est obligatoire.',
            'rating.required'      => 'La note est obligatoire.',
            'rating.between'       => 'La note doit être comprise entre 1 et 5 étoiles.',
            'comment.required'     => 'Le commentaire est obligatoire.',
            'comment.max'          => 'Le commentaire ne peut pas dépasser 1000 caractères.',
            'reviewed_on.before_or_equal' => 'La date d\'affichage ne peut pas être dans le futur.',
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

            // Les avis ne s'attachent qu'à un produit vendable seul (jamais à un 'item')
            if ($product->isItem()) {
                $v->errors()->add(
                    'product_id',
                    'Les avis ne peuvent être ajoutés que sur un produit simple ou un package.'
                );
                return;
            }

            // Limite stricte à MAX_REVIEWS
            if ($product->reviews()->count() >= Product::MAX_REVIEWS) {
                $v->errors()->add(
                    'product_id',
                    'Ce produit a déjà '.Product::MAX_REVIEWS.' avis. Supprimez-en un avant d\'en ajouter.'
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'author_name'     => is_string($this->author_name) ? trim($this->author_name) : $this->author_name,
            'author_location' => is_string($this->author_location) ? trim($this->author_location) : $this->author_location,
            'title'           => is_string($this->title) ? trim($this->title) : $this->title,
            'comment'         => is_string($this->comment) ? trim($this->comment) : $this->comment,
        ]);
    }
}
