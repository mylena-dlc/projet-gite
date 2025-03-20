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
docker compose exec php ls -lah /var/www/.env
 
ls -lah /var/www/.env
cat /var/www/.env
docker compose exec php chmod 644 /var/www/.env
chmod 644 /var/www/.env
ls -lah /var/www/.env
docker compose exec php ls -lah /var/www/
 
ls -lah /var/www/
docker compose exec php php -r 'var_dump(getenv("DATABASE_URL"));'
 
php -r 'var_dump(getenv("DATABASE_URL"));'
debug:dotenv
php bin/console debug:dotenv
exit
docker compose exec php php -r 'var_dump(getenv("DATABASE_URL"));'
php -r 'var_dump(getenv("DATABASE_URL"));'
exit
 docker compose restart php
 restart php
exit
php -r 'var_dump(getenv("DATABASE_URL"));'
php -r 'var_dump($_SERVER["DATABASE_URL"] ?? "NOT FOUND");'
php -r 'var_dump($_ENV["DATABASE_URL"] ?? "NOT FOUND");'
cat /var/www/.env
export $(grep -v '^#' /var/www/.env | xargs)
php -r 'var_dump(getenv("DATABASE_URL"));'
exit
docker compose exec php php -r 'var_dump(getenv("DATABASE_URL"));'
php -r 'var_dump(getenv("DATABASE_URL"));'
exit
php -r 'var_dump(getenv("DATABASE_URL"));'
exit
php -r 'var_dump(getenv("APP_SECRET"));'
docker ps
exit
docker compose run php bash
exit
[200~sudo chown -R www-data:www-data /var/www
sudo chmod -R 775 /var/www
exit
sudo chown -R www-data:www-data /var/www
sudo chmod -R 775 /var/www
exit
find /var/www -not -user www-data -or -not -group www-data
 
exit
