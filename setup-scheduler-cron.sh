#!/bin/bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "$0")" && pwd)"
PHP_BIN="${PHP_BIN:-/usr/local/bin/php}"
MARKER="# coin-scheduler"
CRON_CMD="* * * * * cd ${APP_DIR} && ${PHP_BIN} artisan schedule:run >> ${APP_DIR}/storage/logs/scheduler.log 2>&1 ${MARKER}"
CRON_CMD_HALF="* * * * * sleep 30 && cd ${APP_DIR} && ${PHP_BIN} artisan schedule:run >> ${APP_DIR}/storage/logs/scheduler.log 2>&1 ${MARKER}-half"

(crontab -l 2>/dev/null | grep -v "${MARKER}" || true; echo "${CRON_CMD}"; echo "${CRON_CMD_HALF}") | crontab -

echo "Installed scheduler cron:"
crontab -l | grep "${MARKER}"
