<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation pour l'ajout d'un sous-produit (type = 'item') à un package.
 *
 * Le contrôleur appelant (ProductController::storeItem) :
 *  - force type = 'item', parent_id = $package->id, track_stock = false, price = null
 *  - hérite category_id du package parent
 *
 * On ne valide donc ici que les champs réellement saisis par l'admin.
 */
class StoreProductItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->is_active && $user->isAdmin();
    }

    public function rules(): array
    {
        return [
            // Identification
            'name'              => ['required', 'string', 'max:160'],

            // Contenu
            'short_description' => ['nullable', 'string', 'max:500'],
            'description'       => ['nullable', 'string', 'max:10000'],
            'video_url'         => ['nullable', 'url', 'max:500'],

            // Rôle dans le package
            'package_role'       => ['required', Rule::in([
                Product::ROLE_PRINCIPAL,
                Product::ROLE_BONUS,
            ])],
            'package_sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],

            // Médias
            'featured_image'  => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'gallery'         => ['nullable', 'array', 'max:10'],
            'gallery.*'       => ['image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],

            // Attributs flexibles (taille, couleur…) — optionnels
            'attributes'      => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'           => 'Le nom du sous-produit est obligatoire.',
            'package_role.required'   => 'Indiquez si ce sous-produit est le principal ou un bonus.',
            'package_role.in'         => 'Le rôle doit être « principal » ou « bonus ».',
            'video_url.url'           => 'Le lien vidéo doit être une URL valide.',
            'featured_image.image'    => "L'image principale doit être un fichier image valide.",
            'featured_image.max'      => "L'image principale ne doit pas dépasser 4 Mo.",
            'gallery.max'             => 'Vous pouvez joindre au maximum 10 images de galerie.',
            'gallery.*.image'         => 'Chaque fichier de la galerie doit être une image.',
            'gallery.*.max'           => 'Chaque image de la galerie ne doit pas dépasser 4 Mo.',
        ];
    }

    /**
     * Vérifications métier additionnelles, exécutées après les règles de base.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            /** @var Product|null $package */
            $package = $this->route('product');

            if (!$package) {
                $v->errors()->add('parent_id', 'Package parent introuvable.');
                return;
            }

            // 1. Le parent doit bien être un package
            if (!$package->isPackage()) {
                $v->errors()->add('parent_id', 'Seul un package peut recevoir des sous-produits.');
                return;
            }

            // 2. Un seul "principal" par package
            if ($this->input('package_role') === Product::ROLE_PRINCIPAL
                && $package->principalItem()->exists()) {
                $v->errors()->add(
                    'package_role',
                    'Ce package a déjà un produit principal. Retirez-le avant d\'en désigner un autre.'
                );
            }
        });
    }

    /**
     * Pré-traitement des entrées avant validation : normalise les booléens et
     * trim les champs texte pour éviter les espaces parasites.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'              => is_string($this->name) ? trim($this->name) : $this->name,
            'short_description' => is_string($this->short_description) ? trim($this->short_description) : $this->short_description,
            'video_url'         => is_string($this->video_url) ? trim($this->video_url) : $this->video_url,
        ]);
    }
}
