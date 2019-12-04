#!/bin/bash

echo "Ensuring /data directories exist."
mkdir -p /data/public
mkdir -p /data/private
mkdir -p /data/translations
mkdir -p /data/config

echo "Ensuring the /data-local directories exist. (Ephemeral)"
mkdir -p /data-local/twig

echo "Ensuring /data owner is www-data:www-data"
chown -R www-data:www-data /data/*
chown -R www-data:www-data /data-local/*

echo "Copying drupal settings into place"
cp /drupal-scripts/settings*.php /var/www/html/sites/default/

echo "Configuring apache"
rm -f /etc/apache2/sites-available/*.conf
cp /drupal-scripts/vhost.conf /etc/apache2/sites-available/000-default.conf

echo "Copying SA-CORE-2013-003 htaccess files"
cp /drupal-scripts/SA-CORE-2013-003.htaccess /tmp/.htaccess
cp /drupal-scripts/SA-CORE-2013-003.htaccess /data/public/.htaccess

echo "Removing write on sites/default/files"
chmod og-w -R /var/www/html/sites/default

echo "Starting up"

if [ ! -z "$*" ]; then
  eval $*
else
  docker-php-entrypoint apache2-foreground
fi
