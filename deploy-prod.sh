#!/bin/bash
set -euo pipefail

cd "$(dirname "$0")"

chmod +x deploy-prod.sh start-reverb.sh setup-reverb-cron.sh setup-scheduler-cron.sh 2>/dev/null || true

BRANCH="${DEPLOY_BRANCH:-main}"
REMOTE="${DEPLOY_REMOTE:-origin}"

broadcast_driver() {
  if [[ -f .env ]]; then
    local value
    value="$(grep -E '^BROADCAST_CONNECTION=' .env | tail -n1 | cut -d= -f2- | tr -d '\r' | tr -d '"' | tr -d "'")"
    echo "${value:-null}"
  else
    echo "null"
  fi
}

echo "==> git pull (${REMOTE}/${BRANCH})"
git fetch "${REMOTE}" "${BRANCH}"
git reset --hard "${REMOTE}/${BRANCH}"

echo "==> composer install"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> npm build"
if [[ -z "${NODE_BIN_PATH:-}" ]]; then
  for candidate in \
    /home2/argussl/node-local/node/bin \
    /opt/alt/alt-nodejs22/root/usr/bin \
    /opt/alt/alt-nodejs20/root/usr/bin \
    /opt/alt/alt-nodejs18/root/usr/bin
  do
    if [[ -x "${candidate}/npm" ]]; then
      NODE_BIN_PATH="${candidate}"
      break
    fi
  done
fi
export PATH="${NODE_BIN_PATH:-}:${PATH}"
command -v npm >/dev/null
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

BROADCAST="$(broadcast_driver)"
echo "==> broadcast driver: ${BROADCAST}"

if [[ "${BROADCAST}" == "reverb" ]]; then
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
else
  echo "==> skipping reverb (not needed for ${BROADCAST})"
  pkill -f "artisan reverb:start" 2>/dev/null || true
  if [ -x ./setup-reverb-cron.sh ]; then
    ./setup-reverb-cron.sh
  else
    bash ./setup-reverb-cron.sh
  fi
fi

echo "==> scheduler cron"
if [ -x ./setup-scheduler-cron.sh ]; then
  ./setup-scheduler-cron.sh
else
  bash ./setup-scheduler-cron.sh
fi

echo "DEPLOY_OK"
