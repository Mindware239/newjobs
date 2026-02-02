importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js');

function init(cfg) {
  try { firebase.initializeApp(cfg); } catch (e) {}
  try {
    const messaging = firebase.messaging();
    messaging.onBackgroundMessage(function(payload) {
      const title = (payload && payload.notification && payload.notification.title) || (payload && payload.data && payload.data.title) || 'Notification';
      const body = (payload && payload.notification && payload.notification.body) || (payload && payload.data && payload.data.body) || '';
      const link = (payload && payload.data && (payload.data.link || payload.data.click_action || payload.data.url)) || '/';
      const icon = (payload && payload.notification && payload.notification.icon) || '/uploads/Mindware-infotech.png';
      const options = { body: body, icon: icon, data: { link: link }, actions: [{action:'view',title:'View'}, {action:'later',title:'Later'}], requireInteraction: false };
      self.registration.showNotification(title, options);
    });
  } catch (e) {}
}

const cfg = {
  apiKey: self.FCM_WEB_API_KEY || '',
  projectId: self.FCM_WEB_PROJECT_ID || '',
  messagingSenderId: self.FCM_WEB_MESSAGING_SENDER_ID || '',
  appId: self.FCM_WEB_APP_ID || ''
};

if (cfg.apiKey && cfg.projectId && cfg.messagingSenderId && cfg.appId) {
  init(cfg);
} else {
  try {
    fetch('/api/fcm-web-config')
      .then(function(res){ return res.json(); })
      .then(function(data){
        if (data && data.apiKey && data.projectId && data.messagingSenderId && data.appId) {
          init({ apiKey: data.apiKey, projectId: data.projectId, messagingSenderId: data.messagingSenderId, appId: data.appId });
        }
      })
      .catch(function(){});
  } catch (e) {}
}

self.addEventListener('push', function(event) {
  try {
    const payload = event && event.data ? event.data.json() : null;
    const title = (payload && payload.notification && payload.notification.title) || (payload && payload.data && payload.data.title) || 'Notification';
    const body = (payload && payload.notification && payload.notification.body) || (payload && payload.data && payload.data.body) || '';
    const link = (payload && payload.data && (payload.data.link || payload.data.click_action || payload.data.url)) || '/';
    const icon = (payload && payload.notification && payload.notification.icon) || '/uploads/Mindware-infotech.png';
    const options = { body: body, icon: icon, data: { link: link }, actions: [{action:'view',title:'View'}, {action:'later',title:'Later'}], requireInteraction: false };
    event.waitUntil(self.registration.showNotification(title, options));
  } catch (e) {}
});

self.addEventListener('notificationclick', function(event) {
  event.notification.close();
  const target = (event && event.notification && event.notification.data && event.notification.data.link) || '/';
  if (event.action === 'later') { return; }
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(windowClients) {
      for (var i = 0; i < windowClients.length; i++) {
        var client = windowClients[i];
        if (client.url === target && 'focus' in client) return client.focus();
      }
      if (clients.openWindow) return clients.openWindow(target);
    })
  );
});
