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

function handleGuestAdminMessage(payload) {
    const message = payload?.message;

    if (! message?.is_from_admin) {
        return;
    }

    appendSupportMessage(message, { threadId: 'guest-support-thread' });
    scrollSupportThreadToBottom('guest-support-thread');
    showIncomingMessageToast(message, 'Получено новое сообщение');
}

function bootGuestSupportRealtime(ticketId) {
    if (! ticketId) {
        return;
    }

    if (guestEchoTicketId === ticketId && guestEchoChannel) {
        return;
    }

    const echo = initEcho();

    if (! echo) {
        reverbLog('error', 'guest Echo init failed');

        return;
    }

    guestEchoTicketId = ticketId;

    guestEchoChannel = echo.private(`support.guest.${ticketId}`)
        .listen('.SupportTicketMessageSent', (payload) => {
            reverbLog('info', 'guest channel: SupportTicketMessageSent', {
                ticketId: payload?.ticket?.id ?? payload?.message?.ticket_id ?? null,
            });

            handleGuestAdminMessage(payload);
        })
        .listen('.SupportTicketUpdated', (payload) => {
            reverbLog('info', 'guest channel: SupportTicketUpdated', {
                ticketId: payload?.ticket?.id ?? null,
            });
        });

    reverbLog('info', 'guest support realtime subscribed', { ticketId });
}

function openGuestSupportFromPage() {
    Livewire.dispatch('open-guest-support');
}

function registerGuestSupportLivewireHandlers() {
    Livewire.on('guest-support-opened', (payload) => {
        const ticketId = readLivewireEventPayload(payload, 'ticketId');

        if (ticketId) {
            bootGuestSupportRealtime(Number(ticketId));
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

const initialTicketId = window.coinReverb?.guestTicketId;

if (hasEchoKey()) {
    initEcho();

    if (initialTicketId) {
        document.addEventListener('livewire:init', () => {
            bootGuestSupportRealtime(Number(initialTicketId));
        }, { once: true });
    }
}
