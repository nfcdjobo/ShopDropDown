@extends('layouts.app')

@section('title', $product->meta_title ?: $product->name)
@section('meta_description', $product->meta_description ?: $product->short_description)

@php
    /*
    |--------------------------------------------------------------------------
    | Données préparées pour la vue
    |--------------------------------------------------------------------------
    */

    // Galerie : image principale en tête + images de la galerie produit
    $allImages = collect();
    if ($product->featured_image_url) {
        $allImages->push([
            'url' => $product->featured_image_url,
            'alt' => $product->name,
        ]);
    }
    foreach ($product->images as $img) {

        if ($img->url) {
            $allImages->push([
                'url' => $img->url,
                'alt' => $img->alt_text ?: $product->name,
            ]);
        }
    }

    // Embed vidéo (YouTube ou Vimeo)


    // Embed vidéo (YouTube, Shorts, Vimeo)
    // Helper de parsing vidéo — réutilisé pour le produit principal ET chaque sous-produit
    $buildVideoEmbed = function (?string $url): ?string {
        if (!$url) return null;
        $url = trim($url);

        // YouTube tous formats : watch, youtu.be, embed, shorts, v/
        if (preg_match('#(?:youtube\.com/(?:watch\?(?:[^&]*&)*v=|embed/|shorts/|v/)|youtu\.be/)([\w-]{11})#i', $url, $m)) {
            return "https://www.youtube.com/embed/{$m[1]}?rel=0";
        }

        // Vimeo (avec hash optionnel pour les vidéos privées)
        if (preg_match('#vimeo\.com/(?:video/|channels/[\w-]+/|groups/[\w-]+/videos/)?(\d+)(?:/(\w+))?#i', $url, $m)) {
            return "https://player.vimeo.com/video/{$m[1]}" . (!empty($m[2]) ? "?h={$m[2]}" : '');
        }

        return null;
    };

    $videoEmbed = $buildVideoEmbed($product->video_url);

    // Compte à rebours : timestamp ISO pour le JS
    $deliveryIso = $deliveryEstimate?->toIso8601String();
    $deliveryHumanDate = $deliveryEstimate?->locale('fr')->isoFormat('dddd D MMMM');

// Routes injectées côté serveur (pas de globals JS)
    $cartStoreUrl  = route('cart.store');
    $checkoutUrl   = route('checkout.index');
    $csrfToken     = csrf_token();

    // ─── WhatsApp ────────────────────────────────────────────────────
    // Source : bannière unique partagée par BanniereServiceProvider.
    // Stratégie : on privilégie numero_whatsapp pour pouvoir injecter
    // un message pré-rempli avec le nom du produit ; à défaut on retombe
    // sur lien_whatsapp tel quel (lien brut, sans message).
    $whatsappLink = null;
    if (isset($banniere) && $banniere) {
        $message = "Bonjour, je souhaite commander : {$product->name} (réf. {$product->sku})";

        if ($banniere->numero_whatsapp) {
            $cleanNumber = preg_replace('/[^0-9]/', '', $banniere->numero_whatsapp);
            $whatsappLink = "https://wa.me/{$cleanNumber}?text=" . urlencode($message);
        } elseif ($banniere->lien_whatsapp) {
            $whatsappLink = $banniere->lien_whatsapp;
        }
    }
@endphp

@section('content')

{{-- =============================================================
     ROOT — état partagé pour sticky bar et galerie
     ============================================================= --}}
<div x-data="productPage({
        deliveryAt: @js($deliveryIso),
        cartStoreUrl: @js($cartStoreUrl),
        checkoutUrl: @js($checkoutUrl),
        csrf: @js($csrfToken),
        productId: @js($product->id),
        productName: @js($product->name),
        productPrice: {{ (float) $product->price }},
        productCategory: @js($product->category?->name),
        currency: 'XOF',
        offerDurationHours: {{ $product->has_offer ? (int) $product->offer_duration_hours : 'null' }},
        offerDiscountPercent: {{ $product->has_offer ? (int) $product->discount_percent : 'null' }},
     })"
     class="pb-28 lg:pb-32">

    {{-- =============================================================
         BREADCRUMB
         ============================================================= --}}
    <nav class="max-w-7xl mx-auto px-5 lg:px-10 pt-6 lg:pt-8 text-xs text-ink-muted flex items-center gap-2 flex-wrap">
        <a href="{{ route('home') }}" class="hover:text-terracotta-500 transition-colors">Accueil</a>
        <i class="fa-solid fa-chevron-right text-[8px] opacity-50"></i>
        @if($product->category)
            <a href="{{ route('home') }}#featured" class="hover:text-terracotta-500 transition-colors">{{ $product->category->name }}</a>
            <i class="fa-solid fa-chevron-right text-[8px] opacity-50"></i>
        @endif
        <span class="text-ink truncate">{{ $product->name }}</span>
    </nav>


    {{-- =============================================================
         HEADER PRODUIT — pleine largeur, titre en premier
         ============================================================= --}}
    <header class="max-w-7xl mx-auto px-5 lg:px-10 pt-6 lg:pt-10">
            @if($product->category)
                <p class="text-[11px] uppercase tracking-widest text-terracotta-500 font-bold">{{ $product->category->name }}</p>
            @endif

            <h1 class="mt-2 font-display font-semibold text-4xl sm:text-5xl lg:text-7xl leading-[1.02] tracking-tight">
            {{ $product->name }}
            </h1>

            <div class="mt-4 flex items-center gap-4 flex-wrap">
                {{-- Étoiles + nombre d'avis --}}
                @if($product->has_rating)
                    @php [$full, $half, $empty] = $product->stars_breakdown; @endphp
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-0.5 text-amber-500">
                            @for($i = 0; $i < $full; $i++)<i class="fa-solid fa-star text-sm"></i>@endfor
                            @if($half)<i class="fa-solid fa-star-half-stroke text-sm"></i>@endif
                            @for($i = 0; $i < $empty; $i++)<i class="fa-solid fa-star text-sm text-ink-muted/20"></i>@endfor
                        </div>
                        <span class="text-sm font-semibold">{{ $product->formatted_rating }}</span>
                        <span class="text-sm text-ink-muted">({{ $product->rating_count }} avis)</span>
                    </div>
                @endif

                @if($product->isPackage())
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-violet-700 bg-violet-50 px-2.5 py-1 rounded-full">
                        <i class="fa-solid fa-boxes-stacked text-[10px]"></i>
                        Pack complet
                    </span>
                @endif
            </div>

            @if($product->short_description)
                <p class="mt-5 text-base lg:text-lg text-ink-muted leading-relaxed max-w-2xl">{{ $product->short_description }}</p>
            @endif
        {{-- </div> --}}
    </header>


    {{-- =============================================================
         HERO PRODUIT — galerie | infos
         ============================================================= --}}
    <section class="max-w-7xl mx-auto px-5 lg:px-10 pt-6 lg:pt-10 grid lg:grid-cols-12 gap-8 lg:gap-12">

        {{-- ─── Galerie ───────────────────────────────────────────── --}}
        <div class="lg:col-span-7" x-data="{ active: 0, images: @js($allImages->values()->all()) }">
            {{-- Image principale --}}
            <div class="relative aspect-[4/5] sm:aspect-[1/1] rounded-3xl overflow-hidden bg-sand-100 shadow-soft">
                <template x-for="(img, i) in images" :key="i">
                    <img :src="img.url" :alt="img.alt"
                         x-show="active === i"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-[1.02]"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute inset-0 w-full h-full object-cover">
                </template>

                <template x-if="images.length === 0">
                    <div class="absolute inset-0 flex items-center justify-center text-ink-muted">
                        <i class="fa-regular fa-image text-5xl"></i>
                    </div>
                </template>

                {{-- Badges (promo, low-stock) --}}
                <div class="absolute top-4 left-4 flex flex-col gap-2">
                    @if($product->on_sale)
                        <span class="bg-terracotta-500 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-full flex items-center gap-1.5 shadow-soft">
                            <i class="fa-solid fa-tag text-[9px]"></i>
                            -{{ $product->discount_percent }}%
                        </span>
                    @endif
                    @if($product->isPackage())
                        <span class="bg-violet-600 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-full flex items-center gap-1.5 shadow-soft">
                            <i class="fa-solid fa-boxes-stacked text-[9px]"></i>
                            Pack
                        </span>
                    @endif
                </div>

                @if($product->is_low_stock)
                    <span class="absolute top-4 right-4 bg-white/95 backdrop-blur text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-full flex items-center gap-1.5 shadow-soft">
                        <i class="fa-solid fa-fire text-[9px] text-terracotta-500"></i>
                        Plus que {{ $product->stock }}
                    </span>
                @endif

                {{-- Navigation flèches (desktop) --}}
                <template x-if="images.length > 1">
                    <div>
                        <button type="button"
                                @click="active = (active - 1 + images.length) % images.length"
                                class="hidden sm:flex absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/90 backdrop-blur hover:bg-white shadow-soft items-center justify-center">
                            <i class="fa-solid fa-chevron-left text-sm"></i>
                        </button>
                        <button type="button"
                                @click="active = (active + 1) % images.length"
                                class="hidden sm:flex absolute right-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-white/90 backdrop-blur hover:bg-white shadow-soft items-center justify-center">
                            <i class="fa-solid fa-chevron-right text-sm"></i>
                        </button>
                    </div>
                </template>
            </div>

            {{-- Thumbnails --}}
            <template x-if="images.length > 1">
                <div class="mt-4 grid grid-cols-5 gap-2 sm:gap-3">
                    <template x-for="(img, i) in images" :key="i">
                        <button type="button" @click="active = i"
                                class="aspect-square rounded-xl overflow-hidden ring-2 transition-all"
                                :class="i === active ? 'ring-terracotta-500' : 'ring-transparent hover:ring-ink/20'">
                            <img :src="img.url" :alt="img.alt"
                                 class="w-full h-full object-cover"
                                 :class="i === active ? '' : 'opacity-70 hover:opacity-100'">
                        </button>
                    </template>
                </div>
            </template>
        </div>

        {{-- ─── Infos & CTA ───────────────────────────────────────── --}}
        <div class="lg:col-span-5 flex flex-col">

            {{-- Prix --}}
            <div class="mt-6 flex items-baseline gap-3">
                <p class="font-display text-4xl lg:text-5xl font-semibold">{{ $product->formatted_price }}</p>
                @if($product->on_sale)
                    <p class="text-lg text-ink-muted line-through">{{ number_format($product->compare_price, 0, ',', ' ') }} FCFA</p>
                    <span class="text-xs font-bold text-terracotta-500 uppercase tracking-wider">
                        Économisez {{ number_format($product->compare_price - $product->price, 0, ',', ' ') }} F
                    </span>
                @endif
            </div>

            @if($product->isPackage())
                <p class="mt-2 text-xs text-violet-700 font-semibold inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-gift text-[10px]"></i>
                    Tout est inclus dans le pack — voir le détail plus bas
                </p>
            @endif

            {{-- ─── Délai temps réel ──────────────────────────────── --}}
            {{-- @if($product->has_availability)
                <div class="mt-6 p-5 rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-50/50 ring-1 ring-emerald-200">
                    <div class="flex items-start gap-3">
                        <span class="w-10 h-10 rounded-xl bg-white text-emerald-600 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-truck-fast"></i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">{{ $product->availability_text }}</p>

                            @if($deliveryIso)
                                <p class="mt-1 text-sm text-ink">
                                    Livré au plus tard le <strong>{{ $deliveryHumanDate }}</strong>
                                </p>
                                <div class="mt-3 grid grid-cols-4 gap-1.5">
                                    @foreach(['days' => 'jours', 'hours' => 'h', 'minutes' => 'min', 'seconds' => 'sec'] as $key => $label)
                                        <div class="text-center bg-white rounded-lg py-2">
                                            <p class="font-display text-xl font-bold tabular-nums leading-none" x-text="String(countdown.{{ $key }}).padStart(2,'0')"></p>
                                            <p class="text-[10px] uppercase tracking-wider text-ink-muted mt-1">{{ $label }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif --}}

            {{-- ─── Bloc 1 : Délai de livraison (statique) ────────── --}}
            @if($product->has_availability)
                <div class="mt-6 p-5 rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-50/50 ring-1 ring-emerald-200">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-white text-emerald-600 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-truck-fast"></i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">{{ $product->availability_text }}</p>
                            @if($deliveryHumanDate)
                                <p class="mt-0.5 text-sm text-ink">
                                    Livraison estimée le <strong>{{ $deliveryHumanDate }}</strong>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- ─── Bloc 2 : Offre limitée (compteur dynamique) ───── --}}
            @if($product->has_offer)
                <div x-show="offerVisible" x-cloak
                     class="mt-4 p-5 rounded-2xl bg-gradient-to-br from-red-50 to-amber-50 ring-1 ring-red-200">
                    <div class="flex items-start gap-3">
                        <span class="w-10 h-10 rounded-xl bg-white text-red-600 flex items-center justify-center flex-shrink-0 animate-pulse">
                            <i class="fa-solid fa-fire"></i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wider text-red-700">
                                Offre -<span x-text="offerDiscountPercent"></span>% — Plus que
                            </p>
                            <div class="mt-2 grid grid-cols-4 gap-1.5">
                                @foreach(['hours' => 'h', 'minutes' => 'min', 'seconds' => 'sec'] as $key => $label)
                                    <div class="text-center bg-white rounded-lg py-2">
                                        <p class="font-display text-xl font-bold tabular-nums leading-none text-red-700"
                                           x-text="String(offerCountdown.{{ $key }}).padStart(2,'0')"></p>
                                        <p class="text-[10px] uppercase tracking-wider text-ink-muted mt-1">{{ $label }}</p>
                                    </div>
                                @endforeach
                                <div class="text-center bg-white rounded-lg py-2">
                                    <p class="font-display text-xl font-bold tabular-nums leading-none text-red-700"
                                       x-text="String(offerCountdown.days).padStart(2,'0')"></p>
                                    <p class="text-[10px] uppercase tracking-wider text-ink-muted mt-1">jours</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ─── Boutons d'action ──────────────────────────────── --}}
            <div class="mt-6 flex flex-col gap-3" x-data="{ qty: 1 }">
                {{-- Quantité --}}
                <div class="flex items-center gap-3">
                    <span class="text-xs uppercase tracking-wider font-semibold text-ink-muted">Quantité</span>
                    <div class="inline-flex items-center bg-ivory-100 rounded-full">
                        <button type="button" @click="qty = Math.max(1, qty - 1)"
                                class="w-10 h-10 rounded-full hover:bg-white flex items-center justify-center">
                            <i class="fa-solid fa-minus text-xs"></i>
                        </button>
                        <span class="w-10 text-center font-semibold tabular-nums" x-text="qty"></span>
                        <button type="button" @click="qty = Math.min({{ $product->track_stock ? $product->stock : 99 }}, qty + 1)"
                                class="w-10 h-10 rounded-full hover:bg-white flex items-center justify-center">
                            <i class="fa-solid fa-plus text-xs"></i>
                        </button>
                    </div>
                </div>

                {{-- Acheter maintenant (proéminent) --}}
                <button type="button"
                        @click="buyNow(qty)"
                        :disabled="busy"
                        class="h-14 rounded-full bg-terracotta-500 hover:bg-terracotta-700 disabled:bg-ink-muted/50 text-white font-semibold text-base inline-flex items-center justify-center gap-2 shadow-lift transition-colors">
                    <i class="fa-solid fa-bolt" x-show="!busy"></i>
                    <i class="fa-solid fa-spinner fa-spin" x-show="busy" x-cloak></i>
                    <span>Commander maintenant</span>
                </button>

                <div class="grid grid-cols-2 gap-3">
                    {{-- Ajouter au panier --}}
                    <button type="button"
                            @click="addToCart(qty)"
                            :disabled="busy"
                            class="h-12 rounded-full bg-ink hover:bg-ink/90 text-ivory-50 font-semibold text-sm inline-flex items-center justify-center gap-2 transition-colors">
                        <i class="fa-solid fa-cart-plus text-sm"></i>
                        Au panier
                    </button>

                    {{-- WhatsApp --}}
                    @if($whatsappLink)
                        <a href="{{ $whatsappLink }}" target="_blank" rel="noopener"
                           class="h-12 rounded-full bg-[#25D366] hover:bg-[#1da851] text-white font-semibold text-sm inline-flex items-center justify-center gap-2 transition-colors">
                            <i class="fa-brands fa-whatsapp text-base"></i>
                            WhatsApp
                        </a>
                    @endif
                </div>
            </div>

            {{-- Trust points --}}
            <ul class="mt-8 grid grid-cols-2 gap-3 text-xs">
                <li class="flex items-center gap-2 text-ink-muted">
                    <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                    Paiement à la livraison
                </li>
                <li class="flex items-center gap-2 text-ink-muted">
                    <i class="fa-solid fa-truck text-emerald-600"></i>
                    Livraison partout en CI
                </li>
                <li class="flex items-center gap-2 text-ink-muted">
                    <i class="fa-solid fa-arrow-rotate-left text-emerald-600"></i>
                    Retour 7 jours
                </li>
                <li class="flex items-center gap-2 text-ink-muted">
                    <i class="fa-solid fa-headset text-emerald-600"></i>
                    Support réactif
                </li>
            </ul>
        </div>
    </section>

    {{-- ============================================================= SOUS-PRODUITS (uniquement si package) ============================================================= --}}
    @if($product->isPackage() && $product->items->isNotEmpty())
        <section class="max-w-7xl mx-auto px-5 lg:px-10 mt-20 lg:mt-28">
            <div class="text-center max-w-2xl mx-auto mb-10 lg:mb-14">
                <p class="text-xs uppercase tracking-widest text-violet-700 font-bold flex items-center justify-center gap-2">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    Tout est inclus
                </p>
                <h2 class="mt-3 font-display font-semibold text-3xl lg:text-5xl leading-tight tracking-tight">
                    Ce que vous recevez <span class="italic text-violet-700">dans le pack</span>
                </h2>
                <p class="mt-4 text-ink-muted">{{ $product->items->count() }} produits soigneusement assemblés pour vous offrir une expérience complète.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-6">
                @foreach($product->items as $item)
                    @php
                        $isPrincipal = $item->package_role === \App\Models\Product::ROLE_PRINCIPAL;
                        $itemVideo   = $buildVideoEmbed($item->video_url);

                        // Liste hétérogène : images d'abord, puis (éventuelle) vidéo en dernier slide.
                        // Format : ['type' => 'image'|'video', 'url'|'embed' => ..., 'alt' => ?]
                        $itemMedia = collect();
                        if ($item->featured_image_url) {
                            $itemMedia->push(['type' => 'image', 'url' => $item->featured_image_url, 'alt' => $item->name]);
                        }
                        foreach ($item->images as $img) {
                            if ($img->url) {
                                $itemMedia->push(['type' => 'image', 'url' => $img->url, 'alt' => $img->alt_text ?: $item->name]);
                            }
                        }
                        if ($itemVideo) {
                            $itemMedia->push(['type' => 'video', 'embed' => $itemVideo, 'alt' => $item->name]);
                        }
                    @endphp

                    <article class="group {{ $isPrincipal ? 'sm:col-span-2 lg:col-span-1' : '' }}"
                             x-data="{ active: 0, count: {{ $itemMedia->count() }} }">

                        {{-- Slide actif --}}
                        <div class="relative aspect-[4/5] rounded-2xl overflow-hidden bg-sand-100 mb-3 ring-1 {{ $isPrincipal ? 'ring-emerald-200' : 'ring-ink/5' }}">
                            @if($itemMedia->isNotEmpty())
                                @foreach($itemMedia as $mediaIndex => $media)
                                    @if($media['type'] === 'image')
                                        <img src="{{ $media['url'] }}" alt="{{ $media['alt'] }}" loading="lazy"
                                             x-show="active === {{ $mediaIndex }}"
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0"
                                             x-transition:enter-end="opacity-100"
                                             class="absolute inset-0 w-full h-full object-cover">
                                    @else
                                        {{-- Iframe rendue uniquement quand active : évite de charger
                                             toutes les vidéos YouTube de la page au chargement initial. --}}
                                        <template x-if="active === {{ $mediaIndex }}">
                                            <iframe src="{{ $media['embed'] }}"
                                                    class="absolute inset-0 w-full h-full"
                                                    frameborder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                    allowfullscreen></iframe>
                                        </template>
                                    @endif
                                @endforeach
                            @else
                                <div class="absolute inset-0 flex items-center justify-center text-ink-muted">
                                    <i class="fa-regular fa-image text-3xl"></i>
                                </div>
                            @endif

                            {{-- Badge rôle --}}
                            <span class="absolute top-3 left-3 z-10 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full flex items-center gap-1.5 bg-white/90 backdrop-blur text-violet-700">
                                <i class="fa-solid fa-gift text-[9px]"></i>
                                Bonus inclus
                            </span>

                            {{-- Flèches (si plusieurs médias) --}}
                            <template x-if="count > 1">
                                <div>
                                    <button type="button"
                                            @click.stop="active = (active - 1 + count) % count"
                                            class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/90 backdrop-blur hover:bg-white shadow-soft flex items-center justify-center z-10">
                                        <i class="fa-solid fa-chevron-left text-xs"></i>
                                    </button>
                                    <button type="button"
                                            @click.stop="active = (active + 1) % count"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/90 backdrop-blur hover:bg-white shadow-soft flex items-center justify-center z-10">
                                        <i class="fa-solid fa-chevron-right text-xs"></i>
                                    </button>

                                    {{-- Pastilles --}}
                                    <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5 z-10">
                                        <template x-for="i in count" :key="i">
                                            <button type="button" @click.stop="active = i - 1"
                                                    class="w-1.5 h-1.5 rounded-full transition-all"
                                                    :class="active === i - 1 ? 'bg-white w-4' : 'bg-white/60 hover:bg-white/80'">
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Mini thumbnails (≥ 2 médias) --}}
                        @if($itemMedia->count() > 1)
                            <div class="grid gap-1.5 mb-3" style="grid-template-columns: repeat({{ min($itemMedia->count(), 5) }}, minmax(0, 1fr));">
                                @foreach($itemMedia as $mediaIndex => $media)
                                    <button type="button" @click="active = {{ $mediaIndex }}"
                                            class="relative aspect-square rounded-lg overflow-hidden ring-2 transition-all"
                                            :class="active === {{ $mediaIndex }} ? 'ring-{{ $isPrincipal ? 'emerald' : 'violet' }}-500' : 'ring-transparent hover:ring-ink/20'">
                                        @if($media['type'] === 'image')
                                            <img src="{{ $media['url'] }}" alt="" class="w-full h-full object-cover"
                                                 :class="active === {{ $mediaIndex }} ? '' : 'opacity-70 hover:opacity-100'">
                                        @else
                                            {{-- Thumbnail vidéo : pas de poster YouTube ici (anti-CORS) ;
                                                 placeholder sombre + icône play, suffisant pour signaler le slide. --}}
                                            <div class="w-full h-full bg-ink flex items-center justify-center"
                                                 :class="active === {{ $mediaIndex }} ? '' : 'opacity-70 hover:opacity-100'">
                                                <i class="fa-solid fa-play text-white text-xs"></i>
                                            </div>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <h3 class="font-display text-lg font-semibold leading-tight">{{ $item->name }}</h3>
                        @if($item->short_description)
                            <p class="mt-1.5 text-sm text-ink-muted leading-relaxed">{{ $item->short_description }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- =============================================================
         DESCRIPTION + VIDÉO
         ============================================================= --}}
    @if($product->description || $videoEmbed)
        <section class="max-w-7xl mx-auto px-5 lg:px-10 mt-20 lg:mt-28">
            <div class="grid {{ $videoEmbed && $product->description ? 'lg:grid-cols-2' : 'lg:grid-cols-1' }} gap-10 lg:gap-14 items-start">

                @if($product->description)
                    <div class="prose prose-ink max-w-none">
                        <p class="text-xs uppercase tracking-widest text-terracotta-500 font-bold not-prose mb-3">À savoir</p>
                        <h2 class="font-display font-semibold text-3xl lg:text-4xl leading-tight tracking-tight not-prose mb-6">
                            Le produit en <span class="italic">détail</span>
                        </h2>
                        <div class="text-ink-muted leading-relaxed whitespace-pre-line">{{ $product->description }}</div>
                    </div>
                @endif

                @if($videoEmbed)
                    <div class="{{ $product->description ? '' : 'max-w-3xl mx-auto w-full' }}">
                        <div class="aspect-video rounded-2xl overflow-hidden bg-ink shadow-lift ring-1 ring-ink/5">
                            <iframe src="{{ $videoEmbed }}"
                                    class="w-full h-full"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- =============================================================
         PREUVES VISUELLES
         ============================================================= --}}
    @if($product->proofs->isNotEmpty())
        <section class="max-w-7xl mx-auto px-5 lg:px-10 mt-20 lg:mt-28">
            <div class="text-center max-w-2xl mx-auto mb-10 lg:mb-14">
                <p class="text-xs uppercase tracking-widest text-terracotta-500 font-bold flex items-center justify-center gap-2">
                    <i class="fa-solid fa-camera-retro"></i>
                    Preuves
                </p>
                <h2 class="mt-3 font-display font-semibold text-3xl lg:text-5xl leading-tight tracking-tight">
                    Des résultats <span class="italic">concrets</span>
                </h2>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 lg:gap-5">
                @foreach($product->proofs as $i => $proof)
                    @php
                        // Variation d'aspect ratio pour casser la monotonie
                        $aspect = match($i % 6) {
                            0, 4 => 'aspect-[4/5]',
                            1, 3 => 'aspect-square',
                            2, 5 => 'aspect-[3/4]',
                            default => 'aspect-square',
                        };
                    @endphp
                    <figure class="group relative {{ $aspect }} rounded-2xl overflow-hidden bg-sand-100 ring-1 ring-ink/5">
                        <img src="{{ $proof->url }}" alt="{{ $proof->alt_text ?: 'Preuve produit' }}"
                             loading="lazy"
                             class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">

                        @if($proof->caption)
                            <figcaption class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-ink/90 via-ink/60 to-transparent">
                                <p class="text-white text-sm leading-snug">{{ $proof->caption }}</p>
                            </figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    {{-- =============================================================
         AVIS CLIENTS
         ============================================================= --}}
    @if($product->reviews->isNotEmpty())
        <section class="bg-ivory-100 mt-20 lg:mt-28 py-20 lg:py-28">
            <div class="max-w-7xl mx-auto px-5 lg:px-10">
                <div class="grid lg:grid-cols-12 gap-10 lg:gap-14 items-start">

                    {{-- Récap note moyenne --}}
                    <div class="lg:col-span-4 lg:sticky lg:top-24">
                        <p class="text-xs uppercase tracking-widest text-terracotta-500 font-bold">Témoignages</p>
                        <h2 class="mt-3 font-display font-semibold text-3xl lg:text-5xl leading-tight tracking-tight">
                            Ils ont <span class="italic">adoré</span>
                        </h2>

                        @if($product->has_rating)
                            <div class="mt-8 p-6 rounded-2xl bg-white ring-1 ring-ink/5 shadow-soft">
                                <div class="flex items-center gap-3">
                                    <p class="font-display text-5xl font-semibold tabular-nums">{{ $product->formatted_rating }}</p>
                                    <div>
                                        <div class="flex items-center gap-0.5 text-amber-500 mb-1">
                                            @php [$full, $half, $empty] = $product->stars_breakdown; @endphp
                                            @for($i = 0; $i < $full; $i++)<i class="fa-solid fa-star text-sm"></i>@endfor
                                            @if($half)<i class="fa-solid fa-star-half-stroke text-sm"></i>@endif
                                            @for($i = 0; $i < $empty; $i++)<i class="fa-solid fa-star text-sm text-ink-muted/20"></i>@endfor
                                        </div>
                                        <p class="text-xs text-ink-muted">Sur {{ $product->rating_count }} avis</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Liste des avis --}}
                    <div class="lg:col-span-8 grid sm:grid-cols-2 gap-4 lg:gap-5">
                        @foreach($product->reviews as $review)
                            <article class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6 flex flex-col">
                                <div class="flex items-center gap-0.5 text-amber-500 mb-3">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa-solid fa-star text-xs {{ $i <= $review->rating ? '' : 'text-ink-muted/20' }}"></i>
                                    @endfor
                                </div>

                                @if($review->title)
                                    <p class="font-display font-semibold text-lg leading-snug mb-2">{{ $review->title }}</p>
                                @endif

                                <p class="text-ink-muted leading-relaxed text-sm flex-1">{{ $review->comment }}</p>

                                <div class="mt-5 pt-4 border-t border-ink/5 flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-full bg-terracotta-50 text-terracotta-500 flex items-center justify-center font-display font-bold text-sm">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($review->author_name, 0, 1)) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-sm truncate">{{ $review->author_name }}</p>
                                        <p class="text-xs text-ink-muted truncate">
                                            @if($review->author_location){{ $review->author_location }}@endif
                                            @if($review->author_location && $review->reviewed_on) · @endif
                                            @if($review->reviewed_on){{ $review->reviewed_on->locale('fr')->isoFormat('MMM YYYY') }}@endif
                                        </p>
                                    </div>
                                    <i class="fa-solid fa-circle-check text-emerald-500 text-sm ml-auto" title="Achat vérifié"></i>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- =============================================================
         FAQ
         ============================================================= --}}
    @if($product->faqs->isNotEmpty())
        <section class="max-w-3xl mx-auto px-5 lg:px-10 mt-20 lg:mt-28">
            <div class="text-center mb-10 lg:mb-14">
                <p class="text-xs uppercase tracking-widest text-terracotta-500 font-bold flex items-center justify-center gap-2">
                    <i class="fa-solid fa-circle-question"></i>
                    Questions fréquentes
                </p>
                <h2 class="mt-3 font-display font-semibold text-3xl lg:text-5xl leading-tight tracking-tight">
                    On répond à <span class="italic">tout</span>
                </h2>
            </div>

            <div class="flex flex-col gap-3" x-data="{ open: null }">
                @foreach($product->faqs as $i => $faq)
                    <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">
                        <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}"
                                class="w-full px-5 lg:px-6 py-4 flex items-center justify-between gap-4 text-left hover:bg-ivory-100/50 transition-colors">
                            <span class="font-display font-semibold text-base lg:text-lg flex-1">{{ $faq->question }}</span>
                            <i class="fa-solid fa-plus text-sm text-ink-muted transition-transform"
                               :class="open === {{ $i }} ? 'rotate-45 text-terracotta-500' : ''"></i>
                        </button>
                        <div x-show="open === {{ $i }}" x-collapse>
                            <div class="px-5 lg:px-6 pb-5 text-ink-muted leading-relaxed whitespace-pre-line">{{ $faq->answer }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- =============================================================
         PRODUITS LIÉS
         ============================================================= --}}
    @if($relatedProducts->isNotEmpty())
        <section class="max-w-7xl mx-auto px-5 lg:px-10 mt-20 lg:mt-28">
            <div class="flex items-end justify-between gap-4 mb-10">
                <div>
                    <p class="text-xs uppercase tracking-widest text-terracotta-500 font-bold">Aussi à découvrir</p>
                    <h2 class="mt-3 font-display font-semibold text-3xl lg:text-4xl leading-tight tracking-tight">
                        Vous aimerez <span class="italic">aussi</span>
                    </h2>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
                @foreach($relatedProducts as $related)
                    <a href="{{ route('products.show', $related) }}" class="group">
                        <div class="aspect-[4/5] rounded-2xl overflow-hidden bg-sand-100 mb-3 relative">
                            @if($related->featured_image_url)
                                <img src="{{ $related->featured_image_url }}" alt="{{ $related->name }}" loading="lazy"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-ink-muted">
                                    <i class="fa-regular fa-image text-3xl"></i>
                                </div>
                            @endif
                        </div>
                        @if($related->category)
                            <p class="text-[10px] uppercase tracking-widest text-ink-muted">{{ $related->category->name }}</p>
                        @endif
                        <h3 class="mt-1 font-display text-base lg:text-lg font-semibold leading-tight line-clamp-2 group-hover:text-terracotta-500 transition-colors">
                            {{ $related->name }}
                        </h3>
                        <p class="mt-1 font-semibold text-sm">{{ $related->formatted_price }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- =============================================================
         STICKY BOTTOM BAR — visible dès qu'on scroll sous le hero CTA
         ============================================================= --}}
    <div x-show="showSticky" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-full opacity-0"
         class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-lg border-t border-ink/10 shadow-[0_-8px_24px_-12px_rgba(0,0,0,0.15)]">
        <div class="max-w-7xl mx-auto px-4 lg:px-10 py-3 flex items-center gap-3">
            {{-- Miniature + nom (caché sur mobile pour économiser l'espace) --}}
            <div class="hidden sm:flex items-center gap-3 flex-shrink-0 min-w-0">
                @if($product->featured_image_url)
                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-sand-100 flex-shrink-0">
                        <img src="{{ $product->featured_image_url }}" alt="" class="w-full h-full object-cover">
                    </div>
                @endif
                <div class="min-w-0 hidden lg:block">
                    <p class="font-semibold text-sm truncate max-w-[14rem]">{{ $product->name }}</p>
                    <p class="text-xs text-ink-muted">{{ $product->formatted_price }}</p>
                </div>
            </div>

            {{-- Prix mobile (visible quand miniature/nom cachés) --}}
            <div class="sm:hidden">
                <p class="font-display text-lg font-semibold leading-none">{{ $product->formatted_price }}</p>
                @if($product->on_sale)
                    <p class="text-[11px] text-ink-muted line-through">{{ number_format($product->compare_price, 0, ',', ' ') }} F</p>
                @endif
            </div>

            <div class="flex-1"></div>

            {{-- WhatsApp --}}
            @if($whatsappLink)
                <a href="{{ $whatsappLink }}" target="_blank" rel="noopener"
                   aria-label="Contacter sur WhatsApp"
                   class="flex-shrink-0 w-12 h-12 rounded-full bg-[#25D366] hover:bg-[#1da851] text-white inline-flex items-center justify-center shadow-soft transition-colors">
                    <i class="fa-brands fa-whatsapp text-xl"></i>
                </a>
            @endif

            {{-- Commander --}}
            <button type="button"
                    @click="buyNow(1)"
                    :disabled="busy"
                    class="flex-1 sm:flex-none h-12 px-5 sm:px-7 rounded-full bg-terracotta-500 hover:bg-terracotta-700 disabled:bg-ink-muted/50 text-white font-semibold text-sm inline-flex items-center justify-center gap-2 shadow-soft transition-colors">
                <i class="fa-solid fa-bolt text-sm" x-show="!busy"></i>
                <i class="fa-solid fa-spinner fa-spin text-sm" x-show="busy" x-cloak></i>
                <span>Commander</span>
            </button>
        </div>
    </div>
</div>

{{-- =============================================================
     LOGIQUE ALPINE — exposée comme un component
     ============================================================= --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productPage', (config) => ({

            _fbq(event, params = {}) {
                if (typeof window.fbq === 'function') {
                    try { window.fbq('track', event, params); } catch (_) {}
                }
            },

            _trackViewContent() {
                this._fbq('ViewContent', {
                    content_ids:  [config.productId],
                    content_name: config.productName,
                    content_type: 'product',
                    content_category: config.productCategory ?? undefined,
                    value:    config.productPrice,
                    currency: config.currency,
                });
            },

            _trackAddToCart(qty) {
                this._fbq('AddToCart', {
                    content_ids:  [config.productId],
                    content_name: config.productName,
                    content_type: 'product',
                    value:    config.productPrice * qty,
                    currency: config.currency,
                });
            },


            _trackInitiateCheckout(qty) {
                this._fbq('InitiateCheckout', {
                    content_ids:    [config.productId],
                    content_name:   config.productName,
                    contents:       [{ id: config.productId, quantity: qty }],
                    num_items:      qty,
                    value:    config.productPrice * qty,
                    currency: config.currency,
                });
            },

            // ─── Compteur d'offre (urgence par visiteur, persisté en localStorage)
            offerCountdown: { days: 0, hours: 0, minutes: 0, seconds: 0 },
            offerVisible:   false,
            offerDiscountPercent: config.offerDiscountPercent,

            // ─── Initialise et gère le compteur d'offre limitée ──────
            // Stratégie : la deadline est lue depuis localStorage. Si absente
            // ou expirée, on en génère une nouvelle (now + offerDurationHours).
            // Conséquence assumée : l'offre redémarre indéfiniment pour chaque
            // visiteur, et un vidage de cache réinitialise complètement le cycle.
            _initOfferCountdown() {
                if (!config.offerDurationHours) return;

                const storageKey = `offer_deadline:${config.productId}`;
                const durationMs = config.offerDurationHours * 3600 * 1000;

                const computeDeadline = () => {
                    const stored = parseInt(localStorage.getItem(storageKey) || '0', 10);
                    // Deadline expirée OU jamais posée → on en (re)pose une.
                    if (!stored || stored <= Date.now()) {
                        const fresh = Date.now() + durationMs;
                        try { localStorage.setItem(storageKey, String(fresh)); }
                        catch (_) { /* localStorage indisponible (mode privé Safari) — on continue sans persistance */ }
                        return fresh;
                    }
                    return stored;
                };

                let deadline = computeDeadline();
                this.offerVisible = true;

                const tick = () => {
                    let diff = deadline - Date.now();

                    // Compteur à zéro : on régénère et on continue sans rien casser.
                    if (diff <= 0) {
                        deadline = computeDeadline();
                        diff = deadline - Date.now();
                    }

                    this.offerCountdown = {
                        days:    Math.floor(diff / 86400000),
                        hours:   Math.floor((diff % 86400000) / 3600000),
                        minutes: Math.floor((diff % 3600000) / 60000),
                        seconds: Math.floor((diff % 60000) / 1000),
                    };
                };

                tick();
                setInterval(tick, 1000);
            },


            // ─── Compte à rebours ──────────────────────────────────
            countdown: { days: 0, hours: 0, minutes: 0, seconds: 0 },

            // ─── Sticky bar ────────────────────────────────────────
            showSticky: false,

            // ─── État d'envoi ──────────────────────────────────────
            busy: false,

            init() {
                // Compte à rebours
                if (config.deliveryAt) {
                    const target = new Date(config.deliveryAt).getTime();
                    const tick = () => {
                        const diff = Math.max(0, target - Date.now());
                        this.countdown = {
                            days:    Math.floor(diff / 86400000),
                            hours:   Math.floor((diff % 86400000) / 3600000),
                            minutes: Math.floor((diff % 3600000) / 60000),
                            seconds: Math.floor((diff % 60000) / 1000),
                        };
                    };
                    tick();
                    setInterval(tick, 1000);
                }

                // Sticky bar : visible dès que l'utilisateur descend
                // sous l'écran initial (~hauteur de fenêtre).
                const onScroll = () => {
                    this.showSticky = window.scrollY > window.innerHeight * 0.6;
                };
                window.addEventListener('scroll', onScroll, { passive: true });
                onScroll();

                this._trackViewContent();
                this._initOfferCountdown();   // ← ajouter cette ligne
            },

            // ─── Helper interne : POST vers cart.store + maj DOM ───
            async _postToCart(qty) {
                const res = await fetch(config.cartStoreUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': config.csrf,
                    },
                    body: JSON.stringify({
                        product_id: config.productId,
                        quantity: qty,
                    }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || "Erreur lors de l'ajout au panier");
                }

                // MAJ compteur et montant du header — même mécanique
                // que window.SD.addToCart() pour rester cohérent avec
                // les éléments DOM exposés par le layout.
                const label = document.getElementById('cart-summary-label');
                const badge = document.getElementById('cart-badge');
                if (label) label.textContent = data.formatted_subtotal;
                if (badge) {
                    badge.textContent = data.items_count;
                    badge.classList.toggle('hidden', !data.items_count);
                    badge.classList.toggle('flex',   !!data.items_count);
                }

                return data;
            },

            async addToCart(qty) {
                if (this.busy) return;
                this.busy = true;
                try {
                    const data = await this._postToCart(qty);
                    this._trackAddToCart(qty);
                    // Préfère le toast global SD s'il existe (cohérence visuelle
                    // avec le reste de l'app) ; sinon fallback maison.
                    if (window.SD?.toast) {
                        window.SD.toast(data.message || 'Produit ajouté au panier', 'success');
                    } else {
                        this.flash('Ajouté au panier ✓');
                    }
                } catch (e) {
                    if (window.SD?.toast) {
                        window.SD.toast(e.message, 'error');
                    } else {
                        this.flash(e.message, true);
                    }
                } finally {
                    this.busy = false;
                }
            },

            async buyNow(qty) {
                if (this.busy) return;
                this.busy = true;
                try {
                    await this._postToCart(qty);
                    this._trackAddToCart(qty);
                    this._trackInitiateCheckout(qty);
                    window.location.href = config.checkoutUrl;
                } catch (e) {
                    this.busy = false;
                    if (window.SD?.toast) {
                        window.SD.toast(e.message, 'error');
                    } else {
                        this.flash(e.message, true);
                    }
                }
            },

            flash(message, isError = false) {
                const el = document.createElement('div');
                el.className = 'fixed top-6 left-1/2 -translate-x-1/2 z-[100] px-5 py-3 rounded-full text-sm font-semibold shadow-lift '
                    + (isError ? 'bg-red-500 text-white' : 'bg-emerald-500 text-white');
                el.textContent = message;
                document.body.appendChild(el);
                setTimeout(() => el.remove(), 2200);
            },
        }));
    });
</script>

@endsection
