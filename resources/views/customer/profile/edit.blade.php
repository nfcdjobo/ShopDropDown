@extends('layouts.app')

@section('title', 'Mon profil — Shop Dropdown')

@section('content')

<div class="max-w-3xl mx-auto px-5 lg:px-8 py-10 lg:py-14">

    {{-- ── EN-TÊTE ───────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('customer.dashboard') }}"
           class="w-9 h-9 rounded-xl bg-white ring-1 ring-ink/10 shadow-soft flex items-center justify-center hover:ring-terracotta-300 transition-colors">
            <i class="fa-solid fa-arrow-left text-sm text-ink-muted"></i>
        </a>
        <div>
            <p class="text-xs uppercase tracking-widest text-terracotta-500 font-bold">Espace client</p>
            <h1 class="font-display text-3xl font-semibold tracking-tight">Mon profil</h1>
        </div>
    </div>

    {{-- ── FLASH SUCCESS ─────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-4">
            <i class="fa-solid fa-circle-check text-emerald-500 shrink-0"></i>
            <p class="text-sm text-emerald-800 font-medium">{{ session('success') }}</p>
        </div>
    @endif

    {{-- ── ERREURS GLOBALES ──────────────────────────────────────────── --}}
    @if($errors->any())
        <div class="mb-6 flex items-start gap-3 rounded-2xl bg-red-50 border border-red-200 px-5 py-4">
            <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 shrink-0"></i>
            <ul class="text-sm text-red-800 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-6">

        {{-- ================================================================
             CARTE : INFORMATIONS PERSONNELLES
             ================================================================ --}}
        <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">

            <div class="px-6 py-5 border-b border-ink/5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-terracotta-50 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-user text-terracotta-500"></i>
                </div>
                <div>
                    <h2 class="font-display text-lg font-semibold">Informations personnelles</h2>
                    <p class="text-xs text-ink-muted">Ces informations seront pré-remplies lors de vos prochaines commandes.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('customer.profile.update') }}" class="p-6 space-y-5"
                  x-data="{ submitting: false }"
                  @submit="submitting = true">
                @csrf
                @method('PATCH')

                <div class="grid sm:grid-cols-2 gap-4">

                    {{-- Prénom --}}
                    <div>
                        <label for="first_name" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">
                            Prénom *
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm pointer-events-none"></i>
                            <input id="first_name" type="text" name="first_name" required
                                   value="{{ old('first_name', $user->first_name) }}"
                                   placeholder="Kouassi"
                                   class="w-full pl-11 pr-4 h-12 rounded-xl text-sm border transition-colors outline-none
                                          @error('first_name') border-red-400 bg-red-50 focus:ring-red-200
                                          @else border-ink/15 bg-ivory-50 focus:border-terracotta-400 focus:bg-white focus:ring-terracotta-100 @enderror
                                          focus:ring-2">
                        </div>
                        @error('first_name')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation text-[11px]"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Nom --}}
                    <div>
                        <label for="last_name" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">
                            Nom *
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm pointer-events-none"></i>
                            <input id="last_name" type="text" name="last_name" required
                                   value="{{ old('last_name', $user->last_name) }}"
                                   placeholder="Konan"
                                   class="w-full pl-11 pr-4 h-12 rounded-xl text-sm border transition-colors outline-none
                                          @error('last_name') border-red-400 bg-red-50 focus:ring-red-200
                                          @else border-ink/15 bg-ivory-50 focus:border-terracotta-400 focus:bg-white focus:ring-terracotta-100 @enderror
                                          focus:ring-2">
                        </div>
                        @error('last_name')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation text-[11px]"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Téléphone --}}
                    <div>
                        <label for="phone" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">
                            Téléphone *
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm pointer-events-none"></i>
                            <input id="phone" type="tel" name="phone" required
                                   value="{{ old('phone', $user->phone) }}"
                                   placeholder="+225 07 XX XX XX XX"
                                   class="w-full pl-11 pr-4 h-12 rounded-xl text-sm border transition-colors outline-none
                                          @error('phone') border-red-400 bg-red-50 focus:ring-red-200
                                          @else border-ink/15 bg-ivory-50 focus:border-terracotta-400 focus:bg-white focus:ring-terracotta-100 @enderror
                                          focus:ring-2">
                        </div>
                        @error('phone')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation text-[11px]"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">
                            Adresse email *
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm pointer-events-none"></i>
                            <input id="email" type="email" name="email" required
                                   value="{{ old('email', $user->email) }}"
                                   placeholder="exemple@email.com"
                                   class="w-full pl-11 pr-4 h-12 rounded-xl text-sm border transition-colors outline-none
                                          @error('email') border-red-400 bg-red-50 focus:ring-red-200
                                          @else border-ink/15 bg-ivory-50 focus:border-terracotta-400 focus:bg-white focus:ring-terracotta-100 @enderror
                                          focus:ring-2">
                        </div>
                        @error('email')
                            <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation text-[11px]"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>

                {{-- Bouton --}}
                <div class="pt-2 flex items-center gap-4">
                    <button type="submit"
                            :disabled="submitting"
                            :class="submitting ? 'opacity-70 cursor-not-allowed' : ''"
                            class="inline-flex items-center gap-2 btn-primary px-6 py-3 rounded-xl text-sm font-semibold transition-opacity">
                        <i class="fa-solid fa-circle-notch fa-spin" x-show="submitting" x-cloak></i>
                        <i class="fa-solid fa-floppy-disk" x-show="!submitting"></i>
                        <span x-text="submitting ? 'Enregistrement…' : 'Enregistrer les modifications'"></span>
                    </button>

                    <a href="{{ route('customer.dashboard') }}"
                       class="text-sm text-ink-muted hover:text-ink transition-colors">
                        Annuler
                    </a>
                </div>

            </form>
        </div>

        {{-- ================================================================
             CARTE : ADRESSES ENREGISTRÉES (lecture seule pour l'instant)
             ================================================================ --}}
        @if($addresses->isNotEmpty())
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">

                <div class="px-6 py-5 border-b border-ink/5 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sand-100 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-location-dot text-ink-muted"></i>
                    </div>
                    <div>
                        <h2 class="font-display text-lg font-semibold">Adresses enregistrées</h2>
                        <p class="text-xs text-ink-muted">Issues de vos commandes précédentes.</p>
                    </div>
                </div>

                <div class="divide-y divide-ink/5">
                    @foreach($addresses as $address)
                        <div class="flex items-start gap-4 px-6 py-5">
                            {{-- Icône --}}
                            <div class="w-9 h-9 rounded-xl bg-ivory-100 flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fa-solid fa-house text-ink-muted text-sm"></i>
                            </div>

                            {{-- Détails --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if($address->label)
                                        <span class="text-sm font-semibold">{{ $address->label }}</span>
                                    @endif
                                    @if($address->is_default)
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-terracotta-50 text-terracotta-600 ring-1 ring-terracotta-200">
                                            Par défaut
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-ink mt-1">{{ $address->full_name }}</p>
                                <p class="text-xs text-ink-muted mt-0.5">{{ $address->full_address }}</p>
                                @if($address->phone)
                                    <p class="text-xs text-ink-muted mt-0.5">
                                        <i class="fa-solid fa-phone text-[10px] mr-1"></i>{{ $address->phone }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ================================================================
             CARTE : SÉCURITÉ
             ================================================================ --}}
        <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">
            <div class="px-6 py-5 border-b border-ink/5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sand-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-shield-halved text-ink-muted"></i>
                </div>
                <div>
                    <h2 class="font-display text-lg font-semibold">Sécurité</h2>
                    <p class="text-xs text-ink-muted">Gérez votre mot de passe.</p>
                </div>
            </div>
            <div class="px-6 py-5 flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold">Mot de passe</p>
                    <p class="text-xs text-ink-muted mt-0.5">Dernière modification inconnue</p>
                </div>
                <a href="{{ route('password.change') }}"
                   class="shrink-0 inline-flex items-center gap-2 text-sm font-semibold border border-ink/15 rounded-xl px-4 py-2.5 hover:border-terracotta-400 hover:text-terracotta-500 transition-colors">
                    <i class="fa-solid fa-key text-xs"></i>
                    Modifier
                </a>
            </div>
        </div>

    </div>{{-- /space-y-6 --}}

</div>

@endsection
