@php
    $reverbClient = config('broadcasting.connections.reverb.client', []);
    $coinReverbConfig = [
        'key' => config('broadcasting.connections.reverb.key'),
        'host' => $reverbClient['host'] ?? 'localhost',
        'port' => (int) ($reverbClient['port'] ?? 8080),
        'scheme' => $reverbClient['scheme'] ?? 'http',
        'supportUserId' => auth()->id(),
        'debug' => (bool) config('broadcasting.connections.reverb.debug', false),
    ];
@endphp
<script>
    window.coinReverb = @json($coinReverbConfig);
</script>
