<div id="admin-vox-call-modal" hidden class="admin-vox-call-modal">
    <div style="width:min(100%,420px);border-radius:18px;border:1px solid rgba(255,255,255,0.12);background:linear-gradient(170deg,#101826,#070a10);box-shadow:0 28px 80px rgba(0,0,0,0.55);padding:28px 26px 24px;text-align:center;">
        <div data-vox-modal-spinner style="width:46px;height:46px;margin:0 auto 18px;border-radius:50%;border:3px solid rgba(255,255,255,0.08);border-top-color:#6ea8ff;animation:adminVoxSpin 0.9s linear infinite;"></div>
        <div data-vox-modal-title style="font-size:20px;font-weight:600;color:#f0f4fa;"></div>
        <div data-vox-modal-subtitle style="margin-top:10px;font-size:14px;line-height:1.55;color:rgba(232,237,245,0.78);"></div>
        <div data-vox-modal-timer style="display:none;margin-top:16px;font-family:'JetBrains Mono',monospace;font-size:28px;letter-spacing:0.06em;color:#f0f4fa;">00:00</div>
        <div data-vox-modal-duration style="display:none;margin-top:12px;font-size:14px;color:rgba(232,237,245,0.72);"></div>
        <div data-vox-modal-audio hidden aria-hidden="true" style="position:absolute;width:0;height:0;overflow:hidden;"></div>
        <div style="margin-top:24px;display:flex;flex-direction:column;gap:10px;">
            <button type="button" data-vox-modal-hangup class="admin-btn" style="width:100%;padding:13px 16px;border-radius:11px;border:1px solid rgba(255,95,95,0.55);background:linear-gradient(140deg,#ff5f5f,#d93636);color:#fff;font-size:14px;font-weight:600;">
                {{ __('coin.voximplant.hangup_call') }}
            </button>
            <button type="button" data-vox-modal-close class="admin-btn admin-btn-primary" hidden style="width:100%;padding:12px 16px;border-radius:11px;font-size:14px;font-weight:600;">
                {{ __('coin.close') }}
            </button>
        </div>
    </div>
</div>
<style>
    .admin-vox-call-modal {
        position: fixed;
        inset: 0;
        z-index: 10050;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(4, 8, 16, 0.82);
        backdrop-filter: blur(4px);
    }

    .admin-vox-call-modal[hidden] {
        display: none !important;
    }

    .admin-vox-call-modal:not([hidden]) {
        display: flex;
    }

    @keyframes adminVoxSpin {
        to { transform: rotate(360deg); }
    }
</style>
