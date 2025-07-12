const CACHE_NAME = 'natante-pwa-v1';
const CACHED_URLS = [
    '/',
    '/css/style.css',
    '/js/app.js',
    '/js/gps.js',
    '/js/sync.js',
    '/icons/icona_natante.png'
];

// Install
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(CACHED_URLS);
        })
    );
});

// Fetch
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request).then(response => {
            if (response) {
                return response;
            }
            
            return fetch(event.request).then(response => {
                // Cache nuove risorse
                if (response.status === 200) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            }).catch(() => {
                // Fallback offline per API calls
                if (event.request.url.includes('/api/')) {
                    return new Response(JSON.stringify({
                        error: 'Offline',
                        message: 'Dati salvati localmente'
                    }), {
                        headers: { 'Content-Type': 'application/json' }
                    });
                }
            });
        })
    );
});



// Background Sync
self.addEventListener('sync', event => {
    if (event.tag === 'background-sync') {
        event.waitUntil(syncGPSData());
    }
});

async function syncGPSData() {
    // Implementa sync in background quando torna la connessione
    console.log('Background sync GPS data');
}

