/* PWA Natante — Service Worker */
const CACHE_NAME = 'natante-pwa-v2';

const CACHED_URLS = [
  '/',
  '/index.php',
  '/barca.php',
  '/info_barca.php',
  '/manifest.json',
  // icone
  '/icons/icona_natante-192.png',
  '/icons/icona_natante-512.png',
  '/icons/icona_natante.png'
];

// INSTALL: precache di base + attiva subito
self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) =>
      cache.addAll(CACHED_URLS).catch(() => Promise.resolve())
    )
  );
});

// ACTIVATE: pulizia vecchie cache + prendi controllo subito
self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)));
    await self.clients.claim();
  })());
});

// FETCH:
// - asset statici: cache-first
// - navigazioni/API GET: network-first con fallback offline
self.addEventListener('fetch', (event) => {
  const req = event.request;
  const url = new URL(req.url);

  // gestisco solo stessa origin
  if (url.origin !== self.location.origin) return;

  // Asset statici (css/js/img/font): CACHE FIRST
  if (req.destination === 'style' || req.destination === 'script' ||
      req.destination === 'image' || req.destination === 'font') {
    event.respondWith(
      caches.match(req).then((cached) => cached || fetch(req).then((res) => {
        const copy = res.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(req, copy));
        return res;
      }).catch(async () => {
        if (req.destination === 'image') {
          return caches.match('/icons/icona_natante-192.png') || caches.match('/icons/icona_natante.png');
        }
        return new Response('Offline', { status: 503, headers: { 'Content-Type': 'text/plain' } });
      }))
    );
    return;
  }

  // Navigazioni (HTML) e GET API: NETWORK FIRST
  const isNavigation = req.mode === 'navigate';
  const isGetApi = (req.method === 'GET' && req.destination === '' && /\/api\//.test(url.pathname) );
  if (isNavigation || isGetApi || (req.destination === '' && req.method === 'GET')) {
    event.respondWith(
      fetch(req).then((res) => {
        const copy = res.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(req, copy));
        return res;
      }).catch(async () => {
        // fallback offline su richiesta specifica o sulla home
        return (await caches.match(req)) ||
               (await caches.match('/index.php')) ||
               new Response('Offline', { status: 503 });
      })
    );
    return;
  }
});

// Background Sync opzionale (se usi SyncManager)
self.addEventListener('sync', (event) => {
  if (event.tag === 'background-sync') {
    event.waitUntil(syncGPSData());
  }
});

async function syncGPSData() {
  // TODO: implementa il tuo sync (IndexedDB → POST a rete ripristinata)
  console.log('Background sync GPS data');
}
