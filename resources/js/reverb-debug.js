const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

function shouldSendToServer(message, context = {}) {
    if (context.monitor || message.startsWith('[ws_state]')) {
        return window.coinReverb?.monitor !== false;
    }

    return Boolean(window.coinReverb?.debug);
}

function sendReverbLog(level, message, context = {}) {
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

export function reverbLog(level, message, context = {}) {
    const prefix = `[reverb:${level}]`;

    if (level === 'error') {
        console.error(prefix, message, context);
    } else {
        console.info(prefix, message, context);
    }

    if (! shouldSendToServer(message, context)) {
        return;
    }

    sendReverbLog(level, message, context);
}

export function reverbConnectionLog(event, context = {}) {
    reverbLog('info', `[ws_state] ${event}`, { ...context, monitor: true });
}

export function attachEchoConnectionMonitor(echo, label = 'echo') {
    if (! echo?.connector?.pusher) {
        return;
    }

    if (echo.__coinConnectionMonitorAttached) {
        return;
    }

    echo.__coinConnectionMonitorAttached = true;

    const connection = echo.connector.pusher.connection;
    let connectedAt = null;

    const logTransition = (event, extra = {}) => {
        reverbConnectionLog(event, {
            label,
            state: connection.state,
            socketId: connection.socket_id ?? null,
            ...extra,
        });
    };

    logTransition(`initial:${connection.state}`);

    connection.bind('connecting', () => logTransition('connecting'));
    connection.bind('connected', () => {
        connectedAt = Date.now();
        logTransition('connected');
    });
    connection.bind('disconnected', () => {
        logTransition('disconnected', {
            uptimeMs: connectedAt ? Date.now() - connectedAt : null,
        });
        connectedAt = null;
    });
    connection.bind('unavailable', () => logTransition('unavailable'));
    connection.bind('failed', () => logTransition('failed'));
    connection.bind('error', (error) => logTransition('error', {
        error: error?.error?.data ?? error?.error ?? error,
    }));
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
        monitor: runtime.monitor !== false,
        authEndpoint: `${window.location.origin}/broadcasting/auth`,
    });
}
