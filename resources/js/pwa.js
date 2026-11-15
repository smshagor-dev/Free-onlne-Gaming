// Activate installable Free Games PWA with a public, offline-only fallback.
// The worker never caches authenticated HTML, APIs, or account responses.
if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker.register("/sw.js", {scope: "/"})
            .catch(() => {
                // The website continues working when PWA support is unavailable.
            });
    });
}
