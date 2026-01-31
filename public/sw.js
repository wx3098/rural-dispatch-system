self.addEventListener('push', event => {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    const data = event.data?.json() ?? {};
    const title = data.title || '配送依頼のお知らせ';
    const message = data.message || '新しい依頼が届きました。確認してください。';
    const icon = '/icon.png';

    event.waitUntil(
        self.registration.showNotification(title, {
            body: message,
            icon: icon,
            badge: icon,
        })
    );
});

// 通知をクリックした時の動き
self.addEventListener('notificationclick', event => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow('/')
    );
});