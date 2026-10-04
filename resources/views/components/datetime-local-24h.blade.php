@props([
    'name',
    'value' => null,
    'id' => null,
    'required' => false,
])

@php
    $id = $id ?? $name;
    $raw = trim((string) ($value ?? ''));
    $date = '';
    $hour = 12;
    $minute = 0;

    if ($raw !== '') {
        try {
            $parsed = \Illuminate\Support\Carbon::parse(str_replace('T', ' ', $raw));
            $date = $parsed->format('Y-m-d');
            $hour = (int) $parsed->format('G');
            $minute = (int) $parsed->format('i');
        } catch (\Throwable) {
            if (preg_match('/^(\d{4}-\d{2}-\d{2})[T ](\d{1,2}):(\d{2})/', $raw, $matches)) {
                $date = $matches[1];
                $hour = min(23, max(0, (int) $matches[2]));
                $minute = min(59, max(0, (int) $matches[3]));
            }
        }
    }

    $formatted = $date !== ''
        ? sprintf('%sT%02d:%02d', $date, $hour, $minute)
        : '';
    $controlStyle = 'box-sizing:border-box;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.12);background:#070a10;color:#e8edf5;';
    $monoStyle = $controlStyle.'font-family:\'JetBrains Mono\',monospace;';
@endphp

<div
    {{ $attributes->except('style')->class('datetime-local-24h') }}
    style="display:grid;grid-template-columns:1.4fr 0.7fr 0.7fr;gap:8px;margin-top:8px;"
    data-datetime-local-24h
    data-target="{{ $id }}"
>
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $formatted }}">
    <input
        type="date"
        id="{{ $id }}-date"
        data-datetime-local-24h-date
        value="{{ $date }}"
        @if($required) required @endif
        aria-label="{{ __('coin.admin.time_date') }}"
        style="{{ $controlStyle }}width:100%;"
    >
    <select
        id="{{ $id }}-hour"
        data-datetime-local-24h-hour
        aria-label="{{ __('coin.admin.time_hour') }}"
        style="{{ $monoStyle }}width:100%;"
    >
        @for ($h = 0; $h <= 23; $h++)
            <option value="{{ sprintf('%02d', $h) }}" @selected($h === $hour)>{{ sprintf('%02d', $h) }}</option>
        @endfor
    </select>
    <select
        id="{{ $id }}-minute"
        data-datetime-local-24h-minute
        aria-label="{{ __('coin.admin.time_minute') }}"
        style="{{ $monoStyle }}width:100%;"
    >
        @for ($m = 0; $m <= 59; $m++)
            <option value="{{ sprintf('%02d', $m) }}" @selected($m === $minute)>{{ sprintf('%02d', $m) }}</option>
        @endfor
    </select>
</div>
