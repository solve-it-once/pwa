
const CACHE_URLS = [/*cacheUrls*/];
const CACHE_OFFLINE_IMAGE = 'offline-image.png';
// @todo add all images from the manifest.
CACHE_URLS.push(CACHE_OFFLINE_IMAGE);

// Perform install steps
self.addEventListener('install', function (event) {
  caches.open('pwa')
    .then((cache) => cache.addAll(CACHE_URLS));
  // Use the service woker ASAP.
  event.waitUntil(self.skipWaiting());
});

/**
 *  use network with cache fallback.
 */
self.addEventListener('fetch', function (event) {
  /**
   * @param {Response} response
   *
   * @return {Promise}
   */
  function cacheNetworkResponse(response) {
    // Don't cache redirects or errors.
    if (response.ok) {
      if (response.headers.get('Content-length') > 3 * 1024 * 1024) {
        console.log('Response not cacheable, too big: ', response)
      }
      // Copy now and not in the then() because by that time it's too late,
      // the request has already been used and can't be touched again.
      caches
        .open('pwa')
        .then((cache) => cache.put(event.request, response));
    }
    else {
      console.log("Response not cacheable: ", response);
    }
    return response.clone();
  }

  function networkWithCacheFallback (request) {
    return fetch(request)
      .then(cacheNetworkResponse)
      .catch((error) => {
        const response = caches.match(request);
        if (!response) {
          return Promise.reject(new Error('Ressource not in cache'));
        }
        return response;
      })
      .catch((error) => caches.match('/offline'));
  }

  const url = new URL(event.request.url);
  const isMethodGet = event.request.method === 'GET';
  const includedProtocol = ['http:', 'https:'].indexOf(url.protocol) !== -1;

  // Make sure the url is one we don't exclude from cache.
  if (isMethodGet && includedProtocol) {
    event.respondWith(networkWithCacheFallback(event.request));
  }
  else {
    console.log('Excluded URL: ', event.request.url);
  }
});

