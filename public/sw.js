const CACHE_NAME = 'pembdahub-mobile-v7';
const urlsToCache = [
  '/m/',
  '/m/dashboard',
  '/manifest.json?v=6',
  '/images/icons/icon-192x192.png?v=6',
  '/images/icons/icon-512x512.png?v=6'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(urlsToCache);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cache) => {
          if (cache !== CACHE_NAME) {
            return caches.delete(cache);
          }
        })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  // Only handle GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  // Do not intercept build assets, API calls, or external CDN requests
  // Let the browser handle these natively
  try {
    const url = new URL(event.request.url);
    if (
      url.pathname.startsWith('/build/') ||
      url.pathname.startsWith('/api/') ||
      url.hostname !== self.location.hostname
    ) {
      return;
    }
  } catch (e) {
    return;
  }

  // Network first policy for live Laravel dynamic pages
  event.respondWith(
    fetch(event.request).catch(() => {
      return caches.match(event.request);
    })
  );
});
