#!/bin/sh
set -eu

# Free Render services have no pre-deploy command or one-off jobs. Laravel
# migrations are idempotent, so a single-instance service can opt in at boot.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Running Laravel database migrations..."
    php artisan migrate --force
fi

exec "$@"
