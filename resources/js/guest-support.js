import './bootstrap';
import { hasEchoKey, initEcho } from './echo';
import { reverbLog } from './reverb-debug';
import { showIncomingMessageToast, showSupportToast } from './support-toast';
import { appendSupportMessage, scrollSupportThreadToBottom } from './support-chat';

window.showSupportToast = showSupportToast;
window.showIncomingMessageToast = showIncomingMessageToast;
window.appendSupportMessage = appendSupportMessage;
window.scrollSupportThreadToBottom = scrollSupportThreadToBottom;

function readLivewireEventPayload(payload, key = null) {
    const item = Array.isArray(payload) ? payload[0] : payload;

    if (key === null) {
        return item;
    }

    return item?.[key];
}

window.readLivewireEventPayload = readLivewireEventPayload;

let guestEchoTicketId = null;
let guestEchoChannel = null;

function handleGuestAdminMessage(message) {
    if (! message?.is_from_admin) {
        return;
    }

    appendSupportMessage(message, { threadId: 'guest-support-thread' });
    scrollSupportThreadToBottom('guest-support-thread');
    showIncomingMessageToast(message, document.getElementById('guest-support-root')?.dataset?.newMessageToast || 'New message');
}

function bootGuestSupportRealtime(ticketId) {
    if (! ticketId) {
        return;
    }

    const echo = initEcho();

    if (! echo) {
        reverbLog('error', 'guest Echo init failed');

        return;
    }

    if (guestEchoTicketId && guestEchoTicketId !== ticketId && guestEchoChannel) {
        echo.leave(`support.guest.${guestEchoTicketId}`);
        guestEchoChannel = null;
    }

    if (guestEchoTicketId === ticketId && guestEchoChannel) {
        return;
    }

    guestEchoTicketId = ticketId;

    guestEchoChannel = echo.private(`support.guest.${ticketId}`)
        .listen('.SupportTicketMessageSent', (payload) => {
            reverbLog('info', 'guest channel: SupportTicketMessageSent', {
                ticketId: payload?.ticket?.id ?? payload?.message?.ticket_id ?? null,
            });

            handleGuestAdminMessage(payload?.message);
        })
        .listen('.SupportTicketUpdated', (payload) => {
            reverbLog('info', 'guest channel: SupportTicketUpdated', {
                ticketId: payload?.ticket?.id ?? null,
            });
        });

    echo.connector.pusher.bind('pusher:subscription_error', (status) => {
        if (String(status?.channel ?? '').includes(`support.guest.${ticketId}`)) {
            reverbLog('error', 'guest channel subscription error', status);
        }
    });

    reverbLog('info', 'guest support realtime subscribed', { ticketId });
}

window.bootGuestSupportRealtime = bootGuestSupportRealtime;

function updateGuestRealtimeConfig(config) {
    window.coinReverb = Object.assign(window.coinReverb ?? {}, config);
}

function openGuestSupportFromPage() {
    Livewire.dispatch('open-guest-support');
}

function registerGuestSupportLivewireHandlers() {
    Livewire.on('guest-support-opened', (payload) => {
        const ticketId = readLivewireEventPayload(payload, 'ticketId');
        const guestToken = readLivewireEventPayload(payload, 'guestToken');

        if (guestToken) {
            updateGuestRealtimeConfig({ guestToken, guestTicketId: ticketId });
        }

        if (ticketId) {
            bootGuestSupportRealtime(Number(ticketId));
        }
    });

    Livewire.on('guest-support-new-messages', (payload) => {
        const messages = readLivewireEventPayload(payload, 'messages') ?? [];

        for (const message of messages) {
            handleGuestAdminMessage(message);
        }
    });

    Livewire.on('support-thread-scroll', () => {
        scrollSupportThreadToBottom('guest-support-thread');
    });

    Livewire.on('support-append-message', (payload) => {
        const message = readLivewireEventPayload(payload, 'message');

        if (! message) {
            return;
        }

        appendSupportMessage(message, { threadId: 'guest-support-thread' });
    });

    Livewire.on('support-message-sent', () => {
        showSupportToast('Сообщение отправлено');
    });
}

document.addEventListener('livewire:init', () => {
    registerGuestSupportLivewireHandlers();

    document.querySelectorAll('a[href="#support"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            openGuestSupportFromPage();
        });
    });

    if (window.location.hash === '#support') {
        openGuestSupportFromPage();
    }

    if (window.coinReverb?.guestTicketId) {
        bootGuestSupportRealtime(Number(window.coinReverb.guestTicketId));
    }
});

window.addEventListener('hashchange', () => {
    if (window.location.hash === '#support') {
        openGuestSupportFromPage();
    }
});

document.querySelectorAll('[data-open-guest-support]').forEach((element) => {
    element.addEventListener('click', (event) => {
        if (element.tagName === 'A') {
            event.preventDefault();
        }
    });
});

if (hasEchoKey() && window.coinReverb?.guestTicketId) {
    initEcho();
}
