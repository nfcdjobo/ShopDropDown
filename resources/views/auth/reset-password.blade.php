<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nouveau mot de passe — {{ config('app.name') }}</title>

    {{-- Page sensible : le jeton figure dans l'URL → ni indexation, ni fuite via le Referer --}}
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;1,9..144,500&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-ivory-100 text-ink antialiased">

<main class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">

        <p class="text-center font-display text-2xl font-semibold tracking-tight mb-6">
            {{ config('app.name') }}
        </p>

        <div class="bg-white rounded-3xl shadow-lift ring-1 ring-ink/5 p-6 sm:p-9">

            <h1 class="font-display font-semibold text-3xl tracking-tight leading-tight">
                Nouveau <span class="italic text-terracotta-500">mot de passe</span>
            </h1>
            <p class="mt-2 text-sm text-ink-muted">
                Choisissez un mot de passe d'au moins 8 caractères pour sécuriser votre compte.
            </p>

            {{-- Toutes les erreurs : validation, jeton invalide, trop de tentatives --}}
            @if ($errors->any())
                <div role="alert" class="mt-6 rounded-2xl bg-red-50 border border-red-200 text-red-900 px-4 py-3 text-sm">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="flex gap-2">
                                <i class="fa-solid fa-circle-exclamation mt-0.5" aria-hidden="true"></i>
                                <span>{{ $error }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ url('admin/reinitialiser-mot-de-passe') }}"
                  x-data="{
                      pw: '', pc: '', show: false,
                      get lengthOk() { return this.pw.length >= 8; },
                      get matchOk()  { return this.pc.length > 0 && this.pw === this.pc; },
                      get valid()    { return this.lengthOk && this.matchOk; },
                  }"
                  class="mt-6 space-y-5">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="block text-xs uppercase tracking-widest text-ink-muted font-semibold mb-1.5">
                        Adresse email
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email', $email) }}"
                           required autocomplete="username" inputmode="email"
                           class="w-full h-12 px-4 rounded-xl bg-ivory-100 ring-1 ring-ink/5 focus:ring-2 focus:ring-terracotta-500 focus:bg-white outline-none transition">
                </div>

                <div>
                    <label for="password" class="block text-xs uppercase tracking-widest text-ink-muted font-semibold mb-1.5">
                        Nouveau mot de passe
                    </label>
                    <div class="relative">
                        <input id="password" name="password" required minlength="8" autocomplete="new-password"
                               x-model="pw" :type="show ? 'text' : 'password'"
                               class="w-full h-12 pl-4 pr-12 rounded-xl bg-ivory-100 ring-1 ring-ink/5 focus:ring-2 focus:ring-terracotta-500 focus:bg-white outline-none transition">
                        <button type="button" @click="show = !show"
                                class="absolute right-1.5 top-1/2 -translate-y-1/2 w-9 h-9 rounded-lg text-ink-muted hover:bg-ivory-200 transition"
                                :aria-label="show ? 'Masquer les mots de passe' : 'Afficher les mots de passe'">
                            <i class="fa-regular" :class="show ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs uppercase tracking-widest text-ink-muted font-semibold mb-1.5">
                        Confirmer le mot de passe
                    </label>
                    <input id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                           x-model="pc" :type="show ? 'text' : 'password'"
                           class="w-full h-12 px-4 rounded-xl bg-ivory-100 ring-1 ring-ink/5 focus:ring-2 focus:ring-terracotta-500 focus:bg-white outline-none transition">
                </div>

                {{-- Aide en direct --}}
                <ul class="text-xs space-y-1.5" aria-live="polite">
                    <li class="flex items-center gap-2" :class="lengthOk ? 'text-emerald-700' : 'text-ink-muted'">
                        <i class="fa-solid" :class="lengthOk ? 'fa-circle-check' : 'fa-circle'" aria-hidden="true"></i>
                        8 caractères minimum
                    </li>
                    <li class="flex items-center gap-2" :class="matchOk ? 'text-emerald-700' : 'text-ink-muted'">
                        <i class="fa-solid" :class="matchOk ? 'fa-circle-check' : 'fa-circle'" aria-hidden="true"></i>
                        Les deux mots de passe sont identiques
                    </li>
                </ul>

                <button type="submit" :disabled="!valid"
                        class="btn-primary w-full h-12 rounded-full text-sm font-semibold gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                    Enregistrer le mot de passe
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-ink-muted">
            <a href="{{ url('/') }}" class="font-semibold hover:text-terracotta-500 transition-colors">
                <i class="fa-solid fa-arrow-left text-xs mr-1" aria-hidden="true"></i> Retour à la boutique
            </a>
        </p>
    </div>
</main>

</body>
</html>
