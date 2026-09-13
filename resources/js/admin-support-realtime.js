import './bootstrap';
import { initEcho } from './echo';

const badgeStyle = 'margin-left:6px;padding:3px 8px;border-radius:999px;background:linear-gradient(140deg,#ffb454,#e8872e);color:#1a1208;font-family:\'JetBrains Mono\',monospace;font-size:10px;font-weight:700;box-shadow:0 0 14px rgba(255,180,84,0.45);';
const rowBadgeStyle = 'flex-shrink:0;font-family:\'JetBrains Mono\',monospace;font-size:11px;font-weight:700;min-width:22px;text-align:center;padding:4px 9px;border-radius:999px;background:linear-gradient(140deg,#ffb454,#e8872e);color:#1a1208;box-shadow:0 0 14px rgba(255,180,84,0.45);';

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function appendChatMessage(message) {
    const thread = document.getElementById('support-thread');

    if (! thread || ! message || document.querySelector(`[data-message-id="${message.id}"]`)) {
        return;
    }

    const wrapper = document.createElement('div');
    wrapper.dataset.messageId = String(message.id);
    wrapper.style.cssText = `padding:16px;border-radius:12px;border:1px solid rgba(255,255,255,0.08);background:${message.is_from_admin ? 'rgba(255,180,84,0.06)' : 'rgba(255,255,255,0.03)'};`;

    wrapper.innerHTML = `
        <div style="display:flex;justify-content:space-between;gap:12px;font-size:12px;color:rgba(232,237,245,0.62);">
            <span>${escapeHtml(message.author_label ?? (message.is_from_admin ? 'Support team' : 'User'))}</span>
            <span>${escapeHtml(message.created_at ?? '')}</span>
        </div>
        <div style="margin-top:10px;font-size:14px;line-height:1.6;white-space:pre-wrap;"></div>
    `;

    wrapper.querySelector('div:last-child').textContent = message.body;
    thread.appendChild(wrapper);
    wrapper.scrollIntoView({ behavior: 'smooth', block: 'end' });
}

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

function handleTicketPayload(payload, ticketId) {
    const messageTicketId = Number(payload.message?.ticket_id ?? payload.ticket?.id ?? 0);

    if (messageTicketId !== Number(ticketId)) {
        return;
    }

    if (payload.message) {
        appendChatMessage(payload.message);
    }

    updateTicketMeta(payload.ticket);
    handleAdminPayload(payload);
}

if (import.meta.env.VITE_REVERB_APP_KEY) {
    const echo = initEcho();
    const ticketId = window.supportChatConfig?.ticketId;

    echo.private('support.admin')
        .listen('.SupportTicketMessageSent', (payload) => {
            handleAdminPayload(payload);

            if (ticketId && Number(payload.message?.ticket_id) === Number(ticketId)) {
                appendChatMessage(payload.message);
                updateTicketMeta(payload.ticket);
            }
        })
        .listen('.SupportTicketUpdated', handleAdminPayload);

    if (ticketId) {
        echo.private(`support.ticket.${ticketId}`)
            .listen('.SupportTicketMessageSent', (payload) => handleTicketPayload(payload, ticketId))
            .listen('.SupportTicketUpdated', (payload) => handleTicketPayload(payload, ticketId));
    }
}
