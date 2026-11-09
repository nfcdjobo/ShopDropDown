@extends('layouts.app')

@if($isFiltered)
    @section('title', $q !== '' ? 'Recherche : ' . $q : ($activeCategory?->name ?? $selectionLabel))
    {{-- Pages de résultats : utiles aux visiteurs, pas à Google (évite le contenu dupliqué) --}}
    @section('robots', 'noindex, follow')
@else
    @section('title', 'Boutique en ligne — Livraison rapide en Côte d\'Ivoire')
@endif
@section('meta_description', 'Découvrez notre sélection de produits soigneusement choisis. Paiement à la livraison, livraison rapide à Abidjan et partout en Côte d\'Ivoire.')
@section('meta_keywords', 'boutique en ligne Abidjan, e-commerce Côte d\'Ivoire, livraison rapide, paiement à la livraison')

@section('content')

@php
    // Classes des pastilles de catégories
    $chipBase   = 'shrink-0 inline-flex items-center h-9 px-4 rounded-full text-sm font-medium transition-colors whitespace-nowrap';
    $chipActive = 'bg-ink text-ivory-50';
    $chipIdle   = 'bg-ivory-100 hover:bg-ivory-200';

    $whatsappLink = isset($banniere) ? ($banniere->lien_whatsapp ?? null) : null;
@endphp

{{-- =============== RECHERCHE + CATÉGORIES =============== --}}
<section class="bg-white border-b border-ink/5">
    <div class="max-w-7xl mx-auto px-4 lg:px-10 py-4 lg:py-6">

        <form method="GET" action="{{ route('home') }}" role="search"
              class="flex items-stretch rounded-full ring-2 ring-ink focus-within:ring-terracotta-500 overflow-hidden bg-white transition">
            @if($activeCategory)
                <input type="hidden" name="categorie" value="{{ $activeCategory->id }}">
            @endif
            <label for="q" class="sr-only">Rechercher un produit</label>
            <i class="fa-solid fa-magnifying-glass self-center ml-4 text-ink-muted" aria-hidden="true"></i>
            <input id="q" type="search" name="q" value="{{ $q }}" maxlength="100"
                   enterkeyhint="search" autocomplete="off"
                   placeholder="Rechercher un produit…"
                   class="flex-1 min-w-0 px-3 h-12 lg:h-14 bg-transparent outline-none text-sm lg:text-base">
            <button type="submit"
                    class="px-5 lg:px-9 bg-ink text-ivory-50 text-sm font-semibold hover:bg-terracotta-500 transition-colors">
                <span class="hidden sm:inline">Rechercher</span>
                <i class="fa-solid fa-magnifying-glass sm:hidden" aria-hidden="true"></i>
            </button>
        </form>

        @if($categories->isNotEmpty())
            <nav aria-label="Catégories"
                 class="mt-3 flex gap-2 overflow-x-auto pb-1 -mx-4 px-4 lg:mx-0 lg:px-0 [scrollbar-width:none]">
                <a href="{{ route('home') }}"
                   class="{{ $chipBase }} {{ !$isFiltered ? $chipActive : $chipIdle }}">Tout</a>
                @foreach($categories as $cat)
                    <a href="{{ route('home', ['categorie' => $cat->id]) }}"
                       class="{{ $chipBase }} {{ $activeCategory?->id === $cat->id ? $chipActive : $chipIdle }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </nav>
        @endif
    </div>
</section>

@unless($isFiltered)

{{-- =============== HERO : catégories | carrousel | avantages =============== --}}
<section class="bg-ivory-100">
    <h1 class="sr-only">Boutique en ligne — livraison rapide en Côte d'Ivoire</h1>

    <div class="max-w-7xl mx-auto px-4 lg:px-10 py-4 lg:py-6 grid lg:grid-cols-12 gap-4 lg:gap-5">

        {{-- Colonne catégories (grand écran) --}}
        @if($categories->isNotEmpty())
            <aside class="hidden lg:block lg:col-span-3 bg-white rounded-3xl ring-1 ring-ink/5 p-3 h-[26rem] overflow-y-auto">
                <p class="px-3 pt-2 pb-3 text-xs uppercase tracking-widest text-ink-muted font-bold">Catégories</p>
                <ul>
                    @foreach($categories as $cat)
                        <li>
                            <a href="{{ route('home', ['categorie' => $cat->id]) }}"
                               class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium hover:bg-ivory-100 hover:text-terracotta-500 transition-colors">
                                <span class="truncate">{{ $cat->name }}</span>
                                <i class="fa-solid fa-chevron-right text-[10px] text-ink-muted" aria-hidden="true"></i>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </aside>
        @endif

        {{-- Carrousel --}}
        <div class="{{ $categories->isNotEmpty() ? 'lg:col-span-6' : 'lg:col-span-9' }}">
            @if($heroProducts->isNotEmpty())
                <div x-data="heroSlider({{ $heroProducts->count() }})"
                     @mouseenter="stop()" @mouseleave="play()"
                     @touchstart.passive="swipeStart($event)" @touchend.passive="swipeEnd($event)"
                     role="region" aria-roledescription="carrousel" aria-label="Produits à la une"
                     class="relative h-56 sm:h-72 lg:h-[26rem] rounded-3xl overflow-hidden bg-gradient-to-br from-terracotta-50 via-ivory-50 to-sand-100 ring-1 ring-ink/5">

                    @foreach($heroProducts as $idx => $hp)
                        <a href="{{ route('products.show', $hp) }}"
                           @if(!$loop->first) x-cloak @endif
                           x-show="i === {{ $idx }}"
                           x-transition.opacity.duration.500ms
                           :aria-hidden="i !== {{ $idx }} ? 'true' : 'false'"
                           :tabindex="i === {{ $idx }} ? 0 : -1"
                           class="absolute inset-0 grid grid-cols-5">

                            <div class="col-span-3 p-5 sm:p-8 lg:p-12 flex flex-col justify-center">
                                <span class="self-start inline-flex items-center gap-1.5 rounded-full bg-white/80 backdrop-blur px-3 py-1 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-terracotta-600 ring-1 ring-ink/5">
                                    @if($hp->on_sale)
                                        <i class="fa-solid fa-tag"></i> Promo -{{ $hp->discount_percent }}%
                                    @elseif($hp->is_featured)
                                        <i class="fa-solid fa-heart"></i> Coup de cœur
                                    @else
                                        <i class="fa-solid fa-wand-magic-sparkles"></i> Nouveau
                                    @endif
                                </span>
                                <h2 class="mt-3 font-display font-semibold tracking-tight leading-[1.05] text-xl sm:text-3xl lg:text-4xl line-clamp-3">
                                    {{ $hp->name }}
                                </h2>
                                <p class="mt-2 sm:mt-4 font-bold text-lg sm:text-2xl text-terracotta-600">
                                    {{ $hp->formatted_price }}
                                    @if($hp->on_sale)
                                        <span class="ml-2 text-xs sm:text-sm font-normal text-ink-muted line-through">{{ number_format($hp->compare_price, 0, ',', ' ') }} FCFA</span>
                                    @endif
                                </p>
                                <span class="mt-3 sm:mt-6 btn-primary self-start gap-2 px-5 sm:px-7 h-10 sm:h-12 rounded-full text-xs sm:text-sm font-semibold">
                                    Voir l'offre <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                </span>
                            </div>

                            <div class="col-span-2 relative">
                                @if($hp->featured_image_url)
                                    <img src="{{ $hp->featured_image_url }}" alt="{{ $hp->name }}"
                                         @if($loop->first) fetchpriority="high" @else loading="lazy" @endif
                                         class="absolute inset-0 w-full h-full object-cover">
                                @endif
                            </div>
                        </a>
                    @endforeach

                    @if($heroProducts->count() > 1)
                        <button type="button" @click="prev(); play()" aria-label="Produit précédent"
                                class="hidden sm:flex absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 shadow-soft items-center justify-center hover:bg-white transition">
                            <i class="fa-solid fa-chevron-left text-sm" aria-hidden="true"></i>
                        </button>
                        <button type="button" @click="next(); play()" aria-label="Produit suivant"
                                class="hidden sm:flex absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 shadow-soft items-center justify-center hover:bg-white transition">
                            <i class="fa-solid fa-chevron-right text-sm" aria-hidden="true"></i>
                        </button>

                        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5">
                            @foreach($heroProducts as $idx => $hp)
                                <button type="button" @click="i = {{ $idx }}; play()"
                                        :aria-current="i === {{ $idx }} ? 'true' : 'false'"
                                        aria-label="Aller au produit {{ $idx + 1 }}"
                                        class="h-1.5 rounded-full transition-all duration-300"
                                        :class="i === {{ $idx }} ? 'w-6 bg-ink' : 'w-1.5 bg-ink/30'"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="h-56 sm:h-72 lg:h-[26rem] rounded-3xl bg-gradient-to-br from-terracotta-50 via-ivory-50 to-sand-100 ring-1 ring-ink/5 flex flex-col items-center justify-center text-center p-8">
                    <p class="font-display text-3xl lg:text-5xl font-semibold tracking-tight">Le shopping, <span class="italic text-terracotta-500">sans friction.</span></p>
                    <p class="mt-3 text-ink-muted">Nos premiers produits arrivent très vite.</p>
                </div>
            @endif
        </div>

        {{-- Colonne avantages (grand écran) --}}
        <aside class="hidden lg:flex lg:col-span-3 flex-col gap-4 h-[26rem]">
            <div class="flex-1 bg-white rounded-3xl ring-1 ring-ink/5 p-5">
                <p class="text-xs uppercase tracking-widest text-ink-muted font-bold">Acheter en confiance</p>
                <ul class="mt-4 space-y-4 text-sm">
                    <li class="flex gap-3">
                        <i class="fa-solid fa-hand-holding-dollar text-terracotta-500 mt-0.5" aria-hidden="true"></i>
                        <span><strong class="block">Paiement à la livraison</strong><span class="text-ink-muted">Vous payez à la réception.</span></span>
                    </li>
                    <li class="flex gap-3">
                        <i class="fa-solid fa-truck-fast text-terracotta-500 mt-0.5" aria-hidden="true"></i>
                        <span><strong class="block">Livraison rapide</strong><span class="text-ink-muted">À Abidjan et partout en Côte d'Ivoire.</span></span>
                    </li>
                    <li class="flex gap-3">
                        <i class="fa-solid fa-bolt text-terracotta-500 mt-0.5" aria-hidden="true"></i>
                        <span><strong class="block">Commande express</strong><span class="text-ink-muted">Sans création de compte.</span></span>
                    </li>
                </ul>
            </div>

            @if($whatsappLink)
                <a href="{{ $whatsappLink }}" target="_blank" rel="noopener"
                   class="bg-[#25D366] text-white rounded-3xl p-5 flex items-center gap-4 hover:brightness-95 transition">
                    <i class="fa-brands fa-whatsapp text-4xl" aria-hidden="true"></i>
                    <span>
                        <strong class="block leading-tight">Une question ?</strong>
                        <span class="text-sm text-white/90">Écrivez-nous sur WhatsApp</span>
                    </span>
                </a>
            @endif
        </aside>
    </div>
</section>

{{-- =============== BANDEAU VALEURS (mobile + tablette) =============== --}}
<section class="bg-ink text-ivory-50 lg:hidden">
    <div class="px-4 py-5 grid grid-cols-2 gap-4">
        @foreach([
            ['fa-truck-fast',        'Livraison rapide'],
            ['fa-hand-holding-dollar','Paiement à la livraison'],
            ['fa-arrow-rotate-left', 'Retour sous 7 jours'],
            ['fa-bolt',              'Sans compte requis'],
        ] as [$icon, $label])
            <p class="flex items-center gap-2 text-xs font-semibold">
                <i class="fa-solid {{ $icon }} text-terracotta-400" aria-hidden="true"></i> {{ $label }}
            </p>
        @endforeach
    </div>
</section>

{{-- =============== BANDES DE PRODUITS =============== --}}
@foreach([
    ['promos',     'Offres du moment', 'fa-tag',      $deals],
    ['nouveautes', 'Nouveautés',       'fa-wand-magic-sparkles', $newArrivals],
    ['favoris',    'Coups de cœur',    'fa-heart',    $favorites],
] as [$key, $title, $icon, $items])
    @if($items->isNotEmpty())
        <section class="pt-8 lg:pt-12">
            <div class="max-w-7xl mx-auto px-4 lg:px-10">
                <div class="flex items-end justify-between gap-4 mb-4 lg:mb-6">
                    <h2 class="font-display font-semibold text-2xl lg:text-4xl tracking-tight flex items-center gap-2.5">
                        <i class="fa-solid {{ $icon }} text-terracotta-500 text-lg lg:text-2xl" aria-hidden="true"></i>
                        {{ $title }}
                    </h2>
                    <a href="{{ route('home', ['selection' => $key]) }}"
                       class="shrink-0 text-sm font-semibold hover:text-terracotta-500 transition-colors inline-flex items-center gap-1.5">
                        Voir tout <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
                    </a>
                </div>

                <div class="flex gap-3 lg:gap-4 overflow-x-auto snap-x snap-mandatory pb-3 -mx-4 px-4 lg:mx-0 lg:px-0
                            [scrollbar-width:thin]
                            [&>article]:w-40 sm:[&>article]:w-44 lg:[&>article]:w-52 [&>article]:shrink-0 [&>article]:snap-start">
                    @include('landing.partials.product-cards', ['products' => $items, 'animate' => false])
                </div>
            </div>
        </section>
    @endif
@endforeach

@endunless

{{-- =============== CATALOGUE / RÉSULTATS =============== --}}
<section id="featured" class="py-10 lg:py-14">
    <div class="max-w-7xl mx-auto px-4 lg:px-10">

        <div class="flex flex-wrap items-end justify-between gap-x-6 gap-y-3 mb-5 lg:mb-8">
            <div>
                @if($isFiltered)
                    <h1 class="font-display font-semibold text-3xl lg:text-5xl tracking-tight leading-[1]">
                        @if($q !== '')
                            Résultats pour <span class="italic">« {{ $q }} »</span>
                        @else
                            {{ $activeCategory?->name ?? $selectionLabel }}
                        @endif
                    </h1>
                @else
                    <h2 class="font-display font-semibold text-2xl lg:text-4xl tracking-tight">Tous nos produits</h2>
                @endif
                <p class="mt-1.5 text-sm text-ink-muted">
                    {{ $products->total() }} {{ Str::plural('produit', $products->total()) }}
                </p>
            </div>

            {{-- Filtres actifs, retirables un par un --}}
            @if($isFiltered)
                @php
                    $keep = fn (array $drop) => array_filter([
                        'q'         => in_array('q', $drop) ? null : $q,
                        'categorie' => in_array('categorie', $drop) ? null : $activeCategory?->id,
                        'selection' => in_array('selection', $drop) ? null : $selection,
                    ]);
                @endphp
                <div class="flex flex-wrap items-center gap-2">
                    @if($q !== '')
                        <a href="{{ route('home', $keep(['q'])) }}" class="inline-flex items-center gap-2 h-8 px-3 rounded-full bg-ink text-ivory-50 text-xs font-medium hover:bg-terracotta-500 transition-colors">
                            « {{ Str::limit($q, 24) }} » <i class="fa-solid fa-xmark" aria-label="Retirer"></i>
                        </a>
                    @endif
                    @if($activeCategory)
                        <a href="{{ route('home', $keep(['categorie'])) }}" class="inline-flex items-center gap-2 h-8 px-3 rounded-full bg-ink text-ivory-50 text-xs font-medium hover:bg-terracotta-500 transition-colors">
                            {{ $activeCategory->name }} <i class="fa-solid fa-xmark" aria-label="Retirer"></i>
                        </a>
                    @endif
                    @if($selection)
                        <a href="{{ route('home', $keep(['selection'])) }}" class="inline-flex items-center gap-2 h-8 px-3 rounded-full bg-ink text-ivory-50 text-xs font-medium hover:bg-terracotta-500 transition-colors">
                            {{ $selectionLabel }} <i class="fa-solid fa-xmark" aria-label="Retirer"></i>
                        </a>
                    @endif
                    <a href="{{ route('home') }}" class="text-xs font-semibold underline underline-offset-4 hover:text-terracotta-500">Tout effacer</a>
                </div>
            @endif
        </div>

        @if($products->isEmpty())
            <div class="text-center py-16 bg-white rounded-3xl ring-1 ring-ink/5">
                <i class="fa-regular fa-face-smile text-4xl text-ink-muted" aria-hidden="true"></i>
                @if($isFiltered)
                    <p class="font-display text-2xl mt-4">Aucun produit trouvé</p>
                    <p class="text-ink-muted mt-2 max-w-md mx-auto">Vérifiez l'orthographe ou essayez un mot plus court, ou parcourez une catégorie.</p>
                    <div class="mt-6 flex flex-wrap justify-center gap-2 px-4">
                        @foreach($categories->take(8) as $cat)
                            <a href="{{ route('home', ['categorie' => $cat->id]) }}" class="{{ $chipBase }} {{ $chipIdle }}">{{ $cat->name }}</a>
                        @endforeach
                    </div>
                    <a href="{{ route('home') }}" class="btn-primary mt-6 gap-2 px-6 h-11 rounded-full text-sm font-semibold">Voir tous les produits</a>
                @else
                    <p class="font-display text-2xl mt-4">Bientôt dans la boutique</p>
                    <p class="text-ink-muted mt-2">Nos produits arrivent très vite.</p>
                @endif
            </div>
        @else
            <div id="products-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 lg:gap-5">
                @include('landing.partials.product-cards', ['products' => $products, 'animate' => true])
            </div>

            @if($products->hasMorePages())
                <div id="load-more-wrap" class="mt-10 lg:mt-14 text-center">
                    <p id="load-more-status" class="text-sm text-ink-muted mb-4">
                        {{ $products->count() }} sur {{ $products->total() }} produits
                    </p>
                    {{-- Fonctionne aussi sans JavaScript (lien vers la page suivante, indexable) --}}
                    <a id="load-more" href="{{ $products->nextPageUrl() }}#featured"
                       data-next="{{ $products->nextPageUrl() }}"
                       class="btn-ghost gap-2 px-7 h-12 rounded-full font-semibold text-sm">
                        Voir plus de produits
                        <i class="fa-solid fa-chevron-down text-xs" aria-hidden="true"></i>
                    </a>
                </div>
            @endif
        @endif
    </div>
</section>

{{-- =============== À PROPOS / RASSURANCE =============== --}}
<section id="about" class="bg-ivory-100 py-20 lg:py-28">
    <div class="max-w-5xl mx-auto px-5 lg:px-10 text-center">
        <p class="text-xs uppercase tracking-widest text-terracotta-500 font-bold reveal flex items-center justify-center gap-2">
            <i class="fa-solid fa-heart"></i>
            Notre promesse
        </p>
        <h2 class="mt-4 font-display font-semibold text-4xl lg:text-6xl leading-[0.95] tracking-tight reveal" style="transition-delay:.05s">
            Une boutique conçue <br class="hidden sm:block">
            <span class="italic">pour vous faire gagner du temps.</span>
        </h2>
        <p class="mt-6 text-ink-muted text-lg max-w-2xl mx-auto leading-relaxed reveal" style="transition-delay:.1s">
            Chez Shop Drop, vous n'avez qu'à choisir. Pas de création de compte avant d'acheter,
            pas de paiement anticipé, pas de surprise. Nous préparons, nous livrons, vous payez sur place.
        </p>
        <div class="mt-10 reveal" style="transition-delay:.15s">
            <a href="#featured" class="btn-primary gap-2 px-7 h-14 rounded-full font-semibold text-sm">
                Commencer à shopper
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    // Carrousel du hero : défilement auto (désactivé si « réduire les animations »), flèches, pastilles, glissement tactile
    function heroSlider(total) {
        return {
            i: 0,
            total: total,
            timer: null,
            startX: 0,
            init: function () { this.play(); },
            play: function () {
                if (this.total < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                this.stop();
                var self = this;
                this.timer = setInterval(function () { self.next(); }, 5000);
            },
            stop: function () { clearInterval(this.timer); },
            next: function () { this.i = (this.i + 1) % this.total; },
            prev: function () { this.i = (this.i - 1 + this.total) % this.total; },
            swipeStart: function (e) { this.startX = e.changedTouches[0].clientX; },
            swipeEnd: function (e) {
                var d = e.changedTouches[0].clientX - this.startX;
                if (Math.abs(d) > 40) { d < 0 ? this.next() : this.prev(); this.play(); }
            },
        };
    }
</script>
<script>
    (function () {
        var btn = document.getElementById('load-more');
        if (!btn) return;

        var grid   = document.getElementById('products-grid');
        var status = document.getElementById('load-more-status');
        var wrap   = document.getElementById('load-more-wrap');
        var original = btn.innerHTML;

        btn.addEventListener('click', async function (e) {
            e.preventDefault();
            var next = btn.dataset.next;
            if (!next) return;

            btn.setAttribute('aria-busy', 'true');
            btn.style.pointerEvents = 'none';
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Chargement…';

            try {
                var res = await fetch(next, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok) throw new Error('Impossible de charger les produits.');
                var data = await res.json();

                grid.insertAdjacentHTML('beforeend', data.html);
                status.textContent = grid.children.length + ' sur ' + data.total + ' produits';

                if (data.next) {
                    btn.dataset.next = data.next;
                    btn.href = data.next + '#featured';
                    btn.innerHTML = original;
                } else {
                    wrap.remove();            // plus rien à charger
                    return;
                }
            } catch (err) {
                btn.innerHTML = original;
                if (window.SD && SD.toast) SD.toast(err.message || 'Erreur réseau', 'error');
            } finally {
                btn.removeAttribute('aria-busy');
                btn.style.pointerEvents = '';
            }
        });
    })();
</script>
@endpush
