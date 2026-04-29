@extends('admin.layouts.app')

@section('title', 'Modifier : ' . $user->full_name)
@section('page_title', 'Modifier l\'utilisateur')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-terracotta-500">Administration</a>
    <i class="fa-solid fa-chevron-right text-[9px] text-ink-muted/50"></i>
    <a href="{{ route('admin.users.index') }}" class="hover:text-terracotta-500">Utilisateurs</a>
    <i class="fa-solid fa-chevron-right text-[9px] text-ink-muted/50"></i>
    <span class="truncate max-w-[30vw]">{{ $user->full_name }}</span>
@endsection

@php
    $isSelf    = $user->id === auth()->id();
    $canEdit   = !$user->isSuperAdmin() || auth()->user()->isSuperAdmin();
    $roleColors = [
        'super_admin' => 'bg-purple-50 text-purple-700',
        'admin'       => 'bg-blue-50 text-blue-700',
        'customer'    => 'bg-ivory-100 text-ink-muted',
    ];
@endphp

@section('content')

<div class="max-w-3xl">

    {{-- Alerte "vous vous modifiez vous-même" --}}
    @if($isSelf)
        <div class="mb-6 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 flex items-center gap-3">
            <i class="fa-solid fa-circle-info text-amber-600 flex-shrink-0"></i>
            <p class="text-sm text-amber-800">Vous modifiez votre propre compte. Le rôle et le statut ne peuvent pas être changés.</p>
        </div>
    @endif

    <form id="user-form" action="{{ route('admin.users.update', $user) }}" method="POST" class="">
        @csrf @method('PATCH')

        <div class="grid lg:grid-cols-3 gap-6 lg:gap-8">

            {{-- ======== PRINCIPAL (2/3) ======== --}}
            <div class="lg:col-span-2 flex flex-col gap-6">

                {{-- Identité --}}
                <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6">
                    <h2 class="font-display text-lg font-semibold mb-5">Identité</h2>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Prénom *</label>
                            <input type="text" name="first_name" id="first_name" required
                                   value="{{ old('first_name', $user->first_name) }}"
                                   class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                            @error('first_name')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="last_name" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Nom *</label>
                            <input type="text" name="last_name" id="last_name" required
                                   value="{{ old('last_name', $user->last_name) }}"
                                   class="w-full px-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                            @error('last_name')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="email" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Email *</label>
                            <div class="relative">
                                <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                                <input type="email" name="email" id="email" required
                                       value="{{ old('email', $user->email) }}"
                                       class="w-full pl-11 pr-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                            </div>
                            @error('email')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="phone" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Téléphone</label>
                            <div class="relative">
                                <i class="fa-solid fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                                <input type="tel" name="phone" id="phone"
                                       value="{{ old('phone', $user->phone) }}"
                                       placeholder="+225 07 XX XX XX XX"
                                       class="w-full pl-11 pr-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                            </div>
                            @error('phone')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- Nouveau mot de passe optionnel --}}
                <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5 lg:p-6"
                     x-data="{ showPwd: false, changePwd: false }">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="font-display text-lg font-semibold">Mot de passe</h2>
                            <p class="text-xs text-ink-muted mt-0.5">Laissez vide pour ne pas changer.</p>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold text-terracotta-500">
                            <input type="checkbox" @change="changePwd = $event.target.checked" class="w-4 h-4 accent-terracotta-500">
                            Modifier
                        </label>
                    </div>

                    <div x-show="changePwd" x-cloak x-transition class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Nouveau mot de passe</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                                <input :type="showPwd ? 'text' : 'password'" name="password" id="password"
                                       placeholder="8 caractères minimum"
                                       class="w-full pl-11 pr-11 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                                <button type="button" @click="showPwd = !showPwd"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-ink-muted hover:text-ink">
                                    <i :class="showPwd ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                                </button>
                            </div>
                            @error('password')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-xs uppercase tracking-wider font-semibold text-ink-muted mb-1.5">Confirmation</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-ink-muted text-sm"></i>
                                <input :type="showPwd ? 'text' : 'password'" name="password_confirmation" id="password_confirmation"
                                       placeholder="Répéter le mot de passe"
                                       class="w-full pl-11 pr-4 h-11 rounded-xl bg-ivory-100 border border-transparent focus:border-terracotta-500 focus:bg-white focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                            </div>
                        </div>
                    </div>

                    <div x-show="!changePwd" class="flex items-center justify-between bg-ivory-100 rounded-xl px-4 py-3">
                        <span class="text-sm text-ink-muted">Mot de passe actuel conservé</span>
                        {{-- Réinitialisation rapide par l'admin --}}
                        @if(!$isSelf)
                            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}"
                                  onsubmit="return confirm('Générer un nouveau mot de passe temporaire et l\'envoyer à {{ $user->email }} ?')">
                                @csrf
                                <button type="submit" class="text-xs text-terracotta-500 hover:text-terracotta-700 font-semibold hover:underline">
                                    <i class="fa-solid fa-rotate-right mr-1"></i>
                                    Réinitialiser par email
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ======== SIDEBAR (1/3) ======== --}}
            <div class="flex flex-col gap-6">

                {{-- Rôle --}}
                <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                    <h2 class="font-display text-lg font-semibold mb-4">Rôle</h2>

                    @if($isSelf)
                        {{-- L'admin ne peut pas changer son propre rôle --}}
                        <input type="hidden" name="role" value="{{ $user->role->value }}">
                        <div class="bg-ivory-100 rounded-xl px-4 py-3 flex items-center gap-3">
                            <i class="fa-solid {{ $user->isSuperAdmin() ? 'fa-shield-halved' : ($user->isAdmin() ? 'fa-user-tie' : 'fa-user') }} text-ink-muted"></i>
                            <div>
                                <p class="text-sm font-semibold">{{ $user->isSuperAdmin() ? 'Super admin' : ($user->isAdmin() ? 'Administrateur' : 'Client') }}</p>
                                <p class="text-xs text-ink-muted">Votre propre rôle ne peut pas être modifié</p>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col gap-2">
                            @foreach($roles as $role)
                                <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl hover:bg-ivory-100 transition-colors">
                                    <input type="radio" name="role" value="{{ $role['value'] }}"
                                           {{ old('role', $user->role->value) === $role['value'] ? 'checked' : '' }}
                                           class="w-4 h-4 accent-terracotta-500 flex-shrink-0">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid {{ $role['icon'] }} text-xs text-ink-muted"></i>
                                            <p class="text-sm font-semibold">{{ $role['label'] }}</p>
                                        </div>
                                        <p class="text-xs text-ink-muted mt-0.5">
                                            @if($role['value'] === 'customer') Boutique uniquement
                                            @elseif($role['value'] === 'admin') Gestion commandes
                                            @else Accès complet
                                            @endif
                                        </p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('role')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                    @endif
                </div>

                {{-- Statut --}}
                @if(!$isSelf)
                    <div class="bg-white rounded-2xl ring-1 ring-ink/5 shadow-soft p-5">
                        <h2 class="font-display text-lg font-semibold mb-4">Statut</h2>

                        <label class="flex items-center justify-between cursor-pointer">
                            <div>
                                <p class="text-sm font-semibold">Compte actif</p>
                                <p class="text-xs text-ink-muted mt-0.5">L'utilisateur peut se connecter</p>
                            </div>
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <span class="w-11 h-6 bg-ink/15 rounded-full relative peer-checked:bg-terracotta-500 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-transform peer-checked:after:translate-x-5 flex-shrink-0"></span>
                        </label>
                    </div>
                @endif

                {{-- Métadonnées --}}
                <div class="bg-ivory-100 rounded-2xl ring-1 ring-ink/5 p-5">
                    <h3 class="text-xs uppercase tracking-widest font-semibold text-ink-muted mb-3">Informations</h3>
                    <dl class="text-xs space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-ink-muted">Rôle actuel</dt>
                            <dd>
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-1.5 py-0.5 rounded-full {{ $roleColors[$user->role->value] ?? '' }}">
                                    {{ $user->isSuperAdmin() ? 'Super admin' : ($user->isAdmin() ? 'Admin' : 'Client') }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-muted">Commandes</dt>
                            <dd class="font-semibold">
                                <a href="{{ route('admin.orders.index', ['q' => $user->email]) }}" class="text-terracotta-500 hover:underline">
                                    {{ $user->orders()->count() }}
                                </a>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-muted">Inscrit le</dt>
                            <dd>{{ $user->created_at->format('d/m/Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-muted">Dernière co.</dt>
                            <dd>{{ $user->last_login_at?->diffForHumans() ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-muted">Profil complété</dt>
                            <dd>{{ $user->profile_completed ? 'Oui' : 'Non' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-ink-muted">MDP temp.</dt>
                            <dd>
                                @if($user->must_change_password)
                                    <span class="text-amber-600 font-semibold">En attente</span>
                                @else
                                    <span class="text-emerald-600">Non</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </form>

    {{-- Actions --}}
    <div class="mt-8 flex flex-col-reverse sm:flex-row items-center justify-between gap-4">
        <div>
            @if(auth()->user()->isSuperAdmin() && !$isSelf)
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                      onsubmit="return confirm('Archiver définitivement le compte de {{ $user->full_name }} ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-700 inline-flex items-center gap-2">
                        <i class="fa-solid fa-trash"></i>
                        Archiver le compte
                    </button>
                </form>
            @endif
        </div>

        <div class="flex flex-col-reverse sm:flex-row items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('admin.users.index') }}" class="w-full sm:w-auto text-center px-6 h-11 rounded-full bg-white ring-1 ring-ink/10 text-sm font-semibold hover:bg-ivory-100 transition-colors inline-flex items-center justify-center gap-2">
                Retour
            </a>
            <button type="submit" form="user-form" class="w-full sm:w-auto px-6 h-11 rounded-full bg-terracotta-500 text-white text-sm font-semibold hover:bg-terracotta-700 transition-colors shadow-soft inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-check text-xs"></i>
                Enregistrer les modifications
            </button>
        </div>
    </div>
</div>

@endsection
