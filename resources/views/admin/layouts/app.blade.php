<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration') — {{ $banniere?->appelation ?? config('app.name', 'Shop Dropdown') }}</title>

    {{-- Favicon --}}
    @if($banniere?->logo)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $banniere->logo) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $banniere->logo) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $banniere->logo) }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;0,9..144,900;1,9..144,400&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @include('components.meta-pixel')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="antialiased bg-ivory-100 text-ink"
      x-data="{ sidebar: false, userMenu: false }"
      :class="{ 'overflow-hidden lg:overflow-auto': sidebar }">

    @php
        // Définit l'item de nav actif à partir du nom de route courant.
        $routeName = request()->route()?->getName() ?? '';
        $nav = [
            ['label' => 'Tableau de bord', 'route' => 'admin.dashboard',  'icon' => 'fa-chart-line',  'active' => $routeName === 'admin.dashboard'],
            ['label' => 'Commandes',       'route' => 'admin.orders.index','icon' => 'fa-cart-shopping','active' => str_starts_with($routeName, 'admin.orders'), 'badge' => \App\Models\Order::where('status', \App\Enums\OrderStatus::Pending)->count()],
            ['label' => 'Produits',        'route' => 'admin.products.index','icon' => 'fa-box',       'active' => str_starts_with($routeName, 'admin.products')],
            ['label' => 'Catégories',      'route' => 'admin.categories.index','icon' => 'fa-layer-group','active' => str_starts_with($routeName, 'admin.categories')],
            ['label' => 'Clients',      'route' => 'admin.users.index','icon' => 'fa-layer-group','active' => str_starts_with($routeName, 'admin.users')],
            ['label' => 'Bannière', 'route' => 'admin.banniere.edit', 'icon' => 'fa-flag', 'active' => str_starts_with($routeName, 'admin.banniere')],
        ];
    @endphp


    {{-- =============== SIDEBAR DESKTOP =============== --}}
    <aside class="hidden lg:flex fixed inset-y-0 left-0 w-64 xl:w-72 bg-ink text-ivory-50 flex-col z-30">
        {{-- Logo --}}
        {{-- <div class="h-20 px-6 flex items-center gap-3 border-b border-ivory-50/10">
            <span class="w-9 h-9 rounded-full bg-terracotta-500 text-white flex items-center justify-center font-display font-bold">S</span>
            <div>
                <p class="font-display text-lg font-semibold leading-tight">Shop<span class="text-terracotta-500">·</span>Dropdown</p>
                <p class="text-[10px] uppercase tracking-widest text-ivory-50/50 font-semibold">Administration</p>
            </div>
        </div> --}}
        <div class="h-20 px-6 flex items-center gap-3 border-b border-ivory-50/10">
            @if($banniere?->logo)
                <img src="{{ asset('storage/' . $banniere->logo) }}" class="w-9 h-9 rounded-full object-cover" alt="">
            @else
                <span class="w-9 h-9 rounded-full bg-terracotta-500 text-white flex items-center justify-center font-display font-bold">
                    {{ strtoupper(substr($banniere?->appelation ?? 'S', 0, 1)) }}
                </span>
            @endif
            <div>
                <p class="font-display text-lg font-semibold leading-tight">{{ $banniere?->appelation ?? 'Shop' }}</p>
                <p class="text-[10px] uppercase tracking-widest text-ivory-50/50 font-semibold">Administration</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-4 py-6 flex flex-col gap-1">
            <p class="px-3 text-[10px] uppercase tracking-widest text-ivory-50/40 font-semibold mb-2">Pilotage</p>

            @foreach($nav as $item)
                <a href="{{ route($item['route']) }}"
                   class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                          {{ $item['active']
                                ? 'bg-terracotta-500 text-white shadow-soft'
                                : 'text-ivory-50/80 hover:bg-ivory-50/10 hover:text-white' }}">
                    <i class="fa-solid {{ $item['icon'] }} w-5 text-center {{ $item['active'] ? 'text-white' : 'text-ivory-50/50 group-hover:text-white' }}"></i>
                    <span class="flex-1">{{ $item['label'] }}</span>
                    @if(!empty($item['badge']) && $item['badge'] > 0)
                        <span class="min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-bold flex items-center justify-center
                                     {{ $item['active'] ? 'bg-white text-terracotta-500' : 'bg-terracotta-500 text-white' }}">
                            {{ $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>

        {{-- Footer sidebar : retour boutique --}}
        <div class="border-t border-ivory-50/10 p-4">
            <a href="{{ route('home') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-ivory-50/70 hover:bg-ivory-50/10 hover:text-white transition-colors">
                <i class="fa-solid fa-arrow-up-right-from-square w-5 text-center text-ivory-50/50"></i>
                <span>Voir la boutique</span>
            </a>
        </div>
    </aside>

    {{-- =============== DRAWER MOBILE =============== --}}
    <div x-show="sidebar" x-cloak class="fixed inset-0 z-50 lg:hidden" x-transition.opacity>
        <div @click="sidebar = false" class="absolute inset-0 bg-ink/70 backdrop-blur-sm"></div>

        <aside class="absolute top-0 left-0 h-full w-80 max-w-[85vw] bg-ink text-ivory-50 shadow-2xl flex flex-col"
               x-show="sidebar"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full">

            <div class="h-16 px-5 flex items-center justify-between border-b border-ivory-50/10">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-terracotta-500 text-white flex items-center justify-center font-display font-bold text-sm">S</span>
                    <span class="font-display text-lg font-semibold">Shop<span class="text-terracotta-500">·</span>Dropdown</span>
                </div>
                <button @click="sidebar = false" class="w-9 h-9 rounded-full hover:bg-ivory-50/10 flex items-center justify-center" aria-label="Fermer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-4 py-5 flex flex-col gap-1">
                @foreach($nav as $item)
                    <a href="{{ route($item['route']) }}"
                       @click="sidebar = false"
                       class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition-colors
                              {{ $item['active']
                                    ? 'bg-terracotta-500 text-white'
                                    : 'text-ivory-50/80 hover:bg-ivory-50/10 hover:text-white' }}">
                        <i class="fa-solid {{ $item['icon'] }} w-5 text-center"></i>
                        <span class="flex-1">{{ $item['label'] }}</span>
                        @if(!empty($item['badge']) && $item['badge'] > 0)
                            <span class="min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-bold flex items-center justify-center
                                         {{ $item['active'] ? 'bg-white text-terracotta-500' : 'bg-terracotta-500 text-white' }}">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-ivory-50/10 p-4">
                <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-ivory-50/70 hover:bg-ivory-50/10 hover:text-white">
                    <i class="fa-solid fa-arrow-up-right-from-square w-5 text-center"></i>
                    Voir la boutique
                </a>
            </div>
        </aside>
    </div>

    {{-- =============== MAIN AREA =============== --}}
    <div class="lg:pl-64 xl:pl-72 min-h-screen flex flex-col">

        {{-- Top bar --}}
        <header class="sticky top-0 z-20 bg-ivory-100/80 backdrop-blur-md border-b border-ink/5">
            <div class="h-16 lg:h-20 px-4 sm:px-6 lg:px-10 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <button @click="sidebar = true" class="lg:hidden w-10 h-10 rounded-full hover:bg-white flex items-center justify-center flex-shrink-0" aria-label="Menu">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    <div class="min-w-0">
                        @hasSection('breadcrumb')
                            <nav class="text-xs text-ink-muted flex items-center gap-1.5 flex-wrap" aria-label="Fil d'Ariane">
                                @yield('breadcrumb')
                            </nav>
                        @endif
                        <h1 class="font-display text-xl lg:text-2xl font-semibold tracking-tight truncate">
                            @yield('page_title', 'Administration')
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                    @yield('topbar_actions')

                    {{-- User menu --}}
                    <div class="relative" @click.outside="userMenu = false">
                        <button @click="userMenu = !userMenu" class="flex items-center gap-2 pl-1 pr-3 h-10 rounded-full bg-white hover:bg-ivory-200 ring-1 ring-ink/5 transition-colors">
                            <span class="w-8 h-8 rounded-full bg-ink text-ivory-50 flex items-center justify-center text-xs font-bold">
                                {{ auth()->user()->initials }}
                            </span>
                            <span class="hidden sm:inline text-sm font-semibold">{{ auth()->user()->first_name }}</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-ink-muted"></i>
                        </button>

                        <div x-show="userMenu" x-cloak x-transition
                             class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-lift ring-1 ring-ink/5 p-2 z-30">
                            <div class="px-3 py-2 border-b border-ink/5 mb-1">
                                <p class="text-sm font-semibold truncate">{{ auth()->user()->full_name }}</p>
                                <p class="text-xs text-ink-muted truncate">{{ auth()->user()->email }}</p>
                                <span class="inline-flex mt-1 items-center gap-1 text-[10px] font-bold uppercase tracking-wider bg-terracotta-50 text-terracotta-700 px-2 py-0.5 rounded-full">
                                    <i class="fa-solid fa-shield-halved text-[9px]"></i>
                                    {{ auth()->user()->isSuperAdmin() ? 'Super admin' : 'Administrateur' }}
                                </span>
                            </div>
                            <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm hover:bg-ivory-100">
                                <i class="fa-solid fa-arrow-up-right-from-square w-4 text-ink-muted"></i>
                                Voir la boutique
                            </a>
                            <a href="{{ route('password.change') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm hover:bg-ivory-100">
                                <i class="fa-solid fa-key w-4 text-ink-muted"></i>
                                Mot de passe
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-red-600 hover:bg-red-50">
                                    <i class="fa-solid fa-right-from-bracket w-4"></i>
                                    Se déconnecter
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session()->hasAny(['success', 'error', 'warning', 'info']))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
                 class="fixed top-24 right-4 lg:right-8 z-50 max-w-sm">
                @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl shadow-lift px-5 py-4 flex items-start gap-3">
                        <i class="fa-solid fa-circle-check text-lg mt-0.5"></i>
                        <p class="text-sm font-medium flex-1">{{ session('success') }}</p>
                        <button @click="show = false" class="text-emerald-700/60 hover:text-emerald-900"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @elseif(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-900 rounded-2xl shadow-lift px-5 py-4 flex items-start gap-3">
                        <i class="fa-solid fa-circle-exclamation text-lg mt-0.5"></i>
                        <p class="text-sm font-medium flex-1">{{ session('error') }}</p>
                        <button @click="show = false" class="text-red-700/60 hover:text-red-900"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @elseif(session('warning'))
                    <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl shadow-lift px-5 py-4 flex items-start gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-lg mt-0.5"></i>
                        <p class="text-sm font-medium flex-1">{{ session('warning') }}</p>
                        <button @click="show = false"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                @endif
            </div>
        @endif

        {{-- Content --}}
        <main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 lg:py-10">
            @yield('content')
        </main>

        <footer class="px-4 sm:px-6 lg:px-10 py-6 text-xs text-ink-muted border-t border-ink/5">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-2">
                <span>© {{ date('Y') }} Shop Dropdown. Tous droits réservés.</span>
                <span>v1.0 · Administration</span>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
