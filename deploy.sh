#!/bin/bash

# php artisan down

git pull

export COMPOSER_ALLOW_SUPERUSER=1
composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction

php artisan optimize:clear
php artisan filament:optimize-clear

php artisan migrate --force

npm ci
npm run build

#php artisan filament:upgrade || true

#php artisan filament:optimize
#
#php artisan config:cache
#php artisan route:cache
#php artisan view:cache
#php artisan event:cache

php artisan queue:restart
#php artisan pulse:restart

#sudo supervisorctl reread
#sudo supervisorctl update
#sudo supervisorctl restart all
sudo supervisorctl status
# php artisan up
