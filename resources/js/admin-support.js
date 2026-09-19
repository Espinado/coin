import './bootstrap';
import { showSupportToast } from './support-toast';
import { appendSupportMessage, scrollSupportThreadToBottom } from './support-chat';

const config = window.supportChatConfig;

if (config?.ticketId) {
    const replyForm = document.getElementById('support-reply-form');
    const replyBody = document.getElementById('support-reply-body');
    const replyStatus = document.getElementById('support-reply-status');
    const replyError = document.getElementById('support-reply-error');
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

                appendSupportMessage(response.data.message);
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
                window.coinHidePageOverlay?.();

                if (submitButton) {
                    submitButton.disabled = false;
                }

                replyBody.focus();
            }
        });
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

    scrollSupportThreadToBottom('auto');
}
