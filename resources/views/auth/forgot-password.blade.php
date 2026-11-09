<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mot de passe oublié — {{ config('app.name', 'Shop Drop') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,400&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased min-h-screen bg-ink text-ivory-50">

    {{-- Décor --}}
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

        <div class="w-full max-w-md">
            <div class="bg-white/5 backdrop-blur-sm rounded-3xl ring-1 ring-ivory-50/10 p-8 lg:p-10">

                {{-- Icône --}}
                <div class="w-16 h-16 rounded-2xl bg-terracotta-500/20 text-terracotta-400 flex items-center justify-center mx-auto mb-6">
                    <i class="fa-solid fa-key text-2xl"></i>
                </div>

                <h1 class="font-display text-2xl lg:text-3xl font-semibold text-center mb-2">Mot de passe oublié</h1>
                <p class="text-sm text-ivory-50/50 text-center mb-8">Saisissez votre adresse email. Si elle correspond à un compte admin, vous recevrez un lien de réinitialisation.</p>

                {{-- Message de statut (après envoi) --}}
                @if(session('status'))
                    <div class="mb-6 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl px-5 py-4 flex items-start gap-3">
                        <i class="fa-solid fa-envelope-circle-check text-emerald-400 text-lg mt-0.5 flex-shrink-0"></i>
                        <div>
                            <p class="text-sm font-semibold text-emerald-300">Email envoyé !</p>
                            <p class="text-xs text-emerald-400/80 mt-1">{{ session('status') }}</p>
                            <p class="text-xs text-emerald-400/60 mt-2">Vérifiez aussi votre dossier spam. Le lien expire dans 60 minutes.</p>
                        </div>
                    </div>
                @endif

                @unless(session('status'))
                    <form method="POST" action="{{ route('admin.password.email') }}" class="flex flex-col gap-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-xs uppercase tracking-widest font-semibold text-ivory-50/50 mb-2">Adresse email</label>
                            <div class="relative">
                                <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-ivory-50/30 text-sm"></i>
                                <input type="email" name="email" id="email"
                                       value="{{ old('email') }}"
                                       required autofocus autocomplete="email"
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

                        <button type="submit"
                                class="w-full h-12 rounded-2xl bg-terracotta-500 hover:bg-terracotta-600 text-white font-semibold text-sm transition-colors shadow-lg shadow-terracotta-500/25 flex items-center justify-center gap-2.5">
                            <i class="fa-solid fa-paper-plane text-sm"></i>
                            Envoyer le lien de réinitialisation
                        </button>
                    </form>
                @endunless

                {{-- Liens --}}
                <div class="mt-6 flex flex-col items-center gap-3 border-t border-ivory-50/10 pt-5">
                    <a href="{{ route('admin.login') }}" class="text-sm text-terracotta-500 hover:text-terracotta-400 hover:underline inline-flex items-center gap-2">
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                        Retour à la connexion
                    </a>
                </div>
            </div>
        </div>

        <p class="mt-12 text-[10px] text-ivory-50/20 text-center">
            © {{ date('Y') }} Shop Drop · Accès sécurisé
        </p>
    </div>

</body>
</html>
