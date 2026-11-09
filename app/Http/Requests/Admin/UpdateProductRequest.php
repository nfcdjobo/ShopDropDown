<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ProductImageRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    use ProductImageRules;

    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id'         => ['nullable', 'string', 'exists:categories,id'],
            'type'                => ['required', 'in:simple,package'],
            'name'                => ['required', 'string', 'max:180'],
            'slug'                => ['nullable', 'string', 'max:200', Rule::unique('products', 'slug')->ignore($productId)],
            'sku'                 => ['nullable', 'string', 'max:60', Rule::unique('products', 'sku')->ignore($productId)],
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

            'featured_image'      => ['nullable', ...$this->imageFileRules()],

            // ─── Galerie ─────────────────────────────────────────────────
            'gallery'             => ['nullable', 'array', 'max:10'],
            'gallery.*'           => $this->imageFileRules(),
            'delete_images'       => ['nullable', 'array'],
            'delete_images.*'     => ['string', 'exists:product_images,id'],

            'rating_value'        => ['nullable', 'numeric', 'min:0'],
            'rating_count'        => ['nullable', 'integer', 'min:0'],

            // ─── Preuves visuelles : ajout ───────────────────────────────
            'proofs'              => ['nullable', 'array', 'max:10'],
            'proofs.*'            => $this->imageFileRules(),
            'proof_captions'      => ['nullable', 'array'],
            'proof_captions.*'    => ['nullable', 'string', 'max:200'],
            'proof_alt_texts'     => ['nullable', 'array'],
            'proof_alt_texts.*'   => ['nullable', 'string', 'max:200'],

            // ─── Preuves visuelles : suppression ─────────────────────────
            'delete_proofs'       => ['nullable', 'array'],
            'delete_proofs.*'     => ['string', 'exists:product_proofs,id'],

            // ─── Preuves visuelles : édition des existantes ──────────────
            // Format : existing_proofs[uuid][caption], [alt_text], [sort_order]
            'existing_proofs'              => ['nullable', 'array'],
            'existing_proofs.*'            => ['array'],
            'existing_proofs.*.caption'    => ['nullable', 'string', 'max:200'],
            'existing_proofs.*.alt_text'   => ['nullable', 'string', 'max:200'],
            'existing_proofs.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'offer_duration_hours'  => ['nullable', 'integer', 'min:1', 'max:720']
        ];
    }

    public function messages(): array
    {
        return array_merge($this->imageMessages(), [
            'availability_max_days.gte' => 'Le délai maximum doit être supérieur ou égal au délai minimum.',
        ]);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active'   => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
            'track_stock' => $this->boolean('track_stock'),
        ]);
    }
}
