<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id'         => ['nullable', 'string', 'exists:categories,id'],
            'type'                => ['required', 'in:simple,package'],
            'name'                => ['required', 'string', 'max:180'],
            'slug'                => ['nullable', 'string', 'max:200', Rule::unique('products', 'slug')],
            'sku'                 => ['nullable', 'string', 'max:60', Rule::unique('products', 'sku')],
            'short_description'   => ['nullable', 'string', 'max:500'],
            'description'         => ['nullable', 'string'],
            'video_url'           => ['nullable', 'url', 'max:255'],

            'price'               => ['required', 'numeric', 'min:0'],
            'compare_price'       => ['nullable', 'numeric', 'min:0', 'gte:price'],
            'cost_price'          => ['nullable', 'numeric', 'min:0'],

            'stock'               => ['required', 'integer', 'min:0'],
            'track_stock'         => ['nullable', 'boolean'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],

            // ─── Délai de disponibilité ──────────────────────────────────
            'availability_min_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'availability_max_days' => ['nullable', 'integer', 'min:0', 'max:365', 'gte:availability_min_days'],
            'availability_label'    => ['nullable', 'string', 'max:100'],

            'is_active'           => ['nullable', 'boolean'],
            'is_featured'         => ['nullable', 'boolean'],

            'meta_title'          => ['nullable', 'string', 'max:150'],
            'meta_description'    => ['nullable', 'string', 'max:300'],

            'rating_value'        => ['nullable', 'numeric', 'min:0'],
            'rating_count'        => ['nullable', 'integer', 'min:0'],

            'featured_image'      => ['nullable', 'image', 'max:4096'],

            // ─── Galerie produit ─────────────────────────────────────────
            'gallery'             => ['nullable', 'array', 'max:10'],
            'gallery.*'           => ['image', 'max:4096'],

            // ─── Preuves visuelles (captures de résultats) ───────────────
            'proofs'              => ['nullable', 'array', 'max:10'],
            'proofs.*'            => ['image', 'max:4096'],
            'proof_captions'      => ['nullable', 'array'],
            'proof_captions.*'    => ['nullable', 'string', 'max:200'],
            'proof_alt_texts'     => ['nullable', 'array'],
            'proof_alt_texts.*'   => ['nullable', 'string', 'max:200'],
            'offer_duration_hours'  => ['nullable', 'integer', 'min:1', 'max:720']
        ];
    }

    public function messages(): array
    {
        return [
            'availability_max_days.gte' => 'Le délai maximum doit être supérieur ou égal au délai minimum.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active'   => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
            'track_stock' => $this->boolean('track_stock', true),
        ]);
    }
}
