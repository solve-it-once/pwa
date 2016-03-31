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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('pwa.settings');

    foreach (Element::children($form) as $variable) {
      $config->set($variable, $form_state->getValue($form[$variable]['#parents']));
    }
    $config->save();

    if (method_exists($this, '_submitForm')) {
      $this->_submitForm($form, $form_state);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['pwa.settings'];
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form = [];

    $form['manifest'] = [
      '#type' => 'fieldset',
      '#title' => t('Manifest'),
      '#description' => t("Change values of the manifest file used to add the website as an app."),
    ];
    $form['manifest']['pwa_short_name'] = [
      '#type' => 'textfield',
      '#title' => t('Short name'),
      '#description' => t("Name of the shortcut created on the device."),
      '#default_value' => variable_get('pwa_short_name', variable_get('site_name')),
      '#required' => TRUE,
    ];
    $form['manifest']['pwa_name'] = [
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#description' => t("Usually the named displayed on the splash screen on launch."),
      '#default_value' => variable_get('pwa_name', variable_get('site_name')),
      '#required' => TRUE,
    ];
    $form['manifest']['pwa_background_color'] = [
      '#type' => 'textfield',
      '#title' => t('Background color'),
      '#description' => t("Color of the browser bar when launching from shortcut."),
      '#size' => 4,
      '#default_value' => variable_get('pwa_background_color', '#ffffff'),
    ];
    $form['manifest']['pwa_theme_color'] = [
      '#type' => 'textfield',
      '#title' => t('Theme color'),
      '#description' => t("Color of the background spalsh screen when launching from shortcut."),
      '#size' => 4,
      '#default_value' => variable_get('pwa_theme_color', '#ffffff'),
    ];
    if (module_exists('color')) {
      $form['manifest']['pwa_background_color']['#value_callback'] = 'color_palette_color_value';
      $form['manifest']['pwa_theme_color']['#value_callback'] = 'color_palette_color_value';
    }
    $form['manifest']['pwa_start_url'] = [
      '#type' => 'textfield',
      '#title' => t('Start URL'),
      '#description' => t("Home page when launched from shortcut, can append a query string to gather stats. For example <code>/home?startfrom=manifest</code>."),
      '#default_value' => variable_get('pwa_start_url', '/' . variable_get('site_frontpage', '')),
    ];
    $form['manifest']['pwa_orientation'] = [
      '#type' => 'select',
      '#title' => t('Orientation'),
      '#options' => [
        'portrait' => t('Portrait'),
        'landscape' => t('Landscape'),
      ],
      '#default_value' => variable_get('pwa_orientation', 'portrait'),
    ];
    $form['manifest']['pwa_display'] = [
      '#type' => 'select',
      '#title' => t('Display'),
      '#options' => [
        'fullscreen' => 'Fullscreen',
        'standalone' => 'Standalone (as an app)',
        'minimal-ui' => 'Minimal UI',
        'browser' => 'Browser',
      ],
      '#default_value' => variable_get('pwa_display', 'standalone'),
    ];
    /*
  $form['manifest']['pwa_icons'] = [
    '#type' => 'textfield',
    '#title' => t('icons'),
    '#desctiption' => t(" "),
    '#default_value' => variable_get('pwa_icons', ''),
  ];*/

    $form['sw'] = [
      '#type' => 'fieldset',
      '#title' => t('ServiceWorker'),
      '#description' => t("Configure behavior of the Service Worker."),
    ];
    $form['sw']['pwa_swcache_exclude'] = [
      '#type' => 'textarea',
      '#title' => t('Exclude URLs patterns'),
      '#description' => t("Paths matching those patterns will not be cached by the serviceworker. One javascript regex per line."),
      '#default_value' => variable_get('pwa_swcache_exclude', implode("\n", [
        'admin/.*'
        ])),
    ];
    $form['sw']['pwa_swcache_urls'] = [
      '#type' => 'textarea',
      '#title' => t('Urls to cache on install'),
      '#description' => t("When the serviceworker is installed cache those URLs. If an URL is a page: all it's css, js, and images will be cached automatically."),
      '#default_value' => variable_get('pwa_swcache_urls', implode("\n", [
        '/',
        '/offline',
        variable_get('pwa_start_url', ''),
      ])),
    ];
    $form['sw']['pwa_swcache_version'] = [
      '#type' => 'textfield',
      '#title' => t('Cache version'),
      '#description' => t("Changing this number will invalidate all serviceworker caches. Use it when assets have significantly changed or if you want to force a cache refresh for all clients."),
      '#size' => 1,
      '#default_value' => variable_get('pwa_swcache_version', 1),
    ];

    $form = parent::buildForm($form, $form_state);
    // Wait for all the values to be saved before refreshing cache.
    $form['#submit'][] = 'pwa_admin_configuration_submit';

    return $form;
  }

  public function _submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    pwa_flush_caches();
  }

}
