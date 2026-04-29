@extends('admin.layouts.app')

@section('title', 'Nouvel utilisateur')
@section('page_title', 'Nouvel utilisateur')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-terracotta-500">Administration</a>
    <i class="fa-solid fa-chevron-right text-[9px] text-ink-muted/50"></i>
    <a href="{{ route('admin.users.index') }}" class="hover:text-terracotta-500">Utilisateurs</a>
    <i class="fa-solid fa-chevron-right text-[9px] text-ink-muted/50"></i>
    <span>Nouveau</span>
@endsection

@section('content')

<form action="{{ route('admin.users.store') }}" method="POST" class="max-w-3xl">
    @csrf

    <div class="grid lg:grid-cols-3 gap-6 lg:gap-8">

        {{-- ======== PRINCIPAL (2/3) ======== --}}
        <div class="lg:col-span-2 flex flex-col gap-6">

            {{-- Identité --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6">
                <h2 class="font-display text-lg font-semibold mb-1">Identité</h2>
                <p class="text-xs text-ink-muted mb-5">Un mot de passe temporaire sera généré et envoyé par email.</p>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Prénom *</label>
                        <input type="text" name="first_name" id="first_name" required
                               value="{{ old('first_name') }}"
                               placeholder="Kouassi"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                        @error('first_name')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="last_name" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Nom *</label>
                        <input type="text" name="last_name" id="last_name" required
                               value="{{ old('last_name') }}"
                               placeholder="Konan"
                               class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                        @error('last_name')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="email" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Email *</label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                            <input type="email" name="email" id="email" required
                                   value="{{ old('email') }}"
                                   placeholder="exemple@shopdropdown.ci"
                                   class="w-full pl-11 pr-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                        </div>
                        @error('email')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="phone" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Téléphone</label>
                        <div class="relative">
                            <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                            <input type="tel" name="phone" id="phone"
                                   value="{{ old('phone') }}"
                                   placeholder="+225 07 XX XX XX XX"
                                   class="w-full pl-11 pr-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                        </div>
                        @error('phone')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Info mot de passe --}}
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-start gap-4">
                <i class="fa-solid fa-key text-amber-600 text-lg mt-0.5 flex-shrink-0"></i>
                <div class="text-sm">
                    <p class="font-semibold text-amber-900">Mot de passe temporaire</p>
                    <p class="text-amber-700 mt-1">Un mot de passe aléatoire sécurisé sera automatiquement généré et envoyé à l'adresse email renseignée. L'utilisateur devra le changer à sa première connexion.</p>
                </div>
            </div>
        </div>

        {{-- ======== SIDEBAR (1/3) ======== --}}
        <div class="flex flex-col gap-6">

            {{-- Rôle --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                <h2 class="font-display text-lg font-semibold mb-4">Rôle</h2>

                <div class="flex flex-col gap-2">
                    @foreach($roles as $role)
                        <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl hover:bg-ivory-100 transition-colors group">
                            <input type="radio" name="role" value="{{ $role['value'] }}"
                                   {{ old('role', 'customer') === $role['value'] ? 'checked' : '' }}
                                   class="w-4 h-4 accent-terracotta-500 flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid {{ $role['icon'] }} text-xs text-ink-muted"></i>
                                    <p class="text-sm font-semibold">{{ $role['label'] }}</p>
                                </div>
                                <p class="text-xs text-ink-muted mt-0.5">
                                    @if($role['value'] === 'customer') Accès boutique & suivi de commandes
                                    @elseif($role['value'] === 'admin') Accès back-office (commandes)
                                    @else Accès complet à tous les modules
                                    @endif
                                </p>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('role')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Options --}}
            <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                <h2 class="font-display text-lg font-semibold mb-4">Options</h2>

                <div class="flex flex-col gap-4">
                    <label class="flex items-center justify-between cursor-pointer">
                        <div>
                            <p class="text-sm font-semibold">Compte actif</p>
                            <p class="text-xs text-ink-muted mt-0.5">L'utilisateur peut se connecter</p>
                        </div>
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="sr-only peer">
                        <span class="w-11 h-6 bg-ink/15 rounded-full relative peer-checked:bg-terracotta-500 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-transform peer-checked:after:translate-x-5 flex-shrink-0"></span>
                    </label>

                    <div class="h-px bg-ink/5"></div>

                    <label class="flex items-center justify-between cursor-pointer">
                        <div>
                            <p class="text-sm font-semibold">Envoyer le mot de passe</p>
                            <p class="text-xs text-ink-muted mt-0.5">Par email à la création</p>
                        </div>
                        <input type="hidden" name="send_password_email" value="0">
                        <input type="checkbox" name="send_password_email" value="1"
                               {{ old('send_password_email', true) ? 'checked' : '' }}
                               class="sr-only peer">
                        <span class="w-11 h-6 bg-ink/15 rounded-full relative peer-checked:bg-terracotta-500 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-transform peer-checked:after:translate-x-5 flex-shrink-0"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="mt-8 flex flex-col-reverse sm:flex-row items-center justify-end gap-3">
        <a href="{{ route('admin.users.index') }}" class="w-full sm:w-auto text-center px-6 h-11 rounded-full bg-white ring-1 ring-ink/10 text-sm font-semibold hover:bg-ivory-100 transition-colors inline-flex items-center justify-center gap-2">
            <i class="fa-solid fa-xmark text-xs"></i>
            Annuler
        </a>
        <button type="submit" class="w-full sm:w-auto px-6 h-11 rounded-full bg-terracotta-500 text-white text-sm font-semibold hover:bg-terracotta-700 transition-colors shadow-soft inline-flex items-center justify-center gap-2">
            <i class="fa-solid fa-user-plus text-xs"></i>
            Créer l'utilisateur
        </button>
    </div>
</form>

@endsection
