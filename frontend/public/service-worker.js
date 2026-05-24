const CACHE_NAME = 'mamun-erp-cache-v1';
const urlsToCache = [
  '/',
  '/index.html',
  '/manifest.json',
  '/offline.html',
  // Add other critical CSS/JS files here
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
        if (response) {
          return response;
        }
        return fetch(event.request).catch(() => {
            if (event.request.mode === 'navigate') {
                return caches.match('/offline.html');
            }
        });
      })
  );
});

// Background sync for offline mutations
self.addEventListener('sync', event => {
  if (event.tag === 'sync-offline-mutations') {
    event.waitUntil(syncOfflineData());
  }
});

async function syncOfflineData() {
    // Logic to push indexedDB cached requests to API
    console.log("Background sync triggered");
}
