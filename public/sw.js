"use strict";

// Cache only public, versioned app-shell assets. Never persist account pages,
// private documents, API responses, or authenticated HTML.
const PREFIX = "free-games-static-";
const CACHE_NAME = PREFIX + "v2";
const OFFLINE_URL = "/offline.html";
const STATIC_URLS = [
    OFFLINE_URL,
    "/manifest.json",
    "/favicon.svg",
    "/icons/free-games-192.png",
    "/icons/free-games-512.png"
];
const STATIC_PATHS = new Set(STATIC_URLS);
const PRIVATE_PREFIXES = [
    "/user/",
    "/admin/",
    "/sm-shagor/free-games/admin-main/control-back-office/",
    "/api/",
    "/private-files/",
    "/login",
    "/registration",
    "/verify",
    "/forgot-password",
    "/password/",
    "/reset-password",
    "/2fa/",
];

self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(STATIC_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(
                keys.filter(key => key.startsWith(PREFIX) && key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener("fetch", (event) => {
    const request = event.request;
    if (request.method !== "GET") return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    if (PRIVATE_PREFIXES.some(prefix => url.pathname.startsWith(prefix))) {
        // Let the network process authentication and other sensitive pages.
        return;
    }

    if (request.mode === "navigate") {
        event.respondWith(
            fetch(request).catch(async () =>
                (await caches.match(OFFLINE_URL)) ||
                Response.error()
            )
        );
        return;
    }

    if (STATIC_PATHS.has(url.pathname)) {
        event.respondWith(
            caches.match(url.pathname).then(cached => cached || fetch(request))
        );
    }
});
