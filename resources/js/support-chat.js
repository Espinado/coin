export function scrollSupportThreadToBottom(behavior = 'auto') {
    const thread = document.getElementById('support-thread');

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

export function appendSupportMessage(message, options = {}) {
    const thread = document.getElementById('support-thread');

    if (! thread || ! message?.id) {
        return false;
    }

    if (document.querySelector(`[data-message-id="${message.id}"]`)) {
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
    }

    wrapper.innerHTML = `
        <div style="display:flex;justify-content:space-between;gap:12px;font-size:12px;color:rgba(214,238,248,0.66);">
            <span>${escapeSupportHtml(author)}</span>
            <span>${escapeSupportHtml(message.created_at ?? '')}</span>
        </div>
        <div style="margin-top:10px;font-size:14px;line-height:1.6;white-space:pre-wrap;"></div>
    `;

    wrapper.querySelector('div:last-child').textContent = message.body ?? '';
    thread.appendChild(wrapper);
    scrollSupportThreadToBottom('smooth');

    return true;
}
