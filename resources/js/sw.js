// public/sw.js
const CACHE_NAME = 'my-pwa-cache-v1';
const ASSETS = [
  '/',
  '/css/app.css',
  '/js/app.js',
  // أضف مسارات الملفات التي تريد تخزينها (مثل الصور، الخطوط، etc.)
];

// Install Service Worker
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(ASSETS))
  );
});

// Fetch Requests
self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request)
      .then(response => response || fetch(event.request))
  );
});
