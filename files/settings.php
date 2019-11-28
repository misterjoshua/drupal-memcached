<?php

// @codingStandardsIgnoreFile
// Use the image build's deployment identifier.
$settings['deployment_identifier'] = file_exists("/deployment-identifier")
    ? file_get_contents('/deployment-identifier')
    : "deployment";

// Database
$databases = [];
$databases['default']['default'] = array (
  'database' => getenv('DRUPAL_DB_NAME'),
  'username' => getenv('DRUPAL_DB_USERNAME'),
  'password' => getenv('DRUPAL_DB_PASSWORD'),
  'host' => getenv('DRUPAL_DB_HOST'),
  'port' => getenv('DRUPAL_DB_PORT'),
  'driver' => getenv('DRUPAL_DB_DRIVER'),
  'prefix' => getenv('DRUPAL_DB_PREFIX'),
  'collation' => 'utf8mb4_general_ci',
);

if (getenv('DRUPAL_MEMCACHE_SERVER')) {
  $settings['memcache'] = [
    'servers' => [ getenv('DRUPAL_MEMCACHE_SERVER') => 'default' ],
    'bins' => [ 'default' => 'default' ],
    'key_prefix' => '',
  ];

  // Register some boot-time container definitions
  require_once __DIR__.'/settings.memcached.php';
  _settings_memcached($settings, $class_loader);
}

// Password hash salt.
$settings['hash_salt'] = getenv('DRUPAL_HASH_SALT');

// Admin UI restrictions
$settings['update_free_access'] = FALSE;
$settings['allow_authorize_operations'] = FALSE;

// File paths
$settings['file_public_base_url'] = "//{$_SERVER['HTTP_HOST']}/files";
$settings['file_public_path'] = '/data/public';
$settings['translation_path'] = '/data/translations';
$settings['file_private_path'] = '/data/private';
$config['system.file']['path']['temporary'] = '/tmp';

// Site config
$config_directories = [];
$config_directories['sync'] = '/data/config';
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';

// Http security
$settings['trusted_host_patterns'] = [
  getenv('DRUPAL_TRUSTED_HOST_PATTERN'),
];

// Miscellaneous
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];
$settings['entity_update_batch_size'] = 50;
$settings['entity_update_backup'] = TRUE;

// Allow overrides via settings.local.php
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}

