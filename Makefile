
clear:
	php artisan queue:clear

flush:
	php artisan queue:flush

import:
	php artisan queue:work --queue=import-worker

list:
	php artisan route:list --except-vendor

log:
	php artisan app:clear-log-files

migrate:
	php artisan migrate

optimize:
	php artisan optimize:clear

prune:
	php artisan telescope:prune --hours=0

prune-batch:
	php artisan queue:prune-batches --hours=0

pulse-clear:
	php artisan pulse:clear

pulse-work:
	php artisan pulse:work

test:
	php artisan test

tinker:
	php artisan tinker

work:
	php artisan queue:work

npm-dev:
	npm run dev

npm-build:
	npm run build
