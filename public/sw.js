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

// ══════════════════════════════════════════════════════════════════
// WEB PUSH NOTIFICATION ENGINE (PWA HP STATUS BAR & INTENDED LINK)
// ══════════════════════════════════════════════════════════════════

self.addEventListener('push', (event) => {
  let notificationPromise;

  if (event.data) {
    try {
      const data = event.data.json();
      notificationPromise = showNotificationFromData(data);
    } catch (e) {
      // Fallback jika payload data berformat text mentah
      notificationPromise = showNotificationFromData({
        title: 'PembdaHUB',
        body: event.data.text() || 'Ada pembaruan penting di PembdaHUB',
        url: '/notifications'
      });
    }
  } else {
    // Fetch-on-Wakeup Pattern: Ambil payload notifikasi terbaru dari server
    notificationPromise = fetch('/push/latest')
      .then(res => res.json())
      .then(res => {
        if (res.has_notification && res.data) {
          return showNotificationFromData(res.data);
        }
        return showNotificationFromData({
          title: 'PembdaHUB Update',
          body: 'Ada pembaruan pada akun PembdaHUB Anda.',
          url: '/notifications'
        });
      })
      .catch(() => {
        return showNotificationFromData({
          title: 'PembdaHUB',
          body: 'Pemberitahuan aktivitas terbaru.',
          url: '/notifications'
        });
      });
  }

  event.waitUntil(notificationPromise);
});

function showNotificationFromData(data) {
  const title = data.title || 'PembdaHUB';
  const options = {
    body: data.body || 'Ada pembaruan di PembdaHUB',
    icon: data.icon || '/images/icons/icon-192x192.png?v=6',
    badge: data.badge || '/images/icons/icon-192x192.png?v=6',
    data: {
      url: data.url || '/notifications',
      timestamp: data.timestamp || Date.now()
    },
    vibrate: [100, 50, 100],
    requireInteraction: false,
    tag: data.type || 'pembdahub-general'
  };

  return self.registration.showNotification(title, options);
}

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const targetUrl = (event.notification.data && event.notification.data.url) 
    ? event.notification.data.url 
    : '/notifications';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
      // Jika ada tab yang sudah terbuka, fokuskan dan arahkan
      for (let client of windowClients) {
        if (client.url === targetUrl && 'focus' in client) {
          return client.focus();
        }
      }
      // Jika belum ada tab yang cocok, buka window baru
      if (clients.openWindow) {
        return clients.openWindow(targetUrl);
      }
    })
  );
});
