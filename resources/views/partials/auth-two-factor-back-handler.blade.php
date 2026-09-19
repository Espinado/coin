@push('scripts')
<script>
(function () {
    var cancelUrl = @json($cancelUrl);

    if (window.history && window.history.pushState) {
        window.history.pushState({ coinAuth2fa: true }, '', window.location.href);

        window.addEventListener('popstate', function () {
            window.location.replace(cancelUrl);
        });
    }
})();
</script>
@endpush
