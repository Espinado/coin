import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

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
    };
}

export function initEcho() {
    const config = reverbConfig();

    if (! config.key) {
        return null;
    }

    if (window.Echo) {
        return window.Echo;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: config.key,
        wsHost: config.wsHost,
        wsPort: config.wsPort,
        wssPort: config.wssPort,
        forceTLS: config.forceTLS,
        enabledTransports: ['ws', 'wss'],
        authEndpoint: `${window.location.origin}/broadcasting/auth`,
        auth: {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        },
        disableStats: true,
    });

    return window.Echo;
}
