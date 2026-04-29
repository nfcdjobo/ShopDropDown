@extends('layouts.app')

@section('title', 'Mon panier')
@section('meta_description', 'Vérifiez votre panier avant de commander.')

@section('content')

    {{-- ================================================================
     URLs injectées par Blade (server-side) — aucune dépendance JS
     ================================================================ --}}
    @php
        $clearUrl = route('cart.clear');
        $checkoutUrl = route('checkout.index');
    @endphp

    <div class="max-w-7xl mx-auto px-5 lg:px-10 py-8 lg:py-14" x-data="cartPage({
        isEmpty: {{ $cart->is_empty ? 'true' : 'false' }},
        itemCount: {{ $cart->items_count }},
        subtotal: {{ Js::from($cart->formatted_subtotal) }},
        csrfToken: {{ Js::from(csrf_token()) }},
    })">

        {{-- ── TITRE ────────────────────────────────────────────────── --}}
        <div class="mb-8 lg:mb-12">
            <p class="text-xs uppercase tracking-widest text-terracotta-500 font-bold flex items-center gap-2">
                <i class="fa-solid fa-bag-shopping"></i>
                Votre sélection
            </p>
            <h1 class="mt-2 font-display text-4xl lg:text-5xl font-semibold tracking-tight">
                Mon panier
                <span x-show="!isEmpty" class="text-ink-muted font-normal text-2xl lg:text-3xl">
                    (<span x-text="itemCount"></span>)
                </span>
            </h1>
        </div>

        {{-- ── PANIER VIDE ──────────────────────────────────────────── --}}
        <div x-show="isEmpty" x-cloak class="py-24 text-center">
            <div class="w-24 h-24 rounded-full bg-ivory-100 flex items-center justify-center mx-auto">
                <i class="fa-regular fa-bag-shopping text-4xl text-ink-muted"></i>
            </div>
            <h2 class="mt-6 font-display text-2xl lg:text-3xl font-semibold">Votre panier est vide</h2>
            <p class="mt-3 text-ink-muted max-w-md mx-auto">Vous n'avez pas encore ajouté de produit. Découvrez notre
                sélection !</p>
            <a href="{{ route('home') }}"
                class="mt-8 inline-flex items-center gap-2 px-7 h-14 rounded-full bg-ink text-ivory-50 font-semibold text-sm hover:bg-terracotta-500 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
                Continuer mes achats
            </a>
        </div>

        {{-- ── CONTENU PANIER ───────────────────────────────────────── --}}
        <div x-show="!isEmpty" class="grid lg:grid-cols-3 gap-8 lg:gap-12">

            {{-- ======= LISTE DES ARTICLES (2/3) ======= --}}
            <div class="lg:col-span-2 flex flex-col gap-4">

                {{-- En-tête liste --}}
                <div
                    class="hidden sm:grid grid-cols-12 text-xs uppercase tracking-widest text-ink-muted font-semibold pb-2 border-b border-ink/10 px-1">
                    <span class="col-span-6">Produit</span>
                    <span class="col-span-2 text-center">Prix unit.</span>
                    <span class="col-span-2 text-center">Qté</span>
                    <span class="col-span-2 text-right">Total</span>
                </div>

                {{-- Articles --}}
                @forelse($cart->items as $item)
                    @php
                        $updateUrl = route('cart.update', $item);
                        $deleteUrl = route('cart.destroy', $item);
                        $maxQty = $item->product?->track_stock ? min(99, $item->product->stock) : 99;
                    @endphp

                    <div id="cart-item-{{ $item->id }}" x-data="cartItem({
                        id: '{{ $item->id }}',
                        quantity: {{ $item->quantity }},
                        unitPrice: {{ (float) $item->unit_price }},
                        maxQty: {{ $maxQty }},
                        updateUrl: '{{ $updateUrl }}',
                        deleteUrl: '{{ $deleteUrl }}',
                    })" x-show="!removed"
                        x-transition:leave="transition duration-300" x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-4 sm:p-5">

                        <div class="grid grid-cols-12 gap-3 items-center">

                            {{-- Image + Nom --}}
                            <div class="col-span-12 sm:col-span-6 flex items-center gap-4">
                                {{-- Image --}}
                                <a href="{{ $item->product ? route('products.show', $item->product->slug) : '#' }}"
                                    class="flex-shrink-0 w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden bg-ivory-100">
                                    @if ($item->product?->featured_image_url)
                                        <img src="{{ $item->product->featured_image_url }}"
                                            alt="{{ $item->product->name }}"
                                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-ink-muted">
                                            <i class="fa-regular fa-image text-2xl"></i>
                                        </div>
                                    @endif
                                </a>

                                {{-- Infos --}}
                                <div class="min-w-0 flex-1">
                                    <a href="{{ $item->product ? route('products.show', $item->product->slug) : '#' }}"
                                        class="font-display text-base sm:text-lg font-semibold leading-tight line-clamp-2 hover:text-terracotta-500 transition-colors">
                                        {{ $item->product?->name ?? 'Produit supprimé' }}
                                    </a>

                                    {{-- Options (couleur, taille…) --}}
                                    @if (!empty($item->options))
                                        <p class="mt-1 text-xs text-ink-muted">
                                            {{ collect($item->options)->map(fn($v, $k) => "$k : $v")->implode(' · ') }}
                                        </p>
                                    @endif

                                    {{-- Stock warning --}}
                                    @if ($item->product?->is_low_stock)
                                        <p
                                            class="mt-1 text-[10px] font-semibold text-amber-700 bg-amber-50 inline-flex items-center gap-1 px-2 py-0.5 rounded-full">
                                            <i class="fa-solid fa-fire text-[9px]"></i>
                                            Plus que {{ $item->product->stock }} en stock
                                        </p>
                                    @endif

                                    {{-- Prix (mobile) --}}
                                    <p class="mt-1.5 sm:hidden text-sm font-semibold">
                                        {{ number_format((float) $item->unit_price, 0, ',', ' ') }} FCFA
                                    </p>

                                    {{-- Bouton supprimer (mobile) --}}
                                    <button @click="remove()" :disabled="busy"
                                        class="mt-2 sm:hidden text-xs text-red-500 hover:text-red-700 font-semibold inline-flex items-center gap-1.5 disabled:opacity-50">
                                        <i class="fa-solid fa-trash text-[10px]"></i>
                                        Supprimer
                                    </button>
                                </div>
                            </div>

                            {{-- Prix unitaire (desktop) --}}
                            <div class="hidden sm:flex col-span-2 justify-center">
                                <span class="text-sm font-semibold">
                                    {{ number_format((float) $item->unit_price, 0, ',', ' ') }} FCFA
                                </span>
                            </div>

                            {{-- Quantité --}}
                            <div class="col-span-7 sm:col-span-2 flex justify-start sm:justify-center">
                                <div class="inline-flex items-center rounded-full bg-ivory-100 h-10 px-1">
                                    <button @click="decrement()" :disabled="busy || quantity <= 1"
                                        class="w-8 h-8 rounded-full hover:bg-white transition-colors flex items-center justify-center disabled:opacity-40"
                                        aria-label="Diminuer">
                                        <i class="fa-solid fa-minus text-xs"></i>
                                    </button>
                                    <span x-text="quantity"
                                        class="w-8 text-center text-sm font-semibold tabular-nums"></span>
                                    <button @click="increment()" :disabled="busy || quantity >= maxQty"
                                        class="w-8 h-8 rounded-full hover:bg-white transition-colors flex items-center justify-center disabled:opacity-40"
                                        aria-label="Augmenter">
                                        <i class="fa-solid fa-plus text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Sous-total + supprimer (desktop) --}}
                            <div class="col-span-5 sm:col-span-2 flex flex-col items-end gap-2">
                                <span x-text="formattedSubtotal"
                                    class="text-sm sm:text-base font-display font-semibold tabular-nums"></span>

                                {{-- Loader --}}
                                <span x-show="busy" class="text-[10px] text-ink-muted flex items-center gap-1">
                                    <i class="fa-solid fa-spinner fa-spin text-[10px]"></i>
                                    Mise à jour…
                                </span>

                                <button @click="remove()" :disabled="busy"
                                    class="hidden sm:inline-flex items-center gap-1 text-xs text-ink-muted hover:text-red-600 transition-colors disabled:opacity-50"
                                    aria-label="Supprimer l'article">
                                    <i class="fa-solid fa-trash text-[10px]"></i>
                                    Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    {{-- Géré par x-show="isEmpty" --}}
                @endforelse

                {{-- Bouton vider le panier --}}
                @if (!$cart->is_empty)
                    <div class="flex items-center justify-between mt-2">
                        <a href="{{ route('home') }}"
                            class="text-sm text-ink-muted hover:text-terracotta-500 transition-colors inline-flex items-center gap-2">
                            <i class="fa-solid fa-arrow-left text-xs"></i>
                            Continuer mes achats
                        </a>

                        <form method="POST" action="{{ route('cart.clear') }}"
                            onsubmit="return confirm('Vider entièrement le panier ?');">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="text-sm text-red-500 hover:text-red-700 font-semibold inline-flex items-center gap-2 transition-colors">
                                <i class="fa-solid fa-trash text-xs"></i>
                                Vider le panier
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            {{-- ======= RÉCAPITULATIF COMMANDE (1/3) ======= --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-6 lg:sticky lg:top-28">

                    <h2 class="font-display text-xl font-semibold mb-6">Récapitulatif</h2>

                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-ink-muted">
                                Sous-total (<span x-text="itemCount"></span> article<span x-show="itemCount > 1">s</span>)
                            </dt>
                            <dd class="font-semibold" x-text="subtotal"></dd>
                        </div>

                        <div class="flex justify-between text-emerald-700">
                            <dt class="flex items-center gap-2">
                                <i class="fa-solid fa-truck text-[11px]"></i>
                                Livraison
                            </dt>
                            <dd class="font-semibold">Gratuite</dd>
                        </div>

                        <div class="flex justify-between text-emerald-700">
                            <dt class="flex items-center gap-2">
                                <i class="fa-solid fa-credit-card text-[11px]"></i>
                                Paiement
                            </dt>
                            <dd class="font-semibold">À la livraison</dd>
                        </div>
                    </dl>

                    <div class="my-5 h-px bg-ink/10"></div>

                    <div class="flex justify-between items-baseline">
                        <span class="font-display text-lg font-semibold">Total</span>
                        <span class="font-display text-2xl font-semibold text-terracotta-500" x-text="subtotal"></span>
                    </div>

                    <a href="{{ $checkoutUrl }}"
                        class="mt-6 flex items-center justify-center gap-2.5 w-full h-14 rounded-2xl bg-terracotta-500 text-white font-semibold text-sm hover:bg-terracotta-700 transition-colors shadow-soft">
                        <i class="fa-solid fa-bolt"></i>
                        Commander maintenant
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>

                    <p class="mt-4 text-[11px] text-ink-muted text-center flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-lock text-[10px]"></i>
                        Paiement sécurisé · Cash à la livraison
                    </p>

                    {{-- Garanties --}}
                    <div class="mt-6 pt-5 border-t border-ink/10 space-y-2.5">
                        @foreach ([['fa-truck', 'Livraison 24h à Abidjan'], ['fa-arrow-rotate-left', 'Retour gratuit sous 7 jours'], ['fa-headset', 'Support 7j/7']] as [$icon, $label])
                            <div class="flex items-center gap-2.5 text-xs text-ink-muted">
                                <i class="fa-solid {{ $icon }} w-4 text-center text-terracotta-500/70"></i>
                                {{ $label }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function() {
                'use strict';

                var CSRF = document.querySelector('meta[name="csrf-token"]') ? document.querySelector(
                    'meta[name="csrf-token"]').content : '';

                /* ── badge header ───────────────────────────────────────────── */
                function updateHeaderBadge(count, label) {
                    var badge = document.getElementById('cart-badge');
                    var lbl = document.getElementById('cart-summary-label');
                    if (badge) {
                        badge.textContent = count > 0 ? count : '';
                        badge.className = badge.className.replace(/\b(flex|hidden)\b/g, '').trim() + (count > 0 ? ' flex' :
                            ' hidden');
                    }
                    if (lbl && label) lbl.textContent = label;
                }

                /* ── composant parent (totaux + état vide) ──────────────────── */
                window.cartPage = function(opts) {
                    return {
                        isEmpty: opts.isEmpty,
                        itemCount: opts.itemCount,
                        subtotal: opts.subtotal,
                        csrfToken: opts.csrfToken,

                        /* appelé par chaque cartItem après maj serveur */
                        onUpdate(data) {
                            this.itemCount = data.items_count || 0;
                            this.subtotal = data.formatted_subtotal || '0 FCFA';
                            this.isEmpty = this.itemCount === 0;
                            updateHeaderBadge(this.itemCount, this.subtotal);
                        },
                    };
                };

                /* ── composant enfant (une ligne du panier) ─────────────────── */
                window.cartItem = function(opts) {
                    return {
                        id: opts.id,
                        quantity: opts.quantity,
                        unitPrice: opts.unitPrice,
                        maxQty: opts.maxQty,
                        updateUrl: opts.updateUrl,
                        deleteUrl: opts.deleteUrl,
                        busy: false,
                        removed: false,

                        get formattedSubtotal() {
                            return new Intl.NumberFormat('fr-FR').format(
                                Math.round(this.quantity * this.unitPrice)
                            ) + ' FCFA';
                        },

                        increment() {
                            if (this.quantity < this.maxQty) this.setQty(this.quantity + 1);
                        },
                        decrement() {
                            if (this.quantity > 1) this.setQty(this.quantity - 1);
                        },

                        setQty(newQty) {
                            this.quantity = newQty;
                            this.sync();
                        },

                        async sync() {
                            if (this.busy) return;
                            this.busy = true;
                            try {
                                var r = await fetch(this.updateUrl, {
                                    method: 'PATCH',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': CSRF,
                                    },
                                    body: JSON.stringify({
                                        quantity: this.quantity
                                    }),
                                });
                                var json = await r.json();
                                if (json.success) {
                                    /* remonte au cartPage parent */
                                    this.$dispatch('cart-updated', json);
                                }
                            } catch (e) {
                                console.error('[cart] sync error:', e);
                            } finally {
                                this.busy = false;
                            }
                        },

                        async remove() {
                            if (this.busy) return;
                            this.busy = true;
                            try {
                                var r = await fetch(this.deleteUrl, {
                                    method: 'DELETE',
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': CSRF,
                                    },
                                });
                                var json = await r.json();
                                if (json.success) {
                                    this.removed = true;
                                    this.$dispatch('cart-updated', json);
                                }
                            } catch (e) {
                                console.error('[cart] remove error:', e);
                                this.busy = false;
                            }
                        },
                    };
                };

                /* ── écoute les événements remontés par les cartItem ───────── */
                document.addEventListener('alpine:init', function() {
                    document.addEventListener('cart-updated', function(e) {
                        /* trouve le cartPage (le parent x-data racine) et appelle onUpdate */
                        var root = document.querySelector('[x-data^="cartPage"]');
                        if (root && root._x_dataStack) {
                            var ctx = root._x_dataStack[0];
                            if (ctx && typeof ctx.onUpdate === 'function') {
                                ctx.onUpdate(e.detail);
                            }
                        }
                    });
                });

            })();
        </script>
    @endpush

@endsection
