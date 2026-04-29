@extends('layouts.app')

@section('title', 'Finaliser ma commande')
@section('meta_description', 'Vérifiez votre panier et passez votre commande en quelques secondes.')

@section('content')

    <div class="max-w-7xl mx-auto px-5 lg:px-10 py-8 lg:py-14">

        {{-- ── TITRE ────────────────────────────────────────────────── --}}
        <div class="mb-8 lg:mb-12">
            <p class="text-xs uppercase tracking-widest text-terracotta-500 font-bold flex items-center gap-2">
                <i class="fa-solid fa-bolt"></i>
                Commande rapide
            </p>
            <h1 class="mt-2 font-display text-4xl lg:text-5xl font-semibold tracking-tight">
                Finaliser ma commande
            </h1>
            <p class="mt-2 text-sm text-ink-muted">Paiement à la livraison · Aucun frais à l'avance</p>
        </div>

        <div class="grid lg:grid-cols-12 gap-8 lg:gap-12 items-start">

            {{-- ================================================================
             COL GAUCHE — FORMULAIRE (7/12)
             ================================================================ --}}
            <div class="lg:col-span-7 xl:col-span-8">

                @auth
                    {{-- ─────────────────────────────────────────────────────────────
                 MODE CONNECTÉ — formulaire pré-rempli + confirmation directe
                 ───────────────────────────────────────────────────────────── --}}
                    <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">

                        {{-- En-tête --}}
                        <div class="px-6 py-5 border-b border-ink/5 flex items-center gap-3">
                            <span
                                class="w-10 h-10 rounded-full bg-ink text-ivory-50 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                {{ auth()->user()->initials }}
                            </span>
                            <div class="min-w-0">
                                <p class="font-semibold truncate">{{ auth()->user()->full_name }}</p>
                                <p class="text-xs text-ink-muted truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <span
                                class="ml-auto text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full flex items-center gap-1.5 flex-shrink-0">
                                <i class="fa-solid fa-circle-check text-[10px]"></i>
                                Connecté
                            </span>
                        </div>

                        <form method="POST" action="{{ route('checkout.store') }}" class="p-6">
                            @csrf

                            <h2 class="font-display text-xl font-semibold mb-5">Adresse de livraison25</h2>

                            @if ($errors->any())
                                <div class="mb-5 bg-red-50 border border-red-200 rounded-2xl px-5 py-4 flex items-start gap-3">
                                    <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5"></i>
                                    <ul class="text-sm text-red-700 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="grid sm:grid-cols-2 gap-4">

                                <div class="sm:col-span-2">
                                    <label
                                        class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Nom
                                        complet *</label>
                                    <div class="relative">
                                        <i
                                            class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                                        <input type="text" name="full_name" required
                                            value="{{ old('full_name', auth()->user()->full_name) }}"
                                            class="w-full pl-11 pr-4 h-12 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                                    </div>
                                    @error('full_name')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Téléphone
                                        *</label>
                                    <div class="relative">
                                        <i
                                            class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                                        <input type="tel" name="phone" required
                                            value="{{ old('phone', auth()->user()->phone) }}" placeholder="+225 07 XX XX XX XX"
                                            class="w-full pl-11 pr-4 h-12 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                                    </div>
                                    @error('phone')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Email
                                        *</label>
                                    <div class="relative">
                                        <i
                                            class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                                        <input type="email" name="email" required
                                            value="{{ old('email', auth()->user()->email) }}"
                                            class="w-full pl-11 pr-4 h-12 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                                    </div>
                                    @error('email')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Ville
                                        *</label>
                                    <div class="relative">
                                        <i
                                            class="fa-solid fa-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                                        <input type="text" name="city" required value="{{ old('city', 'Abidjan') }}"
                                            placeholder="Abidjan"
                                            class="w-full pl-11 pr-4 h-12 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                                    </div>
                                    @error('city')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Commune
                                        / Quartier</label>
                                    <div class="relative">
                                        <i
                                            class="fa-solid fa-map-pin absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                                        <input type="text" name="address" required value="{{ old('address') }}"
                                            placeholder="Cocody, Plateau, Yopougon…"
                                            class="w-full pl-11 pr-4 h-12 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                                    </div>
                                    @error('address')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label
                                        class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Instructions
                                        de livraison <span class="font-normal text-ink-muted/70">(optionnel)</span></label>
                                    <textarea name="notes" rows="2" placeholder="Point de repère, étage, instructions particulières…"
                                        class="w-full px-4 py-3 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm resize-none">{{ old('notes') }}</textarea>
                                </div>
                            </div>

                            <button type="submit"
                                class="mt-6 w-full h-14 rounded-2xl bg-terracotta-500 text-white font-semibold text-sm hover:bg-terracotta-700 transition-colors shadow-soft flex items-center justify-center gap-2.5">
                                <i class="fa-solid fa-check-circle"></i>
                                Confirmer ma commande
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </button>

                            <p class="mt-3 text-[11px] text-ink-muted text-center flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-lock text-[10px]"></i>
                                Paiement uniquement à la livraison — rien à payer maintenant
                            </p>
                        </form>
                    </div>
                @else
                    {{-- ─────────────────────────────────────────────────────────────
                 MODE INVITÉ — 2 onglets : "Nouvelle commande" / "J'ai un compte"
                 L'invité ne sait pas qu'il est en train de créer un compte.
                 ───────────────────────────────────────────────────────────── --}}
                    <div x-data="{
                        tab: '{{ $errors->has('password') || session('login_error') ? 'login' : 'guest' }}'
                    }">

                        {{-- ── Onglets navigation ── --}}
                        <div class="flex gap-1 mb-6 bg-ivory-100 p-1 rounded-2xl">
                            <button @click="tab='guest'"
                                :class="tab === 'guest' ?
                                    'bg-white shadow-soft text-ink' :
                                    'text-ink-muted hover:text-ink'"
                                class="flex-1 h-11 rounded-xl text-sm font-semibold transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-bag-shopping text-xs"></i>
                                Nouvelle commande
                            </button>
                            <button @click="tab='login'"
                                :class="tab === 'login' ?
                                    'bg-white shadow-soft text-ink' :
                                    'text-ink-muted hover:text-ink'"
                                class="flex-1 h-11 rounded-xl text-sm font-semibold transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-right-to-bracket text-xs"></i>
                                J'ai déjà commandé
                            </button>
                        </div>

                        {{-- ══════════════════════════════════════════════════
                     PANNEAU 1 — Informations de livraison (invité)
                     Présenté comme "infos de livraison", pas inscription
                     ══════════════════════════════════════════════════ --}}
                        <div x-show="tab==='guest'" x-cloak>
                            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-6">

                                <h2 class="font-display text-xl font-semibold mb-1">Vos informations de livraison</h2>
                                <p class="text-sm text-ink-muted mb-6">Renseignez vos coordonnées pour recevoir votre colis.</p>

                                @if ($errors->any() && !$errors->has('password'))
                                    <div
                                        class="mb-5 bg-red-50 border border-red-200 rounded-2xl px-5 py-4 flex items-start gap-3">
                                        <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 flex-shrink-0"></i>
                                        <ul class="text-sm text-red-700 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('checkout.guest') }}">
                                    @csrf

                                    <div class="grid sm:grid-cols-2 gap-4">

                                        <div>
                                            <label
                                                class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Prénom
                                                *</label>
                                            <input type="text" name="first_name" required value="{{ old('first_name') }}"
                                                placeholder="Kouassi"
                                                class="w-full px-4 h-12 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                                            @error('first_name')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label
                                                class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Nom
                                                *</label>
                                            <input type="text" name="last_name" required value="{{ old('last_name') }}"
                                                placeholder="Konan"
                                                class="w-full px-4 h-12 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                                            @error('last_name')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label
                                                class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Téléphone
                                                *</label>
                                            <div class="relative">
                                                <i
                                                    class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                                                <input type="tel" name="phone" required value="{{ old('phone') }}"
                                                    placeholder="+225 07 XX XX XX XX"
                                                    class="w-full pl-11 pr-4 h-12 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                                            </div>
                                            @error('phone')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label
                                                class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Email
                                                *</label>
                                            <div class="relative">
                                                <i
                                                    class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                                                <input type="email" name="email" required value="{{ old('email') }}"
                                                    placeholder="exemple@email.com"
                                                    class="w-full pl-11 pr-4 h-12 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                                            </div>
                                            @error('email')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label
                                                class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Ville
                                                *</label>
                                            <div class="relative">
                                                <i
                                                    class="fa-solid fa-location-dot absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                                                <input type="text" name="city" required
                                                    value="{{ old('city', 'Abidjan') }}"
                                                    class="w-full pl-11 pr-4 h-12 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                                            </div>
                                            @error('city')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label
                                                class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Commune
                                                / Quartier *</label>
                                            <div class="relative">
                                                <i
                                                    class="fa-solid fa-map-pin absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                                                <input type="text" name="address" required value="{{ old('address') }}"
                                                    placeholder="Cocody, Plateau, Yopougon…"
                                                    class="w-full pl-11 pr-4 h-12 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                                            </div>
                                            @error('address')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="sm:col-span-2">
                                            <label
                                                class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Instructions
                                                <span class="font-normal text-ink-muted/70">(optionnel)</span></label>
                                            <textarea name="notes" rows="2" placeholder="Point de repère, instructions particulières…"
                                                class="w-full px-4 py-3 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm resize-none">{{ old('notes') }}</textarea>
                                        </div>
                                    </div>

                                    <button type="submit"
                                        class="mt-6 w-full h-14 rounded-2xl bg-terracotta-500 text-white font-semibold text-sm hover:bg-terracotta-700 transition-colors shadow-soft flex items-center justify-center gap-2.5">
                                        <i class="fa-solid fa-check-circle"></i>
                                        Valider ma commande
                                        <i class="fa-solid fa-arrow-right text-xs"></i>
                                    </button>

                                    {{-- Micro-copy rassurant --}}
                                    <div
                                        class="mt-4 flex flex-col sm:flex-row items-center gap-3 sm:gap-6 justify-center text-[11px] text-ink-muted">
                                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-lock text-[10px]"></i>
                                            Paiement à la livraison</span>
                                        <span class="flex items-center gap-1.5"><i
                                                class="fa-solid fa-rotate-left text-[10px]"></i> Retour 7 jours</span>
                                        <span class="flex items-center gap-1.5"><i
                                                class="fa-solid fa-envelope text-[10px]"></i> Confirmation par email</span>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- ══════════════════════════════════════════════════
                     PANNEAU 2 — Connexion (client existant)
                     ══════════════════════════════════════════════════ --}}
                        <div x-show="tab==='login'" x-cloak>
                            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-6" x-data="{ showPwd: false }">

                                <h2 class="font-display text-xl font-semibold mb-1">Connectez-vous</h2>
                                <p class="text-sm text-ink-muted mb-6">Votre panier sera conservé et fusionné avec votre
                                    compte.</p>

                                @if ($errors->has('email') && $errors->has('password'))
                                    <div
                                        class="mb-5 bg-red-50 border border-red-200 rounded-2xl px-5 py-4 flex items-start gap-3">
                                        <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5"></i>
                                        <p class="text-sm text-red-700">{{ $errors->first('email') }}</p>
                                    </div>
                                @endif

                                @if (session('success'))
                                    <div
                                        class="mb-5 bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-4 flex items-start gap-3">
                                        <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                                        <p class="text-sm text-emerald-700">{{ session('success') }}</p>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('checkout.login') }}">
                                    @csrf

                                    <div class="flex flex-col gap-4">
                                        <div>
                                            <label
                                                class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Adresse
                                                email *</label>
                                            <div class="relative">
                                                <i
                                                    class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                                                <input type="email" name="email" required autofocus
                                                    value="{{ old('email') }}" placeholder="votreemail@exemple.ci"
                                                    class="w-full pl-11 pr-4 h-12 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                                            </div>
                                        </div>

                                        <div>
                                            <label
                                                class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Mot
                                                de passe *</label>
                                            <div class="relative">
                                                <i
                                                    class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                                                <input :type="showPwd ? 'text' : 'password'" name="password" required
                                                    placeholder="••••••••"
                                                    class="w-full pl-11 pr-12 h-12 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                                                <button type="button" @click="showPwd = !showPwd"
                                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-ink-muted hover:text-ink">
                                                    <i :class="showPwd ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"
                                                        class="text-sm"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <label class="flex items-center gap-2.5 cursor-pointer">
                                            <input type="checkbox" name="remember"
                                                class="w-4 h-4 accent-terracotta-500 rounded">
                                            <span class="text-sm text-ink-muted">Rester connecté</span>
                                        </label>
                                    </div>

                                    <button type="submit"
                                        class="mt-6 w-full h-14 rounded-2xl bg-ink text-ivory-50 font-semibold text-sm hover:bg-terracotta-500 transition-colors shadow-soft flex items-center justify-center gap-2.5">
                                        <i class="fa-solid fa-right-to-bracket"></i>
                                        Me connecter et commander
                                    </button>

                                    <div class="mt-4 text-center">
                                        <a href="{{ route('admin.password.forgot') }}"
                                            class="text-xs text-ink-muted hover:text-terracotta-500 hover:underline">
                                            Mot de passe oublié ?
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    {{-- fin @else invité --}}
                @endauth
            </div>

            {{-- ================================================================
             COL DROITE — RÉCAPITULATIF PANIER (sticky, toujours visible)
             Visible en permanence peu importe le panneau actif côté gauche.
             C'est la clé UX : le visiteur voit ses articles tout le temps.
             ================================================================ --}}
            <div class="lg:col-span-5 xl:col-span-4">
                <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden lg:sticky lg:top-28">

                    {{-- En-tête récap --}}
                    <div class="px-6 py-4 border-b border-ink/5 flex items-center justify-between">
                        <h2 class="font-display text-lg font-semibold">Votre commande</h2>
                        <a href="{{ route('cart.index') }}"
                            class="text-xs text-terracotta-500 hover:underline flex items-center gap-1">
                            <i class="fa-solid fa-pen text-[10px]"></i>
                            Modifier
                        </a>
                    </div>

                    {{-- Articles --}}
                    <div class="divide-y divide-ink/5 max-h-80 overflow-y-auto">
                        @foreach ($cart->items as $item)
                            <div class="flex items-center gap-3 px-6 py-3">
                                {{-- Miniature --}}
                                <div class="w-14 h-14 rounded-xl bg-ivory-100 overflow-hidden flex-shrink-0 relative">
                                    @if ($item->product?->featured_image_url)
                                        <img src="{{ $item->product->featured_image_url }}"
                                            alt="{{ $item->product?->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-ink-muted">
                                            <i class="fa-regular fa-image text-lg"></i>
                                        </div>
                                    @endif
                                    {{-- Badge quantité --}}
                                    <span
                                        class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-ink text-ivory-50 text-[10px] font-bold flex items-center justify-center">
                                        {{ $item->quantity }}
                                    </span>
                                </div>

                                {{-- Nom + prix --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold truncate leading-tight">
                                        {{ $item->product?->name ?? 'Produit' }}
                                    </p>
                                    @if (!empty($item->options))
                                        <p class="text-[11px] text-ink-muted truncate">
                                            {{ collect($item->options)->map(fn($v, $k) => "$k: $v")->implode(' · ') }}
                                        </p>
                                    @endif
                                    <p class="text-[11px] text-ink-muted mt-0.5">
                                        {{ number_format((float) $item->unit_price, 0, ',', ' ') }} FCFA ×
                                        {{ $item->quantity }}
                                    </p>
                                </div>

                                {{-- Sous-total --}}
                                <p class="text-sm font-display font-semibold flex-shrink-0">
                                    {{ $item->formatted_subtotal }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    {{-- Totaux --}}
                    <div class="px-6 py-4 border-t border-ink/5 space-y-2.5">
                        <div class="flex justify-between text-sm">
                            <span class="text-ink-muted">Sous-total</span>
                            <span class="font-semibold">{{ $cart->formatted_subtotal }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-emerald-700">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-truck text-[11px]"></i>
                                Livraison
                            </span>
                            <span class="font-semibold">Gratuite</span>
                        </div>
                        <div class="flex justify-between text-sm text-emerald-700">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-credit-card text-[11px]"></i>
                                Paiement
                            </span>
                            <span class="font-semibold">À la livraison</span>
                        </div>
                        <div class="pt-2 border-t border-ink/10 flex justify-between items-baseline">
                            <span class="font-display text-base font-semibold">Total</span>
                            <span
                                class="font-display text-2xl font-semibold text-terracotta-500">{{ $cart->formatted_subtotal }}</span>
                        </div>
                    </div>

                    {{-- Badges de confiance --}}
                    <div
                        class="px-6 py-4 bg-ivory-50 border-t border-ink/5 flex items-center justify-center gap-6 text-[11px] text-ink-muted">
                        <span class="flex items-center gap-1.5"><i
                                class="fa-solid fa-shield-halved text-terracotta-500 text-[10px]"></i> Sécurisé</span>
                        <span class="flex items-center gap-1.5"><i
                                class="fa-solid fa-truck text-terracotta-500 text-[10px]"></i> Livraison 24h</span>
                        <span class="flex items-center gap-1.5"><i
                                class="fa-solid fa-rotate-left text-terracotta-500 text-[10px]"></i> Retour facile</span>
                    </div>
                </div>
            </div>

        </div>{{-- /grid --}}
    </div>

@endsection
