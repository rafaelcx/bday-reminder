#!/bin/sh
set -e

if [ "$ENABLE_CRON" = "true" ]; then
  echo "Starting cron..."
  cron
else
  echo "Skipping cron..."
fi

exec php -S 0.0.0.0:8000 -t public/
