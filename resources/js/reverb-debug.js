const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

export function reverbLog(level, message, context = {}) {
    const prefix = `[reverb:${level}]`;

    if (level === 'error') {
        console.error(prefix, message, context);
    } else {
        console.info(prefix, message, context);
    }

    if (! window.coinReverb?.debug) {
        return;
    }

    fetch(`${window.location.origin}/reverb-debug`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
            level,
            message,
            context: {
                ...context,
                page: window.location.pathname,
                host: window.location.host,
            },
        }),
        credentials: 'same-origin',
    }).catch((error) => {
        console.warn('[reverb:debug] failed to send log', error);
    });
}

export function attachEchoDebug(echo, label = 'echo') {
    if (! echo?.connector?.pusher) {
        reverbLog('error', `${label}: pusher connector missing`);

        return;
    }

    const connection = echo.connector.pusher.connection;

    reverbLog('info', `${label}: attaching debug listeners`, {
        socketId: connection.socket_id ?? null,
        state: connection.state,
    });

    connection.bind('connecting', () => reverbLog('info', `${label}: connecting`));
    connection.bind('connected', () => reverbLog('info', `${label}: connected`, {
        socketId: connection.socket_id,
    }));
    connection.bind('disconnected', () => reverbLog('warn', `${label}: disconnected`));
    connection.bind('unavailable', () => reverbLog('error', `${label}: unavailable`));
    connection.bind('failed', () => reverbLog('error', `${label}: failed`));
    connection.bind('error', (error) => reverbLog('error', `${label}: connection error`, {
        error: error?.error?.data ?? error?.error ?? error,
    }));
    connection.bind('state_change', (states) => reverbLog('info', `${label}: state change`, states));

    echo.connector.pusher.bind('pusher:subscription_error', (payload) => {
        reverbLog('error', `${label}: subscription error`, payload);
    });

    echo.connector.pusher.bind('pusher:subscription_succeeded', (payload) => {
        reverbLog('info', `${label}: subscription succeeded`, {
            channel: payload?.channel ?? payload,
        });
    });
}

export function logEchoConfig(label = 'echo') {
    const runtime = window.coinReverb ?? {};

    reverbLog('info', `${label}: runtime config`, {
        host: runtime.host,
        port: runtime.port,
        scheme: runtime.scheme,
        hasKey: Boolean(runtime.key),
        debug: Boolean(runtime.debug),
        authEndpoint: `${window.location.origin}/broadcasting/auth`,
    });
}
