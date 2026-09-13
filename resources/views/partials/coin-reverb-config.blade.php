@php
    $coinReverbConfig = [
        'key' => config('broadcasting.connections.reverb.key'),
        'host' => env('VITE_REVERB_HOST', env('REVERB_HOST', 'localhost')),
        'port' => (int) env('VITE_REVERB_PORT', env('REVERB_PORT', 8080)),
        'scheme' => env('VITE_REVERB_SCHEME', env('REVERB_SCHEME', 'http')),
    ];
@endphp
<script>
    window.coinReverb = @json($coinReverbConfig);
</script>
