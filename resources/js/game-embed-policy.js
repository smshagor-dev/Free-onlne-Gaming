/**
 * First-party-only game ad cleanup.
 *
 * This never intercepts network requests, changes provider scripts, proxies
 * a third-party game, or reaches into a cross-origin iframe. Only game markup
 * explicitly marked as site-owned is eligible.
 */
const OWNED_AD_SELECTORS = ['[data-owned-ad]', '.owned-game-ad'];
const selector = OWNED_AD_SELECTORS.join(', ');

document.querySelectorAll('iframe[data-embed-mode="same_origin"][data-owned-ad-cleanup="1"]')
    .forEach(frame => {
        let observer = null;

        frame.addEventListener('load', () => {
            observer?.disconnect();
            observer = null;

            try {
                const requested = new URL(frame.getAttribute('src'), window.location.href);
                if (requested.origin !== window.location.origin) return;

                const windowInFrame = frame.contentWindow;
                if (!windowInFrame || windowInFrame.location.origin !== window.location.origin) return;

                const doc = windowInFrame.document;
                if (!doc?.documentElement) return;

                const removeOwnedAds = () => {
                    doc.querySelectorAll(selector).forEach(element => element.remove());
                };

                removeOwnedAds();
                observer = new MutationObserver(() => removeOwnedAds());
                observer.observe(doc.documentElement, {childList: true, subtree: true});
            } catch {
                // Browser blocks DOM access when a game redirects to a
                // third-party origin. This is expected and must not be bypassed.
                observer?.disconnect();
                observer = null;
            }
        });
    });
