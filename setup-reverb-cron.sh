#!/bin/bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")" && pwd)"
CRON_CMD="*/5 * * * * /usr/bin/flock -n /tmp/coin-reverb.lock env PHP_BIN=/usr/local/bin/php bash ${APP_DIR}/start-reverb.sh >> ${APP_DIR}/storage/logs/reverb-watchdog.log 2>&1"

(crontab -l 2>/dev/null | grep -v coin-reverb || true; echo "${CRON_CMD}") | crontab -

echo "Installed cron:"
crontab -l | grep coin-reverb
