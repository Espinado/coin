@php
    $broadcastDriver = config('broadcasting.default');
    $realtimeEnabled = in_array($broadcastDriver, ['reverb', 'pusher'], true);

    if ($broadcastDriver === 'pusher') {
        $pusher = config('broadcasting.connections.pusher', []);
        $coinReverbConfig = [
            'broadcaster' => 'pusher',
            'key' => $realtimeEnabled ? ($pusher['key'] ?? null) : null,
            'cluster' => $pusher['options']['cluster'] ?? 'mt1',
            'host' => null,
            'port' => 443,
            'scheme' => 'https',
            'supportUserId' => auth()->id(),
            'debug' => false,
            'monitor' => true,
        ];
    } else {
        $reverbClient = config('broadcasting.connections.reverb.client', []);
        $coinReverbConfig = [
            'broadcaster' => 'reverb',
            // Blank key when Reverb is off so Echo does not connect to a stale host.
            'key' => $broadcastDriver === 'reverb' ? config('broadcasting.connections.reverb.key') : null,
            'cluster' => null,
            'host' => $reverbClient['host'] ?? 'localhost',
            'port' => (int) ($reverbClient['port'] ?? 8080),
            'scheme' => $reverbClient['scheme'] ?? 'http',
            'supportUserId' => auth()->id(),
            'debug' => (bool) config('broadcasting.connections.reverb.debug', false),
            'monitor' => (bool) config('broadcasting.connections.reverb.connection_monitor', true),
        ];
    }
@endphp
<script>
    window.coinReverb = @json($coinReverbConfig);
</script>
