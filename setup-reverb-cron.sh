#!/bin/bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")" && pwd)"

broadcast_driver() {
  if [[ -f "${APP_DIR}/.env" ]]; then
    local value
    value="$(grep -E '^BROADCAST_CONNECTION=' "${APP_DIR}/.env" | tail -n1 | cut -d= -f2- | tr -d '\r' | tr -d '"' | tr -d "'")"
    echo "${value:-null}"
  else
    echo "null"
  fi
}

# Always drop stale reverb watchdog lines first.
CURRENT_CRON="$(crontab -l 2>/dev/null | grep -v coin-reverb | grep -v 'start-reverb.sh' || true)"

BROADCAST="$(broadcast_driver)"
if [[ "${BROADCAST}" != "reverb" ]]; then
  printf '%s\n' "${CURRENT_CRON}" | crontab -
  echo "Removed reverb cron (broadcast=${BROADCAST})"
  exit 0
fi

CRON_CMD="*/5 * * * * /usr/bin/flock -n /tmp/coin-reverb.lock env PHP_BIN=/usr/local/bin/php bash ${APP_DIR}/start-reverb.sh >> ${APP_DIR}/storage/logs/reverb-watchdog.log 2>&1"

(printf '%s\n' "${CURRENT_CRON}"; echo "${CRON_CMD}") | crontab -

echo "Installed cron:"
crontab -l | grep coin-reverb
