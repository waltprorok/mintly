#!/bin/bash
# php artisan down
git pull origin master

export COMPOSER_ALLOW_SUPERUSER=1
composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction

sudo -u www-data php artisan optimize:clear
sudo -u www-data php artisan filament:optimize-clear

sudo -u www-data php artisan migrate --force

npm ci
npm run build

sudo -u www-data php artisan filament:upgrade || true
sudo -u www-data php artisan filament:optimize

sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache

sudo -u www-data php artisan queue:restart
#php artisan pulse:restart

sudo supervisorctl status
# php artisan up
