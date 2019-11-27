<?php

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