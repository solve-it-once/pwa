<?php /**
 * @file
 * Contains \Drupal\pwa\Controller\DefaultController.
 */

namespace Drupal\pwa\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Default controller for the pwa module.
 */
class DefaultController extends ControllerBase {

  public function pwa_serviceworker_file_data($version = 1) {
    $data = cache_get('pwa:serviceworker', 'cache');
    if ($data) {
      $data = $data->data;
    }
    else {
      $data = pwa_serviceworker_file();
      cache_set('pwa:serviceworker', $data, 'cache');
    }

    return $data;
  }

  public function pwa_offline_page() {
    return [
      '#theme' => 'html_tag',
      '#tag' => 'h1',
      '#value' => 'You are offline.',
      '#attributes' => [
        'data-drupal-pwa-offline' => TRUE
        ],
    ];
  }

}
