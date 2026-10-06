import './bootstrap';
import '../css/site.css';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER;

if (csrf && userId && pusherKey && pusherCluster) {
    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: pusherCluster,
        forceTLS: true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': csrf,
            },
        },
    });

    window.Echo.private(`notifications.${userId}`)
        .listen('NotificationCreated', () => {
            window.dispatchEvent(new CustomEvent('notification-created'));
        });
}

if (import.meta.env.PROD && 'serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // PWA registration is optional and must never block the application.
        });
    });
}
