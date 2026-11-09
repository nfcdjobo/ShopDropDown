{{-- ═══════════════════════════════════════════════════════════════
     PWA : enregistrement du service worker + invitation à installer
     Inclus à la fin du <body> du layout (variable $siteName héritée).
     ═══════════════════════════════════════════════════════════════ --}}

<div id="pwa-banner" role="dialog" aria-live="polite" aria-labelledby="pwa-title" style="display:none;bottom:calc(.75rem + env(safe-area-inset-bottom, 0px))"
    class="fixed inset-x-3 z-[60] sm:inset-x-auto sm:right-5 sm:max-w-sm">
    <div class="bg-white rounded-2xl shadow-soft ring-1 ring-ink/10 p-4 flex items-start gap-3">
        <img src="{{ asset('images/icons/icon-192.png') }}" alt="" width="48" height="48"
            class="w-12 h-12 rounded-xl flex-none">

        <div class="flex-1 min-w-0">
            <p id="pwa-title" class="font-display font-semibold text-base leading-tight">
                Installer {{ $siteName }}
            </p>

            <p id="pwa-text-default" class="mt-1 text-sm text-ink-muted">
                Accédez à la boutique en un geste depuis votre écran d'accueil, même avec une connexion lente.
            </p>

            <p id="pwa-text-ios" style="display:none" class="mt-1 text-sm text-ink-muted">
                Touchez <i class="fa-solid fa-arrow-up-from-bracket mx-0.5"></i> <strong>Partager</strong>, puis
                <strong>« Sur l'écran d'accueil »</strong>.
            </p>

            <div class="mt-3 flex items-center gap-2">
                <button id="pwa-accept" type="button"
                    class="btn-primary px-4 py-2 rounded-full text-sm font-semibold">Installer</button>
                <button id="pwa-dismiss" type="button"
                    class="px-3 py-2 rounded-full text-sm font-medium text-ink-muted hover:bg-ivory-200">Plus tard</button>
            </div>
        </div>

        <button id="pwa-close" type="button" aria-label="Fermer"
            class="w-8 h-8 -mr-1 -mt-1 inline-flex items-center justify-center rounded-full hover:bg-ivory-200 flex-none">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
</div>

<script>
    // ───────── Service worker ─────────
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js', { scope: '/' })
                .catch(function (err) { console.warn('[PWA] SW non enregistré :', err); });
        });
    }

    // ───────── Invitation à l'installation ─────────
    (function () {
        var DISMISS_KEY    = 'sd_pwa_dismissed_at';
        var INSTALLED_KEY  = 'sd_pwa_installed';
        var DISMISS_DAYS   = 7;      // ne pas réafficher avant N jours
        var SHOW_DELAY_MS  = 6000;   // laisser l'utilisateur découvrir la page d'abord

        var deferredPrompt = null;
        var banner   = document.getElementById('pwa-banner');
        var textDef  = document.getElementById('pwa-text-default');
        var textIos  = document.getElementById('pwa-text-ios');
        var accept   = document.getElementById('pwa-accept');
        var buttons  = document.querySelectorAll('[data-pwa-install]');

        function store(method, key, value) {
            try { return localStorage[method](key, value); } catch (e) { return null; }
        }

        function isStandalone() {
            return window.matchMedia('(display-mode: standalone)').matches
                || window.navigator.standalone === true
                || (document.referrer || '').indexOf('android-app://') === 0;
        }

        function isIOS() {
            return /iphone|ipad|ipod/i.test(navigator.userAgent)
                || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        }

        function recentlyDismissed() {
            var at = parseInt(store('getItem', DISMISS_KEY), 10);
            return at && (Date.now() - at) < DISMISS_DAYS * 86400000;
        }

        function alreadyInstalled() {
            return isStandalone() || store('getItem', INSTALLED_KEY) === '1';
        }

        function setButtons(visible) {
            buttons.forEach(function (b) { b.style.display = visible ? '' : 'none'; });
        }

        function showBanner(mode) {
            if (alreadyInstalled()) return;
            var ios = mode === 'ios';
            textDef.style.display = ios ? 'none' : '';
            textIos.style.display = ios ? '' : 'none';
            accept.style.display  = ios ? 'none' : '';
            banner.style.display  = 'block';
        }

        function hideBanner(remember) {
            banner.style.display = 'none';
            if (remember) store('setItem', DISMISS_KEY, String(Date.now()));
        }

        async function install() {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();                       // doit suivre un geste de l'utilisateur
            var choice = await deferredPrompt.userChoice;
            deferredPrompt = null;                         // prompt() ne s'utilise qu'une fois
            hideBanner(choice.outcome !== 'accepted');
            if (choice.outcome !== 'accepted') setButtons(false);
        }

        // Chrome / Edge / Samsung Internet / Opera : le navigateur signale que le site est installable
        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            deferredPrompt = e;
            if (alreadyInstalled()) return;
            setButtons(true);
            if (!recentlyDismissed()) setTimeout(function () { showBanner('prompt'); }, SHOW_DELAY_MS);
        });

        // Installation réussie (par notre bouton ou par l'icône de la barre d'adresse)
        window.addEventListener('appinstalled', function () {
            deferredPrompt = null;
            store('setItem', INSTALLED_KEY, '1');
            hideBanner(false);
            setButtons(false);
            if (typeof fbq !== 'undefined') fbq('trackCustom', 'PWAInstalled');
            if (window.SD && SD.toast) SD.toast('Application installée, merci !', 'success');
        });

        // iOS / Safari : pas d'événement, on explique le geste manuel
        if (isIOS() && !alreadyInstalled()) {
            setButtons(true);
            if (!recentlyDismissed()) setTimeout(function () { showBanner('ios'); }, SHOW_DELAY_MS);
        }

        accept.addEventListener('click', install);
        document.getElementById('pwa-dismiss').addEventListener('click', function () { hideBanner(true); });
        document.getElementById('pwa-close').addEventListener('click', function () { hideBanner(true); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') hideBanner(true); });

        // Boutons « Installer l'application » (menu mobile + pied de page)
        buttons.forEach(function (b) {
            b.addEventListener('click', function () {
                if (deferredPrompt) { install(); }
                else if (isIOS())   { showBanner('ios'); }
            });
        });
    })();
</script>
