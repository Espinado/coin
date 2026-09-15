import './bootstrap';
import { hasEchoKey, initEcho } from './echo';
import { reverbLog } from './reverb-debug';
import { showIncomingMessageToast } from './support-toast';
import { appendSupportMessage } from './support-chat';

const badgeStyle = 'margin-left:6px;padding:3px 8px;border-radius:999px;background:linear-gradient(140deg,#ffb454,#e8872e);color:#1a1208;font-family:\'JetBrains Mono\',monospace;font-size:10px;font-weight:700;box-shadow:0 0 14px rgba(255,180,84,0.45);';
const withdrawalsBadgeStyle = 'margin-left:6px;padding:2px 7px;border-radius:999px;background:rgba(255,143,143,0.18);color:#ff8f8f;font-family:\'JetBrains Mono\',monospace;font-size:10px;';
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

function readInitialAdminNavUnread() {
    const badge = document.querySelector('[data-admin-support-nav-badge]');

    return badge ? Number(badge.textContent) : 0;
}

function isAdminSupportTicketPage() {
    return /\/support\/\d+/.test(window.location.pathname);
}

function isAdminSupportSection() {
    return window.location.pathname.includes('/support');
}

let adminNavUnreadCount = readInitialAdminNavUnread();

function renderAdminNavBadge(total) {
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

function readInitialAdminWithdrawalsNavCount() {
    const badge = document.querySelector('[data-admin-withdrawals-nav-badge]');

    return badge ? Number(badge.textContent) : 0;
}

function renderAdminWithdrawalsNavBadge(total) {
    const link = document.querySelector('[data-admin-withdrawals-nav]');

    if (! link) {
        return;
    }

    let badge = link.querySelector('[data-admin-withdrawals-nav-badge]');

    if (! total || total <= 0) {
        badge?.remove();

        return;
    }

    if (! badge) {
        badge = document.createElement('span');
        badge.dataset.adminWithdrawalsNavBadge = '';
        badge.style.cssText = withdrawalsBadgeStyle;
        link.appendChild(badge);
    }

    badge.textContent = String(total);
}

function updateWithdrawalsNavBadge(total) {
    const count = Number(total);

    if (! Number.isFinite(count)) {
        return;
    }

    renderAdminWithdrawalsNavBadge(count);
}

function updateWithdrawalRow(payload) {
    const withdrawalId = payload.withdrawal?.id;

    if (! withdrawalId) {
        return;
    }

    const row = document.querySelector(`tr[data-withdrawal-id="${withdrawalId}"]`);

    if (! row) {
        return;
    }

    const statusCell = row.querySelector('[data-withdrawal-status-cell]');

    if (statusCell && payload.withdrawal?.status_label) {
        statusCell.textContent = payload.withdrawal.status_label;
    }
}

function updateNavBadge(total) {
    const count = Number(total);

    if (! Number.isFinite(count)) {
        return;
    }

    // On overview and other non-support pages, only grow the badge via realtime.
    // Clearing happens on full page load after admin opens Support (markReadByAdmin).
    if (! isAdminSupportSection()) {
        if (count <= adminNavUnreadCount) {
            return;
        }

        adminNavUnreadCount = count;
        renderAdminNavBadge(adminNavUnreadCount);

        return;
    }

    // On support list/ticket pages, sync the server count.
    if (isAdminSupportTicketPage() || count >= adminNavUnreadCount) {
        adminNavUnreadCount = count;
        renderAdminNavBadge(adminNavUnreadCount);
    }
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
    if (payload.total_unread_for_admin !== undefined && payload.total_unread_for_admin !== null) {
        updateNavBadge(Number(payload.total_unread_for_admin));
    }

    updateTicketRow(payload);
}

function notifyAdminAboutUserMessage(payload) {
    const message = payload?.message;

    if (! message || message.is_from_admin) {
        return;
    }

    showIncomingMessageToast(message, 'New user message');
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

    echo.private('admin.withdrawals')
        .listen('.WithdrawalUpdated', (payload) => {
            reverbLog('info', 'admin channel: WithdrawalUpdated', {
                withdrawalId: payload?.withdrawal?.id ?? null,
                pendingCount: payload?.pending_withdrawals_count ?? null,
            });

            if (payload.pending_withdrawals_count !== undefined && payload.pending_withdrawals_count !== null) {
                updateWithdrawalsNavBadge(Number(payload.pending_withdrawals_count));
            }

            updateWithdrawalRow(payload);
        });

    echo.private('support.admin')
        .listen('.SupportTicketMessageSent', (payload) => {
            reverbLog('info', 'admin channel: SupportTicketMessageSent', {
                ticketId: payload?.ticket?.id ?? payload?.message?.ticket_id ?? null,
            });
            handleAdminPayload(payload);
            notifyAdminAboutUserMessage(payload);
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

function bootAdminSupportNavBadge() {
    adminNavUnreadCount = readInitialAdminNavUnread();
    renderAdminNavBadge(adminNavUnreadCount);
    renderAdminWithdrawalsNavBadge(readInitialAdminWithdrawalsNavCount());
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        bootAdminSupportNavBadge();
        bootAdminSupportRealtime();
    });
} else {
    bootAdminSupportNavBadge();
    bootAdminSupportRealtime();
}
