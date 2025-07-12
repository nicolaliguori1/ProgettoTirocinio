const CACHE_NAME = 'faro-pwa-v1';
const CACHED_URLS = [
  '/',
  '/index.html',
  '/css/style.css',
  '/js/faro.js',
  '/icons/icona_faro.png'
];

// Install: cache risorse statiche
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(CACHED_URLS))
  );
});

// Activate: pulizia cache vecchie
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
      );
    })
  );
});

// Fetch: cache-first con fallback online
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request).then(cached => {
      return (
        cached ||
        fetch(event.request).catch(() => {
          if (event.request.destination === 'document') {
            return caches.match('/index.html');
          }
          if (event.request.destination === 'image') {
            return caches.match('/icons/icona_faro.png');
          }
          // Fallback testuale
          return new Response('Offline', {
            status: 503,
            statusText: 'Offline',
            headers: { 'Content-Type': 'text/plain' }
          });
        })
      );
    })
  );
});
