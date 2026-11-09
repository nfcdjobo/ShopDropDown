/* Service worker — Shop Drop
 * Servi à la racine (/sw.js) pour contrôler tout le site.
 * Les pages HTML ne sont JAMAIS mises en cache (panier, CSRF, prix à jour) :
 * on ne garde que les assets statiques et une page de secours hors-ligne. */

const VERSION      = 'v1';
const STATIC_CACHE = 'sd-static-' + VERSION;
const IMAGE_CACHE  = 'sd-images-' + VERSION;
const FONT_CACHE   = 'sd-fonts-' + VERSION;
const OFFLINE_URL  = '/offline';
const IMAGE_LIMIT  = 80;

const PRECACHE = [OFFLINE_URL, '/images/icons/icon-192.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    const keep = [STATIC_CACHE, IMAGE_CACHE, FONT_CACHE];
    event.waitUntil((async () => {
        const names = await caches.keys();
        await Promise.all(names.filter((n) => !keep.includes(n)).map((n) => caches.delete(n)));
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

async function cacheFirst(request, cacheName) {
    const cache = await caches.open(cacheName);
    const hit = await cache.match(request);
    if (hit) return hit;
    const res = await fetch(request);
    if (res.ok) cache.put(request, res.clone());
    return res;
}

async function staleWhileRevalidate(request, cacheName, limit) {
    const cache = await caches.open(cacheName);
    const hit = await cache.match(request);
    const network = fetch(request).then((res) => {
        if (res.ok || res.type === 'opaque') {
            cache.put(request, res.clone());
            if (limit) trimCache(cacheName, limit);
        }
        return res;
    }).catch(() => hit);
    return hit || network;
}

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);

    // 1. Navigation : réseau d'abord, page hors-ligne si le réseau est coupé
    if (request.mode === 'navigate') {
        event.respondWith((async () => {
            try {
                const preload = await event.preloadResponse;
                return preload || await fetch(request);
            } catch (err) {
                const cache = await caches.open(STATIC_CACHE);
                return (await cache.match(OFFLINE_URL)) || Response.error();
            }
        })());
        return;
    }

    // 2. Polices Google
    if (url.hostname === 'fonts.googleapis.com' || url.hostname === 'fonts.gstatic.com') {
        event.respondWith(staleWhileRevalidate(request, FONT_CACHE));
        return;
    }

    // Le reste du cross-origin (Meta Pixel, etc.) passe sans interception
    if (url.origin !== self.location.origin) return;

    // 3. Assets Vite (noms hachés → cache-first sans risque)
    if (url.pathname.startsWith('/build/')) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    // 4. Images (produits, logo, icônes)
    if (request.destination === 'image' || url.pathname.startsWith('/storage/') || url.pathname.startsWith('/images/')) {
        event.respondWith(staleWhileRevalidate(request, IMAGE_CACHE, IMAGE_LIMIT));
    }
});
