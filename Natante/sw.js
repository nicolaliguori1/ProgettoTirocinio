const CACHE_NAME = 'natante-pwa-v2';

const CACHED_URLS = [
  '/',
  '/index.php',
  '/barca.php',
  '/info_barca.php',
  '/manifest.json',
  '/icons/icona_natante-192.png',
  '/icons/icona_natante-512.png',
  '/icons/icona_natante.png'
];


self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) =>
      cache.addAll(CACHED_URLS).catch(() => Promise.resolve())
    )
  );
});


self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)));
    await self.clients.claim();
  })());
});


self.addEventListener('fetch', (event) => {
  const req = event.request;
  const url = new URL(req.url);

 
  if (url.origin !== self.location.origin) return;

  
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


  const isNavigation = req.mode === 'navigate';
  const isGetApi = (req.method === 'GET' && req.destination === '' && /\/api\//.test(url.pathname) );
  if (isNavigation || isGetApi || (req.destination === '' && req.method === 'GET')) {
    event.respondWith(
      fetch(req).then((res) => {
        const copy = res.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(req, copy));
        return res;
      }).catch(async () => {
      
        return (await caches.match(req)) ||
               (await caches.match('/index.php')) ||
               new Response('Offline', { status: 503 });
      })
    );
    return;
  }
});


self.addEventListener('sync', (event) => {
  if (event.tag === 'background-sync') {
    event.waitUntil(syncGPSData());
  }
});

async function syncGPSData() {
  console.log('Background sync GPS data');
}
