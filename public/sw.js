"use strict";

const CACHE_NAME = "offline-cache-v1";
const OFFLINE_URL = '/offline.html';
const PRIVATE_PATH_PREFIXES = [
    '/user/',
    '/admin/',
    '/sm-shagor/free-games/admin-main/control-back-office/',
    '/api/',
    '/casino/play',
    '/bonus-play/play',
    '/casino-cashback/play',
    '/vip-bonus-play/play',
    '/user/transactions',
    '/private-files/'
];

const filesToCache = [
    OFFLINE_URL
];

self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(filesToCache))
    );
});

self.addEventListener("fetch", (event) => {
    const url = new URL(event.request.url);
    const isPrivatePath = url.origin === self.location.origin && PRIVATE_PATH_PREFIXES.some((prefix) => url.pathname.startsWith(prefix));

    if (isPrivatePath) {
        event.respondWith(fetch(event.request));
        return;
    }

    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .catch(() => {
                    return caches.match(OFFLINE_URL);
                })
        );
    } else {
        event.respondWith(
            caches.match(event.request)
                .then((response) => {
                    return response || fetch(event.request);
                })
        );
    }
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});
