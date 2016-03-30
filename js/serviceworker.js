/**
 * @file
 * Adapted from https://github.com/GoogleChrome/samples/tree/gh-pages/service-worker
 * and https://jakearchibald.com/2014/offline-cookbook/
 */

"use strict";

// If at any point you want to force pages that use this service worker to start using a fresh
// cache, then increment the CACHE_VERSION value. It will kick off the service worker update
// flow and the old cache(s) will be purged as part of the activate event handler when the
// updated service worker is activated.
var CACHE_VERSION = 1/*cacheVersion*/;
var CACHE_EXCLUDE = [/*cacheConditionsExclude*/].map(function (r) {return new RegExp(r);});
var CACHE_URLS = [/*cacheUrls*/];
var CACHE_OFFLINE_IMAGE = 'offline-image.png';
// @todo add all images from the manifest.
CACHE_URLS.push(CACHE_OFFLINE_IMAGE);

var CURRENT_CACHE = 'all-cache-v' + CACHE_VERSION;

// Perform install steps
self.addEventListener('install', function (event) {
  // Use the service woker ASAP.
  var tasks = [self.skipWaiting()];
  if (CACHE_URLS.length) {
    tasks.push(caches
      .open(CURRENT_CACHE)
      .then(function (cache) {
        return cache.addAll(CACHE_URLS);
      }));
  }
  event.waitUntil(Promise.all(tasks));
});

self.addEventListener('activate', function(event) {
  // Delete all caches that are not CURRENT_CACHE.
  var tasks = [
    self.clients.claim(),
    caches.keys().then(function(cacheNames) {
      return Promise.all(
        cacheNames.map(function(cacheName) {
          // Delete any cache that doesn't have our version.
          if (CURRENT_CACHE !== cacheName) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  ];

  event.waitUntil(Promise.all(tasks));
});

/**
 * @todo move that when we start using plugins.
 *
 * @param {string} url
 *
 * @return {Function}
 */
function urlNotExcluded(url) {
  return function (condition) {
    return !condition.test(url);
  }
}

/**
 * Default offline page.
 *
 * @param {object} error
 *
 * @return {Response}
 */
function catchOffline(error) {
  return caches.match('/offline');
}

/**
 * Default offline Image.
 *
 * @param {object} error
 *
 * @return {Response}
 */
function catchOfflineImage(error) {
  return caches.match(CACHE_OFFLINE_IMAGE);
}

/**
 * Default catch callback.
 *
 * @param {Error} error
 */
function logError(error) {
  console.log(error);
}

/**
 * Helper to make sure we don't cache http errors.
 *
 * @param {Response} response
 *
 * @return {boolean}
 */
function isCacheableResponse(response) {
  // Don't cache HTTP errors or redirects.
  return response.status < 300;
}

/**
 * Test if an asset should be cached.
 *
 * @param {string} assetUrl
 *
 * @return {boolean}
 */
function isCacheableAsset(assetUrl) {

  // Url is not an asset, don't cache.
  if (!isAssetUrl(assetUrl)) {
    return false;
  }
  // It's an asset but not an image, always cache.
  if (!isImageUrl(assetUrl)) {
    return true;
  }
  // If it looks like an image, only cache images that are part of
  // assets cached on install.
  var assetPath = assetUrl.replace((new URL(assetUrl)).origin, '');
  return CACHE_URLS.some(function (url) { return assetPath === url; });
}

/**
 * Helper for Assets files.
 *
 * @param {string} assetUrl
 *
 * @return {boolean}
 */
function isAssetUrl(assetUrl) {
  return /\.(js|css|jpe?g|png|gif|svg|webp)\??/.test(assetUrl);
}

/**
 * Helper for image files.
 *
 * @param {string} imageUrl
 *
 * @return {boolean}
 */
function isImageUrl(imageUrl) {
  return /\.(jpe?g|png|gif|svg|webp)\??/.test(imageUrl);
}


/**
 * Mix of several strategies:
 *  - only cache GET requests.
 *  - for js/css/fonts assets, use stale while revalidate.
 *  - for html pages, use network with cache fallback.
 *  - Do not cache images or HTTP errors and redirects.
 */
self.addEventListener('fetch', function (event) {

  /**
   * @param {Request} request
   *
   * @return {Promise}
   */
  function fetchRessourceFromCache(request) {
    return this.match(request.url ? request : event.request);
  }

  /**
   * Returns the cached version or reject the promise.
   *
   * @param {undefined|Response} response
   *
   * @return {Promise}
   */
  function returnRessourceFromCache(response) {
    if (!response) {
      return Promise.reject(new Error('Ressource not in cache'));
    }
    return response;
  }

  /**
   *
   * @return {Promise}
   */
  function fetchRessourceFromNetwork() {
    return fetch(event.request);
  }

  /**
   * @param {Response} response
   *
   * @return {Promise}
   */
  function cacheNetworkResponse(response) {
    // Don't cache redirects or errors.
    if (isCacheableResponse(response)) {
      this.put(event.request, response.clone());
    }
    else {
      console.log("Response not cacheable: ", response);
    }
    return response;
  }


  /**
   * Main point of entry.
   *
   * Separate handling of assets from all other requests.
   *
   * @param {Cache} cache
   *
   * @return {Promise}
   */
  function handleRequest(cache) {
    var promiseReturn;
    var fetchRessourceFromThisCache = fetchRessourceFromCache.bind(cache);

    // If it's an asset: stale while revalidate.
    if (isCacheableAsset(url)) {
      promiseReturn = fetchRessourceFromThisCache(event.request)
        .then(returnRessourceFromCache)
        .catch(fetchRessourceFromNetwork)
        .then(cacheNetworkResponse.bind(cache))
        .catch(logError);
    }
    // Non-cacheable images: no cache.
    else if (isImageUrl(url)) {
      promiseReturn = fetch(event.request)
        .catch(catchOfflineImage);
    }
    // Other ressources: network with cache fallback.
    else {
      promiseReturn = fetch(event.request)
        .then(cacheNetworkResponse.bind(cache))
        .catch(fetchRessourceFromThisCache)
        .then(returnRessourceFromCache)
        .catch(catchOffline);
    }

    return promiseReturn;
  }

  var url = event.request.url;
  var isMethodGet = event.request.method === 'GET';
  var notExcluded = CACHE_EXCLUDE.every(urlNotExcluded(url));

  // Make sure the url is one we don't exclude from cache.
  if (isMethodGet && notExcluded) {
    event.respondWith(caches
      .open(CURRENT_CACHE)
      .then(handleRequest)
      .catch(logError)
    );
  }
  else {
    console.log('Excluded URL: ', event.request.url);
  }
});

