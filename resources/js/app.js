import './bootstrap';
import { hasEchoKey, initEcho } from './echo';
import { reverbLog } from './reverb-debug';
import { showSupportToast } from './support-toast';
import { appendSupportMessage, scrollSupportThreadToBottom } from './support-chat';

window.showSupportToast = showSupportToast;
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

    if (! total || total <= 0) {
        badge?.remove();

        return;
    }

    if (! badge) {
        badge = document.createElement('span');
        badge.className = 'coin-support-badge';
        badge.dataset.userSupportNavBadge = '';
        badge.style.cssText = userSupportBadgeStyle;
        nav.appendChild(badge);
    }

    badge.textContent = String(total);
}

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

// Livewire pages ship with wire:id and start Alpine themselves.
if (! document.querySelector('[wire\\:id]')) {
    Alpine.start();
}
