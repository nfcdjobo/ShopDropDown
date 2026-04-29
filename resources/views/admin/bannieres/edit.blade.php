@extends('admin.layouts.app')

@section('title', 'Bannière de la boutique')
@section('page_title', 'Bannière de la boutique')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-terracotta-500">Tableau de bord</a>
    <i class="fa-solid fa-chevron-right text-[8px]"></i>
    <span>Paramètres</span>
    <i class="fa-solid fa-chevron-right text-[8px]"></i>
    <span class="text-ink font-semibold">Bannière</span>
@endsection

@section('content')

{{-- =============== EN-TÊTE =============== --}}
<div class="mb-8 lg:mb-10">
    <p class="text-xs uppercase tracking-widest text-terracotta-500 font-bold flex items-center gap-2">
        <i class="fa-solid fa-flag"></i>
        Identité de la boutique
    </p>
    <h2 class="mt-2 font-display text-3xl lg:text-4xl font-semibold tracking-tight leading-tight">
        Personnalisez votre <span class="italic text-ink-muted">bannière.</span>
    </h2>
    <p class="mt-2 text-sm text-ink-muted max-w-2xl">
        Ces informations apparaissent sur la boutique, les emails et les messages WhatsApp envoyés à vos clients.
    </p>
</div>

<form action="{{ route('admin.banniere.update') }}" method="POST" enctype="multipart/form-data"
      x-data="{ logoPreview: null }" class="grid lg:grid-cols-3 gap-6 lg:gap-8">
    @csrf
    @method('PUT')

    {{-- =============== COLONNE PRINCIPALE =============== --}}
    <div class="lg:col-span-2 flex flex-col gap-6 lg:gap-8">

        {{-- ----------- IDENTITÉ ----------- --}}
        <section class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">
            <header class="px-5 lg:px-6 py-4 flex items-center justify-between border-b border-ink/5">
                <div>
                    <h3 class="font-display text-lg lg:text-xl font-semibold">Identité</h3>
                    <p class="text-xs text-ink-muted mt-0.5">Nom, slogan et logo</p>
                </div>
                <span class="w-9 h-9 rounded-xl bg-terracotta-50 text-terracotta-700 flex items-center justify-center">
                    <i class="fa-solid fa-store"></i>
                </span>
            </header>

            <div class="p-5 lg:p-6 flex flex-col gap-5">
                {{-- Appellation --}}
                <div>
                    <label for="appelation" class="block text-xs uppercase tracking-widest text-ink-muted font-semibold mb-1.5">
                        Appellation <span class="text-terracotta-500">*</span>
                    </label>
                    <input type="text" id="appelation" name="appelation"
                           value="{{ old('appelation', $banniere->appelation) }}"
                           class="w-full h-11 px-4 rounded-xl bg-ivory-100 ring-1 ring-ink/5 focus:ring-2 focus:ring-terracotta-500 focus:bg-white outline-none transition @error('appelation') ring-red-300 @enderror"
                           placeholder="Le nom de votre boutique" required>
                    @error('appelation')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>
                    @enderror
                </div>

                {{-- Slogan --}}
                <div>
                    <label for="slogan" class="block text-xs uppercase tracking-widest text-ink-muted font-semibold mb-1.5">
                        Slogan
                    </label>
                    <input type="text" id="slogan" name="slogan"
                           value="{{ old('slogan', $banniere->slogan) }}"
                           class="w-full h-11 px-4 rounded-xl bg-ivory-100 ring-1 ring-ink/5 focus:ring-2 focus:ring-terracotta-500 focus:bg-white outline-none transition @error('slogan') ring-red-300 @enderror"
                           placeholder="Une phrase qui vous représente">
                    @error('slogan')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>
                    @enderror
                </div>

                {{-- Logo --}}
                <div>
                    <label class="block text-xs uppercase tracking-widest text-ink-muted font-semibold mb-1.5">
                        Logo
                    </label>

                    <div class="flex items-start gap-4">
                        {{-- Aperçu --}}
                        <div class="w-24 h-24 rounded-2xl bg-ivory-100 ring-1 ring-ink/5 overflow-hidden flex-shrink-0 flex items-center justify-center">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" class="w-full h-full object-cover" alt="Aperçu">
                            </template>
                            <template x-if="!logoPreview">
                                @if($banniere->logo)
                                    <img src="{{ asset('storage/' . $banniere->logo) }}" class="w-full h-full object-cover" alt="Logo actuel">
                                @else
                                    <i class="fa-regular fa-image text-2xl text-ink-muted/50"></i>
                                @endif
                            </template>
                        </div>

                        {{-- Champ + actions --}}
                        <div class="flex-1 min-w-0">
                            <label for="logo" class="inline-flex items-center gap-2 px-4 h-10 rounded-full bg-ink text-ivory-50 text-sm font-semibold hover:bg-terracotta-500 transition-colors cursor-pointer">
                                <i class="fa-solid fa-upload"></i>
                                <span>Choisir un fichier</span>
                            </label>
                            <input type="file" id="logo" name="logo" accept="image/*" class="hidden"
                                   @change="logoPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">

                            @if($banniere->logo)
                                <button type="submit" formaction="{{ route('admin.banniere.logo.supprimer') }}" formmethod="POST"
                                        onclick="event.preventDefault(); if(confirm('Supprimer le logo actuel ?')) { document.getElementById('form-supprimer-logo').submit(); }"
                                        class="inline-flex items-center gap-2 px-4 h-10 rounded-full bg-red-50 text-red-700 text-sm font-semibold hover:bg-red-100 transition-colors ml-2">
                                    <i class="fa-solid fa-trash-can"></i>
                                    <span>Supprimer</span>
                                </button>
                            @endif

                            <p class="mt-2 text-xs text-ink-muted">JPG, PNG, WebP ou SVG · 2 Mo maximum.</p>

                            @error('logo')
                                <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ----------- CONTACTS ----------- --}}
        <section class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">
            <header class="px-5 lg:px-6 py-4 flex items-center justify-between border-b border-ink/5">
                <div>
                    <h3 class="font-display text-lg lg:text-xl font-semibold">Contacts</h3>
                    <p class="text-xs text-ink-muted mt-0.5">Téléphones et email</p>
                </div>
                <span class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center">
                    <i class="fa-solid fa-address-book"></i>
                </span>
            </header>

            <div class="p-5 lg:p-6 grid sm:grid-cols-2 gap-5">
                {{-- Téléphone principal --}}
                <div>
                    <label for="telephone_principal" class="block text-xs uppercase tracking-widest text-ink-muted font-semibold mb-1.5">
                        Téléphone principal <span class="text-terracotta-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                        <input type="tel" id="telephone_principal" name="telephone_principal"
                               value="{{ old('telephone_principal', $banniere->telephone_principal) }}"
                               class="w-full h-11 pl-10 pr-4 rounded-xl bg-ivory-100 ring-1 ring-ink/5 focus:ring-2 focus:ring-terracotta-500 focus:bg-white outline-none transition @error('telephone_principal') ring-red-300 @enderror"
                               placeholder="+225 00 00 00 00 00" required>
                    </div>
                    @error('telephone_principal')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>
                    @enderror
                </div>

                {{-- Téléphone secondaire --}}
                <div>
                    <label for="telephone_secondaire" class="block text-xs uppercase tracking-widest text-ink-muted font-semibold mb-1.5">
                        Téléphone secondaire
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                        <input type="tel" id="telephone_secondaire" name="telephone_secondaire"
                               value="{{ old('telephone_secondaire', $banniere->telephone_secondaire) }}"
                               class="w-full h-11 pl-10 pr-4 rounded-xl bg-ivory-100 ring-1 ring-ink/5 focus:ring-2 focus:ring-terracotta-500 focus:bg-white outline-none transition @error('telephone_secondaire') ring-red-300 @enderror"
                               placeholder="Optionnel">
                    </div>
                    @error('telephone_secondaire')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="sm:col-span-2">
                    <label for="email" class="block text-xs uppercase tracking-widest text-ink-muted font-semibold mb-1.5">
                        Email <span class="text-terracotta-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-xs"></i>
                        <input type="email" id="email" name="email"
                               value="{{ old('email', $banniere->email) }}" maxlength="100"
                               class="w-full h-11 pl-10 pr-4 rounded-xl bg-ivory-100 ring-1 ring-ink/5 focus:ring-2 focus:ring-terracotta-500 focus:bg-white outline-none transition @error('email') ring-red-300 @enderror"
                               placeholder="contact@maboutique.com" required>
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- ----------- WHATSAPP ----------- --}}
        <section class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">
            <header class="px-5 lg:px-6 py-4 flex items-center justify-between border-b border-ink/5">
                <div>
                    <h3 class="font-display text-lg lg:text-xl font-semibold">WhatsApp</h3>
                    <p class="text-xs text-ink-muted mt-0.5">Pour le contact rapide avec vos clients</p>
                </div>
                <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <i class="fa-brands fa-whatsapp text-lg"></i>
                </span>
            </header>

            <div class="p-5 lg:p-6 grid sm:grid-cols-2 gap-5">
                {{-- Numéro WhatsApp --}}
                <div>
                    <label for="numero_whatsapp" class="block text-xs uppercase tracking-widest text-ink-muted font-semibold mb-1.5">
                        Numéro WhatsApp
                    </label>
                    <input type="text" id="numero_whatsapp" name="numero_whatsapp"
                           value="{{ old('numero_whatsapp', $banniere->numero_whatsapp) }}"
                           class="w-full h-11 px-4 rounded-xl bg-ivory-100 ring-1 ring-ink/5 focus:ring-2 focus:ring-terracotta-500 focus:bg-white outline-none transition @error('numero_whatsapp') ring-red-300 @enderror"
                           placeholder="+225 00 00 00 00 00">
                    @error('numero_whatsapp')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>
                    @enderror
                </div>

                {{-- Lien WhatsApp --}}
                <div>
                    <label for="lien_whatsapp" class="block text-xs uppercase tracking-widest text-ink-muted font-semibold mb-1.5">
                        Lien WhatsApp
                    </label>
                    <input type="url" id="lien_whatsapp" name="lien_whatsapp"
                           value="{{ old('lien_whatsapp', $banniere->lien_whatsapp) }}"
                           class="w-full h-11 px-4 rounded-xl bg-ivory-100 ring-1 ring-ink/5 focus:ring-2 focus:ring-terracotta-500 focus:bg-white outline-none transition @error('lien_whatsapp') ring-red-300 @enderror"
                           placeholder="https://wa.me/...">
                    @error('lien_whatsapp')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- ----------- LIVRAISON ----------- --}}
        <section class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">
            <header class="px-5 lg:px-6 py-4 flex items-center justify-between border-b border-ink/5">
                <div>
                    <h3 class="font-display text-lg lg:text-xl font-semibold">Livraison</h3>
                    <p class="text-xs text-ink-muted mt-0.5">Frais appliqués par défaut aux commandes</p>
                </div>
                <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
                    <i class="fa-solid fa-truck"></i>
                </span>
            </header>

            <div class="p-5 lg:p-6 flex flex-col gap-5">
                {{-- Toggle activation --}}
                <label for="appliquer_frais_livraison_defaut"
                       class="flex items-start gap-4 p-4 rounded-xl bg-ivory-100 ring-1 ring-ink/5 cursor-pointer hover:bg-ivory-200/50 transition-colors">
                    <input type="hidden" name="appliquer_frais_livraison_defaut" value="0">
                    <input type="checkbox" id="appliquer_frais_livraison_defaut"
                           name="appliquer_frais_livraison_defaut" value="1"
                           class="mt-1 w-4 h-4 rounded text-terracotta-500 focus:ring-terracotta-500"
                           {{ old('appliquer_frais_livraison_defaut', $banniere->appliquer_frais_livraison_defaut) ? 'checked' : '' }}>
                    <div>
                        <p class="text-sm font-semibold">Appliquer les frais par défaut</p>
                        <p class="text-xs text-ink-muted mt-0.5">Lorsque cette option est activée, le montant ci-dessous est ajouté automatiquement à toutes les commandes.</p>
                    </div>
                </label>

                {{-- Montant --}}
                <div>
                    <label for="frais_livraison_defaut" class="block text-xs uppercase tracking-widest text-ink-muted font-semibold mb-1.5">
                        Frais de livraison par défaut
                    </label>
                    <div class="relative">
                        <input type="number" id="frais_livraison_defaut" name="frais_livraison_defaut"
                               value="{{ old('frais_livraison_defaut', $banniere->frais_livraison_defaut) }}"
                               min="0" step="100"
                               class="w-full h-11 pl-4 pr-16 rounded-xl bg-ivory-100 ring-1 ring-ink/5 focus:ring-2 focus:ring-terracotta-500 focus:bg-white outline-none transition @error('frais_livraison_defaut') ring-red-300 @enderror">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs uppercase tracking-widest text-ink-muted font-bold">FCFA</span>
                    </div>
                    @error('frais_livraison_defaut')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        {{-- ----------- TRACKING & ANALYTICS ----------- --}}
<section class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden">
    <header class="px-5 lg:px-6 py-4 flex items-center justify-between border-b border-ink/5">
        <div>
            <h3 class="font-display text-lg lg:text-xl font-semibold">Tracking</h3>
            <p class="text-xs text-ink-muted mt-0.5">Suivi des conversions et publicités</p>
        </div>
        <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
            <i class="fa-brands fa-meta"></i>
        </span>
    </header>

    <div class="p-5 lg:p-6 flex flex-col gap-5">
        {{-- Pixel Meta --}}
        <div>
            <label for="meta_id" class="block text-xs uppercase tracking-widest text-ink-muted font-semibold mb-1.5">
                Pixel Meta (Facebook)
            </label>
            <div class="relative">
                <i class="fa-brands fa-facebook absolute left-4 top-1/2 -translate-y-1/2 text-blue-600 text-sm"></i>
                <input type="text" id="meta_id" name="meta_id"
                       value="{{ old('meta_id', $banniere->meta_id) }}"
                       inputmode="numeric"
                       pattern="\d{15,16}"
                       maxlength="16"
                       class="w-full h-11 pl-10 pr-4 rounded-xl bg-ivory-100 ring-1 ring-ink/5 focus:ring-2 focus:ring-terracotta-500 focus:bg-white outline-none transition font-mono @error('meta_id') ring-red-300 @enderror"
                       placeholder="123456789012345">
            </div>
            @error('meta_id')
                <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs text-ink-muted flex items-start gap-1.5">
                <i class="fa-solid fa-circle-info text-[10px] mt-0.5"></i>
                <span>
                    Identifiant à 15 ou 16 chiffres trouvé dans
                    <a href="https://business.facebook.com/events_manager" target="_blank" rel="noopener" class="text-terracotta-500 hover:underline font-semibold">
                        Meta Events Manager
                    </a>.
                    Laissez vide pour désactiver le tracking.
                </span>
            </p>
        </div>
    </div>
</section>
    </div>

    {{-- =============== COLONNE DROITE : APERÇU =============== --}}
    <div class="flex flex-col gap-6 lg:gap-8">

        {{-- Aperçu --}}
        <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft overflow-hidden lg:sticky lg:top-24">
            <div class="px-5 py-4 flex items-center justify-between border-b border-ink/5">
                <h3 class="font-display text-lg font-semibold">Aperçu</h3>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                    <i class="fa-solid fa-eye text-[10px]"></i>
                    Live
                </span>
            </div>

            <div class="p-5 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-2xl bg-ivory-100 ring-1 ring-ink/5 overflow-hidden flex items-center justify-center">
                    @if($banniere->logo)
                        <img src="{{ asset('storage/' . $banniere->logo) }}" class="w-full h-full object-cover" alt="">
                    @else
                        <i class="fa-regular fa-image text-2xl text-ink-muted/50"></i>
                    @endif
                </div>
                <p class="mt-3 font-display text-lg font-semibold">{{ $banniere->appelation }}</p>
                @if($banniere->slogan)
                    <p class="text-xs italic text-ink-muted">« {{ $banniere->slogan }} »</p>
                @endif

                <div class="mt-5 w-full flex flex-col gap-2 text-left">
                    <div class="flex items-center gap-2.5 px-3 py-2 rounded-lg bg-ivory-100">
                        <i class="fa-solid fa-phone w-4 text-terracotta-500"></i>
                        <span class="text-xs">{{ $banniere->telephone_principal }}</span>
                    </div>
                    <div class="flex items-center gap-2.5 px-3 py-2 rounded-lg bg-ivory-100">
                        <i class="fa-solid fa-envelope w-4 text-indigo-500"></i>
                        <span class="text-xs truncate">{{ $banniere->email }}</span>
                    </div>
                    @if($banniere->numero_whatsapp)
                        <div class="flex items-center gap-2.5 px-3 py-2 rounded-lg bg-ivory-100">
                            <i class="fa-brands fa-whatsapp w-4 text-emerald-500"></i>
                            <span class="text-xs">{{ $banniere->numero_whatsapp }}</span>
                        </div>
                    @endif
                </div>

                <p class="mt-4 text-[10px] uppercase tracking-widest text-ink-muted font-semibold">
                    Mis à jour {{ $banniere->updated_at->diffForHumans() }}
                </p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 flex flex-col gap-3">
            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 h-11 rounded-full bg-terracotta-500 text-white text-sm font-semibold hover:bg-terracotta-600 transition-colors">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Enregistrer les modifications</span>
            </button>
            <a href="{{ route('admin.dashboard') }}"
               class="w-full inline-flex items-center justify-center gap-2 h-11 rounded-full bg-ivory-100 text-ink text-sm font-semibold hover:bg-ivory-200 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Annuler</span>
            </a>
            <p class="text-[11px] text-ink-muted text-center mt-1">
                <i class="fa-solid fa-shield-halved mr-1"></i>
                Cette ligne est protégée et ne peut pas être supprimée.
            </p>
        </div>
    </div>
</form>

{{-- Formulaire caché pour la suppression du logo --}}
<form id="form-supprimer-logo" action="{{ route('admin.banniere.logo.supprimer') }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

@endsection
