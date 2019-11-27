#!/bin/bash

echo "Ensuring /data directories exist."
mkdir -p /data/public
mkdir -p /data/private
mkdir -p /data/translations
mkdir -p /data/config

echo "Ensuring /data owner is www-data:www-data"
chown -R www-data:www-data /data/*

echo "Starting up"

if [ ! -z "$*" ]; then
  eval $*
else
  docker-php-entrypoint apache2-foreground
fi
