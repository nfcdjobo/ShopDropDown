<?php

/**
 * Stockage des médias produits (images principales, galeries, preuves visuelles).
 *
 * Stratégie : on tente d'abord le disque principal (S3 ou compatible : R2, Wasabi,
 * Backblaze…). Si sa configuration est absente ou si l'écriture échoue, on enregistre
 * sur le disque de repli (local). Le disque réellement utilisé est mémorisé en base
 * pour chaque fichier (colonnes `disk`).
 */
return [
    // Disque préféré (voir config/filesystems.php : bucket public + CloudFront).
    // Vide (PRODUCT_MEDIA_DISK=) => stockage local uniquement.
    'disk' => env('PRODUCT_MEDIA_DISK', 's3_public'),

    // Exige une URL publique (CloudFront) sur le disque S3 : sans elle, un fichier
    // envoyé sur S3 ne serait pas affichable, on préfère donc le repli local.
    'require_url' => true,

    // Disque de repli (doit être toujours disponible).
    'fallback_disk' => env('PRODUCT_MEDIA_FALLBACK_DISK', 'public'),

    // Disque des fichiers enregistrés avant l'introduction de la colonne `disk`.
    'legacy_disk' => 'public',

    // Dossier racine : <root>/<id-produit>/<featured|gallery|proofs>/<uuid>.webp
    'root' => 'products',

    // Après un échec S3, on n'essaie plus S3 pendant N secondes (évite d'attendre
    // un timeout pour chaque fichier d'un même envoi).
    'down_cooldown' => 60,

    // Optimisation
    'max_width' => 1600,
    'quality'   => 80,
    'qualities' => [
        'proofs' => 85, // captures de résultats : texte à garder lisible
    ],
];
