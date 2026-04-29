@extends('layouts.app')


@section('title', 'Boutique en ligne — Livraison rapide en Côte d\'Ivoire')
@section('meta_description', 'Découvrez notre sélection de produits soigneusement choisis. Paiement à la livraison, livraison rapide à Abidjan et partout en Côte d\'Ivoire.')
@section('meta_keywords', 'boutique en ligne Abidjan, e-commerce Côte d\'Ivoire, livraison rapide, paiement à la livraison')

@section('content')

{{-- =============== HERO =============== --}}
<section class="relative overflow-hidden bg-ivory-100">
    {{-- décor --}}
    <div class="absolute -top-24 -right-24 w-[32rem] h-[32rem] rounded-full bg-terracotta-50 blur-3xl opacity-70 pointer-events-none"></div>
    <div class="absolute -bottom-32 -left-32 w-[28rem] h-[28rem] rounded-full bg-sand-200 blur-3xl opacity-60 pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-5 lg:px-10 pt-12 lg:pt-20 pb-20 lg:pb-28 grid lg:grid-cols-12 gap-10 lg:gap-16 items-center">


        {{-- Texte --}}
        <div class="lg:col-span-6 order-2 lg:order-1">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/70 backdrop-blur px-4 py-1.5 text-xs font-semibold text-ink-muted ring-1 ring-ink/5 reveal">
                <span class="w-1.5 h-1.5 rounded-full bg-terracotta-500 animate-pulse"></span>
                Nouveau sur Shop Dropdown
            </div>

            <h1 class="mt-6 font-display font-semibold tracking-tight text-5xl sm:text-6xl lg:text-7xl leading-[0.95] reveal" style="transition-delay:.05s">
                Le shopping,
                <span class="italic text-terracotta-500">sans friction.</span>
            </h1>

            <p class="mt-6 text-lg text-ink-muted max-w-lg leading-relaxed reveal" style="transition-delay:.1s">
                Des produits soigneusement choisis, livrés rapidement chez vous.
                Commandez en moins d'une minute — pas de compte requis, paiement à la livraison.
            </p>

            <div class="mt-10 flex flex-col sm:flex-row gap-3 reveal" style="transition-delay:.15s">
                @if($heroProduct)
                    <a href="{{ route('products.show', $heroProduct) }}"
                       class="btn-primary gap-2 px-7 h-14 rounded-full font-semibold text-sm">
                        Découvrir le produit
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                @endif
                <a href="#featured" class="btn-ghost gap-2 px-7 h-14 rounded-full font-semibold text-sm">
                    Voir tous les produits
                </a>
            </div>

            {{-- Trust points --}}
            <div class="mt-12 grid grid-cols-3 gap-6 max-w-lg reveal" style="transition-delay:.2s">
                <div>
                    <p class="font-display text-3xl font-semibold">24h</p>
                    <p class="text-xs text-ink-muted mt-1">Livraison express à Abidjan</p>
                </div>
                <div class="border-l border-ink/10 pl-6">
                    <p class="font-display text-3xl font-semibold">0%</p>
                    <p class="text-xs text-ink-muted mt-1">Frais avant la livraison</p>
                </div>
                <div class="border-l border-ink/10 pl-6">
                    <p class="font-display text-3xl font-semibold">100%</p>
                    <p class="text-xs text-ink-muted mt-1">Satisfait ou remboursé</p>
                </div>
            </div>
        </div>

        {{-- Visuel --}}
        @if($heroProduct)
            <div class="lg:col-span-6 order-1 lg:order-2 reveal">
                <div class="relative">
                    <div class="absolute inset-4 bg-gradient-to-br from-terracotta-400/20 to-sand-200/50 rounded-[3rem] blur-2xl"></div>

                    <a href="{{ route('products.show', $heroProduct) }}" class="relative block group">
                        <div class="aspect-[4/5] rounded-[2.5rem] overflow-hidden bg-sand-100 shadow-lift">
                            @if($heroProduct->featured_image_url)
                                <img src="{{ $heroProduct->featured_image_url }}"
                                     alt="{{ $heroProduct->name }}"
                                     class="w-full h-full object-cover group-hover:scale-[1.04] transition-transform duration-[800ms] ease-out">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-ink-muted text-sm">
                                    <i class="fa-regular fa-image text-4xl"></i>
                                </div>
                            @endif
                        </div>

                        {{-- Card flottante --}}
                        <div class="absolute -bottom-6 left-6 right-6 sm:left-auto sm:right-6 sm:max-w-xs bg-white rounded-2xl shadow-lift p-5 ring-1 ring-ink/5">
                            <p class="text-[10px] uppercase tracking-widest text-terracotta-500 font-bold flex items-center gap-1.5">
                                <i class="fa-solid fa-star text-[10px]"></i>
                                Produit en vedette
                            </p>
                            <h3 class="mt-1 font-display text-xl font-semibold leading-tight line-clamp-2">{{ $heroProduct->name }}</h3>
                            <div class="mt-3 flex items-end justify-between">
                                <div>
                                    <p class="font-display text-2xl font-semibold">{{ $heroProduct->formatted_price }}</p>
                                    @if($heroProduct->on_sale)
                                        <p class="text-xs text-ink-muted line-through">{{ number_format($heroProduct->compare_price, 0, ',', ' ') }} FCFA</p>
                                    @endif
                                </div>
                                <span class="w-10 h-10 rounded-full bg-ink text-ivory-50 flex items-center justify-center group-hover:bg-terracotta-500 transition-colors">
                                    <i class="fa-solid fa-arrow-right text-sm"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        @endif
    </div>
</section>

{{-- =============== BANDEAU VALEURS =============== --}}
<section class="bg-ink text-ivory-50">
    <div class="max-w-7xl mx-auto px-5 lg:px-10 py-8 lg:py-10 grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-10">
        @foreach([
            ['fa-truck',            'Livraison rapide',   'Chez vous en 24h à Abidjan'],
            ['fa-credit-card',      'Paiement sécurisé',  'Cash à la livraison'],
            ['fa-arrow-rotate-left','Retour facile',      '7 jours pour changer d\'avis'],
            ['fa-bolt',             'Commande express',   'Sans inscription obligatoire'],
        ] as $i => [$icon, $title, $desc])
            <div class="flex items-start gap-3 reveal" style="transition-delay: {{ $i * .05 }}s">
                <span class="flex-shrink-0 w-10 h-10 rounded-full bg-ivory-50/10 flex items-center justify-center">
                    <i class="fa-solid {{ $icon }} text-base"></i>
                </span>
                <div>
                    <p class="font-semibold text-sm">{{ $title }}</p>
                    <p class="text-xs text-ivory-50/60 mt-0.5">{{ $desc }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>

{{-- =============== PRODUITS EN VEDETTE =============== --}}
<section id="featured" class="py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-5 lg:px-10">

        <div class="flex items-end justify-between gap-6 mb-12 lg:mb-16">
            <div>
                <p class="text-xs uppercase tracking-widest text-terracotta-500 font-bold reveal">La sélection</p>
                <h2 class="mt-3 font-display font-semibold text-4xl lg:text-6xl leading-[0.95] tracking-tight reveal" style="transition-delay:.05s">
                    Nos <span class="italic">coups de cœur</span>
                </h2>
            </div>
            <a href="#" class="hidden sm:inline-flex items-center gap-2 text-sm font-semibold hover:text-terracotta-500 transition-colors reveal">
                Voir tout
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        @if($featuredProducts->isEmpty())
            <div class="text-center py-20 bg-ivory-100 rounded-3xl">
                <i class="fa-regular fa-face-smile text-4xl text-ink-muted"></i>
                <p class="font-display text-2xl mt-4">Bientôt dans la sélection</p>
                <p class="text-ink-muted mt-2">Nos coups de cœur arrivent très vite.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-8">
                @foreach($featuredProducts as $i => $product)
                    <article class="group reveal" style="transition-delay: {{ $i * .05 }}s">
                        <a href="{{ route('products.show', $product) }}" class="block">
                            <div class="relative aspect-[4/5] rounded-2xl overflow-hidden bg-sand-100 mb-4">
                                @if($product->featured_image_url)
                                    <img src="{{ $product->featured_image_url }}"
                                         alt="{{ $product->name }}"
                                         loading="lazy"
                                         class="w-full h-full object-cover group-hover:scale-[1.05] transition-transform duration-700 ease-out">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-ink-muted">
                                        <i class="fa-regular fa-image text-4xl"></i>
                                    </div>
                                @endif

                                @if($product->on_sale)
                                    <span class="absolute top-3 left-3 bg-terracotta-500 text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full flex items-center gap-1">
                                        <i class="fa-solid fa-tag text-[9px]"></i>
                                        -{{ $product->discount_percent }}%
                                    </span>
                                @endif
                                @if($product->is_low_stock)
                                    <span class="absolute top-3 right-3 bg-white/90 backdrop-blur text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full flex items-center gap-1">
                                        <i class="fa-solid fa-fire text-[9px] text-terracotta-500"></i>
                                        Plus que {{ $product->stock }}
                                    </span>
                                @endif

                                {{-- Action rapide "ajouter" --}}
                                <button type="button"
                                        onclick="event.preventDefault(); event.stopPropagation(); SD.addToCart('{{ $product->id }}', 1, this);"
                                        class="absolute bottom-3 right-3 w-11 h-11 rounded-full bg-ink text-ivory-50 shadow-lg flex items-center justify-center opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-300 hover:bg-terracotta-500"
                                        aria-label="Ajouter au panier">
                                    <i class="fa-solid fa-plus text-base"></i>
                                </button>
                            </div>
                            
                            <div class="px-1">
                                @if($product->category)
                                    <p class="text-[10px] uppercase tracking-widest text-ink-muted">{{ $product->category->name }}</p>
                                @endif
                                <h3 class="mt-1.5 font-display text-lg font-semibold leading-tight line-clamp-2 group-hover:text-terracotta-500 transition-colors">
                                    {{ $product->name }}
                                </h3>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <p class="font-semibold">{{ $product->formatted_price }}</p>
                                    @if($product->on_sale)
                                        <p class="text-sm text-ink-muted line-through">{{ number_format($product->compare_price, 0, ',', ' ') }} FCFA</p>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
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
            Chez Shop Dropdown, vous n'avez qu'à choisir. Pas de création de compte avant d'acheter,
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
