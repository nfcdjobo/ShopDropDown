/* Service worker — Shop DropDown
 * Servi à la racine (/sw.js) pour contrôler tout le site.
 * Les pages HTML ne sont JAMAIS mises en cache (panier, CSRF, prix à jour) :
 * on ne garde que les assets statiques, le logo et une page de secours hors-ligne.
 *
 * Logo : la priorité est le logo de la bannière, transmis à l'enregistrement
 * via /sw.js?logo=<url>. L'image en dur (FALLBACK_LOGO) ne sert que si la bannière
 * n'a pas de logo, ou si le fichier du logo est introuvable. */

const VERSION      = 'v2';
const STATIC_CACHE = 'sd-static-' + VERSION;
const IMAGE_CACHE  = 'sd-images-' + VERSION;
const FONT_CACHE   = 'sd-fonts-' + VERSION;
const OFFLINE_URL  = '/offline';
const IMAGE_LIMIT  = 80;

/* Image de secours, utilisée uniquement quand le logo de la bannière n'est pas disponible */
const FALLBACK_LOGO = new URL('/images/icons/icon-192.png', self.location.origin).href;

/* Logo de la bannière, lu dans l'URL d'enregistrement du worker (même origine uniquement) */
const BANNER_LOGO = (() => {
    try {
        const raw = new URL(self.location.href).searchParams.get('logo');
        if (!raw) return null;
        const url = new URL(raw, self.location.origin);
        return url.origin === self.location.origin ? url.href : null;
    } catch {
        return null;
    }
})();

self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        const cache = await caches.open(STATIC_CACHE);

        // 1. Logo de la bannière en premier : c'est la priorité.
        if (BANNER_LOGO) {
            try {
                await cache.add(BANNER_LOGO);
            } catch (err) {
                console.warn('[sw] Logo de la bannière non pré-caché :', BANNER_LOGO, err);
            }
        }

        // 2. Image de secours, gardée au chaud pour le cas où le logo manque
        try {
            await cache.add(FALLBACK_LOGO);
        } catch (err) {
            console.warn('[sw] Image de secours non pré-cachée :', err);
        }

        // 3. Page hors-ligne
        try {
            await cache.add(OFFLINE_URL);
        } catch (err) {
            console.warn('[sw] Page hors-ligne non pré-cachée :', err);
        }

        await self.skipWaiting();
    })());
});

self.addEventListener('activate', (event) => {
    const keep = new Set([STATIC_CACHE, IMAGE_CACHE, FONT_CACHE]);
    event.waitUntil((async () => {
        const names = await caches.keys();
        await Promise.all(names.filter((n) => !keep.has(n)).map((n) => caches.delete(n)));
        if (self.registration.navigationPreload) await self.registration.navigationPreload.enable();
        await self.clients.claim();
    })());
});

async function trimCache(name, max) {
    const cache = await caches.open(name);
    const keys = await cache.keys();
    if (keys.length > max) {
        await Promise.all(keys.slice(0, keys.length - max).map((k) => cache.delete(k)));
    }
}

/* Écrit dans le cache sans jamais lever d'exception : un échec de mise en cache
 * (quota dépassé, par exemple) ne doit pas casser la réponse servie à la page. */
async function putInCache(cacheName, request, response, limit) {
    try {
        const cache = await caches.open(cacheName);
        await cache.put(request, response);
        if (limit) await trimCache(cacheName, limit);
    } catch (err) {
        console.warn('[sw] Mise en cache impossible :', request.url, err);
    }
}

async function cacheFirst(event, cacheName) {
    const request = event.request;
    const cache = await caches.open(cacheName);
    const hit = await cache.match(request);
    if (hit) return hit;

    const res = await fetch(request);
    if (res.ok) event.waitUntil(putInCache(cacheName, request, res.clone()));
    return res;
}

async function staleWhileRevalidate(event, cacheName, limit) {
    const request = event.request;
    const cache = await caches.open(cacheName);
    const hit = await cache.match(request);

    const refresh = fetch(request).then((res) => {
        if (res.ok || res.type === 'opaque') {
            event.waitUntil(putInCache(cacheName, request, res.clone(), limit));
        }
        return res;
    });

    if (hit) {
        // La mise à jour se fait en arrière-plan : si le réseau est coupé, on garde
        // simplement la copie en cache déjà servie, sans erreur à remonter.
        event.waitUntil(refresh.catch(() => { /* hors-ligne : copie en cache conservée */ }));
        return hit;
    }

    // Pas de copie en cache : la réponse (ou l'erreur réseau) vient du réseau
    return refresh;
}

/* Logo de la bannière d'abord ; l'image en dur seulement si le logo est introuvable */
async function bannerLogoWithFallback(event) {
    const cache = await caches.open(STATIC_CACHE);

    const hit = await cache.match(event.request);
    if (hit) {
        // Copie disponible : on la sert et on rafraîchit en arrière-plan
        event.waitUntil(
            fetch(event.request)
                .then((res) => { if (res.ok) return putInCache(STATIC_CACHE, event.request, res.clone()); })
                .catch(() => { /* hors-ligne : copie en cache conservée */ })
        );
        return hit;
    }

    try {
        const res = await fetch(event.request);
        if (res.ok) {
            event.waitUntil(putInCache(STATIC_CACHE, event.request, res.clone()));
            return res;
        }
    } catch {
        // Réseau indisponible : on passe à l'image de secours
    }

    // Le logo de la bannière est introuvable : image en dur en dernier recours
    const fallback = await cache.match(FALLBACK_LOGO);
    if (fallback) return fallback;
    try {
        return await fetch(FALLBACK_LOGO);
    } catch {
        return Response.error();
    }
}

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);

    // 1. Navigation : réseau d'abord, page hors-ligne si le réseau est coupé
    if (request.mode === 'navigate') {
        // Garde le worker en vie jusqu'à la fin (ou l'annulation) de la requête préchargée,
        // sinon Chrome signale « preload request was cancelled before preloadResponse settled »
        if (event.preloadResponse) event.waitUntil(event.preloadResponse.catch(() => {}));

        event.respondWith((async () => {
            try {
                const preload = await event.preloadResponse;
                return preload || await fetch(request);
            } catch {
                // Réseau indisponible : cas normal hors-ligne, on sert la page de secours
                const cache = await caches.open(STATIC_CACHE);
                return (await cache.match(OFFLINE_URL)) || Response.error();
            }
        })());
        return;
    }

    // 2. Logo de la bannière : priorité, avec l'image en dur en secours.
    //    Gardé dans STATIC_CACHE, donc jamais supprimé par la limite de IMAGE_CACHE.
    if (BANNER_LOGO && url.href === BANNER_LOGO) {
        event.respondWith(bannerLogoWithFallback(event));
        return;
    }

    // 3. Image de secours : même cache que le logo
    if (url.href === FALLBACK_LOGO) {
        event.respondWith(staleWhileRevalidate(event, STATIC_CACHE));
        return;
    }

    // 4. Polices Google
    if (url.hostname === 'fonts.googleapis.com' || url.hostname === 'fonts.gstatic.com') {
        event.respondWith(staleWhileRevalidate(event, FONT_CACHE));
        return;
    }

    // Le reste du cross-origin (Meta Pixel, etc.) passe sans interception
    if (url.origin !== self.location.origin) return;

    // 5. Assets Vite (noms hachés → cache-first sans risque)
    if (url.pathname.startsWith('/build/')) {
        event.respondWith(cacheFirst(event, STATIC_CACHE));
        return;
    }

    // 6. Autres images (produits, icônes)
    if (request.destination === 'image' || url.pathname.startsWith('/storage/') || url.pathname.startsWith('/images/')) {
        event.respondWith(staleWhileRevalidate(event, IMAGE_CACHE, IMAGE_LIMIT));
    }
});