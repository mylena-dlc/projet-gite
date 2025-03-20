ls -ld /var/www/var/cache
ls -ld /var/www/var/log
ls -ld /var/www
ls -ld /var
exit
composer update --no-interaction --prefer-dist
ls -lah /var/www
exit
rm -rf vendor
composer install --no-interaction --prefer-dist
exit
composer show symfony/debug-bundle
composer show symfony/web-profiler-bundle
composer show symfony/profiler-pack
exit
php -r "print_r($_ENV);"
php -r 'print_r($_ENV);'
export $(grep -v '^#' /var/www/.env | xargs)
printenv DATABASE_URL
php bin/console cache:clear
exit
