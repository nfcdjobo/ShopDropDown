@extends('layouts.app')

@section('title', $forced ? 'Sécurisez votre compte — Shop Dropdown' : 'Changer mon mot de passe')
@section('meta_description', 'Définissez votre mot de passe personnel pour accéder à votre espace client Shop Dropdown.')

@section('content')

<div class="min-h-[calc(100vh-4rem)] bg-ivory-100 flex items-center justify-center px-5 py-14 lg:py-20">

    {{-- ============================================================
         CARTE PRINCIPALE
         ============================================================ --}}
    <div class="w-full max-w-md" x-data="passwordChange()">

        {{-- ---- Icône + Titre ---- --}}
        <div class="text-center mb-8">

            @if($forced)
                {{-- Mode forcé : ton onboarding, chaleureux --}}
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-terracotta-50 ring-1 ring-terracotta-200 mb-5">
                    <i class="fa-solid fa-shield-halved text-2xl text-terracotta-500"></i>
                </div>
                <h1 class="font-display font-semibold text-3xl lg:text-4xl tracking-tight leading-tight">
                    Sécurisez votre <span class="italic text-terracotta-500">compte</span>
                </h1>
                <p class="mt-3 text-sm text-ink-muted leading-relaxed max-w-sm mx-auto">
                    Vous avez reçu un mot de passe provisoire par email.
                    Choisissez-en un personnel pour accéder à votre espace.
                </p>
            @else
                {{-- Mode volontaire : ton paramètres --}}
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-sand-100 ring-1 ring-ink/10 mb-5">
                    <i class="fa-solid fa-key text-2xl text-ink-muted"></i>
                </div>
                <h1 class="font-display font-semibold text-3xl lg:text-4xl tracking-tight leading-tight">
                    Changer mon <span class="italic text-terracotta-500">mot de passe</span>
                </h1>
                <p class="mt-3 text-sm text-ink-muted leading-relaxed max-w-sm mx-auto">
                    Pour votre sécurité, votre nouveau mot de passe doit contenir
                    au moins 8 caractères, une lettre et un chiffre.
                </p>
            @endif
        </div>

        {{-- ---- Bannière avertissement middleware ---- --}}
        @if(session('warning'))
            <div class="mb-6 flex items-start gap-3 rounded-2xl bg-amber-50 border border-amber-200 px-4 py-3.5">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 shrink-0"></i>
                <p class="text-sm text-amber-900">{{ session('warning') }}</p>
            </div>
        @endif

        {{-- ---- Erreurs globales ---- --}}
        @if($errors->any())
            <div class="mb-6 flex items-start gap-3 rounded-2xl bg-red-50 border border-red-200 px-4 py-3.5">
                <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 shrink-0"></i>
                <ul class="text-sm text-red-800 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ============================================================
             FORMULAIRE
             ============================================================ --}}
        <div class="bg-white rounded-3xl shadow-soft ring-1 ring-ink/5 p-7 lg:p-9 space-y-5">

            <form method="POST" action="{{ route('password.change.store') }}" novalidate @submit="submitting = true">
                @csrf

                {{-- ---- Mot de passe actuel ---- --}}
                <div>
                    <label for="current_password" class="block text-sm font-semibold text-ink mb-1.5">
                        @if($forced)
                            Mot de passe provisoire reçu par email
                        @else
                            Mot de passe actuel
                        @endif
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <i class="fa-solid fa-lock text-ink-muted/60 text-sm"></i>
                        </div>
                        <input
                            id="current_password"
                            name="current_password"
                            :type="showCurrent ? 'text' : 'password'"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full pl-10 pr-11 py-3 rounded-xl border text-sm transition-colors
                                   @error('current_password') border-red-400 bg-red-50 focus:ring-red-300 @else border-ink/15 bg-ivory-50 focus:border-terracotta-400 focus:ring-terracotta-100 @enderror
                                   focus:outline-none focus:ring-2"
                            required
                        >
                        {{-- Basculer visibilité --}}
                        <button type="button"
                                @click="showCurrent = !showCurrent"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-ink-muted hover:text-ink transition-colors"
                                :aria-label="showCurrent ? 'Masquer le mot de passe' : 'Afficher le mot de passe'">
                            <i :class="showCurrent ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation text-[11px]"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- ---- Nouveau mot de passe ---- --}}
                <div class="pt-1">
                    <label for="password" class="block text-sm font-semibold text-ink mb-1.5">
                        Nouveau mot de passe
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <i class="fa-solid fa-key text-ink-muted/60 text-sm"></i>
                        </div>
                        <input
                            id="password"
                            name="password"
                            :type="showNew ? 'text' : 'password'"
                            autocomplete="new-password"
                            placeholder="Minimum 8 caractères"
                            x-model="newPassword"
                            class="w-full pl-10 pr-11 py-3 rounded-xl border text-sm transition-colors
                                   @error('password') border-red-400 bg-red-50 focus:ring-red-300 @else border-ink/15 bg-ivory-50 focus:border-terracotta-400 focus:ring-terracotta-100 @enderror
                                   focus:outline-none focus:ring-2"
                            required
                        >
                        <button type="button"
                                @click="showNew = !showNew"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-ink-muted hover:text-ink transition-colors"
                                :aria-label="showNew ? 'Masquer' : 'Afficher'">
                            <i :class="showNew ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                        </button>
                    </div>

                    {{-- Jauge de force --}}
                    <div class="mt-2 space-y-1.5" x-show="newPassword.length > 0" x-cloak>
                        <div class="flex gap-1.5">
                            <template x-for="(bar, i) in 4" :key="i">
                                <div class="h-1 flex-1 rounded-full transition-colors duration-300"
                                     :class="i < strength ? strengthColor : 'bg-ink/10'"></div>
                            </template>
                        </div>
                        <p class="text-xs transition-colors" :class="strengthTextColor" x-text="strengthLabel"></p>
                    </div>

                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation text-[11px]"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- ---- Confirmation ---- --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-ink mb-1.5">
                        Confirmer le nouveau mot de passe
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <i class="fa-solid fa-check-double text-ink-muted/60 text-sm"></i>
                        </div>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            :type="showConfirm ? 'text' : 'password'"
                            autocomplete="new-password"
                            placeholder="Répétez votre mot de passe"
                            x-model="confirmPassword"
                            class="w-full pl-10 pr-11 py-3 rounded-xl border text-sm transition-colors
                                   focus:outline-none focus:ring-2
                                   focus:border-terracotta-400 focus:ring-terracotta-100 border-ink/15 bg-ivory-50"
                            :class="confirmPassword.length > 0 && confirmPassword !== newPassword
                                ? 'border-red-400 bg-red-50'
                                : (confirmPassword.length > 0 && confirmPassword === newPassword
                                    ? 'border-emerald-400 bg-emerald-50/40'
                                    : '')"
                            required
                        >
                        <button type="button"
                                @click="showConfirm = !showConfirm"
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-ink-muted hover:text-ink transition-colors"
                                :aria-label="showConfirm ? 'Masquer' : 'Afficher'">
                            <i :class="showConfirm ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                        </button>
                    </div>
                    {{-- Indication de correspondance --}}
                    <p class="mt-1.5 text-xs flex items-center gap-1 transition-colors"
                       x-show="confirmPassword.length > 0"
                       x-cloak
                       :class="confirmPassword === newPassword ? 'text-emerald-600' : 'text-red-500'">
                        <i :class="confirmPassword === newPassword ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation'" class="text-[11px]"></i>
                        <span x-text="confirmPassword === newPassword ? 'Les mots de passe correspondent.' : 'Les mots de passe ne correspondent pas.'"></span>
                    </p>
                </div>

                {{-- ---- Bouton ---- --}}
                {{-- <button
                    type="submit"
                    class="mt-2 w-full btn-primary py-3.5 rounded-2xl text-sm font-semibold flex items-center justify-center gap-2 transition-opacity"
                    :disabled="submitting || (confirmPassword.length > 0 && confirmPassword !== newPassword)"
                    :class="submitting ? 'opacity-70 cursor-not-allowed' : ''"
                    @click="submitting = true"
                >
                    <i class="fa-solid fa-shield-check" x-show="!submitting"></i>
                    <i class="fa-solid fa-circle-notch fa-spin" x-show="submitting" x-cloak></i>
                    <span x-text="submitting ? 'Enregistrement…' : '{{ $forced ? 'Sécuriser mon compte' : 'Mettre à jour le mot de passe' }}'"></span>
                </button> --}}
                <button
                    type="submit"
                    :disabled="submitting || (confirmPassword.length > 0 && confirmPassword !== newPassword)"
                    :class="submitting ? 'opacity-70 cursor-not-allowed' : ''"
                    class="mt-2 w-full btn-primary py-3.5 rounded-2xl text-sm font-semibold flex items-center justify-center gap-2 transition-opacity"
                >
                    <i class="fa-solid fa-shield-check" x-show="!submitting"></i>
                    <i class="fa-solid fa-circle-notch fa-spin" x-show="submitting" x-cloak></i>
                    <span x-text="submitting ? 'Enregistrement…' : '{{ $forced ? 'Sécuriser mon compte' : 'Mettre à jour le mot de passe' }}'"></span>
                </button>

            </form>

            {{-- ---- Lien retour (mode volontaire seulement) ---- --}}
            @unless($forced)
                <div class="pt-2 border-t border-ink/5 text-center">
                    <a href="{{ route('customer.dashboard') }}"
                       class="text-sm text-ink-muted hover:text-ink transition-colors inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                        Retour à mon espace
                    </a>
                </div>
            @endunless

        </div>

        {{-- ---- Indicateurs de sécurité (mode forcé uniquement) ---- --}}
        @if($forced)
            <p class="mt-6 text-center text-xs text-ink-muted flex items-center justify-center gap-2">
                <i class="fa-solid fa-lock text-[11px]"></i>
                Connexion sécurisée · Vos données restent confidentielles
            </p>
        @endif

    </div>
</div>

@push('scripts')
<script>
function passwordChange() {
    return {
        showCurrent:     false,
        showNew:         false,
        showConfirm:     false,
        newPassword:     '',
        confirmPassword: '',
        submitting:      false,

        get strength() {
            const p = this.newPassword;
            if (p.length === 0) return 0;
            let score = 0;
            if (p.length >= 8)                      score++;
            if (/[a-zA-Z]/.test(p))                 score++;
            if (/\d/.test(p))                        score++;
            if (/[^a-zA-Z0-9]/.test(p) || p.length >= 14) score++;
            return score;
        },

        get strengthColor() {
            return ['bg-red-400', 'bg-orange-400', 'bg-amber-400', 'bg-emerald-500'][this.strength - 1] ?? 'bg-ink/10';
        },

        get strengthTextColor() {
            return ['text-red-500', 'text-orange-500', 'text-amber-600', 'text-emerald-600'][this.strength - 1] ?? 'text-ink-muted';
        },

        get strengthLabel() {
            return ['Trop court', 'Faible', 'Correct', 'Fort'][this.strength - 1] ?? '';
        },
    };
}
</script>
@endpush

@endsection
