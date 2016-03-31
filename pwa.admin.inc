<?php
/**
 * @file
 * PWA administration forms.
 */

/**
 * Configure all PWA settings.
 */
function pwa_admin_configuration() {
  $form = [];

  $form['manifest'] = [
    '#type' => 'fieldset',
    '#title' => t('Manifest'),
    '#description' => t("Change values of the manifest file used to add the website as an app."),
  ];
  // @FIXME
// Could not extract the default value because it is either indeterminate, or
// not scalar. You'll need to provide a default value in
// config/install/pwa.settings.yml and config/schema/pwa.schema.yml.
$form['manifest']['pwa_short_name'] = [
    '#type' => 'textfield',
    '#title' => t('Short name'),
    '#description' => t("Name of the shortcut created on the device."),
    '#default_value' => \Drupal::config('pwa.settings')->get('pwa_short_name'),
    '#required' => TRUE,
  ];
  // @FIXME
// Could not extract the default value because it is either indeterminate, or
// not scalar. You'll need to provide a default value in
// config/install/pwa.settings.yml and config/schema/pwa.schema.yml.
$form['manifest']['pwa_name'] = [
    '#type' => 'textfield',
    '#title' => t('Name'),
    '#description' => t("Usually the named displayed on the splash screen on launch."),
    '#default_value' => \Drupal::config('pwa.settings')->get('pwa_name'),
    '#required' => TRUE,
  ];
  $form['manifest']['pwa_background_color'] = [
    '#type' => 'textfield',
    '#title' => t('Background color'),
    '#description' => t("Color of the browser bar when launching from shortcut."),
    '#size' => 4,
    '#default_value' => \Drupal::config('pwa.settings')->get('pwa_background_color'),
  ];
  $form['manifest']['pwa_theme_color'] = [
    '#type' => 'textfield',
    '#title' => t('Theme color'),
    '#description' => t("Color of the background spalsh screen when launching from shortcut."),
    '#size' => 4,
    '#default_value' => \Drupal::config('pwa.settings')->get('pwa_theme_color'),
  ];
  if (module_exists('color')) {
    $form['manifest']['pwa_background_color']['#value_callback'] = 'color_palette_color_value';
    $form['manifest']['pwa_theme_color']['#value_callback'] = 'color_palette_color_value';
  }
  // @FIXME
// Could not extract the default value because it is either indeterminate, or
// not scalar. You'll need to provide a default value in
// config/install/pwa.settings.yml and config/schema/pwa.schema.yml.
$form['manifest']['pwa_start_url'] = [
    '#type' => 'textfield',
    '#title' => t('Start URL'),
    '#description' => t("Home page when launched from shortcut, can append a query string to gather stats. For example <code>/home?startfrom=manifest</code>."),
    '#default_value' => \Drupal::config('pwa.settings')->get('pwa_start_url'),
  ];
  $form['manifest']['pwa_orientation'] = [
    '#type' => 'select',
    '#title' => t('Orientation'),
    '#options' => [
      'portrait' => t('Portrait'),
      'landscape' => t('Landscape'),
    ],
    '#default_value' => \Drupal::config('pwa.settings')->get('pwa_orientation'),
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
    '#default_value' => \Drupal::config('pwa.settings')->get('pwa_display'),
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
  // @FIXME
// Could not extract the default value because it is either indeterminate, or
// not scalar. You'll need to provide a default value in
// config/install/pwa.settings.yml and config/schema/pwa.schema.yml.
$form['sw']['pwa_swcache_exclude'] = [
    '#type' => 'textarea',
    '#title' => t('Exclude URLs patterns'),
    '#description' => t("Paths matching those patterns will not be cached by the serviceworker. One javascript regex per line."),
    '#default_value' => \Drupal::config('pwa.settings')->get('pwa_swcache_exclude'),
  ];
  // @FIXME
// Could not extract the default value because it is either indeterminate, or
// not scalar. You'll need to provide a default value in
// config/install/pwa.settings.yml and config/schema/pwa.schema.yml.
$form['sw']['pwa_swcache_urls'] = [
    '#type' => 'textarea',
    '#title' => t('Urls to cache on install'),
    '#description' => t("When the serviceworker is installed cache those URLs. If an URL is a page: all it's css, js, and images will be cached automatically."),
    '#default_value' => \Drupal::config('pwa.settings')->get('pwa_swcache_urls'),
  ];
  $form['sw']['pwa_swcache_version'] = [
    '#type' => 'textfield',
    '#title' => t('Cache version'),
    '#description' => t("Changing this number will invalidate all serviceworker caches. Use it when assets have significantly changed or if you want to force a cache refresh for all clients."),
    '#size' => 1,
    '#default_value' => \Drupal::config('pwa.settings')->get('pwa_swcache_version'),
  ];

  $form = system_settings_form($form);
  // Wait for all the values to be saved before refreshing cache.
  $form['#submit'][] = 'pwa_admin_configuration_submit';

  return $form;
}

function pwa_admin_configuration_submit($form, &$form_state) {
  pwa_flush_caches();
}
