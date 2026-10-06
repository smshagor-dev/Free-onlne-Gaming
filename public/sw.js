"use strict";

const CACHE_NAME = "public-static-v2";
const OFFLINE_URL = "/offline.html";
const STATIC_PATHS = [
    OFFLINE_URL,
    "/manifest.json",
    "/logo.png"
];
const PRIVATE_PREFIXES = [
    "/user/",
    "/api/",
    "/auth/",
    "/login",
    "/register",
    "/registration",
    "/verify",
    "/2fa/",
    "/password/",
    "/forgot-password",
    "/private-files/",
    "/sm-shagor/free-games/admin-main/control-back-office/",
    "/casino/play",
    "/bonus-play/",
    "/casino-cashback/",
    "/vip-bonus-play/"
];

self.addEventListener("install", (event) => {
    event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_PATHS)));
    self.skipWaiting();
});

self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => Promise.all(
            cacheNames.filter((name) => name !== CACHE_NAME).map((name) => caches.delete(name))
        )).then(() => self.clients.claim())
    );
});

self.addEventListener("fetch", (event) => {
    const request = event.request;
    if (request.method !== "GET") {
        return;
    }

    const url = new URL(request.url);
    const sameOrigin = url.origin === self.location.origin;
    const privateRequest = !sameOrigin || PRIVATE_PREFIXES.some((prefix) => url.pathname.startsWith(prefix));

    if (request.mode === "navigate") {
        event.respondWith(
            fetch(request, { cache: "no-store" }).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    if (privateRequest) {
        event.respondWith(fetch(request, { cache: "no-store" }));
        return;
    }

    const isPublicStatic = url.pathname.startsWith("/build/") || STATIC_PATHS.includes(url.pathname);
    if (!isPublicStatic) {
        event.respondWith(fetch(request));
        return;
    }

    event.respondWith(
        caches.match(request).then((cached) => cached || fetch(request).then((response) => {
            if (response.ok && response.type === "basic") {
                const copy = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
            }
            return response;
        }))
    );
});
