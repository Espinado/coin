@props([
    'name',
    'value' => '09:00',
    'id' => null,
])

@php
    $id = $id ?? $name;
    $raw = trim((string) $value);
    if (preg_match('/^(\d{1,2}):(\d{2})/', $raw, $matches)) {
        $hour = min(23, max(0, (int) $matches[1]));
        $minute = min(59, max(0, (int) $matches[2]));
    } else {
        $hour = 9;
        $minute = 0;
    }
    $formatted = sprintf('%02d:%02d', $hour, $minute);
    $selectStyle = 'flex:1;min-width:0;box-sizing:border-box;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;font-family:\'JetBrains Mono\',monospace;';
@endphp

<div
    {{ $attributes->class('time-24h')->merge(['style' => 'display:flex;gap:8px;align-items:center;margin-top:8px;']) }}
    data-time-24h
    data-target="{{ $id }}"
>
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $formatted }}">
    <select
        id="{{ $id }}-hour"
        data-time-24h-hour
        aria-label="{{ __('coin.admin.time_hour') }}"
        style="{{ $selectStyle }}"
    >
        @for ($h = 0; $h <= 23; $h++)
            <option value="{{ sprintf('%02d', $h) }}" @selected($h === $hour)>{{ sprintf('%02d', $h) }}</option>
        @endfor
    </select>
    <span style="color:rgba(232,237,245,0.55);font-family:'JetBrains Mono',monospace;">:</span>
    <select
        id="{{ $id }}-minute"
        data-time-24h-minute
        aria-label="{{ __('coin.admin.time_minute') }}"
        style="{{ $selectStyle }}"
    >
        @for ($m = 0; $m <= 59; $m++)
            <option value="{{ sprintf('%02d', $m) }}" @selected($m === $minute)>{{ sprintf('%02d', $m) }}</option>
        @endfor
    </select>
</div>
