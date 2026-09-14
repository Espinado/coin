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

function bootGuestSupportRealtime(ticketId) {
    if (! ticketId || guestEchoTicketId === ticketId) {
        return;
    }

    guestEchoTicketId = ticketId;

    const echo = initEcho();

    if (! echo) {
        return;
    }

    echo.private(`support.guest.${ticketId}`)
        .listen('.SupportTicketMessageSent', (payload) => {
            reverbLog('info', 'guest channel: SupportTicketMessageSent', {
                ticketId: payload?.ticket?.id ?? payload?.message?.ticket_id ?? null,
            });

            if (payload?.message?.is_from_admin) {
                Livewire.dispatch('guest-support-realtime', payload);
            }
        })
        .listen('.SupportTicketUpdated', (payload) => {
            reverbLog('info', 'guest channel: SupportTicketUpdated', {
                ticketId: payload?.ticket?.id ?? null,
            });

            Livewire.dispatch('guest-support-realtime', payload);
        });

    reverbLog('info', 'guest support realtime subscribed', { ticketId });
}

function openGuestSupportFromPage() {
    Livewire.dispatch('open-guest-support');
}

document.addEventListener('livewire:init', () => {
    Livewire.on('guest-support-opened', (payload) => {
        const ticketId = readLivewireEventPayload(payload, 'ticketId');

        if (ticketId) {
            bootGuestSupportRealtime(Number(ticketId));
        }
    });

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

if (hasEchoKey() && initialTicketId) {
    initEcho();
    bootGuestSupportRealtime(Number(initialTicketId));
} else if (hasEchoKey()) {
    initEcho();
}
