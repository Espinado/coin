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
        guestAuthEndpoint: runtime.guestAuthEndpoint ?? null,
    };
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function buildGuestAuthorizer(authEndpoint) {
    const endpoint = authEndpoint.startsWith('http')
        ? authEndpoint
        : `${window.location.origin}${authEndpoint}`;

    return (channel, options) => ({
        authorize: (socketId, callback) => {
            fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Support-Guest-Token': window.coinReverb?.guestToken ?? '',
                },
                body: JSON.stringify({
                    socket_id: socketId,
                    channel_name: channel.name,
                }),
            })
                .then((response) => {
                    if (! response.ok) {
                        throw new Error(`Guest channel auth failed (${response.status})`);
                    }

                    return response.json();
                })
                .then((data) => callback(null, data))
                .catch((error) => {
                    reverbLog('error', 'guest channel auth failed', {
                        channel: channel.name,
                        error: String(error),
                    });
                    callback(error);
                });
        },
    });
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

    const authEndpoint = config.guestAuthEndpoint ?? `${window.location.origin}/broadcasting/auth`;

    const echoOptions = {
        broadcaster: 'reverb',
        key: config.key,
        wsHost: config.wsHost,
        wsPort: config.wsPort,
        wssPort: config.wssPort,
        forceTLS: config.forceTLS,
        enabledTransports: ['ws', 'wss'],
        disableStats: true,
    };

    if (config.guestAuthEndpoint) {
        echoOptions.authorizer = buildGuestAuthorizer(authEndpoint);
    } else {
        echoOptions.authEndpoint = authEndpoint;
        echoOptions.auth = {
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
        };
    }

    window.Echo = new Echo(echoOptions);

    if (window.coinReverb?.monitor !== false) {
        attachEchoConnectionMonitor(window.Echo, 'init');
    }

    if (config.debug) {
        attachEchoDebug(window.Echo, 'init');
    }

    return window.Echo;
}
