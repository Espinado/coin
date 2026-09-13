export function showSupportToast(message, variant = 'success') {
    let root = document.getElementById('coin-support-toast-root');

    if (! root) {
        root = document.createElement('div');
        root.id = 'coin-support-toast-root';
        root.setAttribute('aria-live', 'polite');
        root.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;max-width:min(380px,calc(100vw - 32px));';
        document.body.appendChild(root);
    }

    const toast = document.createElement('div');
    toast.setAttribute('role', 'status');
    toast.textContent = message;

    const isAdmin = document.body.classList.contains('admin-shell')
        || document.querySelector('.admin-shell') !== null;

    const successStyles = isAdmin
        ? 'background:linear-gradient(140deg,#ffb454,#e8872e);color:#1a1208;border:1px solid rgba(255,180,84,0.55);box-shadow:0 12px 32px rgba(255,180,84,0.25);'
        : 'background:linear-gradient(140deg,oklch(0.86 0.12 192),oklch(0.66 0.13 205));color:#04121f;border:1px solid oklch(0.86 0.11 195 / 0.5);box-shadow:0 12px 32px oklch(0.6 0.13 200 / 0.25);';

    const incomingStyles = isAdmin
        ? 'background:linear-gradient(140deg,#ffb454,#ff6b2c);color:#1a1208;border:2px solid rgba(255,220,140,0.85);box-shadow:0 0 28px rgba(255,180,84,0.6),0 14px 36px rgba(255,100,40,0.35);animation:coinToastIn 0.28s ease,coinToastPulse 1.1s ease-in-out 4;'
        : 'background:linear-gradient(140deg,oklch(0.88 0.2 35),oklch(0.72 0.22 25));color:#1a0a04;border:2px solid oklch(0.92 0.15 35 / 0.85);box-shadow:0 0 28px oklch(0.82 0.2 35 / 0.6),0 14px 36px oklch(0.72 0.22 25 / 0.35);animation:coinToastIn 0.28s ease,coinToastPulse 1.1s ease-in-out 4;';

    const errorStyles = 'background:rgba(180,40,40,0.92);color:#fff;border:1px solid rgba(255,120,120,0.45);box-shadow:0 12px 32px rgba(0,0,0,0.25);';

    let styles = successStyles;

    if (variant === 'error') {
        styles = errorStyles;
    } else if (variant === 'incoming') {
        styles = incomingStyles;
        toast.style.fontWeight = '700';
        toast.style.fontSize = '14px';
    }

    toast.style.cssText = `pointer-events:auto;padding:14px 18px;border-radius:12px;font-family:'Sora',sans-serif;font-size:13px;font-weight:600;letter-spacing:0.01em;${styles}`;

    root.appendChild(toast);

    const duration = variant === 'incoming' ? 5200 : 3200;

    window.setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-6px)';
        toast.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
        window.setTimeout(() => toast.remove(), 260);
    }, duration);
}

if (typeof document !== 'undefined' && ! document.getElementById('coin-support-toast-styles')) {
    const style = document.createElement('style');
    style.id = 'coin-support-toast-styles';
    style.textContent = '@keyframes coinToastIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}@keyframes coinToastPulse{0%,100%{transform:scale(1)}50%{transform:scale(1.03)}}';
    document.head.appendChild(style);
}
