#!/bin/bash

# We need to install dependencies only for Docker
#[[ ! -e /.dockerenv ]] && [[ ! -e /.dockerinit ]] && exit 0

#set -xe

# Update packages and install composer and PHP dependencies.
apt-get update -yqq
apt-get install git zlib1g-dev libldap2-dev -yqq
rm -rf /var/lib/apt/lists/*

# Compile PHP, include these extensions.
docker-php-ext-install pdo_mysql zip
docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/
docker-php-ext-install ldap

# Install Composer and project dependencies.
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Ping the mysql container
ping -c 3 mysql

# Composer install parallel install plugin
composer global require "hirak/prestissimo:^0.3"

cd projects2

# Composer install project dependencies
composer install --no-progress --no-interaction

# Copy over testing configuration.
cp -f .env.gitlab .env

# Generate an application key. Re-cache.
php artisan key:generate
php artisan config:cache

cat .env

# Run database migrations.
php artisan migrate:refresh

# Run database seed.
#php artisan db:seed

