<?php

namespace App\Http\Requests\Admin\Concerns;

/**
 * Règles et messages communs aux champs image des produits
 * (image principale, galerie, preuves visuelles).
 *
 * - SVG refusé volontairement : un SVG peut embarquer du JavaScript.
 * - Dimensions plafonnées : évite qu'une image géante sature la mémoire à l'encodage.
 */
trait ProductImageRules
{
    /** @return array<int, string> */
    protected function imageFileRules(): array
    {
        return [
            'image',
            'mimes:jpeg,jpg,png,webp',
            'max:4096', // Ko
            'dimensions:max_width=6000,max_height=6000',
        ];
    }

    /** @return array<string, string> */
    protected function imageMessages(): array
    {
        $labels = [
            'featured_image' => "L'image principale",
            'gallery.*'      => 'Chaque image de la galerie',
            'proofs.*'       => 'Chaque preuve visuelle',
        ];

        $messages = [];

        foreach ($labels as $field => $label) {
            $messages["{$field}.image"]      = "{$label} doit être un fichier image valide.";
            $messages["{$field}.mimes"]      = "{$label} doit être au format JPG, PNG ou WebP.";
            $messages["{$field}.max"]        = "{$label} ne doit pas dépasser 4 Mo.";
            $messages["{$field}.dimensions"] = "{$label} ne doit pas dépasser 6000 × 6000 pixels.";
            $messages["{$field}.uploaded"]   = "{$label} n'a pas pu être envoyé (fichier trop lourd pour le serveur ?).";
        }

        return $messages;
    }
}
