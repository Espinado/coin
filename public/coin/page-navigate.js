(function () {
    var STORAGE_KEY = 'coinPageNavigating';
    var overlay = null;
    var pending = 0;
    var waitingForFullLoad = false;
    var hideScheduled = false;

    function getOverlay() {
        if (!overlay) {
            overlay = document.getElementById('coin-page-loading-overlay');
        }

        return overlay;
    }

    function showOverlay() {
        var element = getOverlay();
        if (!element) {
            return;
        }

        element.hidden = false;
        document.documentElement.classList.add('coin-page-navigating');
    }

    function hideOverlay() {
        var element = getOverlay();
        if (!element) {
            return;
        }

        element.hidden = true;
        document.documentElement.classList.remove('coin-page-navigating');
        pending = 0;
        waitingForFullLoad = false;

        try {
            sessionStorage.removeItem(STORAGE_KEY);
        } catch (_) {}
    }

    function scheduleHideAfterNavigation() {
        if (hideScheduled) {
            return;
        }

        hideScheduled = true;

        function finish() {
            waitingForFullLoad = false;
            hideOverlay();
        }

        if (document.readyState === 'complete') {
            requestAnimationFrame(finish);
        } else {
            window.addEventListener('load', finish, { once: true });
        }

        document.addEventListener('DOMContentLoaded', function () {
            requestAnimationFrame(finish);
        }, { once: true });

        window.setTimeout(finish, 5000);
    }

    function markFullPageNavigation() {
        waitingForFullLoad = true;
        pending++;
        hideScheduled = false;

        try {
            sessionStorage.setItem(STORAGE_KEY, '1');
        } catch (_) {}

        showOverlay();
    }

    function releasePending() {
        pending--;

        if (pending > 0 || waitingForFullLoad) {
            return;
        }

        hideOverlay();
    }

    function shouldHandleLink(anchor, event) {
        if (!anchor || !anchor.href || anchor.hasAttribute('data-no-page-spinner')) {
            return false;
        }

        if (anchor.target === '_blank' || anchor.hasAttribute('download')) {
            return false;
        }

        var href = anchor.getAttribute('href') || '';

        if (!href || href === '#' || href.indexOf('javascript:') === 0) {
            return false;
        }

        if (event && (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey)) {
            return false;
        }

        try {
            var url = new URL(anchor.href, window.location.href);

            if (url.origin !== window.location.origin) {
                return false;
            }

            if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) {
                return false;
            }
        } catch (_) {
            return false;
        }

        return true;
    }

    function shouldHandleForm(form) {
        if (!form || form.tagName !== 'FORM' || form.hasAttribute('data-no-page-spinner')) {
            return false;
        }

        if (form.getAttribute('wire:submit') || form.getAttribute('wire:submit.prevent')) {
            return false;
        }

        return true;
    }

    function boot() {
        var hadPendingNav = false;

        try {
            hadPendingNav = sessionStorage.getItem(STORAGE_KEY) === '1';
        } catch (_) {}

        if (hadPendingNav) {
            waitingForFullLoad = true;
            showOverlay();
            scheduleHideAfterNavigation();
        }

        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                hideOverlay();
            }
        });

        document.addEventListener('click', function (event) {
            var anchor = event.target.closest('a');

            if (shouldHandleLink(anchor, event)) {
                markFullPageNavigation();
            }
        }, true);

        document.addEventListener('submit', function (event) {
            var form = event.target;

            if (!shouldHandleForm(form)) {
                return;
            }

            window.setTimeout(function () {
                if (event.defaultPrevented) {
                    return;
                }

                markFullPageNavigation();
            }, 0);
        });

        document.addEventListener('livewire:navigate', function () {
            pending++;
            showOverlay();
        });

        document.addEventListener('livewire:navigated', function () {
            waitingForFullLoad = false;

            try {
                sessionStorage.removeItem(STORAGE_KEY);
            } catch (_) {}

            requestAnimationFrame(function () {
                requestAnimationFrame(releasePending);
            });
        });
    }

    window.coinHidePageOverlay = hideOverlay;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
