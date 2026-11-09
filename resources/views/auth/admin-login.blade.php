<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion Administration — {{ config('app.name', 'Shop Drop') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,400&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased min-h-screen bg-ink text-ivory-50" x-data="{ showPwd: false }">

    {{-- Décor de fond --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-[600px] h-[600px] rounded-full bg-terracotta-500/10 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-[500px] h-[500px] rounded-full bg-ivory-50/5 blur-3xl"></div>
    </div>

    <div class="relative min-h-screen flex flex-col items-center justify-center px-5 py-12">

        {{-- Logo --}}
        <div class="mb-10 text-center">
            <div class="inline-flex items-center gap-3">
                <span class="w-12 h-12 rounded-full bg-terracotta-500 text-white flex items-center justify-center font-display font-bold text-xl">S</span>
                <span class="font-display text-3xl font-semibold tracking-tight">Shop<span class="text-terracotta-500">·</span>DropDown</span>
            </div>
            <p class="mt-2 text-xs uppercase tracking-[.2em] text-ivory-50/40 font-semibold">Espace Administration</p>
        </div>

        {{-- Carte de connexion --}}
        <div class="w-full max-w-md">
            <div class="bg-white/5 backdrop-blur-sm rounded-3xl ring-1 ring-ivory-50/10 p-8 lg:p-10">

                <h1 class="font-display text-2xl lg:text-3xl font-semibold text-center mb-1">Accès sécurisé</h1>
                <p class="text-sm text-ivory-50/50 text-center mb-8">Réservé aux administrateurs de la boutique.</p>

                {{-- Flash error --}}
                @if(session('error'))
                    <div class="mb-6 bg-red-500/10 border border-red-500/30 rounded-2xl px-4 py-3 flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-red-400 flex-shrink-0"></i>
                        <p class="text-sm text-red-300">{{ session('error') }}</p>
                    </div>
                @endif

                {{-- Flash success (après reset mdp) --}}
                @if(session('success'))
                    <div class="mb-6 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl px-4 py-3 flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-400 flex-shrink-0"></i>
                        <p class="text-sm text-emerald-300">{{ session('success') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.post') }}" class="flex flex-col gap-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-xs uppercase tracking-widest font-semibold text-ivory-50/50 mb-2">Adresse email</label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-ivory-50/30 text-sm"></i>
                            <input type="email" name="email" id="email"
                                   value="{{ old('email') }}"
                                   required autofocus autocomplete="username"
                                   placeholder="admin@ShopDrop.ci"
                                   class="w-full pl-11 pr-4 h-12 rounded-2xl bg-ivory-50/10 border border-ivory-50/10 text-ivory-50 placeholder-ivory-50/30 focus:border-terracotta-500 focus:bg-ivory-50/15 focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                        </div>
                        @error('email')
                            <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Mot de passe --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="text-xs uppercase tracking-widest font-semibold text-ivory-50/50">Mot de passe</label>
                            <a href="{{ route('admin.password.forgot') }}" class="text-xs text-terracotta-500 hover:text-terracotta-400 hover:underline">
                                Mot de passe oublié ?
                            </a>
                        </div>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-ivory-50/30 text-sm"></i>
                            <input :type="showPwd ? 'text' : 'password'"
                                   name="password" id="password"
                                   required autocomplete="current-password"
                                   placeholder="••••••••••"
                                   class="w-full pl-11 pr-12 h-12 rounded-2xl bg-ivory-50/10 border border-ivory-50/10 text-ivory-50 placeholder-ivory-50/30 focus:border-terracotta-500 focus:bg-ivory-50/15 focus:ring-2 focus:ring-terracotta-500/20 outline-none transition text-sm">
                            <button type="button" @click="showPwd = !showPwd"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-ivory-50/40 hover:text-ivory-50/80 transition-colors"
                                    :aria-label="showPwd ? 'Masquer' : 'Afficher'">
                                <i :class="showPwd ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Se souvenir de moi --}}
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="remember" id="remember"
                               class="w-4.5 h-4.5 rounded border-ivory-50/20 bg-ivory-50/10 checked:bg-terracotta-500 checked:border-terracotta-500 outline-none transition">
                        <span class="text-sm text-ivory-50/60 group-hover:text-ivory-50/90 transition-colors">Rester connecté sur cet appareil</span>
                    </label>

                    {{-- Bouton --}}
                    <button type="submit"
                            class="w-full h-12 rounded-2xl bg-terracotta-500 hover:bg-terracotta-600 text-white font-semibold text-sm transition-colors shadow-lg shadow-terracotta-500/25 flex items-center justify-center gap-2.5 mt-1">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Se connecter
                    </button>
                </form>
            </div>

            {{-- Lien retour boutique --}}
            <div class="mt-6 text-center">
                <a href="{{ route('home') }}" class="text-xs text-ivory-50/40 hover:text-ivory-50/70 transition-colors inline-flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    Retour à la boutique
                </a>
            </div>
        </div>

        {{-- Footer --}}
        <p class="mt-12 text-[10px] text-ivory-50/20 text-center">
            © {{ date('Y') }} Shop Drop · Accès sécurisé · Toutes les tentatives sont enregistrées
        </p>
    </div>

</body>
</html>
