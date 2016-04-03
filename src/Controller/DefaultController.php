<?php /**
 * @file
 * Contains \Drupal\pwa\Controller\DefaultController.
 */

namespace Drupal\pwa\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Default controller for the pwa module.
 */
class DefaultController extends ControllerBase {

  public function pwa_serviceworker_file_data($version = 1) {
    $data =  \Drupal::cache()->get('pwa:serviceworker');
    if ($data) {
      $data = $data->data;
    }
    else {
      $data = pwa_serviceworker_file();
      \Drupal::cache()->set('pwa:serviceworker', $data);
    }

    return new Response($data, 200, [
      'Content-Type' => 'application/javascript',
      'Service-Worker-Allowed' => '/',
    ]);
  }

  public function pwa_offline_page() {
    return [
      '#type' => 'html_tag',
      '#tag' => 'h1',
      '#value' => 'You are offline.',
      '#attributes' => [
        'data-drupal-pwa-offline' => TRUE
      ],
    ];
  }

}
