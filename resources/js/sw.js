// public/sw.js
const CACHE_NAME = 'my-pwa-cache-v1';
const ASSETS = [
  '/',
  '/css/app.css',
  '/js/app.js',
  // أضف مسارات الملفات التي تريد تخزينها (مثل الصور، الخطوط، etc.)
];

// تثبيت الـ ServiceWorker
self.addEventListener('install', (event) => {
  self.skipWaiting(); // يتخطى الانتظار ويُفعل الـ ServiceWorker الجديد فوراً
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(ASSETS))
  );
});

// تفعيل الـ ServiceWorker وتنظيف الكاش القديم (إن وجد)
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
    }).then(() => self.clients.claim()) // يجعل الـ ServiceWorker يسيطر على الصفحات فوراً بعد التفعيل
  );
});

// اعتراض الطلبات وتقديم الرد من الكاش أو عن طريق الشبكة
self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        // إذا وُجد في الكاش، يتم إرجاع النسخة المخزنة
        return response || fetch(event.request);
      })
  );
});
