@if ($errors->any())
    <div role="alert" style="margin-top:16px;padding:12px 14px;border-radius:10px;border:1px solid rgba(255,143,143,0.45);background:rgba(255,143,143,0.08);font-size:13px;line-height:1.5;color:#ffd8d8;">
        @foreach ($errors->all() as $error)
            <div @if(!$loop->first) style="margin-top:6px;" @endif>{{ $error }}</div>
        @endforeach
    </div>
@endif
