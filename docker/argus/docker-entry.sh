#! /bin/bash -e

# Detect public mirror directory, and update its contents
if [[ -d /public_mirror ]]; then
  rm -rf public_mirror/*
  cp -r public/* public_mirror/
fi

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

# Start messenger daemon
start_messenger() {
  while true; do
    su -s /bin/bash -c "php ${BASE_DIR}/bin/console messenger:consume async -q --no-interaction --time-limit=3600" www-data
  done
}

start_messenger &

# Original entrypoint
docker-php-entrypoint
php-fpm
