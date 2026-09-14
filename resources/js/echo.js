import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { attachEchoConnectionMonitor, attachEchoDebug, logEchoConfig, reverbLog } from './reverb-debug';

window.Pusher = Pusher;

function reverbConfig() {
    const runtime = window.coinReverb ?? {};

    const scheme = runtime.scheme ?? import.meta.env.VITE_REVERB_SCHEME ?? 'http';
    const port = Number(runtime.port ?? import.meta.env.VITE_REVERB_PORT ?? (scheme === 'https' ? 443 : 8080));

    return {
        key: runtime.key ?? import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: runtime.host ?? import.meta.env.VITE_REVERB_HOST,
        wsPort: port,
        wssPort: port,
        forceTLS: scheme === 'https',
        debug: Boolean(runtime.debug),
    };
}

export function hasEchoKey() {
    const config = reverbConfig();

    return Boolean(config.key);
}

export function initEcho() {
    const config = reverbConfig();

    if (! config.key) {
        reverbLog('error', 'Echo not initialized: missing Reverb app key');

        return null;
    }

    if (window.Echo) {
        return window.Echo;
    }

    logEchoConfig('init');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: config.key,
        wsHost: config.wsHost,
        wsPort: config.wsPort,
        wssPort: config.wssPort,
        forceTLS: config.forceTLS,
        enabledTransports: ['ws', 'wss'],
        authEndpoint: runtime.guestAuthEndpoint ?? `${window.location.origin}/broadcasting/auth`,
        auth: {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        },
        disableStats: true,
    });

    if (window.coinReverb?.monitor !== false) {
        attachEchoConnectionMonitor(window.Echo, 'init');
    }

    if (config.debug) {
        attachEchoDebug(window.Echo, 'init');
    }

    return window.Echo;
}
