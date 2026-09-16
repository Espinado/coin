@if (session('status'))
@php
  $toastType = session('status_type', 'success');
@endphp
<div
  id="admin-flash-toast"
  role="status"
  aria-live="polite"
  class="admin-flash-toast {{ $toastType === 'error' ? 'admin-flash-toast--error' : 'admin-flash-toast--success' }}"
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
