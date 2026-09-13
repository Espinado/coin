#!/bin/bash
set -euo pipefail

cd "$(dirname "$0")"

if [[ -z "${PHP_BIN:-}" ]]; then
    for candidate in /usr/local/bin/php /opt/alt/php84/usr/bin/php /opt/cpanel/ea-php84/root/usr/bin/php php; do
        if [[ -x "${candidate}" ]] && "${candidate}" -v 2>&1 | grep -qi '(cli)'; then
            PHP_BIN="${candidate}"
            break
        fi
    done
fi

if [[ -z "${PHP_BIN:-}" ]] || ! "${PHP_BIN}" -v 2>&1 | grep -qi '(cli)'; then
    echo "ERROR: PHP CLI not found (cron often exposes /usr/bin/php as cgi-fcgi)." >&2
    exit 1
fi

is_port_open() {
    (echo > /dev/tcp/127.0.0.1/8080) >/dev/null 2>&1
}

is_reverb_running() {
    pgrep -f "artisan reverb:start" >/dev/null 2>&1
}

# Healthy daemon: keep it running (do not kill on every cron tick).
if is_reverb_running && is_port_open; then
    echo "ALREADY_OK"
    exit 0
fi

pkill -f "artisan reverb:start" 2>/dev/null || true
sleep 1

nohup "${PHP_BIN}" artisan reverb:start --host=127.0.0.1 --port=8080 >> storage/logs/reverb.log 2>&1 &
echo "Started PID $!"

for _ in 1 2 3 4 5; do
    sleep 1

    if is_reverb_running && is_port_open; then
        echo "PROCESS_OK"
        echo "PORT_OK"
        exit 0
    fi
done

echo "PROCESS_FAIL"
tail -20 storage/logs/reverb.log >&2 || true
exit 1
