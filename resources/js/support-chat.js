export function scrollSupportThreadToBottom(threadId = 'support-thread', behavior = 'auto') {
    const thread = document.getElementById(threadId);

    if (! thread) {
        return;
    }

    if (behavior === 'smooth') {
        thread.scrollTo({ top: thread.scrollHeight, behavior: 'smooth' });
    } else {
        thread.scrollTop = thread.scrollHeight;
    }
}

export function escapeSupportHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function renderSupportAttachments(message, isAdminUi) {
    const attachments = Array.isArray(message.attachments) ? message.attachments : [];

    if (attachments.length === 0) {
        return '';
    }

    const config = window.supportChatConfig || {};
    const cards = attachments.map((attachment) => {
        const url = isAdminUi
            ? (attachment.admin_url || attachment.url || '')
            : (attachment.url || '');
        const name = escapeSupportHtml(attachment.original_name || 'JPG');
        const saved = Boolean(attachment.saved_to_kyc);
        let actionHtml = '';

        if (isAdminUi && config.canSaveToKyc && attachment.save_to_kyc_url) {
            if (saved) {
                actionHtml = `<div style="padding:8px 10px;font-size:11px;color:rgba(232,237,245,0.72);">${escapeSupportHtml(config.savedToKycLabel || 'Saved to KYC')}</div>`;
            } else {
                actionHtml = `<div style="padding:8px 10px;">
                    <button type="button" data-save-kyc-url="${escapeSupportHtml(attachment.save_to_kyc_url)}" data-attachment-id="${escapeSupportHtml(attachment.id)}"
                        style="width:100%;padding:6px 8px;border-radius:8px;border:1px solid rgba(255,180,84,0.45);background:rgba(255,180,84,0.12);color:#ffd8a8;font-size:11px;cursor:pointer;">
                        ${escapeSupportHtml(config.saveToKycLabel || 'Save to KYC')}
                    </button>
                </div>`;
            }
        }

        return `<div data-attachment-id="${escapeSupportHtml(attachment.id)}" style="border:1px solid rgba(255,255,255,0.10);border-radius:12px;overflow:hidden;background:rgba(255,255,255,0.02);">
            <a href="${escapeSupportHtml(url)}" target="_blank" rel="noopener" style="display:block;aspect-ratio:1;background:#05070c;">
                <img src="${escapeSupportHtml(url)}" alt="${name}" style="width:100%;height:100%;object-fit:cover;display:block;">
            </a>
            <div style="padding:8px 10px 0;font-size:11px;color:rgba(232,237,245,0.62);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${name}">${name}</div>
            ${actionHtml}
        </div>`;
    }).join('');

    return `<div style="margin-top:12px;display:grid;grid-template-columns:repeat(auto-fill,minmax(${isAdminUi ? '140px' : '96px'},1fr));gap:10px;">${cards}</div>`;
}

export function appendSupportMessage(message, options = {}) {
    const threadId = options.threadId ?? 'support-thread';
    const thread = document.getElementById(threadId);

    if (! thread || ! message?.id) {
        return false;
    }

    if (document.querySelector(`#${threadId} [data-message-id="${message.id}"]`)) {
        return false;
    }

    const isAdminUi = document.body.classList.contains('admin-shell')
        || document.querySelector('.admin-shell') !== null;

    const author = message.author_label
        ?? (message.is_from_admin ? 'Support team' : (options.userLabel ?? 'User'));

    const wrapper = document.createElement('div');
    wrapper.dataset.messageId = String(message.id);

    if (isAdminUi) {
        wrapper.style.cssText = `padding:16px;border-radius:12px;border:1px solid rgba(255,255,255,0.08);background:${message.is_from_admin ? 'rgba(255,180,84,0.06)' : 'rgba(255,255,255,0.03)'};`;
    } else {
        wrapper.style.cssText = `padding:16px 18px;border-radius:14px;border:1px solid rgba(150,235,250,0.12);background:${message.is_from_admin ? 'oklch(0.6 0.13 200 / 0.12)' : 'rgba(150,235,250,0.03)'};`;
        wrapper.className = `coin-support-message${message.is_from_admin ? ' coin-support-message--admin' : ''}`;
    }

    const body = message.body ?? '';
    const hasAttachments = Array.isArray(message.attachments) && message.attachments.length > 0;
    const hidePlaceholderBody = hasAttachments && body === '';

    wrapper.innerHTML = `
        <div style="display:flex;justify-content:space-between;gap:12px;font-size:12px;color:rgba(214,238,248,0.66);">
            <span>${escapeSupportHtml(author)}</span>
            <span>${escapeSupportHtml(message.created_at ?? '')}</span>
        </div>
        ${hidePlaceholderBody ? '' : `<div class="support-message-body" style="margin-top:10px;font-size:14px;line-height:1.6;white-space:pre-wrap;"></div>`}
        ${renderSupportAttachments(message, isAdminUi)}
    `;

    const bodyNode = wrapper.querySelector('.support-message-body');
    if (bodyNode) {
        bodyNode.textContent = body;
    }

    thread.appendChild(wrapper);
    scrollSupportThreadToBottom(threadId, 'smooth');

    return true;
}

export async function saveSupportAttachmentToKyc(button) {
    const url = button?.dataset?.saveKycUrl;
    const config = window.supportChatConfig || {};

    if (! url) {
        return;
    }

    button.disabled = true;

    try {
        const response = await window.axios.post(url, {}, {
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
        });

        const card = button.closest('[data-attachment-id]');
        if (card) {
            button.replaceWith(Object.assign(document.createElement('div'), {
                style: 'padding:0;font-size:11px;color:rgba(232,237,245,0.72);',
                textContent: config.savedToKycLabel || 'Saved to KYC',
            }));

            if (response.data?.user_url) {
                const link = document.createElement('a');
                link.href = response.data.user_url;
                link.textContent = config.savedToKycLabel || 'Saved to KYC';
                link.className = 'admin-btn';
                link.style.cssText = 'padding:6px 8px;font-size:11px;text-align:center;display:block;';
                card.querySelector('div:last-child')?.replaceChildren(link);
            }
        }
    } catch (error) {
        button.disabled = false;
        const message = error.response?.data?.message || 'Could not save to KYC.';
        window.showSupportToast?.(message, 'error');
    }
}

document.addEventListener('click', (event) => {
    const button = event.target.closest?.('[data-save-kyc-url]');
    if (! button) {
        return;
    }

    event.preventDefault();
    saveSupportAttachmentToKyc(button);
});
