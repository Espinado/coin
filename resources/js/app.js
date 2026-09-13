import './bootstrap';
import { hasEchoKey, initEcho } from './echo';
import { reverbLog } from './reverb-debug';
import { showIncomingMessageToast, showSupportToast } from './support-toast';
import { appendSupportMessage, scrollSupportThreadToBottom } from './support-chat';

window.showSupportToast = showSupportToast;
window.showIncomingMessageToast = showIncomingMessageToast;
window.appendSupportMessage = appendSupportMessage;
window.scrollSupportThreadToBottom = scrollSupportThreadToBottom;

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const userSupportBadgeStyle = 'position:relative;font-family:\'JetBrains Mono\',monospace;font-size:10px;font-weight:700;min-width:20px;text-align:center;padding:3px 7px;border-radius:999px;background:linear-gradient(140deg, oklch(0.88 0.2 35), oklch(0.72 0.22 25));color:#1a0a04;box-shadow:0 0 16px oklch(0.82 0.2 35 / 0.55);';

function updateUserSupportNavBadge(total) {
    const nav = document.querySelector('.coin-nav-support');

    if (! nav) {
        return;
    }

    let badge = nav.querySelector('[data-user-support-nav-badge]');
    const unread = Number(total) > 0;

    nav.classList.toggle('coin-nav-support--unread', unread);

    if (! unread) {
        badge?.remove();
        nav.querySelector('[data-user-support-nav-bg]')?.remove();

        return;
    }

    if (! nav.querySelector('[data-user-support-nav-bg]') && ! nav.classList.contains('coin-nav-support--active')) {
        const bg = document.createElement('span');
        bg.dataset.userSupportNavBg = '';
        bg.style.cssText = 'position:absolute;inset:0;border-radius:10px;background:oklch(0.72 0.16 35 / 0.12);border:1px solid oklch(0.82 0.18 35 / 0.35);pointer-events:none;';
        nav.prepend(bg);
    }

    if (! badge) {
        badge = document.createElement('span');
        badge.className = 'coin-support-badge';
        badge.dataset.userSupportNavBadge = '';
        badge.style.cssText = userSupportBadgeStyle;
        nav.appendChild(badge);
    }

    badge.textContent = String(total);
    nav.dataset.unreadSupport = String(total);
}

function syncUserSupportNavBadgeFromDom() {
    const nav = document.querySelector('.coin-nav-support');

    if (! nav) {
        return;
    }

    if (! nav.dataset.unreadSupport) {
        return;
    }

    updateUserSupportNavBadge(Number(nav.dataset.unreadSupport));
}

window.updateUserSupportNavBadge = updateUserSupportNavBadge;

function readLivewireEventPayload(payload, key = null) {
    const item = Array.isArray(payload) ? payload[0] : payload;

    if (key === null) {
        return item;
    }

    return item?.[key];
}

window.readLivewireEventPayload = readLivewireEventPayload;

function bootUserSupportRealtime() {
    const echo = initEcho();
    const userId = window.coinReverb?.supportUserId;

    if (! echo || ! userId) {
        return;
    }

    const handlePayload = (payload) => {
        updateUserSupportNavBadge(Number(payload.total_unread_for_user ?? 0));
    };

    echo.private(`support.user.${userId}`)
        .listen('.SupportTicketMessageSent', (payload) => {
            reverbLog('info', 'user channel: SupportTicketMessageSent', {
                ticketId: payload?.ticket?.id ?? payload?.message?.ticket_id ?? null,
            });
            handlePayload(payload);

            if (payload?.message?.is_from_admin) {
                showIncomingMessageToast(payload.message, 'New message from support');
            }
        })
        .listen('.SupportTicketUpdated', (payload) => {
            reverbLog('info', 'user channel: SupportTicketUpdated', {
                ticketId: payload?.ticket?.id ?? null,
            });
            handlePayload(payload);
        });

    reverbLog('info', 'user support realtime subscribed', { userId });
}

if (hasEchoKey()) {
    initEcho();
    bootUserSupportRealtime();
} else {
    reverbLog('warn', 'user Echo skipped: no Reverb key in runtime config or Vite build');
}

syncUserSupportNavBadgeFromDom();

document.addEventListener('livewire:init', () => {
    Livewire.on('support-unread-updated', (payload) => {
        const count = readLivewireEventPayload(payload, 'count');

        if (count === undefined || count === null) {
            return;
        }

        updateUserSupportNavBadge(Number(count));
    });
});

// Livewire pages ship with wire:id and start Alpine themselves.
if (! document.querySelector('[wire\\:id]')) {
    Alpine.start();
}
