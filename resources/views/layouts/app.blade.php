<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    {{-- ═══════════════════════════════════════════════════════════════ CONFIGURATION DE BASE ═══════════════════════════════════════════════════════════════ --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    {{-- ═══════════════════════════════════════════════════════════════ PRÉPARATION DES DONNÉES SEO ═══════════════════════════════════════════════════════════════ --}}
    @php
        $siteName        = $banniere?->appelation ?? config('app.name', 'Shop Dropdown');
        $siteSlogan      = $banniere?->slogan ?? 'Produits soigneusement sélectionnés, livrés rapidement en Côte d\'Ivoire.';
        $rawTitle        = trim($__env->yieldContent('title'));
        $pageTitle       = $rawTitle ? $rawTitle.' | '.$siteName : $siteName;
        $pageDescription = trim($__env->yieldContent('meta_description')) ?: $siteSlogan;
        $pageKeywords    = trim($__env->yieldContent('meta_keywords')) ?: 'boutique en ligne, e-commerce, Côte d\'Ivoire, Abidjan, livraison, paiement à la livraison, '.$siteName;
        $logoUrl         = $banniere?->logo ? asset('storage/'.$banniere->logo) : null;
        $pageImage       = trim($__env->yieldContent('meta_image')) ?: ($logoUrl ?: asset('images/og-default.jpg'));
        $pageUrl         = url()->current();
        $canonicalUrl    = trim($__env->yieldContent('canonical')) ?: $pageUrl;
        $pageType        = trim($__env->yieldContent('og_type')) ?: 'website';
        $robotsContent   = trim($__env->yieldContent('robots')) ?: 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
        $themeColor      = '#C2410C';

        // ─── JSON-LD : Organisation ──────────────────────────────────────
        $orgSchema = [
            '@context'           => 'https://schema.org',
            '@type'              => 'OnlineStore',
            'name'               => $siteName,
            'description'        => $pageDescription,
            'url'                => url('/'),
            'email'              => $banniere?->email ?? 'hello@shopdropdown.ci',
            'telephone'          => $banniere?->telephone_principal ?? '+22500000000',
            'currenciesAccepted' => 'XOF',
            'paymentAccepted'    => 'Cash on Delivery, Mobile Money',
            'address'            => [
                '@type'           => 'PostalAddress',
                'addressLocality' => 'Abidjan',
                'addressRegion'   => 'Abidjan',
                'addressCountry'  => 'CI',
            ],
            'areaServed' => [
                '@type' => 'Country',
                'name'  => "Côte d'Ivoire",
            ],
        ];

        if ($banniere?->slogan) {
            $orgSchema['alternateName'] = $banniere->slogan;
        }

        if ($logoUrl) {
            $orgSchema['logo']  = ['@type' => 'ImageObject', 'url' => $logoUrl];
            $orgSchema['image'] = $logoUrl;
        }

        $contactPoints = [[
            '@type'             => 'ContactPoint',
            'telephone'         => $banniere?->telephone_principal ?? '+22500000000',
            'contactType'       => 'customer service',
            'areaServed'        => 'CI',
            'availableLanguage' => ['French'],
        ]];

        if ($banniere?->numero_whatsapp) {
            $contactPoints[] = [
                '@type'             => 'ContactPoint',
                'telephone'         => $banniere->numero_whatsapp,
                'contactType'       => 'customer support',
                'availableLanguage' => ['French'],
            ];
        }

        $orgSchema['contactPoint'] = $contactPoints;

        if ($banniere?->lien_whatsapp) {
            $orgSchema['sameAs'] = [$banniere->lien_whatsapp];
        }

        // ─── JSON-LD : Site web ──────────────────────────────────────────
        $websiteSchema = [
            '@context'         => 'https://schema.org',
            '@type'            => 'WebSite',
            'name'             => $siteName,
            'url'              => url('/'),
            'inLanguage'       => 'fr-CI',
            'potentialAction'  => [
                '@type'       => 'SearchAction',
                'target'      => [
                    '@type'       => 'EntryPoint',
                    'urlTemplate' => url('/').'/?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    @endphp

    {{-- ═══════════════════════════════════════════════════════════════ SEO PRIMAIRE ═══════════════════════════════════════════════════════════════ --}}
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="keywords" content="{{ $pageKeywords }}">
    <meta name="author" content="{{ $siteName }}">
    <meta name="publisher" content="{{ $siteName }}">
    <meta name="copyright" content="© {{ date('Y') }} {{ $siteName }}">
    <meta name="language" content="French">
    <meta name="revisit-after" content="7 days">
    <meta name="distribution" content="global">
    <meta name="rating" content="general">

    <meta name="robots" content="{{ $robotsContent }}">
    <meta name="googlebot" content="index, follow">
    <meta name="bingbot" content="index, follow">

    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="alternate" hreflang="fr-CI" href="{{ $pageUrl }}">
    <link rel="alternate" hreflang="x-default" href="{{ $pageUrl }}">

    {{-- ═══════════════════════════════════════════════════════════════ GÉOLOCALISATION ═══════════════════════════════════════════════════════════════ --}}
    <meta name="geo.region" content="CI-AB">
    <meta name="geo.placename" content="Abidjan">
    <meta name="geo.position" content="5.3599517;-4.0082563">
    <meta name="ICBM" content="5.3599517, -4.0082563">

    {{-- ═══════════════════════════════════════════════════════════════ OPEN GRAPH ═══════════════════════════════════════════════════════════════ --}}
    <meta property="og:type" content="{{ $pageType }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $pageUrl }}">
    <meta property="og:image" content="{{ $pageImage }}">
    <meta property="og:image:secure_url" content="{{ $pageImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $pageTitle }}">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:locale:alternate" content="en_US">
    @yield('og_extra')

    {{-- ═══════════════════════════════════════════════════════════════ TWITTER / X CARDS ═══════════════════════════════════════════════════════════════ --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $pageImage }}">
    <meta name="twitter:image:alt" content="{{ $pageTitle }}">
    @yield('twitter_extra')

    {{-- ═══════════════════════════════════════════════════════════════ FAVICONS ═══════════════════════════════════════════════════════════════ --}}

   

    {{-- Favicon --}}
    @if($banniere?->logo)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $banniere->logo) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $banniere->logo) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $banniere->logo) }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif

    {{-- ═══════════════════════════════════════════════════════════════ PWA & APPARENCE MOBILE ═══════════════════════════════════════════════════════════════ --}}
    <meta name="theme-color" content="{{ $themeColor }}">
    <meta name="msapplication-TileColor" content="{{ $themeColor }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $siteName }}">
    <meta name="application-name" content="{{ $siteName }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=yes">

    {{-- ═══════════════════════════════════════════════════════════════ PERFORMANCE — DNS PREFETCH & PRECONNECT ═══════════════════════════════════════════════════════════════ --}}
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- ═══════════════════════════════════════════════════════════════ GOOGLE FONTS ═══════════════════════════════════════════════════════════════ --}}
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;0,9..144,900;1,9..144,400&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- ═══════════════════════════════════════════════════════════════ ASSETS APPLICATION ═══════════════════════════════════════════════════════════════ --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- ═══════════════════════════════════════════════════════════════ DONNÉES STRUCTURÉES (JSON-LD) ═══════════════════════════════════════════════════════════════ --}}
    <script type="application/ld+json">
        {!! json_encode($orgSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>

    <script type="application/ld+json">
        {!! json_encode($websiteSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>

    @stack('json-ld')

    {{-- ═══════════════════════════════════════════════════════════════ VÉRIFICATION DES MOTEURS DE RECHERCHE ═══════════════════════════════════════════════════════════════ --}}
    @if(config('services.google.site_verification'))
        <meta name="google-site-verification" content="{{ config('services.google.site_verification') }}">
    @endif

    @if(config('services.bing.site_verification'))
        <meta name="msvalidate.01" content="{{ config('services.bing.site_verification') }}">
    @endif

    {{-- ═══════════════════════════════════════════════════════════════ META PIXEL ═══════════════════════════════════════════════════════════════ --}}
    @include('components.meta-pixel')


    {{-- ═══════════════════════════════════════════════════════════════ SDK GLOBAL SHOP DROPDOWN ═══════════════════════════════════════════════════════════════ --}}
    <script>
        window.SD = window.SD || {};

        window.SD.routes = {
            cartStore:   @json(route('cart.store')),
            cartSummary: @json(route('cart.summary')),
        };

        window.SD.updateCartUI = function (count, formattedSubtotal) {
            var badge = document.getElementById('cart-badge');
            var label = document.getElementById('cart-summary-label');

            if (badge) {
                badge.textContent = count > 0 ? count : '';
                badge.className = badge.className
                    .replace(/\b(flex|hidden)\b/g, '')
                    .trim() + (count > 0 ? ' flex' : ' hidden');
            }
            if (label && formattedSubtotal) {
                label.textContent = formattedSubtotal;
            }
        };

        window.SD.toast = function (message, type) {
            type = type || 'success';
            var old = document.getElementById('sd-toast');
            if (old) old.remove();

            var isOk = type === 'success';
            var el   = document.createElement('div');
            el.id    = 'sd-toast';
            el.style.cssText = [
                'position:fixed','top:5.5rem','right:1.25rem','z-index:9999',
                'max-width:22rem','transition:opacity .3s ease',
            ].join(';');

            el.innerHTML = '<div style="'
                + 'display:flex;align-items:flex-start;gap:.75rem;'
                + 'padding:1rem 1.25rem;border-radius:1rem;'
                + 'background:' + (isOk ? '#ecfdf5' : '#fef2f2') + ';'
                + 'border:1px solid ' + (isOk ? '#a7f3d0' : '#fecaca') + ';'
                + 'color:' + (isOk ? '#065f46' : '#991b1b') + ';'
                + 'box-shadow:0 4px 24px rgba(0,0,0,.08);'
                + 'font-family:inherit;font-size:.875rem;'
                + '">'
                + '<span style="font-size:1.1rem;margin-top:.05rem;">' + (isOk ? '✅' : '❌') + '</span>'
                + '<span style="font-weight:500;">' + message + '</span>'
                + '</div>';

            document.body.appendChild(el);
            setTimeout(function () {
                el.style.opacity = '0';
                setTimeout(function () { el.remove(); }, 350);
            }, 3500);
        };

        window.SD.addToCart = async function (productId, quantity, triggerEl) {
            if (triggerEl) {
                triggerEl.disabled = true;
                triggerEl.style.transform = 'scale(.95)';
            }

            try {
                var res  = await fetch(window.SD.routes.cartStore, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || '',
                    },
                    body: JSON.stringify({ product_id: productId, quantity: quantity }),
                });

                var json = await res.json();

                if (json.success) {
                    window.SD.updateCartUI(json.items_count || 0, json.formatted_subtotal || '');
                    window.SD.toast(json.message || 'Article ajouté au panier !', 'success');

                    if (typeof fbq !== 'undefined') {
                        fbq('track', 'AddToCart', {
                            content_ids: [productId],
                            content_type: 'product',
                            currency: 'XOF'
                        });
                    }
                } else {
                    window.SD.toast(json.message || "Impossible d'ajouter l'article.", 'error');
                }
            } catch (err) {
                console.error('[SD] addToCart:', err);
                window.SD.toast('Erreur réseau. Veuillez réessayer.', 'error');
            } finally {
                if (triggerEl) {
                    triggerEl.disabled = false;
                    triggerEl.style.transform = '';
                }
            }
        };
    </script>

    @stack('head')
</head>

<body class="antialiased" x-data="{ mobileMenu: false }" :class="{ 'overflow-hidden lg:overflow-auto': mobileMenu }">

    {{-- =============== NAVIGATION =============== --}}
    <header class="sticky top-0 z-40 bg-ivory-50/80 backdrop-blur-md border-b border-ink/5">
        <nav class="max-w-7xl mx-auto px-5 lg:px-10 h-16 lg:h-20 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                <span
                    class="w-8 h-8 rounded-full bg-ink text-ivory-50 flex items-center justify-center font-display font-bold text-sm">S</span>
                <span class="font-display text-xl lg:text-2xl font-semibold tracking-tight">Shop<span
                        class="text-terracotta-500">·</span>Dropdown</span>
            </a>

            <div class="hidden lg:flex items-center gap-10">
                <a href="{{ route('home') }}"
                    class="text-sm font-medium hover:text-terracotta-500 transition-colors">Boutique</a>
                <a href="{{ route('home') }}#featured"
                    class="text-sm font-medium hover:text-terracotta-500 transition-colors">Nos coups de cœur</a>
                <a href="{{ route('home') }}#about"
                    class="text-sm font-medium hover:text-terracotta-500 transition-colors">À propos</a>
                <a href="{{ route('home') }}#contact"
                    class="text-sm font-medium hover:text-terracotta-500 transition-colors">Contact</a>
            </div>

            <div class="flex items-center gap-2 lg:gap-4">
                {{-- @auth
                    <a href="{{ route('customer.dashboard') }}" class="hidden sm:inline-flex items-center gap-2 text-sm font-medium hover:text-terracotta-500 transition-colors">
                        <i class="fa-solid fa-user text-base"></i>
                        <span class="hidden xl:inline">Mon compte</span>
                    </a>
                @else
                    <a href="{{ route('checkout.index') }}"
                        class="hidden sm:inline-flex items-center gap-2 text-sm font-medium hover:text-terracotta-500 transition-colors">
                        <i class="fa-solid fa-user text-base"></i>
                        <span class="hidden xl:inline">Se connecter</span>
                    </a>
                @endauth --}}
                @auth
    <div x-data="{ open: false }" @click.outside="open = false" class="relative hidden sm:block">
        <button @click="open = !open"
                class="inline-flex items-center gap-2 text-sm font-medium hover:text-terracotta-500 transition-colors"
                :aria-expanded="open" aria-haspopup="true">
            <span class="w-8 h-8 rounded-full bg-ink text-ivory-50 flex items-center justify-center text-xs font-bold">
                {{ auth()->user()->initials }}
            </span>
            <span class="hidden xl:inline">{{ auth()->user()->first_name ?: 'Mon compte' }}</span>
            <i class="fa-solid fa-chevron-down text-[10px] transition-transform"
               :class="open ? 'rotate-180' : ''"></i>
        </button>

        <div x-show="open" x-cloak x-transition.opacity.duration.150ms
             class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-soft ring-1 ring-ink/5 overflow-hidden">
            <div class="px-4 py-3 border-b border-ink/5">
                <p class="text-xs text-ink-muted">Connecté en tant que</p>
                <p class="text-sm font-semibold truncate">{{ auth()->user()->email }}</p>
            </div>
            <a href="{{ route('customer.dashboard') }}"
               class="flex items-center gap-3 px-4 py-3 text-sm hover:bg-ivory-100 transition-colors">
                <i class="fa-solid fa-gauge-high text-ink-muted w-4"></i>
                Tableau de bord
            </a>
            <a href="{{ route('customer.orders.index') }}"
               class="flex items-center gap-3 px-4 py-3 text-sm hover:bg-ivory-100 transition-colors">
                <i class="fa-solid fa-bag-shopping text-ink-muted w-4"></i>
                Mes commandes
            </a>
            <a href="{{ route('customer.profile.edit') }}"
               class="flex items-center gap-3 px-4 py-3 text-sm hover:bg-ivory-100 transition-colors">
                <i class="fa-solid fa-user-pen text-ink-muted w-4"></i>
                Mon profil
            </a>
            <form method="POST" action="{{ route('logout') }}" class="border-t border-ink/5">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                    <i class="fa-solid fa-right-from-bracket w-4"></i>
                    Se déconnecter
                </button>
            </form>
        </div>
    </div>
@else
    <a href="{{ route('checkout.index') }}"
        class="hidden sm:inline-flex items-center gap-2 text-sm font-medium hover:text-terracotta-500 transition-colors">
        <i class="fa-solid fa-user text-base"></i>
        <span class="hidden xl:inline">Se connecter</span>
    </a>
@endauth

                <a href="{{ route('cart.index') }}"
                    class="relative inline-flex items-center gap-2 px-3 lg:px-4 h-10 rounded-full bg-ink text-ivory-50 hover:bg-terracotta-500 transition-colors">
                    <i class="fa-solid fa-bag-shopping text-base"></i>
                    <span id="cart-summary-label" class="text-sm font-semibold hidden sm:inline">
                        {{ $cart->formatted_subtotal ?? '0 FCFA' }}
                    </span>
                    <span id="cart-badge"
                        class="{{ isset($cart) && $cart->items_count > 0 ? 'flex' : 'hidden' }} absolute -top-1 -right-1 w-5 h-5 bg-terracotta-500 text-white text-[10px] font-bold rounded-full items-center justify-center ring-2 ring-ivory-50">
                        {{ $cart->items_count ?? '' }}
                    </span>
                </a>

                <button @click="mobileMenu = true"
                    class="lg:hidden w-10 h-10 inline-flex items-center justify-center rounded-full hover:bg-ivory-200"
                    aria-label="Ouvrir le menu">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </div>
        </nav>
    </header>

    {{-- =============== MOBILE DRAWER =============== --}}
    <div x-show="mobileMenu" x-cloak class="fixed inset-0 z-50 lg:hidden" x-transition.opacity>
        <div @click="mobileMenu = false" class="absolute inset-0 bg-ink/60 backdrop-blur-sm"></div>

        <aside class="absolute top-0 right-0 h-full w-full sm:w-96 bg-ivory-50 shadow-2xl flex flex-col"
            x-show="mobileMenu" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full">
            <div class="flex items-center justify-between px-6 h-16 border-b border-ink/5">
                <span class="font-display text-xl font-semibold">Menu</span>
                <button @click="mobileMenu = false"
                    class="w-10 h-10 inline-flex items-center justify-center rounded-full hover:bg-ivory-200"
                    aria-label="Fermer le menu">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-6 py-8 flex flex-col gap-1">
                @foreach ([['Boutique', route('home')], ['Coups de cœur', route('home') . '#featured'], ['À propos', route('home') . '#about'], ['Contact', route('home') . '#contact']] as [$label, $url])
                    <a @click="mobileMenu = false" href="{{ $url }}"
                        class="py-4 text-2xl font-display font-semibold border-b border-ink/5 flex items-center justify-between hover:text-terracotta-500 transition-colors">
                        {{ $label }}
                        <i class="fa-solid fa-arrow-right text-base text-ink-muted"></i>
                    </a>
                @endforeach

                <div class="mt-8 flex flex-col gap-3">
                    @auth
                        <a href="{{ route('customer.dashboard') }}"
                            class="btn-primary px-5 py-3 rounded-full text-center text-sm font-semibold">
                            <i class="fa-solid fa-user mr-2"></i> Mon compte
                        </a>
                    @else
                        <a href="{{ route('checkout.index') }}"
                            class="btn-ghost px-5 py-3 rounded-full text-center text-sm font-semibold">
                            <i class="fa-solid fa-right-to-bracket mr-2"></i> Se connecter
                        </a>
                    @endauth
                    <a href="{{ route('cart.index') }}"
                        class="btn-primary px-5 py-3 rounded-full text-center text-sm font-semibold">
                        <i class="fa-solid fa-bag-shopping mr-2"></i>
                        Voir mon panier
                        @if (isset($cart) && $cart->items_count > 0)
                            · {{ $cart->items_count }}
                        @endif
                    </a>
                </div>
            </nav>

            <div class="px-6 py-6 border-t border-ink/5 text-xs text-ink-muted flex items-center gap-2">
                <i class="fa-solid fa-location-dot"></i>
                Livraison partout en Côte d'Ivoire
            </div>
        </aside>
    </div>

    {{-- =============== FLASH MESSAGES =============== --}}
    @if (session()->hasAny(['success', 'error', 'warning']))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4500)" x-transition
            class="fixed top-24 right-5 z-50 max-w-sm">
            @if (session('success'))
                <div
                    class="bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl shadow-soft px-5 py-4 flex items-start gap-3">
                    <i class="fa-solid fa-circle-check text-lg mt-0.5"></i>
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
            @elseif(session('error'))
                <div
                    class="bg-red-50 border border-red-200 text-red-900 rounded-2xl shadow-soft px-5 py-4 flex items-start gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-lg mt-0.5"></i>
                    <p class="text-sm font-medium">{{ session('error') }}</p>
                </div>
            @elseif(session('warning'))
                <div
                    class="bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl shadow-soft px-5 py-4 flex items-start gap-3">
                    <i class="fa-solid fa-circle-info text-lg mt-0.5"></i>
                    <p class="text-sm font-medium">{{ session('warning') }}</p>
                </div>
            @endif
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    {{-- =============== FOOTER =============== --}}
    <footer id="contact" class="bg-ink text-ivory-50 mt-24">
        <div class="max-w-7xl mx-auto px-5 lg:px-10 py-16 lg:py-24 grid md:grid-cols-2 lg:grid-cols-4 gap-12">
            <div class="lg:col-span-2">
                <h3 class="font-display text-3xl lg:text-4xl font-semibold leading-tight">
                    Commandez aujourd'hui,<br>
                    <span class="text-terracotta-400 italic">recevez vite.</span>
                </h3>
                <p class="mt-4 text-ivory-50/70 max-w-md">Shop Dropdown vous livre partout en Côte d'Ivoire avec soin
                    et rapidité. Paiement à la livraison, sans friction.</p>
            </div>

            <div>
                <h4 class="text-sm font-semibold uppercase tracking-widest text-ivory-50/50 mb-4">Navigation</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:text-terracotta-400">Accueil</a></li>
                    <li><a href="{{ route('cart.index') }}" class="hover:text-terracotta-400">Panier</a></li>
                    <li><a href="{{ route('checkout.index') }}" class="hover:text-terracotta-400">Commander</a></li>
                    @auth<li><a href="{{ route('customer.dashboard') }}" class="hover:text-terracotta-400">Mon
                            compte</a></li>@endauth
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold uppercase tracking-widest text-ivory-50/50 mb-4">Contact</h4>
                <ul class="space-y-2 text-sm text-ivory-50/70">
                    <li><i class="fa-solid fa-location-dot mr-2 text-terracotta-400"></i>Abidjan, Côte d'Ivoire</li>
                    <li><i class="fa-solid fa-envelope mr-2 text-terracotta-400"></i>hello@shopdropdown.ci</li>
                    <li><i class="fa-solid fa-phone mr-2 text-terracotta-400"></i>+225 00 00 00 00</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-ivory-50/10">
            <div
                class="max-w-7xl mx-auto px-5 lg:px-10 py-6 flex flex-col sm:flex-row justify-between gap-3 text-xs text-ivory-50/50">
                <p>© {{ date('Y') }} Shop Dropdown. Tous droits réservés.</p>
                <p>Fait avec soin à Abidjan.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
