<?php

namespace App\Http\Controllers;

use App\Models\Banniere; // ← adaptez au nom réel de votre modèle
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\View\View;

class PwaController extends Controller
{
    public function manifest(): JsonResponse
    {
        $data = Cache::remember('pwa.manifest', 3600, function () {
            $banniere = Banniere::instance();

            $name  = $banniere?->appelation ?? config('app.name', 'Shop Drop');
            $short = mb_strimwidth($name, 0, 12, '');

            $manifest = [
                'id'               => '/',
                'name'             => $name,
                'short_name'       => $short,
                'description'      => $banniere?->slogan
                    ?? "Produits soigneusement sélectionnés, livrés rapidement en Côte d'Ivoire.",
                'lang'             => 'fr-CI',
                'dir'              => 'ltr',
                'start_url'        => '/?source=pwa',
                'scope'            => '/',
                'display'          => 'standalone',
                'display_override' => ['standalone', 'minimal-ui'],
                'orientation'      => 'portrait-primary',
                'background_color' => '#FBF8F3',
                'theme_color'      => '#C2410C',
                'categories'       => ['shopping'],
                'icons'            => [
                    ['src' => asset('images/icons/icon-192.png'),          'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                    ['src' => asset('images/icons/icon-512.png'),          'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                    ['src' => asset('images/icons/icon-maskable-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
                ],
                'shortcuts'        => [
                    [
                        'name'      => 'Boutique',
                        'short_name'=> 'Boutique',
                        'url'       => route('home', [], false),
                        'icons'     => [['src' => asset('images/icons/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png']],
                    ],
                    [
                        'name'      => 'Mon panier',
                        'short_name'=> 'Panier',
                        'url'       => route('cart.index', [], false),
                        'icons'     => [['src' => asset('images/icons/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png']],
                    ],
                ],
            ];

            // Captures d'écran : l'invite d'installation devient « riche » (Chrome Android / desktop)
            $screens = [];
            if (file_exists(public_path('images/screenshots/mobile.png'))) {
                $screens[] = ['src' => asset('images/screenshots/mobile.png'), 'sizes' => '750x1334', 'type' => 'image/png', 'form_factor' => 'narrow', 'label' => 'Accueil de la boutique'];
            }
            if (file_exists(public_path('images/screenshots/desktop.png'))) {
                $screens[] = ['src' => asset('images/screenshots/desktop.png'), 'sizes' => '1280x720', 'type' => 'image/png', 'form_factor' => 'wide', 'label' => 'Boutique sur ordinateur'];
            }
            if ($screens) {
                $manifest['screenshots'] = $screens;
            }

            return $manifest;
        });

        return response()->json($data, 200, [
            'Content-Type'  => 'application/manifest+json; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function offline(): View
    {
        return view('offline');
    }
}
