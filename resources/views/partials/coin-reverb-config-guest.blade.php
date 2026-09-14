@php
    use App\Services\SupportGuestSession;

    $reverbClient = config('broadcasting.connections.reverb.client', []);
    $guestTicket = SupportGuestSession::current();
    $coinReverbConfig = [
        'key' => config('broadcasting.connections.reverb.key'),
        'host' => $reverbClient['host'] ?? 'localhost',
        'port' => (int) ($reverbClient['port'] ?? 8080),
        'scheme' => $reverbClient['scheme'] ?? 'http',
        'guestTicketId' => $guestTicket?->id,
        'guestToken' => $guestTicket?->guest_token,
        'guestAuthEndpoint' => '/guest/broadcasting/auth',
        'debug' => (bool) config('broadcasting.connections.reverb.debug', false),
        'monitor' => (bool) config('broadcasting.connections.reverb.connection_monitor', true),
    ];
@endphp
<script>
    window.coinReverb = @json($coinReverbConfig);
</script>
