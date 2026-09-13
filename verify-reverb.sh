#!/bin/bash
set -e
cd "$(dirname "$0")"

echo "=== Process ==="
ps aux | grep "artisan reverb:start" | grep -v grep || echo "NOT RUNNING"

echo "=== Local port ==="
php -r '$c=@fsockopen("127.0.0.1",8080,$e,$s,2); echo $c?"OK\n":"FAIL\n"; if($c) fclose($c);'

echo "=== WebSocket proxy in .htaccess ==="
grep -q "127.0.0.1:8080/app" public/.htaccess && echo "FOUND" || echo "MISSING"

echo "=== Artisan diagnose ==="
php artisan coin:reverb-diagnose --dispatch

echo "=== Reverb daemon log ==="
tail -8 storage/logs/reverb.log 2>/dev/null || echo "no reverb.log"

echo "=== Reverb debug log ==="
tail -8 storage/logs/reverb-debug.log 2>/dev/null || echo "no reverb-debug.log"

echo VERIFY_OK
