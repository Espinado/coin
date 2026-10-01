@php
    use App\Services\SupportGuestSession;

    $broadcastDriver = config('broadcasting.default');
    $guestTicket = SupportGuestSession::current();

    if ($broadcastDriver === 'pusher') {
        $pusher = config('broadcasting.connections.pusher', []);
        $coinReverbConfig = [
            'broadcaster' => 'pusher',
            'key' => $pusher['key'] ?? null,
            'cluster' => $pusher['options']['cluster'] ?? 'mt1',
            'host' => null,
            'port' => 443,
            'scheme' => 'https',
            'guestTicketId' => $guestTicket?->id,
            'guestToken' => $guestTicket?->guest_token,
            'guestAuthEndpoint' => '/guest/broadcasting/auth',
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
            'guestTicketId' => $guestTicket?->id,
            'guestToken' => $guestTicket?->guest_token,
            'guestAuthEndpoint' => '/guest/broadcasting/auth',
            'debug' => (bool) config('broadcasting.connections.reverb.debug', false),
            'monitor' => (bool) config('broadcasting.connections.reverb.connection_monitor', true),
        ];
    }
@endphp
<script>
    window.coinReverb = @json($coinReverbConfig);
</script>
