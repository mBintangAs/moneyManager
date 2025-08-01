const CACHE_NAME = 'money-manager-cache-v1';
const urlsToCache = [
  '/',
  '/favicon.ico',
  '/manifest.json',
  '/pwa-transactions.js',
  '/bootstrap.min.css',
  '/bootstrap.bundle.min.js',
  '/select2.min.css',
  '/select2.min.js',
  '/jquery.min.js',
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        return response || fetch(event.request);
      })
      .catch(() => {
        // Fallback: jika asset gagal di-fetch (misal CDN offline), tetap return Response kosong
        return new Response('', { status: 200, statusText: 'Offline fallback' });
      })
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.filter(name => name !== CACHE_NAME)
          .map(name => caches.delete(name))
      );
    })
  );
});
