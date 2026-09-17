(function () {
    function boot() {
        var root = document.getElementById('landing-faq');

        if (!root) {
            return;
        }

        root.addEventListener('click', function (event) {
            var toggle = event.target.closest('[data-faq-toggle]');

            if (!toggle) {
                return;
            }

            var item = toggle.closest('[data-faq-item]');

            if (!item) {
                return;
            }

            var wasOpen = item.classList.contains('is-open');

            root.querySelectorAll('[data-faq-item]').forEach(function (node) {
                node.classList.remove('is-open');
            });

            if (!wasOpen) {
                item.classList.add('is-open');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
