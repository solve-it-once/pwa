if ('serviceWorker' in navigator) {

  navigator.serviceWorker.register('/serviceworker-pwa.js', { scope: '/' });

  // Reload page when user is back online on a fallback offline page.
  window.addEventListener('online', function () {
    const loc = window.location;
    // If the page served is the offline fallback, try a refresh when user
    // get back online.
    if (loc.pathname !== '/offline' && document.querySelector('[data-drupal-pwa-offline]')) {
      loc.reload();
    }
  });
}
/*
In case you want to unregister the SW during testing:

navigator.serviceWorker.getRegistration()
  .then(function(registration) {
    registration.unregister();
  });

 */
