<nav style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;">
    <a href="{{ route('admin.dashboard') }}" class="admin-btn">Dashboard</a>
    <a href="{{ route('admin.support.index') }}" class="admin-btn">
        Support tickets
        @if(($openCount ?? 0) > 0)
            <span style="margin-left:6px;padding:2px 7px;border-radius:999px;background:rgba(255,180,84,0.18);color:#ffb454;font-family:'JetBrains Mono',monospace;font-size:10px;">{{ $openCount }}</span>
        @endif
    </a>
</nav>
