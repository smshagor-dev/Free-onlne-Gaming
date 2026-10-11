import './bootstrap';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const userId = document.querySelector('meta[name="user-id"]')?.content;

if (import.meta.env.VITE_PUSHER_APP_KEY && import.meta.env.VITE_PUSHER_APP_CLUSTER && userId) {
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    }
});

// Example: listen to private notifications
window.Echo.private('notifications.' + userId)
    .listen('NotificationCreated', (e) => {
        console.log("🎯 New notification:", e.notification);
    });

window.Echo.private('App.Models.User.' + userId).notification((notification) => {
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification('New Notification', { body: notification.message, icon: '/logo.png' });
    }
    const box = document.getElementById('notifications');
    if (box) {
        const item = document.createElement('div');
        item.className = 'p-2 bg-gray-100';
        item.textContent = notification.message;
        box.prepend(item);
    }
});
}
