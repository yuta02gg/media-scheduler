self.addEventListener('push', function(event) {
  var data = event.data.json();
  event.waitUntil(
      self.registration.showNotification(data.title, {
          body: data.body,
          icon: data.icon || '/images/icons/icon-192x192.png', // アイコンのパスを指定
          data: data.url || '/' // 通知クリック時のURL
      })
  );
});

self.addEventListener('notificationclick', function(event) {
  event.notification.close();
  event.waitUntil(
      clients.openWindow(event.notification.data)
  );
});
