<?php

define('MEMCACHE_CHECK_FILE', '/tmp/cache.backend.memcache_available');
define('MEMCACHE_SERVICE_NAME', 'cache.backend.memcache');
define('MEMCACHE_FALLBACK_SERVICE_NAME', 'cache.backend.memory');

/**
 * Configures memcache in settings.php if we've had at least one request
 * where memcache's cache backend was available after bootup.
 */
function _settings_memcached(&$settings, $class_loader) {
  _settings_memcached_boot($settings, $class_loader);

  register_shutdown_function(function() {
    if (\Drupal::hasService(MEMCACHE_SERVICE_NAME))
      file_put_contents(MEMCACHE_CHECK_FILE, "1");
  });

  if (drupal_installation_attempted()) {
    $settings['cache']['default'] = MEMCACHE_FALLBACK_SERVICE_NAME;
  } else {
    if (file_exists(MEMCACHE_CHECK_FILE))
      $settings['cache']['default'] = MEMCACHE_SERVICE_NAME;
    else
      $settings['cache']['default'] = MEMCACHE_FALLBACK_SERVICE_NAME;
  }
}

function _settings_memcached_boot(&$settings, $class_loader) {
  $memcache_exists = class_exists('Memcache', FALSE);
  $memcached_exists = class_exists('Memcached', FALSE);
  if ($memcache_exists || $memcached_exists) {
    $class_loader->addPsr4('Drupal\\memcache\\', 'modules/contrib/memcache/src');

    // Define custom bootstrap container definition to use Memcache for cache.container.
    $settings['bootstrap_container_definition'] = [
      'parameters' => [],
      'services' => [
        'database' => [
          'class' => 'Drupal\Core\Database\Connection',
          'factory' => 'Drupal\Core\Database\Database::getConnection',
          'arguments' => ['default'],
        ],
        'settings' => [
          'class' => 'Drupal\Core\Site\Settings',
          'factory' => 'Drupal\Core\Site\Settings::getInstance',
        ],
        'memcache.settings' => [
          'class' => 'Drupal\memcache\MemcacheSettings',
          'arguments' => ['@settings'],
        ],
        'memcache.factory' => [
          'class' => 'Drupal\memcache\Driver\MemcacheDriverFactory',
          'arguments' => ['@memcache.settings'],
        ],
        'memcache.timestamp.invalidator.bin' => [
          'class' => 'Drupal\memcache\Invalidator\MemcacheTimestampInvalidator',
          # Adjust tolerance factor as appropriate when not running memcache on localhost.
          'arguments' => ['@memcache.factory', 'memcache_bin_timestamps', 0.001],
        ],
        'memcache.backend.cache.container' => [
          'class' => 'Drupal\memcache\DrupalMemcacheInterface',
          'factory' => ['@memcache.factory', 'get'],
          'arguments' => ['container'],
        ],
        'cache_tags_provider.container' => [
          'class' => 'Drupal\Core\Cache\DatabaseCacheTagsChecksum',
          'arguments' => ['@database'],
        ],
        'cache.container' => [
          'class' => 'Drupal\memcache\MemcacheBackend',
          'arguments' => ['container', '@memcache.backend.cache.container', '@cache_tags_provider.container', '@memcache.timestamp.invalidator.bin'],
        ],
      ],
    ];
  }
}