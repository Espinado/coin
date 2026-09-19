<div id="coin-page-loading-overlay" class="coin-page-loading-overlay" hidden aria-live="polite" aria-busy="true" aria-label="{{ __('coin.please_wait') }}">
    <div class="coin-page-loading-card">
        <svg class="coin-page-loading-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3.5" opacity="0.25"></circle>
            <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" opacity="0.9"></path>
        </svg>
        <span class="coin-page-loading-label">{{ __('coin.please_wait') }}</span>
    </div>
</div>
<style>
    .coin-page-loading-overlay {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(4, 16, 28, 0.92);
    }
    .coin-page-loading-overlay[hidden] { display: none !important; }
    html.coin-page-navigating .coin-page-loading-overlay { display: flex !important; }
    html.coin-page-navigating .coin-page-loading-overlay[hidden] { display: flex !important; }
    .coin-page-loading-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        padding: 28px 34px;
        border-radius: 18px;
        border: 1px solid rgba(150, 235, 250, 0.22);
        background: rgba(8, 27, 44, 0.96);
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.45), 0 0 40px oklch(0.72 0.11 210 / 0.12);
        color: #eafcff;
        font-family: 'Sora', 'Helvetica Neue', Helvetica, sans-serif;
    }
    .admin-shell .coin-page-loading-card {
        border-color: rgba(255, 180, 84, 0.28);
        background: rgba(12, 15, 20, 0.96);
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.5), 0 0 32px rgba(255, 180, 84, 0.1);
        color: #e8edf5;
    }
    .coin-page-loading-spinner {
        width: 46px;
        height: 46px;
        color: oklch(0.86 0.11 195);
        animation: coinPageSpin 0.85s linear infinite;
    }
    .admin-shell .coin-page-loading-spinner { color: #ffb454; }
    .coin-page-loading-label {
        font-family: 'JetBrains Mono', monospace;
        font-size: 13px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(234, 252, 255, 0.92);
    }
    .admin-shell .coin-page-loading-label { color: rgba(232, 237, 245, 0.9); }
    @keyframes coinPageSpin { to { transform: rotate(360deg); } }
</style>
<script>
(function () {
    var overlay = document.getElementById('coin-page-loading-overlay');

    function hide() {
        document.documentElement.classList.remove('coin-page-navigating');
        document.documentElement.classList.remove('coin-nav-open');

        if (overlay) {
            overlay.hidden = true;
        }

        try {
            sessionStorage.removeItem('coinPageNavigating');
        } catch (_) {}
    }

    try {
        if (sessionStorage.getItem('coinPageNavigating') === '1' && overlay) {
            overlay.hidden = false;
            document.documentElement.classList.add('coin-page-navigating');
        }
    } catch (_) {}

    if (document.readyState === 'complete') {
        hide();
    } else {
        window.addEventListener('load', hide, { once: true });
    }

    document.addEventListener('DOMContentLoaded', hide, { once: true });
    window.setTimeout(hide, 5000);
})();
</script>
