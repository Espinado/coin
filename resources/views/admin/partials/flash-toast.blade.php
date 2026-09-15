@if (session('status'))
@php
  $toastType = session('status_type', 'success');
@endphp
<div
  id="admin-flash-toast"
  role="status"
  aria-live="polite"
  style="position: fixed; top: 20px; right: 20px; z-index: 10000; width: min(100%, 380px); padding: 14px 16px; border-radius: 12px; box-shadow: 0 16px 40px rgba(0,0,0,0.35); font-size: 14px; line-height: 1.45; display: flex; align-items: flex-start; gap: 10px; animation: adminToastIn 0.25s ease;
    @if($toastType === 'error')
      border: 1px solid rgba(255,143,143,0.45); background: rgba(48,18,18,0.96); color: #ffd8d8;
    @else
      border: 1px solid rgba(120,230,180,0.35); background: rgba(12,34,28,0.96); color: #dffef0;
    @endif
  "
>
  <span style="flex: none; width: 22px; height: 22px; border-radius: 50%; display: grid; place-items: center; font-size: 13px; font-weight: 700;
    @if($toastType === 'error') background: rgba(255,143,143,0.18); color: #ff8f8f; @else background: rgba(120,230,180,0.16); color: #78e6b4; @endif
  ">{{ $toastType === 'error' ? '!' : '✓' }}</span>
  <span style="flex: 1;">{{ session('status') }}</span>
  <button type="button" onclick="this.closest('#admin-flash-toast')?.remove()" style="flex: none; border: 0; background: transparent; color: inherit; opacity: 0.75; cursor: pointer; font-size: 18px; line-height: 1; padding: 0;">×</button>
</div>
<style>
  @keyframes adminToastIn {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
  }
</style>
<script>
  setTimeout(() => document.getElementById('admin-flash-toast')?.remove(), 6000);
</script>
@endif
