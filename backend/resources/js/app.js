// サービスワーカーの登録と購読のコード

// VAPID 公開鍵を設定（`.env` に設定した VAPID 公開鍵を貼り付け）
const publicVapidKey = 'BIwDB_jXar88M2YUV0wqltScFz1B0T_EplbvX79xXV1tDoYbNeUDi7Skw2KJ7BIny07qwu-Xnl5XRoJN18mi9pA';

// サービスワーカーとプッシュ通知がサポートされているか確認
if ('serviceWorker' in navigator && 'PushManager' in window) {
    window.addEventListener('load', function() {
        // サービスワーカーを登録
        navigator.serviceWorker.register('/service-worker.js')
        .then(function(registration) {
            console.log('Service Worker registered with scope:', registration.scope);
            // ユーザーをプッシュ通知に購読
            subscribeUserToPush(registration);
        })
        .catch(function(error) {
            console.log('Service Worker registration failed:', error);
        });
    });
} else {
    console.warn('プッシュ通知がサポートされていません。');
}

// VAPID 公開鍵を適切な形式に変換する関数
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
}

// ユーザーをプッシュ通知に購読する関数
function subscribeUserToPush(registration) {
    const applicationServerKey = urlBase64ToUint8Array(publicVapidKey);
    registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: applicationServerKey
    })
    .then(function(subscription) {
        console.log('User is subscribed:', subscription);

        // 購読情報をサーバーに送信
        sendSubscriptionToServer(subscription);
    })
    .catch(function(err) {
        console.log('Failed to subscribe the user: ', err);
    });
}

// 購読情報をサーバーに送信する関数
function sendSubscriptionToServer(subscription) {
    return fetch('/api/subscribe', {
        method: 'POST',
        body: JSON.stringify(subscription),
        headers: {
            'Content-Type': 'application/json',
            // CSRF トークンが必要な場合は以下を追加
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(function(response) {
        if (!response.ok) {
            throw new Error('Failed to send subscription to server');
        }
        return response.json();
    })
    .then(function(data) {
        console.log('Subscription sent to server:', data);
    })
    .catch(function(err) {
        console.error('Error sending subscription to server:', err);
    });
}
