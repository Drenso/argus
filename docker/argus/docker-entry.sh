#! /bin/bash -e

# Wait for database

if [[ -z "${DATABASE_CHECK}" ]]; then
  sleep 10
else
  ./wait-for "${DATABASE_CHECK}"
fi

# Migrate database
bin/console doctrine:migrations:migrate --no-interaction --query-time --allow-no-migration -vv

# Remove created cache from migration
rm -rf var/*

# Original entrypoint
docker-php-entrypoint
php-fpm
