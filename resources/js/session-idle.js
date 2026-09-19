const ACTIVITY_COOKIE = 'coin_last_activity';
const ACTIVITY_EVENTS = ['mousedown', 'keydown', 'touchstart', 'scroll', 'click'];

function readMeta(name) {
    return document.querySelector(`meta[name="${name}"]`)?.content?.trim() ?? '';
}

function readActivityCookieMs() {
    const match = document.cookie.match(new RegExp(`(?:^|; )${ACTIVITY_COOKIE}=(\\d+)`));

    return match ? Number(match[1]) : null;
}

function markActivityCookie() {
    const value = String(Date.now());
    document.cookie = `${ACTIVITY_COOKIE}=${value}; path=/; SameSite=Lax; max-age=120`;
}

function performLogout(logoutUrl) {
    const csrf = readMeta('csrf-token');

    if (! logoutUrl || ! csrf) {
        window.location.assign(readMeta('coin-idle-redirect') || '/login');

        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = logoutUrl;
    form.style.display = 'none';

    const token = document.createElement('input');
    token.type = 'hidden';
    token.name = '_token';
    token.value = csrf;
    form.appendChild(token);

    document.body.appendChild(form);
    form.submit();
}

export function bootSessionIdleWatcher() {
    const minutes = Number(readMeta('coin-session-idle-minutes'));

    if (! Number.isFinite(minutes) || minutes <= 0) {
        return;
    }

    const logoutUrl = readMeta('coin-logout-url');
    const timeoutMs = minutes * 60 * 1000;
    let timerId = null;

    const resetTimer = () => {
        markActivityCookie();

        if (timerId !== null) {
            window.clearTimeout(timerId);
        }

        timerId = window.setTimeout(() => performLogout(logoutUrl), timeoutMs);
    };

    ACTIVITY_EVENTS.forEach((eventName) => {
        document.addEventListener(eventName, resetTimer, { passive: true });
    });

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState !== 'visible') {
            return;
        }

        const lastActivity = readActivityCookieMs();

        if (lastActivity !== null && Date.now() - lastActivity >= timeoutMs) {
            performLogout(logoutUrl);

            return;
        }

        resetTimer();
    });

    const registerLivewireHook = () => {
        if (! window.Livewire) {
            return;
        }

        window.Livewire.hook('commit', ({ succeed }) => {
            succeed(() => resetTimer());
        });
    };

    if (window.Livewire) {
        registerLivewireHook();
    } else {
        document.addEventListener('livewire:init', registerLivewireHook, { once: true });
    }

    resetTimer();
}

if (readMeta('coin-session-idle-minutes')) {
    bootSessionIdleWatcher();
}
