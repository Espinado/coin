import './bootstrap';
import { showSupportToast } from './support-toast';

const config = window.supportChatConfig;

if (config?.ticketId) {
    const replyForm = document.getElementById('support-reply-form');
    const replyBody = document.getElementById('support-reply-body');
    const replyStatus = document.getElementById('support-reply-status');
    const replyError = document.getElementById('support-reply-error');
    const thread = document.getElementById('support-thread');
    const statusLabel = document.getElementById('ticket-status-label');

    if (replyForm && replyBody) {
        replyForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (replyError) {
                replyError.textContent = '';
            }

            const body = replyBody.value.trim();
            if (body.length < 2) {
                if (replyError) {
                    replyError.textContent = 'Message must be at least 2 characters.';
                }

                return;
            }

            const submitButton = replyForm.querySelector('[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
            }

            try {
                const response = await window.axios.post(config.replyUrl, {
                    body,
                    status: replyStatus?.value ?? undefined,
                }, {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                appendMessage(response.data.message);
                updateTicketMeta(response.data.ticket);
                replyBody.value = '';
                showSupportToast('Message sent');
            } catch (error) {
                const errorMessage = error.response?.data?.message
                    ?? 'Could not send reply. Please try again.';

                if (replyError) {
                    replyError.textContent = errorMessage;
                }

                showSupportToast(errorMessage, 'error');
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                }
            }
        });
    }

    function appendMessage(message) {
        if (! thread || document.querySelector(`[data-message-id="${message.id}"]`)) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.dataset.messageId = String(message.id);
        wrapper.style.cssText = `padding:16px;border-radius:12px;border:1px solid rgba(255,255,255,0.08);background:${message.is_from_admin ? 'rgba(255,180,84,0.06)' : 'rgba(255,255,255,0.03)'};`;

        wrapper.innerHTML = `
            <div style="display:flex;justify-content:space-between;gap:12px;font-size:12px;color:rgba(232,237,245,0.62);">
                <span>${escapeHtml(message.author_label)}</span>
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

        if (statusLabel) {
            statusLabel.textContent = ticket.status_label ?? ticket.status;
        }

        if (replyStatus && ticket.status) {
            replyStatus.value = ticket.status;
        }
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#39;');
    }
}
