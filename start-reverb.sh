#!/bin/bash
set -euo pipefail

cd "$(dirname "$0")"

pkill -f "artisan reverb:start" 2>/dev/null || true
sleep 1

PHP_BIN="${PHP_BIN:-$(command -v php)}"

nohup "${PHP_BIN}" artisan reverb:start --host=127.0.0.1 --port=8080 >> storage/logs/reverb.log 2>&1 &
echo "Started PID $!"

sleep 2

if pgrep -f "artisan reverb:start" >/dev/null; then
  echo "PROCESS_OK"
else
  echo "PROCESS_FAIL"
  tail -20 storage/logs/reverb.log
  exit 1
fi

if (echo > /dev/tcp/127.0.0.1/8080) >/dev/null 2>&1; then
  echo "PORT_OK"
else
  echo "PORT_FAIL"
  tail -20 storage/logs/reverb.log
  exit 1
fi

tail -5 storage/logs/reverb.log
