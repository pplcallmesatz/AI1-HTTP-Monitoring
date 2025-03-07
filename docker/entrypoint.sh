#!/bin/bash

# Copy .env file if not exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate application key if not set
if [ -z "$(grep '^APP_KEY=' .env)" ] || [ "$(grep '^APP_KEY=' .env | cut -d'=' -f2)" == "" ]; then
    php artisan key:generate
fi

# Wait for database to be ready
if [ ! -z "$DB_HOST" ]; then
    until nc -z -v -w30 $DB_HOST 3306; do
        echo "Waiting for database connection..."
        sleep 5
    done
fi

# Run migrations
php artisan migrate --force

# Start supervisor
/usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf