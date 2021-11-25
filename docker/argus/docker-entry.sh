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
su -s /bin/bash -c "php bin/console doctrine:migrations:migrate --no-interaction --query-time --allow-no-migration -vv" www-data

# Start supervisor
exec supervisord -c /etc/supervisor/supervisord.conf
