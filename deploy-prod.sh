#!/bin/bash
set -euo pipefail

cd "$(dirname "$0")"

chmod +x deploy-prod.sh start-reverb.sh setup-reverb-cron.sh setup-scheduler-cron.sh 2>/dev/null || true

BRANCH="${DEPLOY_BRANCH:-main}"
REMOTE="${DEPLOY_REMOTE:-origin}"

echo "==> git pull (${REMOTE}/${BRANCH})"
git fetch "${REMOTE}" "${BRANCH}"
git reset --hard "${REMOTE}/${BRANCH}"

echo "==> composer install"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> npm build"
export PATH="${NODE_BIN_PATH:-/home2/argussl/node-local/node/bin}:${PATH}"
npm ci
npm run build

echo "==> migrate"
php artisan migrate --force

echo "==> cache"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'OPCACHE_RESET_OK'; } else { echo 'OPCACHE_N/A'; }"
echo

echo "==> reverb"
if [ -x ./start-reverb.sh ]; then
  ./start-reverb.sh
else
  bash ./start-reverb.sh
fi

echo "==> reverb diagnose"
php artisan coin:reverb-diagnose || true

echo "==> reverb cron"
if [ -x ./setup-reverb-cron.sh ]; then
  ./setup-reverb-cron.sh
else
  bash ./setup-reverb-cron.sh
fi

echo "==> scheduler cron"
if [ -x ./setup-scheduler-cron.sh ]; then
  ./setup-scheduler-cron.sh
else
  bash ./setup-scheduler-cron.sh
fi

echo "DEPLOY_OK"
