<?php

/**
 * @file
 * Contains \Drupal\pwa\Form\PwaAdminConfiguration.
 */

namespace Drupal\pwa\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class AdminConfiguration extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pwa_admin_configuration';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['pwa.settings'];
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form = [];
    $pwa_settings = $this->config('pwa.settings');
    $form['manifest'] = [
      '#type' => 'fieldset',
      '#title' => t('Manifest'),
      '#description' => t("Change values of the manifest file used to add the website as an app."),
    ];
    $form['manifest']['background_color'] = [
      '#type' => 'textfield',
      '#title' => t('Background color'),
      '#description' => t("Color of the browser bar when launching from shortcut."),
      '#size' => 4,
      '#default_value' => $pwa_settings->get('background_color'),
    ];
    $form['manifest']['theme_color'] = [
      '#type' => 'textfield',
      '#title' => t('Theme color'),
      '#description' => t("Color of the background spalsh screen when launching from shortcut."),
      '#size' => 4,
      '#default_value' => $pwa_settings->get('theme_color'),
    ];
    $form['manifest']['start_url'] = [
      '#type' => 'textfield',
      '#title' => t('Start URL'),
      '#description' => t("Home page when launched from shortcut, can append a query string to gather stats. For example <code>/home?startfrom=manifest</code>."),
      '#default_value' => $pwa_settings->get('start_url'),
    ];
    $form['manifest']['orientation'] = [
      '#type' => 'select',
      '#title' => t('Orientation'),
      '#options' => [
        'portrait' => t('Portrait'),
        'landscape' => t('Landscape'),
      ],
      '#default_value' => $pwa_settings->get('orientation'),
    ];
    $form['manifest']['display'] = [
      '#type' => 'select',
      '#title' => t('Display'),
      '#options' => [
        'fullscreen' => 'Fullscreen',
        'standalone' => 'Standalone (as an app)',
        'minimal-ui' => 'Minimal UI',
        'browser' => 'Browser',
      ],
      '#default_value' => $pwa_settings->get('display'),
    ];
    $form['sw'] = [
      '#type' => 'fieldset',
      '#title' => t('ServiceWorker'),
      '#description' => t("Configure behavior of the Service Worker."),
    ];
    $form['sw']['preload'] = [
      '#type' => 'textarea',
      '#title' => t('Urls to cache on install'),
      '#description' => t("When the serviceworker is installed cache those URLs. If an URL is a page: all it's css, js, and images will be cached automatically."),
      '#default_value' => implode("\n", $pwa_settings->get('serviceworker_preload')),
    ];
    $form['sw']['cache_exclude'] = [
      '#type' => 'textarea',
      '#title' => t('Exclude URLs patterns'),
      '#description' => t("Paths matching those patterns will not be cached by the serviceworker. One javascript regex per line."),
      '#default_value' => implode("\n", $pwa_settings->get('serviceworker_cache_exclude')),
    ];
    $form['sw']['cache_version'] = [
      '#type' => 'textfield',
      '#title' => t('Cache version'),
      '#description' => t("Changing this number will invalidate all serviceworker caches. Use it when assets have significantly changed or if you want to force a cache refresh for all clients."),
      '#size' => 2,
      '#default_value' => $pwa_settings->get('serviceworker_cache_version'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Check for empty front page path.
    if ($form_state->isValueEmpty('preload')) {
      $form_state->setValueForElement($form['sw']['preload'], implode("\n", ['/', '/offline', $form_state->getValue('start_url')]));
    }
    // Validate front page path.
    if (($value = $form_state->getValue('start_url')) && $value[0] !== '/') {
      $form_state->setErrorByName('start_url', $this->t("The path '%path' has to start with a slash.", ['%path' => $form_state->getValue('start_url')]));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('pwa.settings')
      ->set('start_url', $form_state->getValue('start_url'))
      ->set('background_color', $form_state->getValue('background_color'))
      ->set('theme_color', $form_state->getValue('theme_color'))
      ->set('orientation', $form_state->getValue('orientation'))
      ->set('display', $form_state->getValue('display'))
      ->set('serviceworker_cache_version', $form_state->getValue('cache_version'))
      ->set('serviceworker_preload',  preg_split("/\r\n|\n|\r/", trim($form_state->getValue('preload'))))
      ->set('serviceworker_cache_exclude', preg_split("/\r\n|\n|\r/", trim($form_state->getValue('cache_exclude'))))
      ->save();

    pwa_rebuild();
    parent::submitForm($form, $form_state);
  }
}
