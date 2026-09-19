/*
 * VINYLWAVE service worker.
 * Strategy:
 *  - Static assets (css/js/img/fonts): cache-first (versioned by CACHE_VERSION)
 *  - Catalog pages: network-first with offline fallback to the cached shell
 *  - POST requests and admin pages: never cached
 */
const CACHE_VERSION = 'vinylwave-v1';
const OFFLINE_URLS = [
    'index.php',
    'assets/css/app.css',
    'assets/js/app.js',
    'manifest.json'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_VERSION)
            .then((cache) => cache.addAll(OFFLINE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys.filter((k) => k !== CACHE_VERSION).map((k) => caches.delete(k))
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Only handle same-origin GETs
    if (request.method !== 'GET' || url.origin !== self.location.origin) return;
    // Never cache admin pages or JSON endpoints
    if (url.searchParams.get('page') === 'admin' || url.searchParams.get('page') === 'load-more') return;

    // Static assets: cache-first
    if (url.pathname.includes('/assets/') || url.pathname.includes('/uploads/')) {
        event.respondWith(
            caches.match(request).then((cached) => cached ||
                fetch(request).then((response) => {
                    const copy = response.clone();
                    caches.open(CACHE_VERSION).then((cache) => cache.put(request, copy));
                    return response;
                })
            )
        );
        return;
    }

    // Pages: network-first, fall back to cache (offline catalog browsing)
    event.respondWith(
        fetch(request)
            .then((response) => {
                const copy = response.clone();
                caches.open(CACHE_VERSION).then((cache) => cache.put(request, copy));
                return response;
            })
            .catch(() => caches.match(request).then((cached) => cached || caches.match('index.php')))
    );
});
