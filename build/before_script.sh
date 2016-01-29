#!/bin/bash

# Phragile Setup
chmod -R 777 storage

mysql -e 'create database phragile_test;'

cp build/phragile.env .env
cp build/travis_behat_custom.yml behat_custom.yml
php artisan migrate --force

# Phabricator Setup
gunzip -c build/phabricator.sql.gz | mysql -u travis

mkdir /tmp/phab
cd /tmp/phab
git clone https://github.com/phacility/libphutil.git
git clone https://github.com/phacility/arcanist.git
git clone https://github.com/phacility/phabricator.git

phabricator/bin/config set mysql.user travis
phabricator/bin/config set mysql.pass ''
phabricator/bin/config set phabricator.show-prototypes true
phabricator/bin/storage upgrade -f

cd -

# Start Phragile
php -S localhost:3030 public/index.php &

# Start NGINX & PHP-FPM
cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf
if [ -e  ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.d/www.conf.default ];
then
    cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.d/www.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.d/www.conf
fi
~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm

nginx -c `pwd`/build/nginx.conf || true # nginx emits an error when run as non-root user (but still starts). Do not break the build due to this.
