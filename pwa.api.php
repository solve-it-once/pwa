<?php

/**
 * @file
 * Hooks provided by the Progressive Web App module.
 */

use Drupal\Core\Cache\CacheableMetadata;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alters cached urls list for the service worker file.
 *
 * This hook allows adding or removing urls to the whitelist for the cache urls.
 * This is the same as the "URLs to cache on install" list on the service worker
 * admin page.
 *
 * After you make your modifications you do NOT need to return the results.
 * Since $cacheUrls is passed by reference, any changes made to $cacheUrls are
 * automatically registered.
 *
 * @param array &$cacheUrls
 *   List of urls to cache.
 * @param \Drupal\Core\Cache\CacheableMetadata $cacheableMetadata
 *   Cacheability metadata.
 */
function hook_pwa_cache_urls_alter(&$cacheUrls, CacheableMetadata &$cacheableMetadata) {
  // Get a node URL and its cacheability metadata.
  $generated_url = \Drupal\node\Entity\Node::load('1')->toUrl()->toString(TRUE);
  // Add the URL to the list.
  $cacheUrls[] = $generated_url->getGeneratedUrl();
  // Merge the cacheability metadata.
  $cacheableMetadata = $cacheableMetadata->merge($generated_url);
}

/**
 * Alters excluded urls list for the service worker file.
 *
 * This hook allows adding or removing urls to the blacklist for the excluded
 * urls. This is the same as the "URLs to cache on install" list on the service
 * worker admin page.
 *
 * After you make your modifications you do NOT need to return the results.
 * Since $excludeUrls is passed by reference, any changes made to $excludeUrls
 * are automatically registered.
 *
 * @param array &$excludeUrls
 *   List of url to cache. Paths matching these patterns will not be cached by
 *   the Service Worker. Can be JavaScript regex.
 * @param \Drupal\Core\Cache\CacheableMetadata $cacheableMetadata
 *   Cacheability metadata.
 */
function hook_pwa_exclude_urls_alter(&$excludeUrls, CacheableMetadata &$cacheableMetadata) {
  $excludeUrls[] = "path/pattern/.*";
  $excludeUrls[] = "just/path";
}

/**
 * Alters manifest data.
 *
 * This hook allows altering the generated manifest data before encoding it to JSON.
 *
 * @param array &$manifestData
 *   Manifest data generated in Manifest::getOutput().
 */
function hook_pwa_manifest_alter(&$manifestData) {
  $manifestData['short_name'] = 'App';
}

/**
 * @} End of "addtogroup hooks".
 */
