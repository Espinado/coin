import './bootstrap';
import { hasEchoKey, initEcho } from './echo';
import { reverbLog } from './reverb-debug';
import { showSupportToast } from './support-toast';
import { appendSupportMessage } from './support-chat';

const badgeStyle = 'margin-left:6px;padding:3px 8px;border-radius:999px;background:linear-gradient(140deg,#ffb454,#e8872e);color:#1a1208;font-family:\'JetBrains Mono\',monospace;font-size:10px;font-weight:700;box-shadow:0 0 14px rgba(255,180,84,0.45);';
const rowBadgeStyle = 'flex-shrink:0;font-family:\'JetBrains Mono\',monospace;font-size:11px;font-weight:700;min-width:22px;text-align:center;padding:4px 9px;border-radius:999px;background:linear-gradient(140deg,#ffb454,#e8872e);color:#1a1208;box-shadow:0 0 14px rgba(255,180,84,0.45);';

function updateTicketMeta(ticket) {
    if (! ticket) {
        return;
    }

    const statusLabel = document.getElementById('ticket-status-label');
    const replyStatus = document.getElementById('support-reply-status');

    if (statusLabel) {
        statusLabel.textContent = ticket.status_label ?? ticket.status;
    }

    if (replyStatus && ticket.status) {
        replyStatus.value = ticket.status;
    }
}

function updateNavBadge(total) {
    const link = document.querySelector('[data-admin-support-nav]');

    if (! link) {
        return;
    }

    let badge = link.querySelector('[data-admin-support-nav-badge]');

    if (! total || total <= 0) {
        badge?.remove();

        return;
    }

    if (! badge) {
        badge = document.createElement('span');
        badge.className = 'admin-support-badge';
        badge.dataset.adminSupportNavBadge = '';
        badge.style.cssText = badgeStyle;
        link.appendChild(badge);
    }

    badge.textContent = String(total);
}

function updateTicketRow(payload) {
    const ticketId = payload.ticket?.id ?? payload.message?.ticket_id;

    if (! ticketId) {
        return;
    }

    const row = document.querySelector(`tr[data-support-ticket-id="${ticketId}"]`);

    if (! row) {
        return;
    }

    const unread = Number(payload.unread_for_admin ?? 0);
    const userCell = row.querySelector('[data-support-user-cell]');
    const updatedCell = row.querySelector('[data-support-updated-cell]');

    row.style.background = unread > 0 ? 'rgba(255,180,84,0.05)' : '';

    if (userCell) {
        const name = userCell.querySelector('[data-support-user-name]');

        if (name) {
            name.style.fontWeight = unread > 0 ? '600' : '400';
        }

        let badge = userCell.querySelector('[data-support-row-badge]');

        if (unread > 0) {
            if (! badge) {
                badge = document.createElement('span');
                badge.className = 'admin-support-badge';
                badge.dataset.supportRowBadge = '';
                badge.style.cssText = rowBadgeStyle;
                userCell.querySelector('[data-support-user-wrap]')?.appendChild(badge);
            }

            badge.textContent = String(unread);
        } else {
            badge?.remove();
        }
    }

    if (updatedCell && payload.ticket?.updated_at) {
        updatedCell.textContent = payload.ticket.updated_at;
    }
}

function handleAdminPayload(payload) {
    updateNavBadge(Number(payload.total_unread_for_admin ?? 0));
    updateTicketRow(payload);
}

function handleIncomingMessage(payload, ticketId) {
    const message = payload.message;

    if (! message) {
        return;
    }

    const messageTicketId = Number(message.ticket_id ?? payload.ticket?.id ?? 0);

    if (ticketId && messageTicketId !== Number(ticketId)) {
        return;
    }

    appendSupportMessage(message);
    updateTicketMeta(payload.ticket);
    handleAdminPayload(payload);

    if (! message.is_from_admin) {
        showSupportToast('New user message', 'incoming');
    }
}

function bootAdminSupportRealtime() {
    if (! hasEchoKey()) {
        reverbLog('warn', 'admin Echo skipped: no Reverb key in runtime config or Vite build');

        return;
    }

    const echo = initEcho();

    if (! echo) {
        return;
    }

    const ticketId = window.supportChatConfig?.ticketId;

    echo.private('support.admin')
        .listen('.SupportTicketMessageSent', (payload) => {
            reverbLog('info', 'admin channel: SupportTicketMessageSent', {
                ticketId: payload?.ticket?.id ?? payload?.message?.ticket_id ?? null,
            });
            handleAdminPayload(payload);
            handleIncomingMessage(payload, ticketId);
        })
        .listen('.SupportTicketUpdated', (payload) => {
            reverbLog('info', 'admin channel: SupportTicketUpdated', {
                ticketId: payload?.ticket?.id ?? null,
            });
            handleAdminPayload(payload);
        });

    reverbLog('info', 'admin support.admin subscribed');

    if (ticketId) {
        echo.private(`support.ticket.${ticketId}`)
            .listen('.SupportTicketMessageSent', (payload) => {
                reverbLog('info', 'admin ticket channel: SupportTicketMessageSent', { ticketId });
                handleIncomingMessage(payload, ticketId);
            })
            .listen('.SupportTicketUpdated', (payload) => {
                reverbLog('info', 'admin ticket channel: SupportTicketUpdated', { ticketId });
                updateTicketMeta(payload.ticket);
                handleAdminPayload(payload);
            });

        reverbLog('info', 'admin support.ticket subscribed', { ticketId });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootAdminSupportRealtime);
} else {
    bootAdminSupportRealtime();
}
